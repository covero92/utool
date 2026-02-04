<?php
// debug_csv_structure.php
$file = 'ppr/PPR (2)(PPR - 2026).csv';

if (!file_exists($file))
    die("File not found");

$handle = fopen($file, "r");
echo "<pre>";
$row = 0;
while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
    if ($row > 5)
        break;

    echo "<b>Row $row:</b>\n";
    foreach ($data as $index => $val) {
        // Convert encoding for display
        $val = mb_convert_encoding($val, 'UTF-8', 'Windows-1252');
        echo "[$index] => $val\n";
    }
    echo "-----------------\n";
    $row++;
}
echo "</pre>";
?>