<?php
require_once 'includes/portal_auth.php';
$auth = new PortalAuth();
$pdo = $auth->getPDO();

$stmt = $pdo->query("SELECT column_name, data_type FROM information_schema.columns WHERE table_name = 'users';");
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Columns in users table:\n";
foreach ($columns as $col) {
    echo $col['column_name'] . " (" . $col['data_type'] . ")\n";
}
?>
