<?php
// scripts/find_dashboard_table.php
require_once __DIR__ . '/../includes/db_connection.php';

function checkDB($dbname) {
    echo "Checking database: $dbname... ";
    try {
        // Build DSN manually to switch DB
        $host = getenv('DB_HOST') ?: 'dbserver';
        $port = getenv('DB_PORT') ?: '5432';
        $user = getenv('DB_USER') ?: 'postgres';
        $password = getenv('DB_PASSWORD') ?: 'postgres';
        
        $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
        $pdo = new PDO($dsn, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        
        $stmt = $pdo->query("SELECT table_schema, table_name FROM information_schema.tables WHERE table_name = 'dashboard'");
        $tables = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if ($tables) {
            echo "FOUND!\n";
            print_r($tables);
            
            // Inspect columns
            $cols = $pdo->query("SELECT column_name, data_type FROM information_schema.columns WHERE table_name = 'dashboard'")->fetchAll(PDO::FETCH_ASSOC);
            echo "Columns:\n";
            print_r($cols);
            
            // Inspect data
            $data = $pdo->query("SELECT * FROM dashboard LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
            echo "Data Sample:\n";
            print_r($data);
            
            return true;
        } else {
            echo "Not found.\n";
        }
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
    return false;
}

$candidates = ['suporte_hub', 'suporte-nao-apagar', 'suporte-contagem'];

foreach ($candidates as $db) {
    if (checkDB($db)) break;
}
?>
