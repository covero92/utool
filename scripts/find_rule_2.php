<?php
$xlsxFile = __DIR__ . '/../NFSE Nacional/ANEXO_I-SEFIN_ADN-DPS_NFSe-SNNFSe.xlsx';
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
                
                if (stripos($val, 'E074') !== false) {
                    echo "Found E074 in Row " . $row['r'] . ", Column $colLetter\n";
                    // Print whole row
                    foreach ($row->c as $c) {
                        $attrC = $c->attributes();
                        $rC = (string)$attrC['r'];
                        $valC = (string)$c->v;
                        if (isset($attrC['t']) && $attrC['t'] == 's') {
                            $valC = isset($sharedStrings[intval($valC)]) ? $sharedStrings[intval($valC)] : '';
                        }
                        echo "[$rC]=$valC | ";
                    }
                    echo "\n";
                    exit;
                }
            }
        }
        echo "E074 not found.\n";
    }
    $zip->close();
}
?>
