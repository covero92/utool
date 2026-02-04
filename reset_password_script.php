<?php
// reset_password_script.php
require_once 'includes/db_connection.php';

$pdo = getDBConnection();

if (!$pdo) {
    echo "Falha ao conectar no banco de dados.\n";
    exit(1);
}

$username = 'Saulero';
$newPassword = '123456';
$hash = password_hash($newPassword, PASSWORD_DEFAULT);

try {
    // Check if user exists
    $check = $pdo->prepare("SELECT id FROM users WHERE username = :user");
    $check->execute([':user' => $username]);
    $user = $check->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $stmt = $pdo->prepare("UPDATE users SET password_hash = :hash WHERE username = :user");
        $stmt->execute([':hash' => $hash, ':user' => $username]);
        echo "Senha alterada com sucesso para o usuário '$username'!\n";
    } else {
        echo "Usuário '$username' não encontrado. Tentando 'saulero' (minúsculo)...\n";
        $usernameLower = strtolower($username);

        $stmt = $pdo->prepare("UPDATE users SET password_hash = :hash WHERE username = :user");
        $stmt->execute([':hash' => $hash, ':user' => $usernameLower]);

        if ($stmt->rowCount() > 0) {
            echo "Senha alterada com sucesso para o usuário '$usernameLower'!\n";
        } else {
            echo "ERRO: Usuário não encontrado no banco.\n";
            // Debug: List users
            $all = $pdo->query("SELECT username FROM users")->fetchAll(PDO::FETCH_COLUMN);
            echo "Usuários disponíveis: " . implode(", ", $all) . "\n";
        }
    }

} catch (PDOException $e) {
    echo "Erro SQL: " . $e->getMessage() . "\n";
}
?>