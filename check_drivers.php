<?php
header('Content-Type: text/plain');
echo "--- PDO DRIVERS ---\n";
print_r(PDO::getAvailableDrivers());

echo "\n--- ENV VARS ---\n";
echo "DB_TYPE: " . getenv('DB_TYPE') . "\n";
echo "DB_CONNECTION: " . getenv('DB_CONNECTION') . "\n";

echo "\n--- DB FILE CHECK ---\n";
$sqlite = __DIR__ . '/database/database.sqlite';
if (file_exists($sqlite)) {
    echo "SQLite DB Found at: $sqlite\n";
    echo "Size: " . filesize($sqlite) . " bytes\n";
} else {
    echo "No SQLite DB at default path.\n";
}
?>