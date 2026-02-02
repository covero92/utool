<?php
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

try {
    $spreadsheet = IOFactory::load('c:\xampp\htdocs\utool\NFSE Nacional\relacaoibsnbs.xlsx');
    foreach($spreadsheet->getAllSheets() as $sheet) {
        echo 'Sheet: ' . $sheet->getTitle() . PHP_EOL;
        $rowIterator = $sheet->getRowIterator(1, 2); // Read first 2 rows
        foreach($rowIterator as $row) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);
            $data = [];
            foreach ($cellIterator as $cell) {
                $data[] = $cell->getValue();
            }
            echo implode(' | ', $data) . PHP_EOL;
        }
    }
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
