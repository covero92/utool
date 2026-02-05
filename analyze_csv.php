<?php
// analyze_csv.php
$file = 'ppr/PPR (2)(PPR - 2026).csv';

if (!file_exists($file))
    die("File not found");

$handle = fopen($file, "r");
$data = [];
$row = 0;
while (($line = fgetcsv($handle, 1000, ";")) !== FALSE) {
    // Convert to UTF-8 if needed (Windows CSVs often CP1252)
    $cleanLine = array_map(function ($x) {
        return mb_convert_encoding($x, 'UTF-8', 'Windows-1252'); // Guessing encoding
    }, $line);

    echo "Row $row: " . print_r($cleanLine, true) . "\n";
    $row++;
    if ($row > 10)
        break; // First 10 rows
}
fclose($handle);
?>