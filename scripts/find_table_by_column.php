<?php
// scripts/find_table_by_column.php
require_once __DIR__ . '/../includes/db_connection.php';

function searchColumns($dbname) {
    echo "Searching database: $dbname... \n";
    try {
        $host = getenv('DB_HOST') ?: 'dbserver';
        $port = getenv('DB_PORT') ?: '5432';
        $user = getenv('DB_USER') ?: 'postgres';
        $password = getenv('DB_PASSWORD') ?: 'postgres';
        
        $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
        $pdo = new PDO($dsn, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        
        $sql = "
            SELECT table_schema, table_name, column_name 
            FROM information_schema.columns 
            WHERE column_name ILIKE '%queue%' 
               OR column_name ILIKE '%kpi%' 
               OR column_name ILIKE '%metric%'
               OR column_name ILIKE '%insight%'
        ";
        
        $results = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        
        if ($results) {
            print_r($results);
        } else {
            echo "No matching columns found.\n";
        }
        
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
}

searchColumns('suporte_hub');
searchColumns('suporte-nao-apagar');
?>
