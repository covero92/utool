<?php
// Simple XLSX to JSON converter
// Usage: php convert_xlsx.php

$inputFile = 'c:/xampp/htdocs/utool/NFSE Nacional/listaservico.xlsx';
$outputFile = 'c:/xampp/htdocs/utool/data/nfse_services.json';

if (!file_exists($inputFile)) {
    die("Input file not found.\n");
}

$zip = new ZipArchive;
if ($zip->open($inputFile) === TRUE) {
    // 1. Read Shared Strings
    $sharedStrings = [];
    $xmlStrings = $zip->getFromName('xl/sharedStrings.xml');
    if ($xmlStrings) {
        $dom = new DOMDocument();
        $dom->loadXML($xmlStrings);
        $siNodes = $dom->getElementsByTagName('si');
        foreach ($siNodes as $si) {
            $t = $si->getElementsByTagName('t')->item(0);
            $sharedStrings[] = $t ? $t->nodeValue : '';
        }
    }

    // 2. Read Workbook to get Sheet Names
    $sheetMap = [];
    $xmlWorkbook = $zip->getFromName('xl/workbook.xml');
    if ($xmlWorkbook) {
        $dom = new DOMDocument();
        $dom->loadXML($xmlWorkbook);
        $sheets = $dom->getElementsByTagName('sheet');
        foreach ($sheets as $sheet) {
            $name = $sheet->getAttribute('name');
            $id = $sheet->getAttribute('r:id'); // rId1, rId2...
            // Map rId to sheet file. Usually rId1 -> sheet1.xml, but need relationships.
            // For simplicity, assume order matches or check relationships.
            // Let's check xl/_rels/workbook.xml.rels
            $sheetMap[$id] = $name;
        }
    }

    // 3. Read Relationships to map rId to filename
    $sheetFiles = [];
    $xmlRels = $zip->getFromName('xl/_rels/workbook.xml.rels');
    if ($xmlRels) {
        $dom = new DOMDocument();
        $dom->loadXML($xmlRels);
        $rels = $dom->getElementsByTagName('Relationship');
        foreach ($rels as $rel) {
            $id = $rel->getAttribute('Id');
            $target = $rel->getAttribute('Target');
            if (isset($sheetMap[$id])) {
                $sheetFiles[$sheetMap[$id]] = 'xl/' . $target;
            }
        }
    }

    $finalData = [];

    // 4. Read Sheets
    foreach ($sheetFiles as $sheetName => $file) {
        echo "Processing sheet: $sheetName ($file)...\n";
        $xmlSheet = $zip->getFromName($file);
        if (!$xmlSheet) continue;

        $dom = new DOMDocument();
        $dom->loadXML($xmlSheet);
        $rows = $dom->getElementsByTagName('row');
        
        $sheetData = [];
        $headers = [];
        
        foreach ($rows as $rowIndex => $row) {
            $cells = $row->getElementsByTagName('c');
            $rowData = [];
            $colIndex = 0;
            
            foreach ($cells as $cell) {
                $t = $cell->getAttribute('t'); // s = shared string
                $vNode = $cell->getElementsByTagName('v')->item(0);
                $val = $vNode ? $vNode->nodeValue : '';
                
                if ($t === 's' && is_numeric($val)) {
                    $val = isset($sharedStrings[$val]) ? $sharedStrings[$val] : $val;
                }
                
                // Handle empty cells / sparse columns?
                // For simplicity, just push.
                $rowData[] = trim($val);
            }
            
            if ($rowIndex === 0) {
                $headers = $rowData;
            } else {
                // Combine with headers
                $record = [];
                foreach ($headers as $i => $header) {
                    $record[$header] = isset($rowData[$i]) ? $rowData[$i] : '';
                }
                $sheetData[] = $record;
            }
        }
        $finalData[$sheetName] = $sheetData;
    }

    $zip->close();

    file_put_contents($outputFile, json_encode($finalData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    echo "Saved JSON to $outputFile\n";

} else {
    echo "Failed to open XLSX.\n";
}
?>
