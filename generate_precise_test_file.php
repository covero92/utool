<?php
$lines = [];

// Line 1: Header Arquivo (240 chars)
$line1 = str_pad("75600000", 8, "0"); // Pos 1-8
$line1 .= str_pad("", 232, " "); // Filler
$lines[] = substr(str_pad($line1, 240, " "), 0, 240);

// Line 2: Header Lote
$line2 = str_pad("75600011", 8, "0"); // Pos 1-8
$line2 .= str_pad("", 232, " ");
$lines[] = substr(str_pad($line2, 240, " "), 0, 240);

// Line 3: Segment T
// 1-8: 75600013
// 9-13: 00001 (Seq)
// 14: T
// 15: Filler
// 16-17: 03 (Movimento - Rejeição)
$line3 = "7560001300001T 03"; // 17 chars
$line3 .= str_pad("", 196, " "); // Pos 18 to 213 (213-17 = 196) (Approx, let's execute logic precisely)

// Rebuilding Line 3 using precise offsets
$l3 = str_repeat(" ", 240);
$l3 = substr_replace($l3, "756", 0, 3);
$l3 = substr_replace($l3, "0001", 3, 4); // Lote
$l3 = substr_replace($l3, "3", 7, 1); // Type
$l3 = substr_replace($l3, "00001", 8, 5); // Seq
$l3 = substr_replace($l3, "T", 13, 1); // Seg Code
$l3 = substr_replace($l3, "03", 15, 2); // Movimento (Pos 16-17)

// Motivo Ocorrencia: Pos 214-223 (start 213 0-based, len 10)
$l3 = substr_replace($l3, "A9        ", 213, 10);

$lines[] = $l3;

// Line 4: Segment U
$l4 = str_repeat(" ", 240);
$l4 = substr_replace($l4, "756", 0, 3);
$l4 = substr_replace($l4, "0001", 3, 4);
$l4 = substr_replace($l4, "3", 7, 1);
$l4 = substr_replace($l4, "00002", 8, 5); // Seq
$l4 = substr_replace($l4, "U", 13, 1);
$lines[] = $l4;

// Trailers
$lines[] = str_pad("75600015", 240, " ");
$lines[] = str_pad("75600009", 240, " ");

file_put_contents('test_sicoob_240.ret', implode("\r\n", $lines));
echo "Generated precise test file.\n";
