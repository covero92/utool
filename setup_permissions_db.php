<?php
// setup_permissions_db.php
require_once 'includes/db_connection.php';

try {
    $pdo = getDBConnection();
    if (!$pdo) {
        die("Erro ao conectar ao banco de dados.");
    }

    echo "Conectado ao banco.\n";

    // 1. Create card_permissions table
    $sql = "
    CREATE TABLE IF NOT EXISTS card_permissions (
        id SERIAL PRIMARY KEY,
        card_slug VARCHAR(50) NOT NULL,
        role_id INTEGER NOT NULL REFERENCES roles(id) ON DELETE CASCADE,
        can_view BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE(card_slug, role_id)
    );
    ";

    $pdo->exec($sql);
    echo "Tabela 'card_permissions' criada ou já existente.\n";

    // 2. Clear existing (optional, but good for idempotent setup if we want resets, maybe not for now)
    // $pdo->exec("TRUNCATE TABLE card_permissions");

    // 3. Seed initial permissions (Optional: By default, if no record, maybe allow? Or deny?
    // Let's decide: If no record exists, default is ALLOW for everyone or DENY?
    // Usually safest is DENY, but for migration ease, maybe ALLOW.
    // Let's stick to explicit records.
    
    // For now, just creating the table is enough. Logic will handle defaults.
    
    echo "Setup concluído com sucesso.\n";

} catch (PDOException $e) {
    die("Erro no setup: " . $e->getMessage());
}
?>
