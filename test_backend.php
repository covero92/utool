<?php
// test_backend.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/GenericCnabParser.php';

$file = __DIR__ . '/test_sicoob_240.ret';
$dbPath = __DIR__ . '/database/utool_retorno.sqlite';

echo "Testing Parser on: $file\n";
echo "DB: $dbPath\n";

if (!file_exists($file)) {
    die("File not found!\n");
}

try {
    $pdo = new PDO("sqlite:" . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $parser = new GenericCnabParser($pdo);
    $result = $parser->parseFile($file);

    echo "Success!\n";
    print_r(array_keys($result));

    if (isset($result['error'])) {
        echo "Logic Error: " . $result['error'] . "\n";
    }

} catch (Throwable $e) {
    echo "CRASH: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
