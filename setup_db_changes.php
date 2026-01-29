<?php
// setup_db_changes.php
// Script to apply database schema changes for Online Users feature

require_once 'includes/db_connection.php';

echo "<h1>Setup: Database Updates</h1>";
echo "<pre>";

try {
    $pdo = getDBConnection();
    if (!$pdo) {
        throw new Exception("Could not connect to database.");
    }

    echo "Connected to database.\n";

    // 1. Add last_seen column to users table
    echo "Checking 'last_seen' column in 'users' table...\n";
    
    // Check if column exists
    $stmt = $pdo->prepare("
        SELECT column_name 
        FROM information_schema.columns 
        WHERE table_name='users' AND column_name='last_seen';
    ");
    $stmt->execute();
    
    if ($stmt->rowCount() == 0) {
        echo "Column 'last_seen' does not exist. Adding...\n";
        $pdo->exec("ALTER TABLE users ADD COLUMN last_seen TIMESTAMP NULL;");
        echo "SUCCESS: Column 'last_seen' added.\n";
    } else {
        echo "Column 'last_seen' already exists. Skipping.\n";
    }

    echo "\nAll checks completed.\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

echo "</pre>";
?>
