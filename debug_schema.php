<?php
require_once 'includes/db_connection.php';
$pdo = getDBConnection();

if (!$pdo) { die("DB Connection Failed"); }

function describeTable($pdo, $table) {
    echo "Table: $table\n";
    $stmt = $pdo->prepare("
        SELECT column_name, data_type, is_nullable, column_default
        FROM information_schema.columns
        WHERE table_name = :table
        ORDER BY ordinal_position
    ");
    $stmt->execute([':table' => $table]);
    $cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($cols as $c) {
        echo "  - {$c['column_name']} ({$c['data_type']}) " . 
             ($c['is_nullable']=='YES'?'NULL':'NOT NULL') . 
             " Default: {$c['column_default']}\n";
    }
    echo "\n";
}

describeTable($pdo, 'users');
describeTable($pdo, 'roles');
describeTable($pdo, 'role_permissions'); // Checking if this exists
?>
