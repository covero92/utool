<?php
// setup_profile_schema.php
require_once 'includes/db_connection.php';

echo "Updating database schema for User Profile...\n";

$pdo = getDBConnection();
if (!$pdo) {
    die("Database connection failed.\n");
}

$columns = [
    'preferred_name' => 'VARCHAR(100)',
    'job_title' => 'VARCHAR(100)',
    'birth_date' => 'DATE',
    'bio' => 'TEXT'
];

foreach ($columns as $col => $type) {
    echo "Checking column '$col'...";
    try {
        // Simple check by selecting, if fails, add it
        $pdo->query("SELECT $col FROM users LIMIT 1");
        echo " Exists.\n";
    } catch (PDOException $e) {
        echo " Missing. Adding...\n";
        try {
            $pdo->exec("ALTER TABLE users ADD COLUMN $col $type");
            echo "Column '$col' added successfully.\n";
        } catch (PDOException $ex) {
            echo "Error adding '$col': " . $ex->getMessage() . "\n";
        }
    }
}

echo "Schema update complete.\n";
?>
