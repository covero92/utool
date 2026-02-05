<?php
require_once 'includes/db_connection.php';

try {
    $pdo = getDBConnection();
    if (!$pdo)
        die("Erro de conexão.\n");

    echo "Tentando adicionar coluna 'capabilities'...\n";

    // Attempt to add column directly. 
    // IF NOT EXISTS is supported in Postgres 9.6+. 
    // If older, we catch the error 42701 (duplicate_column).
    try {
        $pdo->exec("ALTER TABLE roles ADD COLUMN IF NOT EXISTS capabilities TEXT DEFAULT '[]'");
        echo "Comando executado com sucesso (IF NOT EXISTS).\n";
    } catch (PDOException $e) {
        // Fallback for older Postgres or other errors
        if ($e->getCode() == '42701') {
            echo "Coluna já existe (Exceção capturada).\n";
        } else {
            // Try without IF NOT EXISTS (very old postgres)
            try {
                $pdo->exec("ALTER TABLE roles ADD COLUMN capabilities TEXT DEFAULT '[]'");
                echo "Coluna adicionada.\n";
            } catch (PDOException $e2) {
                echo "Erro ao adicionar (pode já existir): " . $e2->getMessage() . "\n";
            }
        }
    }

    echo "Verificando se a coluna realmente existe...\n";
    // Verify
    try {
        $stmt = $pdo->query("SELECT capabilities FROM roles LIMIT 1");
        echo "Teste de SELECT bem sucedido! Coluna existe.\n";
    } catch (PDOException $e) {
        echo "FALHA FINAL: A coluna não parece existir. Erro: " . $e->getMessage() . "\n";
    }

} catch (Exception $e) {
    echo "Erro Geral: " . $e->getMessage() . "\n";
}
?>