<?php
// fix_system_v2.php - Plain Text Output
header('Content-Type: text/plain');

echo "--- START DIAGNOSTIC ---\n";

// 1. DATABASE
echo "[1] Testing Database Connection...\n";

function testConn($host, $port, $user, $pass, $db)
{
    echo "  > Connecting to host='$host' user='$user' db='$db' app=postgres... ";
    try {
        $dsn = "pgsql:host=$host;port=$port;dbname=$db";
        $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        echo "SUCCESS!\n";
        return $pdo;
    } catch (PDOException $e) {
        echo "FAIL: " . $e->getMessage() . "\n";
        return null;
    }
}

// Env vars or defaults
$host = getenv('DB_HOST') ?: 'dbserver';
$port = getenv('DB_PORT') ?: '5432';
$dbname = getenv('DB_NAME') ?: 'suporte_hub';
$user = getenv('DB_USER') ?: 'postgres';
$password = getenv('DB_PASSWORD') ?: 'postgres';

$pdo = testConn($host, $port, $user, $password, $dbname);

if (!$pdo) {
    echo "  > Retrying with 'localhost'...\n";
    $pdo = testConn('localhost', $port, $user, $password, $dbname);
}

if ($pdo) {
    echo "[1] DB Connection OK. Updating Password...\n";
    // Reset Password
    $targetUser = 'Saulero';
    $newPass = '123456';
    $hash = password_hash($newPass, PASSWORD_DEFAULT);

    // Find user (case insensitive)
    $stmt = $pdo->prepare("SELECT id, username FROM users WHERE LOWER(username) = LOWER(:u)");
    $stmt->execute([':u' => $targetUser]);
    $u = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($u) {
        $up = $pdo->prepare("UPDATE users SET password_hash = :h WHERE id = :id");
        $up->execute([':h' => $hash, ':id' => $u['id']]);
        echo "  > PASSWORD RESET SUCCESS for user '{$u['username']}'.\n";
    } else {
        echo "  > USER '$targetUser' NOT FOUND.\n";
        // List users
        $list = $pdo->query("SELECT username FROM users")->fetchAll(PDO::FETCH_COLUMN);
        echo "  > Available users: " . implode(', ', $list) . "\n";
    }
} else {
    echo "[1] CRITICAL: COULD NOT CONNECT TO DATABASE.\n";
}

echo "--- END DIAGNOSTIC ---\n";
?>