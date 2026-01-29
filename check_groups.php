<?php
require_once 'includes/db_connection.php';
$pdo = getDBConnection();
$groups = $pdo->query("SELECT DISTINCT okr_group FROM ppr_metrics")->fetchAll(PDO::FETCH_COLUMN);
echo "Groups: " . implode(", ", $groups) . "\n";
?>
