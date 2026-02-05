<?php
header('Content-Type: text/plain');
echo "--- MYSQL PROBE ---\n";

$host = 'localhost';
$user = 'root';
$pass = ''; // Try empty first
$db = 'suporte_hub';

function tryMySQL($h, $u, $p, $d)
{
    try {
        echo "Trying $u@$h...";
        $conn = new PDO("mysql:host=$h;dbname=$d", $u, $p);
        echo " SUCCESS.\n";
        return $conn;
    } catch (PDOException $e) {
        echo " FAIL: " . $e->getMessage() . "\n";
        return null;
    }
}

$pdo = tryMySQL($host, $user, $pass, $db);
if (!$pdo) {
    // Try root with password 'root' (common in MAMP/older XAMPP)
    $pdo = tryMySQL($host, 'root', 'root', $db);
}

if ($pdo) {
    echo "Checking for 'users' table...\n";
    try {
        $stmt = $pdo->query("SELECT count(*) FROM users");
        $count = $stmt->fetchColumn();
        echo "Found 'users' table with $count records.\n";

        // Try to find Saulero
        $stmt = $pdo->query("SELECT id, username FROM users WHERE username LIKE 'Saulero%'");
        $u = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($u) {
            echo "User found: " . print_r($u, true) . "\n";

            // RESET PASSWORD HERE
            $newPass = '123456';
            $hash = password_hash($newPass, PASSWORD_DEFAULT);
            $up = $pdo->prepare("UPDATE users SET password_hash = :h WHERE id = :id");
            $up->execute([':h' => $hash, ':id' => $u['id']]);
            echo "PASSWORD RESET TO 123456 for MySQL User.\n";

        } else {
            echo "User Saulero not found in MySQL.\n";
        }

    } catch (Exception $e) {
        echo "Error querying users: " . $e->getMessage() . "\n";
    }
} else {
    echo "Could not connect to MySQL either.\n";
}
?>