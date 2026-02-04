<?php
// scripts/inspect_dashboard_table.php
require_once __DIR__ . '/../includes/db_connection.php';

$pdo = getDBConnection();
if (!$pdo) die("Failed");

$stmt = $pdo->query("
    SELECT table_name 
    FROM information_schema.tables 
    WHERE table_schema = 'public'
");
$tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

echo "Tables in 'public' schema:\n";
print_r($tables);
?>
