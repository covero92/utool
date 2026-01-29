<?php

$url = "http://moc.sped.fazenda.pr.gov.br/Leiaute.html";
$html = file_get_contents($url);

if ($html === false) {
    die("Error fetching URL");
}

$dom = new DOMDocument();
libxml_use_internal_errors(true);
$dom->loadHTML($html);
libxml_clear_errors();

$xpath = new DOMXPath($dom);
$tables = $dom->getElementsByTagName('table');

$layoutData = [];

foreach ($tables as $table) {
    $rows = $table->getElementsByTagName('tr');
    foreach ($rows as $row) {
        $cols = $row->getElementsByTagName('td');
        
        // We expect around 10 columns
        // #, ID, Campo, Descrição, Ele, Pai, Tipo, Ocor., Tam., Obs.
        if ($cols->length >= 9) {
            $id = trim($cols->item(1)->textContent);
            
            // Skip header row if it exists in tbody
            if ($id == 'ID' || $id == '') continue;

            $item = [
                "id" => $id,
                "campo" => trim($cols->item(2)->textContent),
                "descricao" => trim($cols->item(3)->textContent),
                "ele" => trim($cols->item(4)->textContent),
                "pai" => trim($cols->item(5)->textContent),
                "tipo" => trim($cols->item(6)->textContent),
                "ocor" => trim($cols->item(7)->textContent),
                "tam" => trim($cols->item(8)->textContent),
                "obs" => ($cols->length > 9) ? trim($cols->item(9)->textContent) : ""
            ];
            
            $layoutData[] = $item;
        }
    }
}

$jsonFile = __DIR__ . '/nfe_layout.json';
file_put_contents($jsonFile, json_encode($layoutData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo "Successfully extracted " . count($layoutData) . " layout items to $jsonFile";
?>
