<?php
$html = file_get_contents('c:/xampp/htdocs/utool/dicionariodados.html');
$dom = new DOMDocument;
@$dom->loadHTML($html);
$xpath = new DOMXPath($dom);

$tables = ['notafiscal', 'notafiscalitem'];

foreach ($tables as $tableName) {
    file_put_contents('schema_dump.txt', "TABLE: $tableName\n", FILE_APPEND);
    // Find the table that contains the table name in a cell
    // The structure seems to be: <table><tr><th>Nome da tabela</th><td>tablename</td>...
    
    // We look for a td that contains exactly the table name
    $nodes = $xpath->query("//td[normalize-space(text())='$tableName']");
    
    foreach ($nodes as $node) {
        // Get the ancestor table
        $table = $node->parentNode->parentNode->parentNode; // td -> tr -> tbody -> table (or just table if no tbody)
        if ($table->nodeName !== 'table') {
             $table = $node->parentNode->parentNode; // td -> tr -> table
        }
        
        // Now iterate rows to find fields
        $rows = $table->getElementsByTagName('tr');
        $inFields = false;
        foreach ($rows as $row) {
            $cells = $row->getElementsByTagName('td');
            $headers = $row->getElementsByTagName('th');
            
            if ($headers->length > 0) {
                if (trim($headers->item(0)->textContent) == 'Nome' && trim($headers->item(1)->textContent) == 'Tipo') {
                    $inFields = true;
                    continue;
                }
                if (trim($headers->item(0)->textContent) == 'Chaves Estrangeiras') {
                    $inFields = false;
                }
            }
            
            if ($inFields && $cells->length >= 2) {
                $name = trim($cells->item(0)->textContent);
                $type = trim($cells->item(1)->textContent);
                file_put_contents('schema_dump.txt', "  - $name ($type)\n", FILE_APPEND);
            }
        }
    }
    file_put_contents('schema_dump.txt', "\n", FILE_APPEND);
}
?>
