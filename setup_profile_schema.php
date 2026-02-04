<?php
// setup_profile_schema.php
require_once 'includes/db_connection.php';

try {
    $pdo = getDBConnection();
    if (!$pdo) {
        die("Erro ao conectar no banco.\n");
    }

    echo "Verificando tabela 'users'...\n";

    // 1. Add profile_image
    try {
        $pdo->exec("ALTER TABLE users ADD COLUMN profile_image TEXT NULL");
        echo "[SUCCESS] Coluna 'profile_image' adicionada.\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column') !== false || strpos($e->getMessage(), 'already exists') !== false) {
            echo "[INFO] Coluna 'profile_image' já existe.\n";
        } else {
            echo "[ERROR] Erro ao adicionar 'profile_image': " . $e->getMessage() . "\n";
        }
    }

    // 2. Add nickname
    try {
        $pdo->exec("ALTER TABLE users ADD COLUMN nickname VARCHAR(50) NULL");
        echo "[SUCCESS] Coluna 'nickname' adicionada.\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column') !== false || strpos($e->getMessage(), 'already exists') !== false) {
            echo "[INFO] Coluna 'nickname' já existe.\n";
        } else {
            echo "[ERROR] Erro ao adicionar 'nickname': " . $e->getMessage() . "\n";
        }
    }

    // 3. Add bio
    try {
        $pdo->exec("ALTER TABLE users ADD COLUMN bio TEXT NULL");
        echo "[SUCCESS] Coluna 'bio' adicionada.\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column') !== false || strpos($e->getMessage(), 'already exists') !== false) {
            echo "[INFO] Coluna 'bio' já existe.\n";
        } else {
            echo "[ERROR] Erro ao adicionar 'bio': " . $e->getMessage() . "\n";
        }
    }

    echo "Atualização de schema concluída.\n";

} catch (Exception $e) {
    echo "Erro fatal: " . $e->getMessage() . "\n";
}
?>
