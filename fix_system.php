<?php
// fix_system.php
// A self-contained script to fix DB, Password, and Check Build
// Accessible via Browser to use Apache's PHP environment (with drivers)

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>System Repair Log</h1>";

// 1. Database Connection & Password Reset
echo "<h2>1. Database & Password</h2>";

function getDB()
{
    // Try env defaults first (same as include)
    $host = getenv('DB_HOST') ?: 'dbserver';
    $port = getenv('DB_PORT') ?: '5432';
    $dbname = getenv('DB_NAME') ?: 'suporte_hub';
    $user = getenv('DB_USER') ?: 'postgres';
    $password = getenv('DB_PASSWORD') ?: 'postgres';

    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";

    try {
        echo "Attempting connection to <strong>$host</strong>...<br>";
        $pdo = new PDO($dsn, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        echo "<span style='color:green'>[OK] Connection Successful!</span><br>";
        return $pdo;
    } catch (PDOException $e) {
        echo "<span style='color:red'>[FAIL] Connection Error: " . $e->getMessage() . "</span><br>";
        // Fallback attempt?
        if ($host === 'dbserver') {
            echo "Attempting fallback to <strong>localhost</strong>...<br>";
            try {
                $dsn = "pgsql:host=localhost;port=$port;dbname=$dbname";
                $pdo = new PDO($dsn, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
                echo "<span style='color:green'>[OK] Connection to localhost Successful!</span><br>";
                return $pdo;
            } catch (PDOException $ex) {
                echo "<span style='color:red'>[FAIL] Localhost also failed: " . $ex->getMessage() . "</span><br>";
            }
        }
        return null;
    }
}

$pdo = getDB();

if ($pdo) {
    $targetUser = 'Saulero';
    $newPass = '123456';
    $hash = password_hash($newPass, PASSWORD_DEFAULT);

    echo "Attempting to reset password for <strong>$targetUser</strong>...<br>";

    // Check user with case-insensitive search
    $stmt = $pdo->prepare("SELECT id, username FROM users WHERE LOWER(username) = LOWER(:user)");
    $stmt->execute([':user' => $targetUser]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $update = $pdo->prepare("UPDATE users SET password_hash = :hash WHERE id = :id");
        $update->execute([':hash' => $hash, ':id' => $user['id']]);
        echo "<span style='color:green'>[SUCCESS] Password changed for '{$user['username']}' to '$newPass'.</span><br>";
    } else {
        echo "<span style='color:red'>[FAIL] User '$targetUser' not found.</span><br>";
        $all = $pdo->query("SELECT username FROM users LIMIT 10")->fetchAll(PDO::FETCH_COLUMN);
        echo "Sample users found: " . implode(", ", $all) . "<br>";
    }
}

// 2. React Build Check
echo "<h2>2. React Module Build</h2>";
$distPath = __DIR__ . '/ppr_dashboard/dist';
$assetsPath = $distPath . '/assets';

if (is_dir($distPath)) {
    echo "Dist directory found.<br>";
    if (is_dir($assetsPath)) {
        $files = scandir($assetsPath);
        $jsFound = false;
        $cssFound = false;
        echo "Files in assets:<br><ul>";
        foreach ($files as $f) {
            if ($f === '.' || $f === '..')
                continue;
            echo "<li>$f</li>";
            if (strpos($f, '.js') !== false)
                $jsFound = true;
            if (strpos($f, '.css') !== false)
                $cssFound = true;
        }
        echo "</ul>";

        if ($jsFound && $cssFound) {
            echo "<span style='color:green'>[OK] Build looks complete (JS and CSS found).</span><br>";
        } else {
            echo "<span style='color:orange'>[WARNING] Build might be incomplete (JS or CSS missing).</span><br>";
        }
    } else {
        echo "<span style='color:red'>[FAIL] Assets directory missing! npm run build failed?</span><br>";
    }
} else {
    echo "<span style='color:red'>[FAIL] 'dist' directory missing. Build has not run or failed.</span><br>";
}

// 3. Module Permissions Check
echo "<h2>3. Module Permissions Check</h2>";
if ($pdo) {
    $stmt = $pdo->query("SELECT * FROM card_permissions WHERE card_slug = 'ppr'");
    $perms = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (count($perms) > 0) {
        echo "Permissions found for 'ppr':<br><pre>" . print_r($perms, true) . "</pre>";
    } else {
        echo "No specific restrictions found for 'ppr' in DB (Standard behavior: Visible).<br>";
    }
}
?>