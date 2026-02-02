<?php

$htmlFile = __DIR__ . '/rules_page.html';
$jsonFile = __DIR__ . '/nfe_rules.json';

if (!file_exists($htmlFile)) {
    die("HTML file not found.\n");
}

$dom = new DOMDocument();
libxml_use_internal_errors(true);
// Force UTF-8
$htmlContent = file_get_contents($htmlFile);
// Add meta charset if missing or just prepend it to force libxml to treat as UTF-8
$htmlContent = '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">' . $htmlContent;
$dom->loadHTML($htmlContent);
libxml_clear_errors();

$xpath = new DOMXPath($dom);

// Load existing rules
$existingRules = [];
if (file_exists($jsonFile)) {
    $existingRules = json_decode(file_get_contents($jsonFile), true);
}

// Index existing rules by code for easy lookup
$rulesMap = [];
foreach ($existingRules as $rule) {
    if (isset($rule['code'])) {
        $rulesMap[$rule['code']] = $rule;
    }
}

$tables = $xpath->query('//table[contains(@class, "regras_validacao")]');
$count = 0;
$updated = 0;
$added = 0;

echo "Found " . $tables->length . " tables.\n";

foreach ($tables as $table) {
    $rows = $xpath->query('.//tr', $table);
    
    // Determine column indices based on header
    $headerRow = $rows->item(0);
    $headers = [];
    if ($headerRow) {
        foreach ($xpath->query('.//th', $headerRow) as $th) {
            $headers[] = trim($th->textContent);
        }
    }
    
    // echo "Table headers: " . implode(', ', $headers) . "\n";

    // Map headers to keys
    $colMap = [];
    foreach ($headers as $index => $header) {
        // Remove accents for easier comparison
        $cleanHeader = iconv('UTF-8', 'ASCII//TRANSLIT', $header);
        
        if (stripos($cleanHeader, 'Msg') !== false) $colMap['code'] = $index;
        if (stripos($cleanHeader, 'Descri') !== false) $colMap['description'] = $index;
        if (stripos($cleanHeader, 'Regra') !== false) $colMap['rule'] = $index;
        if (stripos($cleanHeader, 'Aplic') !== false) $colMap['aplic'] = $index;
        if (stripos($cleanHeader, 'Efeito') !== false) $colMap['efeito'] = $index;
        if (stripos($cleanHeader, 'Modelo') !== false) $colMap['modelo'] = $index;
    }

    if (!isset($colMap['code']) || !isset($colMap['description'])) {
        // echo "Skipping table (missing code or description)\n";
        continue; 
    }

    // Process rows
    for ($i = 1; $i < $rows->length; $i++) {
        $row = $rows->item($i);
        $cols = $xpath->query('.//td', $row);
        
        if ($cols->length < count($colMap)) continue;

        $code = trim($cols->item($colMap['code'])->textContent);
        
        // Clean code (remove "Rej_" prefix if present in ID but text content usually has the number)
        // Check if code is numeric
        if (!is_numeric($code)) {
            // Sometimes the code is in the ID attribute of the TD
            $id = $cols->item($colMap['code'])->getAttribute('id');
            if (preg_match('/Rej_(\d+)/', $id, $matches)) {
                $code = $matches[1];
            }
        }

        if (empty($code) || !is_numeric($code)) continue;

        $description = trim($cols->item($colMap['description'])->textContent);
        $ruleText = isset($colMap['rule']) ? trim($cols->item($colMap['rule'])->textContent) : '';
        $aplic = isset($colMap['aplic']) ? trim($cols->item($colMap['aplic'])->textContent) : '';
        $efeito = isset($colMap['efeito']) ? trim($cols->item($colMap['efeito'])->textContent) : '';
        $modelo = isset($colMap['modelo']) ? trim($cols->item($colMap['modelo'])->textContent) : '';

        // Clean up description (remove "Rejeição: " prefix if desired, but user might want it)
        // The user asked to validate against the page.
        
        $newRule = [
            'code' => $code,
            'description' => $description,
            'rule' => $ruleText,
            'aplic' => $aplic,
            'efeito' => $efeito,
            'modelo' => $modelo
        ];

        if (isset($rulesMap[$code])) {
            // Update existing
            $rulesMap[$code] = array_merge($rulesMap[$code], $newRule);
            $updated++;
        } else {
            // Add new
            $rulesMap[$code] = $newRule;
            $added++;
        }
        $count++;
    }
}

// Convert back to array and sort
$finalRules = array_values($rulesMap);
usort($finalRules, function($a, $b) {
    return $a['code'] <=> $b['code'];
});

file_put_contents($jsonFile, json_encode($finalRules, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo "Processed $count rules.\n";
echo "Added: $added\n";
echo "Updated: $updated\n";
echo "Total rules: " . count($finalRules) . "\n";
