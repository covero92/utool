<?php
// includes/GenericCnabParser.php

class GenericCnabParser
{
    private $layoutsDir;
    private $pdo;

    public function __construct($pdo = null)
    {
        $this->layoutsDir = __DIR__ . '/../config/layouts';
        $this->pdo = $pdo;
    }

    public function parseFile($filePath)
    {
        $lines = file($filePath, FILE_IGNORE_NEW_LINES);
        if (empty($lines))
            return ['error' => 'Arquivo vazio'];

        // 1. Identification
        $firstLine = $lines[0];
        $bankCode = substr($firstLine, 0, 3);
        // Robust detection: CNAB 240 Header (Pos 8 (index 7) = '0') vs CNAB 400 (Pos 1 (index 0) = '0')
        // And check length roughly
        $fileType = '400';



        if (strlen($firstLine) >= 238 && isset($firstLine[7]) && $firstLine[7] === '0') {
            $fileType = '240';
        }

        // 2. Load Layout
        $layoutFile = "{$this->layoutsDir}/{$bankCode}/{$fileType}_retorno.json";

        if (!file_exists($layoutFile)) {
            // Fallback or error
            return [
                'error' => "Layout não encontrado para Banco {$bankCode} - CNAB{$fileType}. (Esperado: {$layoutFile})",
                'bank_code' => $bankCode,
                'type' => $fileType
            ];
        }

        $blueprint = json_decode(file_get_contents($layoutFile), true);
        if (!$blueprint) {
            return ['error' => "Erro ao ler JSON de layout."];
        }

        // 3. Process Lines
        $result = [
            'metadata' => [
                'bank' => $blueprint['bank_name'],
                'code' => $blueprint['bank_code'],
                'layout' => $blueprint['layout_type']
            ],
            'lines' => []
        ];

        foreach ($lines as $index => $line) {
            $lineNum = $index + 1;
            // Limit preview
            if ($lineNum > 50 && $lineNum < count($lines) - 5)
                continue;

            $parsedLine = $this->parseLine($line, $blueprint, $lineNum);
            $result['lines'][] = $parsedLine;
        }

        return $result;
    }

    private function parseLine($line, $blueprint, $lineNum)
    {
        $detectedSegment = 'unknown';
        $fields = [];
        $rawContent = $line;

        // Find matching segment definition
        foreach ($blueprint['segments'] as $segKey => $segDef) {
            $match = true;
            foreach ($segDef['match'] as $criteria) {
                // Criteria pos is 1-based in JSON? Usually manuals are 1-based.
                // Let's assume JSON uses 1-based positions like 'start'/'end'.
                $pos = $criteria['pos'] - 1;
                $val = $criteria['value'];

                if (substr($line, $pos, strlen($val)) !== $val) {
                    $match = false;
                    break;
                }
            }

            if ($match) {
                $detectedSegment = $segDef['description'] ?? $segKey;
                $fields = $this->extractFields($line, $segDef['fields']);

                // Enrichment: Check for Occurrences if present
                $fields = $this->enrichWithResolutions($fields, $blueprint['bank_code'], $blueprint['layout_type']);

                break;
            }
        }

        if ($detectedSegment === 'unknown') {
            $fields[] = ['name' => 'Conteúdo', 'value' => $line, 'start' => 1, 'end' => strlen($line)];
        }

        return [
            'number' => $lineNum,
            'segment' => $detectedSegment,
            'content' => $line,
            'fields' => $fields
        ];
    }

    private function extractFields($line, $fieldDefs)
    {
        $extracted = [];
        foreach ($fieldDefs as $def) {
            $start = $def['start'] - 1; // 0-based
            $length = $def['end'] - $def['start'] + 1;
            $value = substr($line, $start, $length);

            // Basic Formatting
            if ($def['type'] === 'date' && strlen($value) === 8) {
                // DDMMAAAA -> DD/MM/AAAA
                $value = substr($value, 0, 2) . '/' . substr($value, 2, 2) . '/' . substr($value, 4, 4);
            } elseif ($def['type'] === 'money') {
                // Last 2 digits are cents. 
                $val = floatval($value) / 100;
                $value = number_format($val, 2, ',', '.');
            }

            $extracted[] = [
                'name' => $def['name'],
                'value' => mb_convert_encoding($value, 'UTF-8', 'ISO-8859-1'), // Handle typical legacy encoding
                'start' => $def['start'],
                'end' => $def['end'],
                'raw' => substr($line, $start, $length) // Keep raw for logic
            ];
        }
        return $extracted;
    }

    private function enrichWithResolutions($fields, $bankCode, $cnabType)
    {
        if (!$this->pdo)
            return $fields;

        // Try to find specific fields (hardcoded logic for enrichment lookups is tricky in generic parser)
        // Compromise: Look for fields named 'cod_movimento', 'motivo_ocorrencia' or similar defined in JSON.

        $movimento = null;
        $ocorrencia = null; // Field usually holds error codes like "A9", "B1"...

        foreach ($fields as $f) {
            if ($f['name'] === 'cod_movimento')
                $movimento = $f['raw'];
            if ($f['name'] === 'motivo_ocorrencia' || $f['name'] === 'cod_ocorrencia')
                $ocorrencia = trim($f['raw']);
        }

        // Logic: If Movimento indicates Rejection (e.g. '03' for Sicoob)
        // This '03' logic is bank specific... ideally should be in JSON too.
        // For prototype, let's assume if we found an 'ocorrencia' field, we check it.

        if ($ocorrencia) {
            // Check DB
            // Sicoob Ocorrencia can be lists "A9B3..." (2 chars each)
            // Splitting logic would be needed. For now, simple lookup.

            $stmt = $this->pdo->prepare("
                SELECT r.* 
                FROM uniplus_resolutions r
                JOIN bank_occurrences o ON o.id = r.occurrence_id
                WHERE o.bank_code = ? 
                AND o.error_code = ?
            ");
            $stmt->execute([$bankCode, $ocorrencia]);
            $resolution = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($resolution) {
                // Append resolution info to the 'motivo_ocorrencia' field
                foreach ($fields as &$f) {
                    if ($f['name'] === 'motivo_ocorrencia' || $f['name'] === 'cod_ocorrencia') {
                        $f['resolution'] = $resolution;
                    }
                }
            }
        }

        return $fields;
    }
}
