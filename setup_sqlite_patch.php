<?php
// setup_sqlite_patch.php
// Fixes missing columns in SQLite Users table and ensures functionality
require_once 'includes/db_connection.php';

$pdo = getDBConnection();
if (!$pdo)
    die("Falha DB");

echo "<h3>Patching Database...</h3>";

// 1. Check/Add role_id column
try {
    $res = $pdo->query("PRAGMA table_info(users)");
    $cols = $res->fetchAll(PDO::FETCH_COLUMN, 1);

    if (!in_array('role_id', $cols)) {
        echo "Adding 'role_id' column... ";
        $pdo->exec("ALTER TABLE users ADD COLUMN role_id INTEGER DEFAULT 1");
        echo "OK.<br>";
    } else {
        echo "'role_id' column exists.<br>";
    }

    if (!in_array('profile_image', $cols))
        $pdo->exec("ALTER TABLE users ADD COLUMN profile_image TEXT");
    if (!in_array('nickname', $cols))
        $pdo->exec("ALTER TABLE users ADD COLUMN nickname TEXT");
    if (!in_array('bio', $cols))
        $pdo->exec("ALTER TABLE users ADD COLUMN bio TEXT");
    if (!in_array('last_seen', $cols))
        $pdo->exec("ALTER TABLE users ADD COLUMN last_seen DATETIME");
    if (!in_array('capabilities', $cols))
        $pdo->exec("ALTER TABLE users ADD COLUMN capabilities TEXT");

} catch (PDOException $e) {
    echo "Schema Check Error: " . $e->getMessage() . "<br>";
}

// 2. Ensure Roles Exist
try {
    $count = $pdo->query("SELECT COUNT(*) FROM roles")->fetchColumn();
    if ($count == 0) {
        $roles = [
            ['Administrador', '["bypass_auth","manage_users","manage_roles","system_config","edit_tools","view_restricted","access_admin_panel"]'],
            ['Suporte', '["bypass_auth","view_restricted"]'],
            ['Usuário', '[]']
        ];
        $ins = $pdo->prepare("INSERT INTO roles (name, capabilities) VALUES (?, ?)");
        foreach ($roles as $r) {
            $ins->execute($r);
        }
        echo "Roles Created.<br>";
    }
} catch (PDOException $e) {
}

// 3. Fix/Create User 'Saulero'
$username = 'Saulero';
$newPass = '123456';
$hash = password_hash($newPass, PASSWORD_DEFAULT);

try {
    // Get Admin Role ID
    $roleId = $pdo->query("SELECT id FROM roles WHERE name = 'Administrador'")->fetchColumn() ?: 1;

    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user) {
        // Update
        $up = $pdo->prepare("UPDATE users SET password_hash = ?, role_id = ?, status = 'active' WHERE id = ?");
        $up->execute([$hash, $roleId, $user['id']]);
        echo "User '$username' updated (Pass: 123456, RoleID: $roleId).<br>";
    } else {
        // Insert
        $in = $pdo->prepare("INSERT INTO users (username, password_hash, full_name, role, status, role_id) VALUES (?, ?, ?, 'admin', 'active', ?)");
        $in->execute([$username, $hash, 'Saulo Morales', $roleId]);
        echo "User '$username' created.<br>";
    }
} catch (Exception $e) {
    echo "User Error: " . $e->getMessage();
}
?>