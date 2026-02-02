<?php
// setup_return_db.php

$dbDir = __DIR__ . '/database';
if (!is_dir($dbDir)) {
    mkdir($dbDir, 0777, true);
}

$dbPath = $dbDir . '/utool_retorno.sqlite';

try {
    $pdo = new PDO("sqlite:" . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1. Table: Bank Occurrences (The dictionary)
    $pdo->exec("CREATE TABLE IF NOT EXISTS bank_occurrences (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        bank_code TEXT NOT NULL,
        cnab_type TEXT NOT NULL,
        occurrence_code TEXT NOT NULL,
        error_code TEXT,
        description TEXT NOT NULL,
        UNIQUE(bank_code, cnab_type, occurrence_code, error_code)
    )");

    // 2. Table: UniPlus Resolutions (The intelligence)
    $pdo->exec("CREATE TABLE IF NOT EXISTS uniplus_resolutions (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        occurrence_id INTEGER NOT NULL,
        ui_title TEXT NOT NULL,
        ui_description TEXT,
        action_screen TEXT,
        action_field TEXT,
        action_instruction TEXT,
        FOREIGN KEY (occurrence_id) REFERENCES bank_occurrences(id)
    )");

    echo "Tables created successfully.\n";

    // 3. Seed Initial Data (Sicoob Examples)
    // Sicoob Code 02 = Entrada Confirmada (Not an error, just status)
    // Sicoob Code 03 = Entrada Rejeitada (Error)

    // Insert 'Rejeição' Occurrence
    $stmt = $pdo->prepare("INSERT OR IGNORE INTO bank_occurrences (bank_code, cnab_type, occurrence_code, error_code, description) VALUES (?, ?, ?, ?, ?)");

    // Config: Sicoob (756), CNAB240, Ocorrencia 03 (Rejeição), Erro A9 (Tarifa Diferenciada - Example)
    $stmt->execute(['756', '240', '03', 'A9', 'Tarifa Diferenciada']);
    $occId = $pdo->lastInsertId();

    if ($occId) {
        // Insert Resolution for this error
        $stmtRes = $pdo->prepare("INSERT OR IGNORE INTO uniplus_resolutions (occurrence_id, ui_title, ui_description, action_screen, action_field, action_instruction) VALUES (?, ?, ?, ?, ?, ?)");
        $stmtRes->execute([
            $occId,
            'Erro de Tarifa',
            'O banco rejeitou a tarifa informada.',
            'Cadastro de Convênio',
            'Valor Tarifa',
            'Verifique se a tarifa do boleto bate com a negociada na agência.'
        ]);
        echo "Seeded resolution for Sicoob A9.\n";
    }

} catch (PDOException $e) {
    echo "DB Error: " . $e->getMessage();
}
