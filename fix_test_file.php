<?php
$file = 'test_sicoob_240.ret';
$lines = file($file, FILE_IGNORE_NEW_LINES);
$newLines = [];
foreach ($lines as $line) {
    // Pad to 240 chars with spaces
    $newLines[] = str_pad($line, 240, ' ', STR_PAD_RIGHT);
}

file_put_contents($file, implode("\r\n", $newLines));
echo "Fixed " . count($newLines) . " lines.\n";
