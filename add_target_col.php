<?php
require_once 'includes/db_connection.php';
$pdo = getDBConnection();

// Check if column exists
$stmt = $pdo->query("SELECT column_name FROM information_schema.columns WHERE table_name = 'ppr_metrics' AND column_name = 'target_value'");
if ($stmt->fetch()) {
    echo "Column target_value already exists.<br>";
} else {
    try {
        $pdo->exec("ALTER TABLE ppr_metrics ADD COLUMN target_value DECIMAL(10,2) DEFAULT 0");
        echo "Column target_value added successfully.<br>";
    } catch (PDOException $e) {
        echo "Error adding column: " . $e->getMessage() . "<br>";
    }
}
echo "Schema Update Complete.";
