<?php
// setup_sqlite.php
// Initializes the SQLite database and populates it with default users
require_once 'includes/db_connection.php';

$pdo = getDBConnection();

if (!$pdo) {
    die("Falha conexao DB.");
}

echo "<h3>Configurando Banco de Dados (SQLite)...</h3>";

// SQLite Syntax for Users
$sql = "
CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT UNIQUE NOT NULL,
    password_hash TEXT NOT NULL,
    full_name TEXT NOT NULL,
    role TEXT NOT NULL DEFAULT 'user',
    status TEXT NOT NULL DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    -- Add missing columns that might be expected by the app
    profile_image TEXT,
    nickname TEXT,
    bio TEXT,
    last_seen DATETIME,
    capabilities TEXT
);

CREATE TABLE IF NOT EXISTS card_permissions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    card_slug TEXT NOT NULL,
    role_id INTEGER,
    can_view BOOLEAN,
    UNIQUE(card_slug, role_id)
);

CREATE TABLE IF NOT EXISTS roles (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT UNIQUE,
    capabilities TEXT
);
";

try {
    $pdo->exec($sql);
    echo "Tabelas criadas com sucesso.<br>";

    // Initialize Roles
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
        echo "Roles padrão inseridas.<br>";
    }

    // Initialize Admin
    $count = $pdo->query("SELECT COUNT(*) FROM users WHERE username = 'admin'")->fetchColumn();
    if ($count == 0) {
        $passHash = password_hash('admin', PASSWORD_DEFAULT);
        $roleId = $pdo->query("SELECT id FROM roles WHERE name = 'Administrador'")->fetchColumn() ?: 1;
        $ins = $pdo->prepare("INSERT INTO users (username, password_hash, full_name, role, status, role_id) VALUES (?, ?, ?, 'admin', 'active', ?)");
        $ins->execute(['admin', $passHash, 'Administrador Padrão', $roleId]);
        echo "Usuário 'admin' criado.<br>";
    }

    // Initialize/Reset Saulero
    $username = 'Saulero';
    $newPass = '123456';
    $hash = password_hash($newPass, PASSWORD_DEFAULT);

    $check = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $check->execute([$username]);
    $user = $check->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $up = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
        $up->execute([$hash, $user['id']]);
        echo "Senha de '$username' redefinida para '123456'.<br>";
    } else {
        $roleId = $pdo->query("SELECT id FROM roles WHERE name = 'Administrador'")->fetchColumn() ?: 1; // Make him admin to see modules!
        $ins = $pdo->prepare("INSERT INTO users (username, password_hash, full_name, role, status, role_id) VALUES (?, ?, ?, 'admin', 'active', ?)");
        $ins->execute([$username, $hash, 'Saulo Morales', $roleId]);
        echo "Usuário '$username' criado com a senha '123456' e permissão de Admin.<br>";
    }

} catch (PDOException $e) {
    echo "Erro SQL: " . $e->getMessage();
}
?>