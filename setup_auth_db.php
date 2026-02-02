<?php
// setup_auth_db.php
require_once 'includes/db_connection.php';

$pdo = getDBConnection();

if (!$pdo) {
    die("Falha na conexão com o banco de dados. Verifique os logs.");
}

echo "Conectado ao PostgreSQL com sucesso.<br>";

// Create Users Table
$sql = "
CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role VARCHAR(20) NOT NULL DEFAULT 'user', -- 'user', 'support', 'admin'
    status VARCHAR(20) NOT NULL DEFAULT 'pending', -- 'pending', 'active', 'blocked'
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
";

try {
    $pdo->exec($sql);
    echo "Tabela 'users' verificada/criada.<br>";

    // Check if default admin exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = 'admin'");
    $stmt->execute();
    $count = $stmt->fetchColumn();

    if ($count == 0) {
        // Create default admin
        // Password: admin (Change immediately!)
        $passHash = password_hash('admin', PASSWORD_DEFAULT);
        $insert = $pdo->prepare("INSERT INTO users (username, password_hash, full_name, role, status) VALUES (:user, :pass, :name, 'admin', 'active')");
        $insert->execute([
            ':user' => 'admin',
            ':pass' => $passHash,
            ':name' => 'Administrador Padrão'
        ]);
        echo "Usuário 'admin' criado com sucesso (Senha: admin).<br>";
    } else {
        echo "Usuário 'admin' já existe.<br>";
    }

} catch (PDOException $e) {
    die("Erro ao configurar banco: " . $e->getMessage());
}
?>
