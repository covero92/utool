<?php
require_once 'includes/db_connection.php';
$pdo = getDBConnection();

echo "=== Metrics ===\n";
$metrics = $pdo->query("SELECT * FROM ppr_metrics")->fetchAll(PDO::FETCH_ASSOC);
print_r($metrics);

echo "\n=== Postgres Table Info ===\n";
$stmt = $pdo->query("SELECT column_name, data_type, is_nullable 
                     FROM information_schema.columns 
                     WHERE table_name = 'ppr_metrics'");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
?>
