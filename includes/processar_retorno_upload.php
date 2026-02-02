<?php
// BUFFERING: Start buffering to catch any warnings/drifting output
ob_start();

function debug_log($msg)
{
    file_put_contents(__DIR__ . '/debug_upload.log', date('Y-m-d H:i:s') . " - $msg\n", FILE_APPEND);
}

debug_log("Starting request processing...");

// Disable display_errors to prevent PHP warnings breaking JSON
ini_set('display_errors', 0);
error_reporting(E_ALL); // Log errors, don't display them

header('Content-Type: application/json');

try {
    debug_log("Loading portal_auth.php...");
    require_once 'portal_auth.php';

    debug_log("Loading GenericCnabParser.php...");
    require_once 'GenericCnabParser.php';

    debug_log("Loading db_connection.php...");
    require_once 'db_connection.php'; // Ensure we have DB access
    debug_log("Dependencies loaded successfully.");

    if (!isLoggedIn()) {
        debug_log("User not logged in.");
        // For now allowing dev tests if auth is commented out elsewhere, but essentially standard:
        // echo json_encode(['error' => 'Não autenticado']);
        // exit;
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método inválido');
    }

    if (!isset($_FILES['remessaFile']) || $_FILES['remessaFile']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Erro no upload do arquivo');
    }

    $file = $_FILES['remessaFile'];
    $tmpPath = $file['tmp_name'];
    $originalName = $file['name'];
    $banckCode = !empty($_POST['bank_code']) ? $_POST['bank_code'] : null;

    debug_log("File uploaded: $originalName to $tmpPath");

    // Setup DB Connection for Enrichment
    $dbPath = __DIR__ . '/../database/utool_retorno.sqlite';
    debug_log("Connecting to SQLite at $dbPath");

    $pdoCtx = new PDO("sqlite:" . $dbPath);
    $pdoCtx->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $parser = new GenericCnabParser($pdoCtx);
    debug_log("Starting parseFile...");
    $result = $parser->parseFile($tmpPath);
    debug_log("Parse complete.");

    if (isset($result['error'])) {
        ob_clean();
        echo json_encode(['success' => false, 'error' => $result['error']]);
    } else {
        // Adapter: Map GenericCnabParser structure to what showResults() expects
        $response = [
            'success' => true,
            'data' => [
                'filename' => $originalName,
                'bank_in_file' => $result['metadata']['code'] ?? '???',
                'type' => "CNAB " . ($result['metadata']['layout'] ?? 'Desconhecido') . " (Retorno)",
                'total_lines' => count($result['lines']),
                'preview_lines' => $result['lines'] // Ensure this matches line structure
            ]
        ];

        // CLEAN BUFFER: Discrad any previous warnings/output
        ob_clean();
        debug_log("Success. Sending JSON.");
        echo json_encode($response);
    }

} catch (Throwable $e) {
    // Return Error JSON
    if (ob_get_length())
        ob_clean();
    debug_log("FATAL ERROR: " . $e->getMessage());
    // Flush buffer (though we cleaned it, just in case)
    ob_end_flush();
}
