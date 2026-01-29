<?php
$type = 'nbs'; // or 'service'
$dir = __DIR__;

function testRead($type) {
    global $dir;
    echo "Testing type: $type\n";
    $file = ($type === 'nbs') ? $dir . '/NFSE Nacional/nbs.csv' : $dir . '/NFSE Nacional/lista_servico_nacional.csv';
    echo "File path: $file\n";

    if (!file_exists($file)) {
        echo "ERROR: File not found!\n";
        return;
    }

    if (($handle = fopen($file, "r")) !== FALSE) {
        $header = fgetcsv($handle, 1000, ";"); // Skip header
        echo "Header: " . print_r($header, true) . "\n";
        
        $count = 0;
        while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
            $data = array_map('trim', $data);
            
            if ($type === 'nbs') {
                 $code = $data[0] ?? '';
                 $desc = $data[1] ?? '';
                 if ($count < 3) echo "Row $count: $code - $desc\n";
            } else {
                 $fullCode = implode('.', array_filter([$data[1]??'', $data[2]??'', $data[3]??''])); 
                 $desc = end($data);
                 if ($count < 3) echo "Row $count: $fullCode - $desc\n";
            }
            $count++;
        }
        fclose($handle);
        echo "Total rows: $count\n\n";
    } else {
        echo "ERROR: Could not open file.\n";
    }
}

testRead('nbs');
testRead('service');
