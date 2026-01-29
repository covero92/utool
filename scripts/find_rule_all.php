<?php
$xlsxFile = __DIR__ . '/../NFSE Nacional/ANEXO_I-SEFIN_ADN-DPS_NFSe-SNNFSe.xlsx';
$zip = new ZipArchive;

if ($zip->open($xlsxFile) === TRUE) {
    // Load Shared Strings
    $sharedStrings = [];
    if ($zip->locateName('xl/sharedStrings.xml') !== false) {
        $xmlStrings = simplexml_load_string($zip->getFromName('xl/sharedStrings.xml'));
        foreach ($xmlStrings->si as $si) {
            $sharedStrings[] = (string)$si->t;
        }
    }

    // Iterate all sheets
    if ($zip->locateName('xl/workbook.xml') !== false) {
        $xml = simplexml_load_string($zip->getFromName('xl/workbook.xml'));
        foreach ($xml->sheets->sheet as $sheet) {
            $name = (string)$sheet['name'];
            $id = (string)$sheet->attributes('http://schemas.openxmlformats.org/officeDocument/2006/relationships')['id'];
            
            // Find Target File
            $targetFile = '';
            if ($zip->locateName('xl/_rels/workbook.xml.rels') !== false) {
                $xmlRels = simplexml_load_string($zip->getFromName('xl/_rels/workbook.xml.rels'));
                foreach ($xmlRels->Relationship as $rel) {
                    if ((string)$rel['Id'] === $id) {
                        $targetFile = 'xl/' . (string)$rel['Target'];
                        break;
                    }
                }
            }

            if ($targetFile) {
                $xmlSheet = simplexml_load_string($zip->getFromName($targetFile));
                $rows = $xmlSheet->sheetData->row;
                foreach ($rows as $row) {
                    foreach ($row->c as $cell) {
                        $val = (string)$cell->v;
                        if (isset($cell['t']) && $cell['t'] == 's') {
                            $val = isset($sharedStrings[intval($val)]) ? $sharedStrings[intval($val)] : '';
                        }
                        // Check inline string
                        if (isset($cell->is)) {
                            $val = (string)$cell->is->t;
                        }

                        if (stripos($val, 'E074') !== false) {
                            echo "Found E074 in Sheet '$name', Row " . $row['r'] . "\n";
                            // Print row
                            foreach ($row->c as $c) {
                                $rC = (string)$c['r'];
                                $vC = (string)$c->v;
                                if (isset($c['t']) && $c['t'] == 's') {
                                    $vC = isset($sharedStrings[intval($vC)]) ? $sharedStrings[intval($vC)] : '';
                                }
                                echo "[$rC]=$vC | ";
                            }
                            echo "\n";
                            exit;
                        }
                    }
                }
            }
        }
    }
    echo "E074 not found in any sheet.\n";
    $zip->close();
}
?>
