<?php
// setup_roles_db.php
require_once 'includes/db_connection.php';

try {
    $pdo = getDBConnection();
    if (!$pdo) die("Erro ao conectar no banco.");

    echo "Iniciando migração de Roles...\n";

    // 1. Create ROLES table
    $sqlRoles = "CREATE TABLE IF NOT EXISTS roles (
        id SERIAL PRIMARY KEY,
        name VARCHAR(50) NOT NULL UNIQUE,
        description TEXT,
        is_system BOOLEAN DEFAULT FALSE
    )";
    $pdo->exec($sqlRoles);
    echo "Tabela 'roles' verificada/criada.\n";

    // 2. Insert Default Roles if they don't exist
    $defaultRoles = [
        ['name' => 'Administrador', 'desc' => 'Acesso total ao sistema', 'sys' => true],
        ['name' => 'Suporte', 'desc' => 'Acesso a ferramentas de suporte e intranet', 'sys' => false],
        ['name' => 'Usuário', 'desc' => 'Acesso básico de leitura', 'sys' => false]
    ];

    foreach ($defaultRoles as $role) {
        $stmt = $pdo->prepare("SELECT id FROM roles WHERE name = ?");
        $stmt->execute([$role['name']]);
        if (!$stmt->fetch()) {
            $insert = $pdo->prepare("INSERT INTO roles (name, description, is_system) VALUES (?, ?, ?)");
            $insert->execute([$role['name'], $role['desc'], $role['sys'] ? 1 : 0]);
            echo "Role '{$role['name']}' criada.\n";
        }
    }

    // 3. Add column role_id to users if not exists
    // Check if column exists
    $checkCol = $pdo->query("SELECT column_name FROM information_schema.columns WHERE table_name='users' AND column_name='role_id'");
    if (!$checkCol->fetch()) {
        $pdo->exec("ALTER TABLE users ADD COLUMN role_id INTEGER REFERENCES roles(id)");
        echo "Coluna 'role_id' adicionada na tabela 'users'.\n";
    }

    // 4. Migrate existing users (string role -> role_id)
    // Mapping: 'admin' -> 'Administrador', 'support' -> 'Suporte', others -> 'Usuário'
    
    // Get Role IDs
    $rolesMap = [];
    foreach($pdo->query("SELECT id, name FROM roles")->fetchAll() as $r) {
        $rolesMap[$r['name']] = $r['id'];
    }

    $users = $pdo->query("SELECT id, role FROM users WHERE role_id IS NULL")->fetchAll();
    foreach ($users as $u) {
        $oldRole = $u['role']; // admin, support, user
        $newRoleId = $rolesMap['Usuário']; // Fallback

        if ($oldRole === 'admin') $newRoleId = $rolesMap['Administrador'];
        if ($oldRole === 'support') $newRoleId = $rolesMap['Suporte'];
        
        $up = $pdo->prepare("UPDATE users SET role_id = ? WHERE id = ?");
        $up->execute([$newRoleId, $u['id']]);
        echo "Usuário ID {$u['id']} migrado de '$oldRole' para Role ID $newRoleId.\n";
    }

    echo "Migração concluída com sucesso!\n";

} catch (PDOException $e) {
    die("Erro na migração: " . $e->getMessage() . "\n");
}
