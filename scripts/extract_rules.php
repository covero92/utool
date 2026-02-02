<?php
// scripts/extract_rules.php

$xlsxFile = __DIR__ . '/../NFSE Nacional/ANEXO_I-SEFIN_ADN-DPS_NFSe-SNNFSe.xlsx';
$jsonFile = __DIR__ . '/../data/nfse_rules.json';

if (!file_exists($xlsxFile)) {
    die("Erro: Arquivo XLSX não encontrado em $xlsxFile\n");
}

$zip = new ZipArchive;
if ($zip->open($xlsxFile) === TRUE) {
    // 1. Find Sheet ID for "RN DPS_NFS-e"
    $sheetId = '';
    if ($zip->locateName('xl/workbook.xml') !== false) {
        $xml = simplexml_load_string($zip->getFromName('xl/workbook.xml'));
        foreach ($xml->sheets->sheet as $sheet) {
            if ((string)$sheet['name'] === 'RN DPS_NFS-e') {
                $sheetId = (string)$sheet->attributes('http://schemas.openxmlformats.org/officeDocument/2006/relationships')['id'];
                break;
            }
        }
    }

    // 2. Find Target File
    $targetFile = '';
    if ($sheetId && $zip->locateName('xl/_rels/workbook.xml.rels') !== false) {
        $xml = simplexml_load_string($zip->getFromName('xl/_rels/workbook.xml.rels'));
        foreach ($xml->Relationship as $rel) {
            if ((string)$rel['Id'] === $sheetId) {
                $targetFile = 'xl/' . (string)$rel['Target'];
                break;
            }
        }
    }

    if ($targetFile) {
        // Load Shared Strings
        $sharedStrings = [];
        if ($zip->locateName('xl/sharedStrings.xml') !== false) {
            $xmlStrings = simplexml_load_string($zip->getFromName('xl/sharedStrings.xml'));
            foreach ($xmlStrings->si as $si) {
                $sharedStrings[] = (string)$si->t;
            }
        }

        // Read Sheet
        $xmlSheet = simplexml_load_string($zip->getFromName($targetFile));
        $rows = $xmlSheet->sheetData->row;
        
        $rules = [];
        
        // Column Mapping (based on data inspection of Row 14+)
        // B -> Path
        // C -> Field
        // D -> Rule Description
        // H -> Error Code
        // I -> Error Message
        // J -> Level
        // K, L, M, N -> Applicability
        // O -> Observations
        
        foreach ($rows as $row) {
            $rowValues = [];
            foreach ($row->c as $cell) {
                $attr = $cell->attributes();
                $r = (string)$attr['r'];
                $colLetter = preg_replace('/[0-9]+/', '', $r);
                
                $val = (string)$cell->v;
                if (isset($attr['t']) && $attr['t'] == 's') {
                    $val = isset($sharedStrings[intval($val)]) ? $sharedStrings[intval($val)] : '';
                }
                $rowValues[$colLetter] = trim($val);
            }

            // Check if we have a code in H (and it looks like a code, not a header)
            if (isset($rowValues['H']) && !empty($rowValues['H']) && $rowValues['H'] !== 'CÓD. ERRO' && strlen($rowValues['H']) < 10) {
                
                // Applicability
                $app = [];
                if (isset($rowValues['K']) && $rowValues['K'] === 'V') $app[] = 'Recepção DPS';
                if (isset($rowValues['L']) && $rowValues['L'] === 'V') $app[] = 'Emissão NFS-e';
                if (isset($rowValues['M']) && $rowValues['M'] === 'V') $app[] = 'ADN Recepção';
                if (isset($rowValues['N']) && $rowValues['N'] === 'V') $app[] = 'ADN Emissão';

                // Clean up observations
                $obs = isset($rowValues['O']) ? trim($rowValues['O']) : '';
                if ($obs === '-' || $obs === 'X') $obs = '';

                $rules[] = [
                    'code' => $rowValues['H'],
                    'message' => isset($rowValues['I']) ? $rowValues['I'] : '',
                    'path' => isset($rowValues['B']) ? $rowValues['B'] : '',
                    'field' => isset($rowValues['C']) ? $rowValues['C'] : '',
                    'rule' => isset($rowValues['D']) ? $rowValues['D'] : '',
                    'level' => isset($rowValues['J']) ? $rowValues['J'] : '',
                    'applicability' => implode(', ', $app),
                    'observations' => $obs
                ];
            }
        }
        
        file_put_contents($jsonFile, json_encode($rules, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        echo "Sucesso! " . count($rules) . " regras extraídas para $jsonFile\n";

    } else {
        echo "Sheet 'RN DPS_NFS-e' não encontrada.\n";
    }
    $zip->close();
} else {
    echo "Falha ao abrir o arquivo XLSX.\n";
}
?>
