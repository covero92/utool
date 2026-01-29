<?php
// extract_xlsx.php

function columnIndexFromString($pString)
{
    $column = 0;
    $pString = strtoupper($pString);
    $length = strlen($pString);
    for ($i = 0; $i < $length; $i++) {
        $column += (ord($pString[$i]) - 64) * pow(26, $length - $i - 1);
    }
    return $column - 1; // 0-indexed
}

function stringFromColumnIndex($n)
{
    $n = $n + 1;
    $s = '';
    while ($n > 0) {
        $m = ($n - 1) % 26;
        $s = chr(65 + $m) . $s;
        $n = floor(($n - m) / 26);
    }
    return $s;
}

function extractXlsxData($inputFile, $outputFile) {
    echo "Iniciando extração de: $inputFile\n";

    if (!file_exists($inputFile)) {
        die("Erro: Arquivo de entrada não encontrado.\n");
    }

    $zip = new ZipArchive;
    if ($zip->open($inputFile) === TRUE) {
        
        $strings = [];
        $sharedStringsXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($sharedStringsXml) {
            $xml = simplexml_load_string($sharedStringsXml);
            foreach ($xml->si as $si) {
                if (isset($si->t)) {
                    $strings[] = (string)$si->t;
                } elseif (isset($si->r)) {
                    $text = '';
                    foreach ($si->r as $run) $text .= (string)$run->t;
                    $strings[] = $text;
                } else {
                    $strings[] = '';
                }
            }
        }

        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $xml = simplexml_load_string($sheetXml);
        $rows = [];

        foreach ($xml->sheetData->row as $row) {
            $rowData = [];
            $rowIndex = (int)$row['r'];
            
            foreach ($row->c as $cell) {
                $ref = (string)$cell['r'];
                $colLetter = preg_replace('/[0-9]+/', '', $ref);
                $colIndex = columnIndexFromString($colLetter);

                $value = '';
                $type = (string)$cell['t'];
                
                if (isset($cell->v)) {
                    $value = (string)$cell->v;
                    if ($type == 's') {
                        $value = isset($strings[(int)$value]) ? $strings[(int)$value] : $value;
                    }
                }
                
                // Store with column letter as key
                $rowData[$colLetter] = trim($value);
            }
            
            if (!empty($rowData)) {
                $rows[] = $rowData;
            }
        }

        $zip->close();

        // 3. Save to JSON
        $json = json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        if (file_put_contents($outputFile, $json)) {
            echo "Sucesso! Dados extraídos para: $outputFile\n";
            echo "Total de linhas: " . count($rows) . "\n";
        } else {
            echo "Erro ao salvar arquivo JSON.\n";
        }

    } else {
        echo "Erro: Falha ao abrir o arquivo XLSX.\n";
    }
}

$inputFile = __DIR__ . '/release-notes.xlsx';
$outputFile = __DIR__ . '/data/release_notes.json';

extractXlsxData($inputFile, $outputFile);
?>
