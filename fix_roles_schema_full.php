<?php
require_once 'includes/db_connection.php';

function addColumnIfNotExists($pdo, $table, $column, $definition)
{
    echo "Verifying column '$column' in table '$table'...\n";
    try {
        // Try simple check
        $stmt = $pdo->query("SELECT $column FROM $table LIMIT 1");
        echo " - Column checks out.\n";
    } catch (Exception $e) {
        echo " - Column missing or other error. Attempting to add...\n";
        try {
            $pdo->exec("ALTER TABLE $table ADD COLUMN $column $definition");
            echo " - Success: Added column '$column'.\n";
        } catch (Exception $e2) {
            echo " - Failed to add: " . $e2->getMessage() . "\n";
        }
    }
}

try {
    $pdo = getDBConnection();
    if (!$pdo)
        die("Erro de conexão.\n");

    echo "Fixing table ROLES schema...\n";

    addColumnIfNotExists($pdo, 'roles', 'description', 'TEXT');
    addColumnIfNotExists($pdo, 'roles', 'is_system', 'BOOLEAN DEFAULT FALSE');
    addColumnIfNotExists($pdo, 'roles', 'capabilities', "TEXT DEFAULT '[]'");

    echo "Done.\n";

} catch (Exception $e) {
    echo "Fatal Error: " . $e->getMessage() . "\n";
}
?>