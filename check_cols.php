<?php
require_once 'includes/db_connection.php';
$pdo = getDBConnection();
$stmt = $pdo->query("SELECT column_name, data_type FROM information_schema.columns WHERE table_name = 'ppr_metrics'");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
