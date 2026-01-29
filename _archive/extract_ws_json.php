<?php
$content = file_get_contents('xml-analyzer.php');

// Extract the table body content
preg_match('/<tbody>(.*?)<\/tbody>/s', $content, $matches);
if (!isset($matches[1])) {
    die("Could not find table body");
}

$tbody = $matches[1];
$dom = new DOMDocument();
@$dom->loadHTML('<?xml encoding="utf-8" ?>' . $tbody, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

$xpath = new DOMXPath($dom);
$rows = $xpath->query('//tr');

$data = [];

foreach ($rows as $row) {
    $cols = $xpath->query('td', $row);
    if ($cols->length >= 4) {
        $uf = trim($cols->item(0)->textContent);
        $ambiente = trim($cols->item(1)->textContent);
        $servico = trim($cols->item(2)->textContent);
        $url = trim($cols->item(3)->textContent);

        if (!isset($data[$uf])) {
            $data[$uf] = [];
        }

        $data[$uf][] = [
            'ambiente' => $ambiente,
            'servico' => $servico,
            'url' => $url
        ];
    }
}

file_put_contents('ws_data.json', json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
echo "Generated ws_data.json\n";
?>
