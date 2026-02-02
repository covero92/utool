<?php
// Configuration
$inputFile = 'notas.txt';
$outputFile = 'update_notas.sql';
$delimiter = ';';

// Check if file exists
if (!file_exists($inputFile)) {
    die("Arquivo $inputFile não encontrado.\n");
}

// Read file
$lines = file($inputFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$numeros = [];

echo "Lendo $inputFile...\n";

foreach ($lines as $index => $line) {
    // Skip optional header if first line doesn't look like data
    if ($index === 0 && stripos($line, 'Número') !== false) {
        continue;
    }

    $cols = explode($delimiter, $line);
    
    // Assuming 'Número' is the first column (index 0)
    if (isset($cols[0])) {
        $num = trim($cols[0]);
        // Basic validation - checks if it looks like a number
        if (preg_match('/^\d+$/', $num)) {
            $numeros[] = "'$num'"; // Wrap in quotes for SQL
        }
    }
}

if (empty($numeros)) {
    die("Nenhum número de nota encontrado.\n");
}

// Generate SQL
$count = count($numeros);
$idsList = implode(",\n    ", $numeros); // Indented list for readability

$sql = "-- Atualização em massa gerada automaticamente\n";
$sql .= "-- Total de registros: $count\n\n";
$sql .= "UPDATE notafiscal \n";
$sql .= "SET idserie = '8', modelo = '100' \n";
$sql .= "WHERE numeronotafiscal IN (\n    $idsList\n);";

// Save file
file_put_contents($outputFile, $sql);

echo "Sucesso! Arquivo '$outputFile' gerado com $count registros.\n";
echo "Você pode carregar este arquivo no Editor SQL para executar.\n";
