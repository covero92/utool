<?php
require_once 'portal_auth.php';
require_once 'CnabAnalyzer.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    // echo json_encode(['error' => 'Não autenticado']);
    // exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Método inválido']);
    exit;
}

if (!isset($_FILES['remessaFile']) || $_FILES['remessaFile']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['error' => 'Erro no upload do arquivo']);
    exit;
}

$file = $_FILES['remessaFile'];
$tmpPath = $file['tmp_name'];
$originalName = $file['name'];
// Check if bank_code was passed (leitura_remessa.php doesn't seem to pass it in JS? 
// Checking handleFileSelect and submit... 
// JS creates FormData(this). If input name="bank_code" exists in form...
// leitura_remessa.php: <form id="uploadRemessaForm"> <input type="file" ...> 
// No bank code input visible in form. So it's typically null.
// But some old logic might rely on it. CnabAnalyzer signature: analyzeFile($filePath, $originalName, $bankCode = null)
// So we extract it if present, otherwise null.
$bankCode = !empty($_POST['bank_code']) ? $_POST['bank_code'] : null;

try {
    $analyzer = new CnabAnalyzer();
    $result = $analyzer->analyzeFile($tmpPath, $originalName, $bankCode);

    if (isset($result['error'])) {
        echo json_encode(['success' => false, 'error' => $result['error']]);
    } else {
        echo json_encode(['success' => true, 'data' => $result]);
    }

} catch (Exception $e) {
    echo json_encode(['error' => 'Erro ao analisar arquivo: ' . $e->getMessage()]);
}
