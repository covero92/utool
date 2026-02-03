<?php
// scripts/list_dbs.php
require_once __DIR__ . '/../includes/db_connection.php';

$pdo = getDBConnection();
if (!$pdo) die("Failed");

$stmt = $pdo->query("SELECT datname FROM pg_database WHERE datistemplate = false");
$dbs = $stmt->fetchAll(PDO::FETCH_COLUMN);

echo "Databases:\n";
print_r($dbs);
?>
