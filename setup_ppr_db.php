<?php
// setup_ppr_db.php
require_once 'includes/db_connection.php';

$pdo = getDBConnection();

if (!$pdo) {
    die("Falha na conexão com o banco de dados.");
}

echo "<h3>Configurando Banco de Dados PPR</h3>";

$commands = [
    // Table: ppr_metrics (Definitions of rows)
    "CREATE TABLE IF NOT EXISTS ppr_metrics (
        id SERIAL PRIMARY KEY,
        key VARCHAR(50) UNIQUE NOT NULL, -- e.g. 'okr1_conteudos', 'okr2_nps'
        name VARCHAR(255) NOT NULL,
        okr_group INT NOT NULL, -- 1 or 2
        type VARCHAR(20) NOT NULL, -- 'numeric', 'percent', 'time'
        target_description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",

    // Table: ppr_values (Monthly data)
    "CREATE TABLE IF NOT EXISTS ppr_values (
        id SERIAL PRIMARY KEY,
        metric_id INT REFERENCES ppr_metrics(id),
        year INT NOT NULL,
        month INT NOT NULL, -- 1-12
        value TEXT, -- flexible storage
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE (metric_id, year, month)
    )",
    
    // Seed initial metrics if not exist
    "INSERT INTO ppr_metrics (key, name, okr_group, type, target_description)
     VALUES 
     ('okr1_conteudo', 'Conteúdos externos', 1, 'numeric', 'Participar pelo menos de um conteúdo por mês.'),
     ('okr1_doc', 'Documentação EAD/Wiki', 1, 'numeric', 'Criar ou dar manutenção em pelo menos 6 documentações.'),
     ('okr2_nps', 'NPS - Zona de Excelência', 2, 'percent', 'Alcançar NPS de 80% ou superior.'),
     ('okr2_tmc', 'Tempo médio Chats', 2, 'time', 'Ter um tempo médio de solução de chats inferior a 26min/mês.')
     ON CONFLICT (key) DO NOTHING;"
];

foreach ($commands as $sql) {
    try {
        $pdo->exec($sql);
        echo "Comando executado com sucesso: " . substr($sql, 0, 50) . "...<br>";
    } catch (PDOException $e) {
        echo "<span style='color:red'>Erro: " . $e->getMessage() . "</span><br>";
    }
}

echo "<br><strong>Configuração concluída.</strong>";
?>
