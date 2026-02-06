<?php
// debug_ppr_json.php
// debug_ppr_json.php
// Logic copied to avoid session dependencies


// We need to expose the $allPPRData variable. 
// Since ppr_manager.php is a script that executes immediately, simply requiring it might output HTML.
// But wait, ppr_manager.php has HTML at the bottom.
// We should probably copy the parsing logic to a separate file to test it, OR just modify ppr_manager.php to optionally debug.

// Better approach: Create a standalone script with the same logic, to avoid side effects.

function parsePPRCsv_Debug($file)
{
    if (!file_exists($file))
        return [];
    $handle = fopen($file, "r");
    $okrs = [];
    $currentOKR = null; 
    $colOffsets = null; 

    while (($row = fgetcsv($handle, 1000, ";")) !== FALSE) {
        $row = array_map(function ($x) {
            return mb_check_encoding($x, 'UTF-8') ? $x : mb_convert_encoding($x, 'UTF-8', 'Windows-1252');
        }, $row);

        $firstCell = trim($row[0] ?? '');
        if (empty($firstCell)) continue;

        if (stripos($firstCell, 'OKR') === 0) {
            $newOKR = [
                'id' => uniqid('okr_'),
                'title' => $firstCell,
                'goals' => []
            ];
            $okrs[] = $newOKR;
            end($okrs);
            $key = key($okrs);
            $currentOKR = &$okrs[$key];
            $colOffsets = null;
            continue;
        }

        $foundJan = false;
        $tempOffsets = [];
        $months = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];

        foreach ($row as $idx => $cell) {
            $cellTrim = trim($cell);
            if (stripos($cellTrim, 'Jan') !== false) {
                $tempOffsets['Jan'] = $idx;
                $foundJan = true;
                break; 
            }
        }

        if ($foundJan) {
            $base = $tempOffsets['Jan'];
            foreach ($months as $i => $m) {
                $colOffsets[$m] = $base + $i;
            }
            continue; 
        }

        if (stripos($firstCell, 'Meta') === 0 || stripos($firstCell, 'Peso') === 0 || stripos($firstCell, '0') === 0) {
            continue;
        }

        if ($currentOKR !== null && $colOffsets !== null) {
            $hasValues = false;
            $tempMonthlyResults = [];

            foreach ($months as $m) {
                $idx = $colOffsets[$m] ?? -1;
                $val = $row[$idx] ?? '';
                if (strlen($val) > 20 && !is_numeric(str_replace(['%', 'm', 's', ' '], '', $val))) {
                    $val = '';
                }
                if (trim($val) !== '') {
                    $hasValues = true;
                }
                $tempMonthlyResults[$m] = [
                    'status' => 'pending',
                    'actual' => '',
                    'target' => $val
                ];
            }

            if (
                !$hasValues ||
                stripos($firstCell, 'O não cumprimento') !== false ||
                stripos($firstCell, 'Para garantir') !== false ||
                stripos($firstCell, 'Pontos atuais') !== false
            ) {
                continue;
            }

            $goal = [
                'id' => uniqid('goal_'),
                'title' => $firstCell,
                'weight' => 10,
                'rule' => $row[$colOffsets['Dez'] + 1] ?? '',
                'monthlyResults' => $tempMonthlyResults
            ];

            $currentOKR['goals'][] = $goal;
        }
    }
    fclose($handle);
    return $okrs;
}

// ... (Parsing logic same as before, simplified for brevity in this thought trace)

// Use explicit absolute path for testing to avoid CLI cwd issues
$baseDir = "c:\\xampp\\htdocs\\utool";
$years = ['2024', '2025', '2026'];
$allPPRData = [];

echo "Starting debug...\n";

foreach ($years as $y) {
    $candidates = [
        "$baseDir/ppr/PPR (2)(PPR - $y).csv",
        "$baseDir/ppr/PPR (2)(PPR-$y).csv"
    ];

    foreach ($candidates as $f) {
        echo "Checking: $f\n";
        if (file_exists($f)) {
            echo "Found file for $y. Parsing...\n";
            $data = parsePPRCsv_Debug($f);
            $count = count($data);
            echo "Parsed $count OKRs for $y.\n";
            $allPPRData[$y] = $data;
            break;
        } else {
             echo "File not found: $f\n";
        }
    }
}

echo "JSON Output:\n";
$json = json_encode($allPPRData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
if ($json === false) {
    echo "JSON Encode Error: " . json_last_error_msg() . "\n";
} else {
    echo $json;
}
?>
