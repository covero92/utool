<?php
// setup_dashboard_table.php
require_once 'includes/db_connection.php';

try {
    $pdo = getDBConnection();
    if (!$pdo) die("Erro de conexão.");

    $sql = "
    CREATE TABLE IF NOT EXISTS dashboard (
        id SERIAL PRIMARY KEY,
        queues JSONB,
        kpi JSONB,
        insights JSONB,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );
    ";
    
    $pdo->exec($sql);
    echo "Tabela 'dashboard' verificada/criada com sucesso.\n";

    // Insert dummy data if empty
    $count = $pdo->query("SELECT COUNT(*) FROM dashboard")->fetchColumn();
    if ($count == 0) {
        $dummy = [
            'queues' => json_encode(['pdv' => 5, 'fiscal' => 2, 'retaguarda' => 12, 'triagem' => 3]),
            'kpi' => json_encode(['chatsAtendidos' => 150, 'totalTickets' => 45, 'tme' => '00:05', 'tma' => '00:15']),
            'insights' => json_encode(['avgHourly' => 12.5, 'systems' => []])
        ];
        
        $stmt = $pdo->prepare("INSERT INTO dashboard (queues, kpi, insights) VALUES (:queues, :kpi, :insights)");
        $stmt->execute($dummy);
        echo "Dados de exemplo inseridos.\n";
    }

} catch (PDOException $e) {
    die("Erro: " . $e->getMessage());
}
?>
