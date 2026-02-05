<?php
require_once 'includes/db_connection.php';

try {
    $pdo = getDBConnection();
    if (!$pdo)
        die("Erro de conexão.\n");

    echo "Verificando tabela roles...\n";

    // Check if column exists
    $check = $pdo->query("SELECT column_name FROM information_schema.columns WHERE table_name='roles' AND column_name='capabilities'");
    if (!$check->fetch()) {
        echo "Coluna 'capabilities' faltando. Adicionando...\n";
        $pdo->exec("ALTER TABLE roles ADD COLUMN capabilities TEXT DEFAULT '[]'");
        echo "Coluna adicionada com sucesso.\n";
    } else {
        echo "Coluna 'capabilities' já existe.\n";
    }

} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
?>