<?php
// includes/db_connection.php

function getDBConnection()
{
    // Priority: Environment Variables -> Defaults
    $host = getenv('DB_HOST') ?: 'dbserver';
    $port = getenv('DB_PORT') ?: '5432';
    $dbname = getenv('DB_NAME') ?: 'suporte_hub';
    $user = getenv('DB_USER') ?: 'postgres';
    $password = getenv('DB_PASSWORD') ?: 'postgres';

    try {
        // Detect Driver
        $available = PDO::getAvailableDrivers();
        $pdo = null;

        // 1. Postgres
        if (in_array('pgsql', $available)) {
            $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
            $pdo = new PDO($dsn, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
        }
        // 2. Local fallback: SQLite (Preferred fallback if PG missing)
        elseif (in_array('sqlite', $available)) {
            $dbPath = __DIR__ . '/../database/database.sqlite';
            $dbDir = dirname($dbPath);
            if (!is_dir($dbDir))
                mkdir($dbDir, 0777, true);

            $dsn = "sqlite:$dbPath";
            $pdo = new PDO($dsn, null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);

            // Allow larger timeout for file locks
            $pdo->setAttribute(PDO::ATTR_TIMEOUT, 5);
        } else {
            // 3. MySQL fallback
            $dsn = "mysql:host=$host;port=3306;dbname=$dbname;charset=utf8mb4";
            if ($user === 'postgres')
                $user = 'root';
            if ($password === 'postgres')
                $password = '';
            $pdo = new PDO($dsn, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
        }

        return $pdo;
    } catch (PDOException $e) {
        // Log error instead of displaying freely in production
        error_log("DB Connection Error: " . $e->getMessage());
        return null;
    }
}
?>