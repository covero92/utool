<?php
// ppr_manager.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Correct Includes based on directory listing
require_once 'includes/db_connection.php'; // Ensure DB
require_once 'includes/auth_guard.php'; // Enforce Global Auth
require_once 'includes/portal_auth.php'; // Handle Helpers

$currentUser = $_SESSION['user_name'] ?? 'Visitante';
$currentRoleId = $_SESSION['user_role_id'] ?? 0;

// Security Check (redundant if auth_guard is strictly enforcing, but good for role checks)
// auth_guard handles the basic public/private check.

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// Helper to find assets
function getAssetPath($dir, $extension)
{
    if (!is_dir($dir))
        return '';
    $files = scandir($dir);
    foreach ($files as $file) {
        if (pathinfo($file, PATHINFO_EXTENSION) === $extension && strpos($file, 'index') !== false) {
            return 'ppr_dashboard/dist/assets/' . $file;
        }
    }
    return '';
}

// Compute paths relative to web root
$baseDistDir = __DIR__ . '/ppr_dashboard/dist/assets';
$cssPath = getAssetPath($baseDistDir, 'css');
$jsPath = getAssetPath($baseDistDir, 'js');

// --- CSV PARSING LOGIC ---
function parsePPRCsv($file)
{
    if (!file_exists($file))
        return [];
    $handle = fopen($file, "r");
    $okrs = [];
    $currentOKR = null; // Store reference to current OKR array

    $colOffsets = null; // Store detected month indices: ['Jan' => 1, 'Fev' => 2...]

    while (($row = fgetcsv($handle, 1000, ";")) !== FALSE) {
        $row = array_map(function ($x) {
            return mb_check_encoding($x, 'UTF-8') ? $x : mb_convert_encoding($x, 'UTF-8', 'Windows-1252');
        }, $row);

        $firstCell = trim($row[0] ?? '');

        if (empty($firstCell))
            continue;

        // Detect OKR Header
        if (stripos($firstCell, 'OKR') === 0) {
            // New OKR, reset context if needed, but usually header follows
            $newOKR = [
                'id' => uniqid('okr_'),
                'title' => $firstCell,
                'goals' => []
            ];
            $okrs[] = $newOKR;
            end($okrs);
            $key = key($okrs);
            $currentOKR = &$okrs[$key];

            // Reset offsets on new OKR (assuming each OKR has its own header row)
            $colOffsets = null;
            continue;
        }

        // Detect Header Row (Contains Jan, Fev, etc)
        // We look for 'Jan' or 'Fev' to be sure
        $foundJan = false;
        $tempOffsets = [];
        $months = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];

        foreach ($row as $idx => $cell) {
            $cellTrim = trim($cell);
            if (stripos($cellTrim, 'Jan') !== false) {
                $tempOffsets['Jan'] = $idx;
                $foundJan = true;
                break; // Found Jan, no need to check other cells in this row for Jan
            }
        }

        if ($foundJan) {
            // We found a header row! Calculate all offsets relative to Jan
            $base = $tempOffsets['Jan'];
            foreach ($months as $i => $m) {
                // Assuming sequential columns
                $colOffsets[$m] = $base + $i;
            }
            continue; // Skip the header row itself
        }

        // Skip Metadata/Helper rows if we haven't found a header yet or if it's explicitly a helper
        if (stripos($firstCell, 'Meta') === 0 || stripos($firstCell, 'Peso') === 0 || stripos($firstCell, '0') === 0) {
            continue;
        }

        // Process Goal Row
        if ($currentOKR !== null && $colOffsets !== null) {
            // We need at least enough columns to cover the map
            // But relying on row count matches header count is risky.

            // Check if this row looks like a goal (has values in the mapped columns)
            $hasValues = false;
            $tempMonthlyResults = [];

            foreach ($months as $m) {
                $idx = $colOffsets[$m] ?? -1;
                $val = $row[$idx] ?? '';

                // Safety: if value is super long text, ignore (it's likely a description text bleeding in)
                if (strlen($val) > 20 && !is_numeric(str_replace(['%', 'm', 's', ' '], '', $val))) {
                    $val = '';
                }

                if (trim($val) !== '') {
                    $hasValues = true;
                }

                $tempMonthlyResults[$m] = [
                    'status' => 'pending',
                    'actualValue' => '',
                    'targetValue' => $val
                ];
            }

            // FILTERING:
            // 1. Must have some target values.
            // 2. Must NOT be an explanatory text row.
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
                'type' => 'numeric',
                'weight' => 10,
                // Rule usually is after Dec. Let's guess: Dec index + 1
                'description' => $row[$colOffsets['Dez'] + 1] ?? '',
                'results' => $tempMonthlyResults
            ];

            $currentOKR['goals'][] = $goal;
        }
    }
    fclose($handle);
    return $okrs;
}

// Load Multiple Years
$years = ['2024', '2025', '2026'];
$allPPRData = [];

foreach ($years as $y) {
    // Try variations of filename found in dir
    $candidates = [
        __DIR__ . "/ppr/PPR (2)(PPR - $y).csv",
        __DIR__ . "/ppr/PPR (2)(PPR-$y).csv"
    ];

    foreach ($candidates as $f) {
        if (file_exists($f)) {
            $allPPRData[$y] = parsePPRCsv($f);
            break;
        }
    }
}

?>
<!doctype html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Gestão de PPR | SuporteHub</title>

    <!-- Inject PHP Session Info -->
    <script>
        window.USER_INFO = {
            name: <?php echo json_encode($currentUser); ?>,
            roleId: <?php echo json_encode($currentRoleId); ?>,
            capabilities: <?php echo json_encode($_SESSION['user_capabilities'] ?? []); ?>
        };
        window.PPR_DATA = <?php echo json_encode($allPPRData ?? []); ?>;
    </script>

    <?php if ($cssPath): ?>
        <link rel="stylesheet" href="<?php echo $cssPath; ?>?v=<?php echo time(); ?>">
    <?php endif; ?>
</head>

<body style="margin: 0; background-color: #f3f5f9;">
    <div id="root"></div>

    <?php if ($jsPath): ?>
        <script type="module" src="<?php echo $jsPath; ?>?v=<?php echo time(); ?>"></script>
    <?php else: ?>
        <div style="padding: 50px; text-align: center; color: #dc3545; font-family: sans-serif;">
            <h2>Erro de Carregamento</h2>
            <p>Os arquivos do Dashboard não foram encontrados.</p>
            <p>Certifique-se de que o build foi gerado em <code>/ppr_dashboard/dist</code>.</p>
        </div>
    <?php endif; ?>
</body>

</html>