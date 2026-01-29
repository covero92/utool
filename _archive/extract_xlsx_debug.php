<?php
// extract_xlsx_debug.php

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

function debugXlsx($inputFile) {
    echo "Debug de: $inputFile\n";

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

        foreach ($xml->sheetData->row as $row) {
            $rIndex = (int)$row['r'];
            echo "Row $rIndex:\n";
            foreach ($row->c as $cell) {
                $ref = (string)$cell['r'];
                $val = '';
                $type = (string)$cell['t'];
                if (isset($cell->v)) {
                    $val = (string)$cell->v;
                    if ($type == 's') $val = isset($strings[(int)$val]) ? $strings[(int)$val] : $val;
                }
                echo "  [$ref] => " . trim(str_replace("\n", "\\n", $val)) . "\n";
            }
        }
        $zip->close();
    }
}

debugXlsx(__DIR__ . '/release-notes.xlsx');
?>
