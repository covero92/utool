<?php
require_once 'includes/GenericCnabParser.php';

$dbPath = __DIR__ . '/utool_retorno.sqlite'; // Assuming it's in root/database or similar?
// Wait, setup_return_db.php created it in __DIR__ . '/database/utool_retorno.sqlite' relative to itself in root?
// setup_return_db.php: $dbPath = __DIR__ . '/database/utool_retorno.sqlite'; (if it was inside includes?)
// No, setup_return_db.php was in root.
// Let's check where setup_return_db.php created it.
// It did: $dbPath = __DIR__ . '/database/utool_retorno.sqlite';
// So it is in c:\xampp\htdocs\utool\database\utool_retorno.sqlite

$dbPath = __DIR__ . '/database/utool_retorno.sqlite';
if (!file_exists($dbPath)) {
    // Try to find it if my assumption is wrong
    $dbPath = 'database/utool_retorno.sqlite';
}

echo "Using DB: $dbPath\n";

try {
    $pdo = new PDO("sqlite:" . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $parser = new GenericCnabParser($pdo);
    $testFile = 'test_sicoob_240.ret';

    echo "Parsing $testFile...\n";
    $result = $parser->parseFile($testFile);

    if (isset($result['error'])) {
        echo "Error: " . $result['error'] . "\n";
    } else {
        echo "Success!\n";
        echo "Bank: " . $result['bank_in_file'] . "\n";
        echo "Type: " . $result['type'] . "\n";
        echo "Total Lines: " . $result['total_lines'] . "\n";

        // Check for resolutions
        $hasResolution = false;
        foreach ($result['lines'] as $line) {
            if (!empty($line['fields'])) {
                foreach ($line['fields'] as $field) {
                    if (isset($field['resolution'])) {
                        echo "\nFound Resolution on Line " . $line['number'] . ":\n";
                        echo " - Field: " . $field['name'] . "\n";
                        echo " - Value: " . $field['value'] . "\n";
                        echo " - Title: " . $field['resolution']['ui_title'] . "\n";
                        $hasResolution = true;
                    }
                }
            }
        }

        if (!$hasResolution) {
            echo "\nNO RESOLUTIONS FOUND. Check occurrence code mapping.\n";
        }
    }

} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}
