<?php
// includes/db_connection.php

function getDBConnection() {
    // Priority: Environment Variables -> Defaults
    $host = getenv('DB_HOST') ?: 'dbserver';
    $port = getenv('DB_PORT') ?: '5432';
    $dbname = getenv('DB_NAME') ?: 'suporte_hub';
    $user = getenv('DB_USER') ?: 'postgres';
    $password = getenv('DB_PASSWORD') ?: 'postgres';

    try {
        $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
        $pdo = new PDO($dsn, $user, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
        return $pdo;
    } catch (PDOException $e) {
        // Log error instead of displaying freely in production
        error_log("DB Connection Error: " . $e->getMessage());
        return null;
    }
}
?>
