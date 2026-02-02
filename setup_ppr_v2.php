<?php
// setup_ppr_v2.php
require_once 'includes/db_connection.php';
$pdo = getDBConnection();

echo "<h1>Setup PPR V2</h1>";

try {
    $pdo->beginTransaction();

    // 1. Create Audit Log Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS ppr_audit_log (
        id SERIAL PRIMARY KEY,
        user_id INT, -- Nullable if system or unknown
        user_name VARCHAR(100),
        action VARCHAR(50) NOT NULL, -- UPDATE, CREATE, DELETE
        entity_type VARCHAR(50), -- ppr_value, ppr_metric
        entity_id INT,
        old_value TEXT,
        new_value TEXT,
        details TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    echo "Audit Log table created.<br>";

    // 2. Modify Metrics to be Year-Specific
    // Check if 'year' column exists
    $cols = $pdo->query("SELECT column_name FROM information_schema.columns WHERE table_name = 'ppr_metrics'")->fetchAll(PDO::FETCH_COLUMN);
    
    if (!in_array('year', $cols)) {
        echo "Migrating ppr_metrics to versioned schema...<br>";
        
        // A. Rename current table to backup/old
        // We can't easily rename if constraints exist, but let's try to alter in place.
        // Actually, renaming is safer for data preservation during dev.
        // But let's try adding column and fixing keys.
        
        $pdo->exec("ALTER TABLE ppr_metrics ADD COLUMN year INT DEFAULT 2025"); 
        // Defaulting to 2025 because most current metrics are 2025. 
        // We will fix 2024 ones later.
        
        // B. Drop Unique Key constraint on 'key'
        // Need to find constraint name. Usually ppr_metrics_key_key
        try {
            $pdo->exec("ALTER TABLE ppr_metrics DROP CONSTRAINT ppr_metrics_key_key");
        } catch (Exception $e) {
            // Might be named distinct, ignore if fail but check manually
            echo "Warning dropping constraint: " . $e->getMessage() . "<br>";
        }
        
        // C. Add Unique Constraint on (key, year)
        $pdo->exec("ALTER TABLE ppr_metrics ADD CONSTRAINT ppr_metrics_key_year_key UNIQUE (key, year)");
        
        echo "Schema updated (Column year added, Unique constraint changed).<br>";

        // D. Seed/Duplicate metrics for 2024
        // Get all current metrics (which are now 2025)
        $stmt = $pdo->query("SELECT * FROM ppr_metrics WHERE year = 2025");
        $metrics = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $metrics2024 = [
            'okr1_conteudo' => 'Participar pelo menos de um conteúdo por mês.',
            'okr1_doc' => 'Criar ou dar manutenção em pelo menos 6 documentações/mês.',
            'okr2_nps' => 'Alcançar NPS de 80% ou superior.',
            'okr2_tmc' => 'Tempo médio de solução < 26min/mês.'
        ];

        $insert = $pdo->prepare("INSERT INTO ppr_metrics (key, name, okr_group, type, target_description, year) VALUES (:key, :name, :okr, :type, :desc, 2024) ON CONFLICT (key, year) DO NOTHING");
        
        foreach ($metrics as $m) {
            // If this metric key existed in 2024 config
            if (isset($metrics2024[$m['key']])) {
                $desc = $metrics2024[$m['key']];
                $insert->execute([
                    ':key' => $m['key'],
                    ':name' => $m['name'],
                    ':okr' => $m['okr_group'],
                    ':type' => $m['type'],
                    ':desc' => $desc
                ]);
                echo "Created 2024 version of {$m['key']}<br>";
            }
        }
        
        // Logic check: 'okr1_conteudo' was in 2024 but NOT in 2025 (replaced by engajamento).
        // But my previous 'import_ppr_2025' script ADDED 'okr1_engajamento' to ppr_metrics (which became year=2025).
        // 'okr1_conteudo' was in the INITIAL seed. So it is currently in DB with year=2025 (default).
        // But it shouldn't be in 2025! It should be 2024 only.
        
        // Fix years for specific keys
        $pdo->exec("UPDATE ppr_metrics SET year = 2024 WHERE key IN ('okr1_conteudo', 'okr2_nps', 'okr2_tmc') AND year = 2025");
        
        // Now 'okr1_doc' is in 2025 (default). We also inserted a 2024 version above.
        // 'okr1_engajamento' is in 2025 (default).
        
        echo "Metric years adjusted.<br>";
        
        // E. Fix ppr_values References
        // ppr_values has (metric_id, year).
        // The metric_id currently points to the row that existed.
        // If we updated that row's year to 2024 (e.g. okr1_conteudo), but ppr_values has year=2025... mismatch?
        // Wait, ppr_values for 2025 should point to 2025 metrics.
        // ppr_values for 2024 should point to 2024 metrics.
        
        // Currently, all ppr_values might be pointing to whatever ID they had.
        // We need to ensure consistency.
        
        // Update ppr_values v
        // SET metric_id = (SELECT id FROM ppr_metrics m WHERE m.key = (SELECT key FROM ppr_metrics old WHERE old.id = v.metric_id) AND m.year = v.year)
        // This query requires that such a metric exists.
        
        // Let's run a fix query.
        // Fix ppr_values logic:
        // We know that ppr_metrics was just modified.
        // We need to make sure ppr_values.metric_id points to the correct year's metric.
        // But ppr_values has 'year' column.
        // So we can join on key?
        // Wait, ppr_values stores metric_id.
        // We need to find the metric Key from the current ID, then find the New ID for that Key + Year.
        
        $sql = "
            UPDATE ppr_values v
            SET metric_id = target.id
            FROM ppr_metrics current
            JOIN ppr_metrics target ON current.key = target.key
            WHERE v.metric_id = current.id
              AND target.year = v.year
              AND v.metric_id != target.id
        ";
        try {
            $pdo->exec($sql);
            echo "Values references updated.<br>";
        } catch (Exception $e) {
            echo "Update Values Error (Ignorable if redundant): " . $e->getMessage() . "<br>";
        }

    } else {
        echo "Schema already V2.<br>";
    }

    $pdo->commit();
    echo "<h3>Success!</h3>";

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo "<h1>Error: " . $e->getMessage() . "</h1>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>
