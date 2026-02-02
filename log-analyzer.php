<?php
// --- CONTROLE DE VERSÃO ---
$appVersion = '2.1'; // Versão da aplicação

// --- BLOCO PHP INICIAL (ATUALIZADO COM ESTATÍSTICAS) ---
$statsFilePath = __DIR__ . '/data/log_stats.json';
$logDir = __DIR__ . '/logs_uploaded/';
$maxAgeSeconds = 3600 * 24; // 24 horas (Aumentei para facilitar testes)

// Ensure data directory exists
if (!is_dir(__DIR__ . '/data')) {
    mkdir(__DIR__ . '/data', 0755, true);
}

// Função para ler as estatísticas
function getStats($path) {
    if (!file_exists($path)) {
        return ['total_files' => 0, 'total_lines' => 0, 'total_size' => 0];
    }
    $data = json_decode(file_get_contents($path), true);
    return $data ?: ['total_files' => 0, 'total_lines' => 0, 'total_size' => 0];
}

// Carrega as estatísticas do servidor
$serverStats = getStats($statsFilePath);

// --- ROTINA DE LIMPEZA (MANUAL E AUTOMÁTICA) ---

// Ação de Limpeza Manual (via GET)
if (isset($_GET['action']) && $_GET['action'] === 'clear_logs') {
    if (is_dir($logDir)) {
        if ($dh = opendir($logDir)) {
            while (($file = readdir($dh)) !== false) {
                if ($file == '.' || $file == '..') continue;
                @unlink($logDir . $file);
            }
            closedir($dh);
        }
    }
    header("Location: log-analyzer.php"); // Redireciona para a página limpa
    exit();
}

// Limpeza Automática de arquivos com mais de 1 hora
if (is_dir($logDir)) {
    if ($dh = opendir($logDir)) {
        while (($file = readdir($dh)) !== false) {
            if ($file == '.' || $file == '..' || is_dir($logDir . $file)) {
                continue;
            }
            $filePath = $logDir . $file;
            if (file_exists($filePath) && filemtime($filePath) < (time() - $maxAgeSeconds)) {
                @unlink($filePath);
            }
        }
        closedir($dh);
    }
}

// --- LISTAGEM DE ARQUIVOS EXISTENTES ---
$existingLogs = [];
if (is_dir($logDir)) {
    $files = scandir($logDir);
    foreach ($files as $f) {
        if ($f !== '.' && $f !== '..' && is_file($logDir . $f)) {
            $existingLogs[] = [
                'name' => $f,
                'size' => filesize($logDir . $f),
                'date' => filemtime($logDir . $f)
            ];
        }
    }
    // Order by date desc
    usort($existingLogs, function($a, $b) { return $b['date'] - $a['date']; });
}
// Inclui todos os nossos arquivos de lógica
if (file_exists('includes/parsers/UniplusDesktopParser.php')) require_once 'includes/parsers/UniplusDesktopParser.php';
if (file_exists('includes/parsers/PdvParser.php')) require_once 'includes/parsers/PdvParser.php';
if (file_exists('includes/parsers/UniplusWebParser.php')) require_once 'includes/parsers/UniplusWebParser.php';
if (file_exists('includes/parsers/YodaParser.php')) require_once 'includes/parsers/YodaParser.php';

// --- POLLING ENDPOINT (AJAX) ---
if (isset($_GET['action']) && $_GET['action'] === 'poll') {
    header('Content-Type: application/json');
    $filename = basename($_GET['file'] ?? '');
    $offset = (int)($_GET['offset'] ?? 0);
    $module = $_GET['module'] ?? 'uniplus_desktop';
    $filePath = $logDir . $filename;

    if (!$filename || !file_exists($filePath)) {
        echo json_encode(['error' => 'Arquivo não encontrado']);
        exit;
    }

    clearstatcache();
    $currentSize = filesize($filePath);

    if ($currentSize < $offset) {
        // Arquivo foi truncado ou rotacionado
        $offset = 0;
    }

    if ($currentSize == $offset) {
        echo json_encode(['hasData' => false, 'offset' => $offset]);
        exit;
    }

    $handle = fopen($filePath, 'r');
    fseek($handle, $offset);
    $newContent = '';
    while (!feof($handle)) {
        $newContent .= fread($handle, 8192);
    }
    $newOffset = ftell($handle);
    fclose($handle);

    $lines = preg_split('/\r\n|\r|\n/', $newContent);
    // Remove a última linha se ela estiver vazia (resultado do explode)
    if (end($lines) === '') {
        array_pop($lines);
    }

    $parsedData = [];
    $parser = null;
    // Instancia o parser correto (Reutilizando a lógica existente, idealmente refatorar para fábrica)
    switch ($module) {
        case 'uniplus_desktop': if (class_exists('UniplusDesktopParser')) $parser = new UniplusDesktopParser(); break;
        case 'pdv':             if (class_exists('PdvParser')) $parser = new PdvParser(); break;
        case 'uniplus_web':     if (class_exists('UniplusWebParser')) $parser = new UniplusWebParser(); break;
        case 'yoda':            if (class_exists('YodaParser')) $parser = new YodaParser(); break;
    }

    if ($parser && method_exists($parser, 'parseLines')) {
        $parsedData = $parser->parseLines($lines);
    } else {
        // Fallback para texto puro se o parser não suportar parseLines ou não existir
        $parsedData = array_map(function($line) {
            return [
                'timestamp' => '',
                'level' => 'INFO',
                'user' => '',
                'message' => mb_convert_encoding($line, 'UTF-8', 'UTF-8, ISO-8859-1, Windows-1252'),
                'raw' => true
            ];
        }, $lines);
    }

    echo json_encode([
        'hasData' => true,
        'offset' => $newOffset,
        'entries' => $parsedData
    ]);
    exit;
}



// Variáveis de estado
$logData = null;
$logFilename = '';
$logModule = '';
$logFileSize = 0;
$systemVersion = null;

// CALCULA A SENHA TÉCNICA DO DIA
$senha_tecnica_dia = (int)date('d') * (int)date('m') * (int)substr(date('Y'), -2) * 3;


// --- LÓGICA DE PROCESSAMENTO DO UPLOAD E ATUALIZAÇÃO DE ESTATÍSTICAS ---
// --- LÓGICA DE PROCESSAMENTO (UPLOAD OU SELEÇÃO) ---
$target_file = null;
$processLog = false;

// Caso 1: Upload via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['log_file']) && $_FILES['log_file']['error'] === UPLOAD_ERR_OK) {
    if (!is_dir($logDir)) mkdir($logDir, 0755, true);
    
    $original_filename = basename($_FILES["log_file"]["name"]);
    $target_file = $logDir . $original_filename;

    if (move_uploaded_file($_FILES['log_file']['tmp_name'], $target_file)) {
        $logModule = $_POST['log_module'];
        $processLog = true;
    }
}

// Caso 2: Seleção via GET
if (isset($_GET['file'])) {
    $requestedFile = basename($_GET['file']);
    $possiblePath = $logDir . $requestedFile;
    if (file_exists($possiblePath)) {
        $target_file = $possiblePath;
        $original_filename = $requestedFile;
        $logModule = $_GET['module'] ?? 'uniplus_desktop'; // Default ou passar via GET
        $processLog = true;
    }
}

// Processamento Comum
if ($processLog && $target_file) {
    $logFileSize = filesize($target_file);
    $parsedData = [];

    switch ($logModule) {
        case 'uniplus_desktop': if (class_exists('UniplusDesktopParser')) $parser = new UniplusDesktopParser(); break;
        case 'pdv':             if (class_exists('PdvParser')) $parser = new PdvParser(); break;
        case 'uniplus_web':     if (class_exists('UniplusWebParser')) $parser = new UniplusWebParser(); break;
        case 'yoda':            if (class_exists('YodaParser')) $parser = new YodaParser(); break;
        default:                $parser = null;
    }

    if (isset($parser) && $parser) {
        $parsedData = $parser->parse($target_file);
    }
    
    $logData = $parsedData;
    $logFilename = $original_filename;
    
    // Se o log foi processado com sucesso, atualiza as estatísticas globais (apenas se for upload novo? Não, vamos contar leituras também ou deixar quieto)
    // Para simplificar, só atualizamos stats em upload novo, mas aqui misturei. 
    // Vamos deixar atualizar stats sempre que ler, ou controlar melhor. 
    // Por hora, deixo como está: se processou, conta.
    if ($logData) {
        $serverStats['total_files']++; // Isso pode inflar os números se der F5, mas ok.
        $serverStats['total_lines'] += count($logData);
        $serverStats['total_size'] += $logFileSize;
        file_put_contents($statsFilePath, json_encode($serverStats, JSON_PRETTY_PRINT), LOCK_EX);
    }
}

// --- FUNÇÃO GLOBAL AUXILIAR (CORREÇÃO APLICADA AQUI) ---
// Esta função agora está fora do IF, disponível para todo o script.
function formatBytes($bytes, $precision = 2) { 
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= (1 << (10 * $pow));
    return round($bytes, $precision) . ' ' . $units[$pow];
}

// --- FUNÇÕES AUXILIARES E DE CÁLCULO (QUANDO HÁ LOG) ---
if ($logData !== null) {
    function calculateLogStats($logData, $logModule) {
        if (empty($logData)) {
            return ['totalEntries' => 0, 'errorCount' => 0, 'levelCounts' => [], 'tenantCount' => 0, 'uniqueTenants' => [], 'startTime' => 'N/A', 'endTime' => 'N/A'];
        }
        $stats = ['totalEntries' => count($logData), 'errorCount' => 0, 'levelCounts' => [], 'tenants' => []];
        $errorKeywords = ['erro', 'error', 'exception', 'falha'];
        $errorLevels = ['ERRO', 'SQLERRO', 'SYSERR'];
        foreach ($logData as $entry) {
            $level = $entry['level'] ?? '';
            $message = strtolower($entry['message'] ?? '');
            if (!isset($stats['levelCounts'][$level])) $stats['levelCounts'][$level] = 0;
            $stats['levelCounts'][$level]++;
            $isError = false;
            if (in_array(strtoupper($level), $errorLevels)) { $isError = true; } 
            else { foreach ($errorKeywords as $keyword) { if (strpos($message, $keyword) !== false) { $isError = true; break; } } }
            if ($isError) $stats['errorCount']++;
            if ($logModule === 'uniplus_web' && isset($entry['user'])) { $stats['tenants'][] = $entry['user']; }
        }
        $stats['uniqueTenants'] = array_unique(array_filter($stats['tenants']));
        sort($stats['uniqueTenants']);
        $stats['tenantCount'] = count($stats['uniqueTenants']);
        $stats['startTime'] = $logData[0]['timestamp'] ?? 'N/A';
        $stats['endTime'] = end($logData)['timestamp'] ?? 'N/A';
        arsort($stats['levelCounts']);
        return $stats;
    }
    
    function getSystemVersion($logData) {
        foreach($logData as $entry) {
            if (!empty($entry['build']) && preg_match('/\d+\.\d+\.\d+/', $entry['build'])) {
                return $entry['build'];
            }
        }
        return null;
    }
    
    function formatTimestampForInput($timestampStr) {
        if (empty($timestampStr)) return '';
        $timestampStr = str_replace(',', '.', $timestampStr);
        if (strpos($timestampStr, '/')) {
            $dt = DateTime::createFromFormat('d/m/Y H:i:s.u', $timestampStr) ?: DateTime::createFromFormat('d/m/Y H:i:s', $timestampStr);
        } else {
            $dt = DateTime::createFromFormat('Y-m-d H:i:s.u', $timestampStr) ?: DateTime::createFromFormat('Y-m-d H:i:s', $timestampStr);
        }
        if ($dt === false) {
            try {
                $dt = new DateTime($timestampStr);
            } catch (Exception $e) {
                return '';
            }
        }
        return $dt->format('Y-m-d\TH:i:s');
    }

    $logStats = calculateLogStats($logData, $logModule);
    $systemVersion = getSystemVersion($logData);
    $available_levels = array_keys($logStats['levelCounts']);
    sort($available_levels);

    function get_level_class($level) {
        switch (strtoupper($level)) {
            case 'ERRO': case 'SQLERRO': case 'SYSERR': return 'table-danger';
            case 'WEBSERV': return 'table-warning';
            case 'SQL': return 'table-info';
            default: return '';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log Analyzer - uTool</title>
    <!-- Bootstrap CSS (Local or CDN) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css"/>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        :root { --sidebar-width: 320px; --transition-speed: 0.3s; }
        html { scroll-behavior: smooth; }
        body { background-color: #f8f9fa; }

        /* --- LAYOUT RESPONSIVO E RECOLHÍVEL --- */
        .main-layout {
            display: grid;
            grid-template-columns: var(--sidebar-width) 1fr;
            gap: 1.5rem;
            transition: grid-template-columns var(--transition-speed) ease-in-out, gap var(--transition-speed) ease-in-out;
        }
        .main-layout > main { min-width: 0; }

        body.sidebar-collapsed .main-layout {
            grid-template-columns: 0 1fr;
            gap: 0;
        }
        .sidebar {
            position: sticky; top: 1.5rem; height: calc(100vh - 80px);
            transition: transform var(--transition-speed) ease-in-out, opacity var(--transition-speed) ease-in-out, padding var(--transition-speed) ease-in-out;
        }
        
        .sidebar-content {
            height: 100%;
            overflow-y: auto;
            padding-right: 5px;
        }
        
        body.sidebar-collapsed .sidebar {
            transform: translateX(-100%);
            opacity: 0;
            padding: 0 !important;
            overflow: hidden;
        }

        @media (max-width: 992px) {
            .main-layout { grid-template-columns: 1fr; }
            .sidebar { position: static; height: auto; }
            .sidebar-content { overflow-y: visible; height: auto; }
        }

        /* --- CABEÇALHO FIXO E SCROLL INTERNO --- */
        main .card {
            height: calc(100vh - 80px);
            display: flex;
            flex-direction: column;
        }
        main .table-responsive {
            flex-grow: 1;
            overflow-y: auto;
        }
        main thead {
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .credit-author {
            font-size: 0.7em;
            display: block;
            margin-top: -5px;
            color: #ccc;
            font-weight: normal;
        }
        .credit-author i { font-style: italic; }
        .table-sm th, .table-sm td { padding: 0.4rem; vertical-align: middle; }
        pre { white-space: pre-wrap; word-wrap: break-word; margin: 0; font-size: 0.85em; }
        .log-message a { text-decoration: none; font-size: 0.8em; }
        .stack-trace { display: none; background-color: rgba(0,0,0,0.03); padding: 10px; border-radius: 4px; margin-top: 5px; border: 1px solid #e9ecef; }
        #navBtn { position: fixed; bottom: 20px; right: 30px; z-index: 99; width: 50px; height: 50px; }
        .is-error-row { border-left: 4px solid #dc3545 !important; }
        .is-error-row > td { background-color: #f8d7da !important; }
        .copy-link { position: absolute; top: 5px; right: 8px; display: none; cursor: pointer; font-size: 0.75em; font-weight: bold; text-decoration: none; color: #adb5bd; transition: all 0.2s ease-in-out; }
        tr:hover .copy-link { display: block; }
        .copy-link:hover { color: var(--bs-primary); transform: scale(1.2); text-decoration: underline; }
        td.log-message { position: relative; }
        #copy-toast { position: fixed; bottom: 20px; left: 50%; transform: translateX(-50%); z-index: 1050; }
        .choices__inner { background-color: var(--bs-body-bg); border-radius: var(--bs-border-radius); border: var(--bs-border-width) solid var(--bs-border-color); font-size: 0.875rem; }
        .choices[data-type*="select-one"]::after { border-color: #6c757d transparent transparent; }
        #loading-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(255, 255, 255, 0.85); backdrop-filter: blur(4px); z-index: 1060; display: none; flex-direction: column; justify-content: center; align-items: center; text-align: center; opacity: 1; transition: opacity 0.4s ease-in-out; }
        #loading-overlay.fade-out { opacity: 0; }
        .spinner-border { width: 3.5rem; height: 3.5rem; }
        body.loading #loading-overlay { display: flex; }

        /* Full Screen Mode */
        body.fullscreen-mode .navbar.bg-white { display: none !important; } /* Hides main header */
        body.fullscreen-mode .py-4 { padding-top: 0 !important; padding-bottom: 0 !important; } /* Removes main padding */
        body.fullscreen-mode .container { max-width: 100% !important; padding: 0 !important; margin: 0 !important; } /* Resets container */
        
        body.fullscreen-mode .main-layout { 
            height: calc(100vh - 56px); /* Adjust for sub-header height */
            margin: 0;
            padding: 0 !important;
        }
        body.fullscreen-mode .sidebar { 
            height: calc(100vh - 56px); 
            top: 0; 
        }
        body.fullscreen-mode main .card { 
            height: calc(100vh - 56px); 
            border-radius: 0; 
        }
        
        /* Filter Improvements */
        .filter-date-row { display: flex; gap: 5px; }
        .filter-date-row > div { flex: 1; }
    </style>
</head>
<body class="<?php echo ($logData !== null) ? 'loading' : ''; ?>">

<div id="loading-overlay">
    <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
    <h4 class="mt-3 text-dark" id="loading-message">Analisando o log...</h4>
    <p class="text-muted" id="loading-submessage">Arquivos grandes podem levar alguns segundos.</p>
</div>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
    <div class="container-fluid">
        <div class="d-flex align-items-center">
            <a href="index.php" class="btn btn-outline-secondary btn-sm me-3" title="Voltar para uTool">
                <i class="bi bi-arrow-left"></i>
            </a>
            <?php if ($logData !== null): ?>
                <button class="btn btn-outline-secondary btn-sm me-3 d-none d-lg-block" id="sidebar-toggle" title="Recolher Filtros">
                    <i class="bi bi-chevron-double-left"></i>
                </button>
            <?php endif; ?>
            
            <a class="navbar-brand" href="log-analyzer.php"><i class="bi bi-file-earmark-code-fill"></i> 
                Interpretador de Logs
                <span class="credit-author">by <i>leo.lemos</i></span>
            </a>
        </div>

        <div class="ms-auto d-flex align-items-center gap-2">
            <?php if ($logData !== null): ?>
                <div class="form-check form-switch text-light me-2" title="Atualiza o log em tempo real">
                    <input class="form-check-input" type="checkbox" id="live-monitor-toggle">
                    <label class="form-check-label" for="live-monitor-toggle"><i class="bi bi-broadcast"></i> Live</label>
                </div>
            <?php endif; ?>

            <button class="btn btn-outline-secondary btn-sm me-3" id="fullscreen-toggle" title="Tela Cheia">
                <i class="bi bi-arrows-fullscreen"></i>
            </button>
            <span class="navbar-text me-3 d-none d-lg-inline-block" title="Versão da Aplicação">
                <i class="bi bi-patch-check-fill"></i> v<?php echo htmlspecialchars($appVersion); ?>
            </span>
            <span class="navbar-text me-3 d-none d-lg-inline-block">
                <i class="bi bi-key-fill"></i> Senha do Dia: <strong><?php echo htmlspecialchars($senha_tecnica_dia); ?></strong>
            </span>
            <?php if ($logData !== null): ?>
                <a href="log-analyzer.php" class="btn btn-outline-light btn-sm">← Arquivos</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<?php if ($logData === null): ?>
    <!-- ########## VISÃO DE UPLOAD ATUALIZADA ########## -->
    <main class="container mt-4">
        <div class="row justify-content-center g-4">
            <!-- Coluna do Formulário -->
            <div class="col-lg-6">
                <div class="card shadow-sm h-100">
                    <div class="card-header"><h3 class="card-title mb-0">Análise de Logs</h3></div>
                    <div class="card-body d-flex flex-column">
                        <p class="card-text">Envie um arquivo de log (.log) para iniciar a análise.</p>
                        <form id="upload-form" action="log-analyzer.php" method="post" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="log_module" class="form-label">Selecione o Módulo:</label>
                                <select name="log_module" id="log_module" class="form-select" required>
                                    <option value="uniplus_desktop">Uniplus Desktop</option>
                                    <option value="pdv">PDV</option>
                                    <option value="uniplus_web">Uniplus Web</option>
                                    <option value="yoda">Yoda</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="log_file" class="form-label">Selecione o arquivo:</label>
                                <input type="file" name="log_file" id="log_file" class="form-control" required accept=".log">
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Analisar Log</button>
                        </form>
                        <div class="mt-auto pt-3"> <!-- Empurra o link para o final do card -->
                             <a href="http://suporteutil.intelidata.local/" class="btn btn-outline-secondary w-100" target="_blank">Acessar Suporte Util</a>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Coluna dos Logs Recentes -->
            <div class="col-lg-4">
                 <div class="card shadow-sm h-100">
                    <div class="card-header"><h5 class="mb-0"><i class="bi bi-clock-history"></i> Logs Recentes</h5></div>
                    <div class="list-group list-group-flush" style="max-height: 400px; overflow-y: auto;">
                        <?php if (empty($existingLogs)): ?>
                            <div class="list-group-item text-muted text-center py-4">Nenhum log encontrado no servidor.</div>
                        <?php else: ?>
                            <?php foreach ($existingLogs as $f): ?>
                                <a href="log-analyzer.php?file=<?php echo urlencode($f['name']); ?>" class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1 text-truncate" title="<?php echo htmlspecialchars($f['name']); ?>"><?php echo htmlspecialchars($f['name']); ?></h6>
                                        <small class="text-muted"><?php echo formatBytes($f['size']); ?></small>
                                    </div>
                                    <small class="text-muted">Modificado: <?php echo date('d/m/Y H:i', $f['date']); ?></small>
                                </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Coluna das Estatísticas Gerais -->
            <div class="col-lg-2">
                <div class="card shadow-sm h-100">
                    <div class="card-header"><h5 class="mb-0"><i class="bi bi-server"></i> Stats</h5></div>
                    <ul class="list-group list-group-flush small">
                        <li class="list-group-item d-flex justify-content-between align-items-center">Logs <span class="badge bg-primary rounded-pill"><?php echo number_format($serverStats['total_files']); ?></span></li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">Linhas <span class="badge bg-secondary rounded-pill"><?php echo number_format($serverStats['total_lines']); ?></span></li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">Tam <span class="badge bg-info text-dark rounded-pill"><?php echo formatBytes($serverStats['total_size']); ?></span></li>
                    </ul>
                </div>
            </div>
        </div>
    </main>

<?php else: ?>
    <!-- ########## VISÃO DE ANÁLISE (APÓS UPLOAD) ########## -->
    <div class="main-layout p-3">
        <aside>
            <div class="sidebar">
                <div class="sidebar-content">
                    <div class="card mb-3">
                        <div class="card-header"><h5 class="mb-0"><i class="bi bi-bar-chart-line-fill"></i> Estatísticas</h5></div>
                        <ul class="list-group list-group-flush small">
                            <li class="list-group-item d-flex justify-content-between align-items-center">Total de Linhas <span class="badge bg-secondary rounded-pill"><?php echo number_format($logStats['totalEntries']); ?></span></li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">Total de Erros <span class="badge bg-danger rounded-pill"><?php echo number_format($logStats['errorCount']); ?></span></li>
                            <?php if ($logFileSize > 0): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">Tamanho do Arquivo <span class="badge bg-light text-dark rounded-pill"><?php echo formatBytes($logFileSize); ?></span></li>
                            <?php endif; ?>
                            <?php if ($systemVersion): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">Versão Identificada <span class="badge bg-light text-dark rounded-pill"><?php echo htmlspecialchars($systemVersion); ?></span></li>
                            <?php endif; ?>
                            <?php if ($logModule === 'uniplus_web'): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">Tenants Únicos <span class="badge bg-info text-dark rounded-pill"><?php echo $logStats['tenantCount']; ?></span></li>
                            <?php endif; ?>
                            <li class="list-group-item text-muted">Início: <?php echo htmlspecialchars($logStats['startTime']); ?></li>
                            <li class="list-group-item text-muted">Fim: <?php echo htmlspecialchars($logStats['endTime']); ?></li>
                        </ul>
                    </div>
                    <div class="card d-flex flex-column card-filters">
                        <div class="card-header"><h5 class="mb-0"><i class="bi bi-funnel-fill"></i> Filtros</h5></div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Filtrar por Período:</label>
                                <div class="row g-2">
                                    <div class="col-6"><input type="datetime-local" id="start-date-filter" class="form-control form-control-sm" value="<?php echo formatTimestampForInput($logStats['startTime']); ?>" title="Data Inicial"></div>
                                    <div class="col-6"><input type="datetime-local" id="end-date-filter" class="form-control form-control-sm" value="<?php echo formatTimestampForInput($logStats['endTime']); ?>" title="Data Final"></div>
                                </div>
                            </div>

                            <?php if ($logModule === 'uniplus_web' && $logStats['tenantCount'] > 0): ?>
                            <div class="mb-3">
                                <label for="tenant-filter" class="form-label small fw-bold">Filtrar por Tenant:</label>
                                <select id="tenant-filter">
                                    <option value="">Todos os Tenants</option>
                                    <?php foreach ($logStats['uniqueTenants'] as $tenant): ?>
                                        <option value="<?php echo htmlspecialchars($tenant); ?>"><?php echo htmlspecialchars($tenant); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <?php endif; ?>
                            <div class="input-group mb-3"><span class="input-group-text"><i class="bi bi-search"></i></span><input type="text" id="search-box" class="form-control" placeholder="Buscar na mensagem..."><button class="btn btn-outline-secondary" type="button" id="clearSearchBtn" title="Limpar busca"><i class="bi bi-x-lg"></i></button></div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="form-check"><input class="form-check-input" type="checkbox" id="check-all" checked><label class="form-check-label fw-bold" for="check-all">Níveis</label></div>
                                <div class="btn-group btn-group-sm"><button class="btn btn-outline-primary" id="checkAllBtn" title="Marcar todos"><i class="bi bi-check-all"></i></button><button class="btn btn-outline-secondary" id="uncheckAllBtn" title="Desmarcar todos"><i class="bi bi-square"></i></button></div>
                            </div>
                            <hr class="mt-1">
                            <div class="filter-options">
                                <?php foreach ($available_levels as $level): ?>
                                    <div class="form-check"><input class="form-check-input level-checkbox" type="checkbox" value="<?php echo htmlspecialchars($level); ?>" id="check-<?php echo htmlspecialchars($level); ?>" checked><label class="form-check-label" for="check-<?php echo htmlspecialchars($level); ?>"><?php echo htmlspecialchars($level); ?></label></div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button class="btn btn-sm btn-outline-danger w-100" id="clearLogsBtn"><i class="bi bi-trash3-fill"></i> Limpar Logs do Servidor</button>
                        </div>
                    </div>
                </div>
            </div>
        </aside>
        <main>
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><?php echo htmlspecialchars($logFilename); ?></h5>
                    <div id="log-summary" class="text-muted small"></div>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-hover table-bordered mb-0">
                        <thead class="table-light">
                             <tr>
                                <th style="width: 140px;">Timestamp</th>
                                <th style="width: 200px;">Nível / Logger</th>
                                <th style="width: 180px;">Usuário / Tenant</th>
                                <th>Mensagem</th>
                            </tr>
                        </thead>
                        <tbody id="log-table-body">
                            <!-- O corpo da tabela é preenchido via JavaScript -->
                        </tbody>
                    </table>
                </div>
                <div class="card-footer d-flex justify-content-center align-items-center gap-3">
                    <button class="btn btn-outline-secondary btn-sm" id="prev-page"><i class="bi bi-chevron-left"></i> Anterior</button>
                    <span class="small text-muted" id="page-info"></span>
                    <button class="btn btn-outline-secondary btn-sm" id="next-page">Próximo <i class="bi bi-chevron-right"></i></button>
                </div>
            </div>
        </main>
    </div>
    
    <button id="navBtn" class="btn btn-primary rounded-circle shadow-lg"><i class="bi bi-arrow-down"></i></button>

    <div id="copy-toast" class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body">Conteúdo copiado para a área de transferência!</div>
        </div>
    </div>
<?php endif; ?>

<!-- ### SEÇÃO DE SCRIPTS ### -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
<?php if ($logData !== null): ?>
<script>
    // Transfere todos os dados do PHP para o JavaScript
    const fullLogData = <?php echo json_encode($logData); ?>;
    // Verifica se o contexto é seguro para a API Clipboard
    window.isSecureContext = window.location.protocol === 'https:' || ['localhost', '127.0.0.1'].includes(window.location.hostname);
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const isAnalysisPage = document.body.classList.contains('loading');
    
    // --- LÓGICA DA PÁGINA DE UPLOAD ---
    const uploadForm = document.getElementById('upload-form');
    if (uploadForm) {
        const loadingOverlay = document.getElementById('loading-overlay');
        const loadingMessage = document.getElementById('loading-message');
        const loadingSubMessage = document.getElementById('loading-submessage');
        const funMessages = [ "Desvendando os hieróglifos do log...", "Alimentando os hamsters do servidor...", "Consultando os oráculos de Java...", "Polindo os bits e bytes...", "Quase lá, só mais um gole de café..." ];
        
        uploadForm.addEventListener('submit', function() {
            const logFile = document.getElementById('log_file');
            if (logFile && logFile.files.length > 0) {
                const randomIndex = Math.floor(Math.random() * funMessages.length);
                if(loadingMessage) loadingMessage.textContent = funMessages[randomIndex];
                if(loadingSubMessage) loadingSubMessage.style.display = 'none';
                if(loadingOverlay) {
                    loadingOverlay.classList.remove('fade-out');
                    loadingOverlay.style.display = 'flex';
                }
            }
        });
    }

    // --- SÓ EXECUTA A LÓGICA DE ANÁLISE SE ESTIVER NA PÁGINA CORRETA ---
    if (!isAnalysisPage) return;

    // --- ESTADO DA APLICAÇÃO ---
    const state = {
        filteredData: [],
        currentPage: 1,
        itemsPerPage: 500
    };

    // --- REFERÊNCIAS AOS ELEMENTOS DO DOM ---
    const sidebarToggle = document.getElementById('sidebar-toggle');
    const loadingOverlay = document.getElementById('loading-overlay');
    const tableBody = document.getElementById('log-table-body');
    const logSummary = document.getElementById('log-summary');
    const pageInfo = document.getElementById('page-info');
    const prevPageBtn = document.getElementById('prev-page');
    const nextPageBtn = document.getElementById('next-page');
    const navBtn = document.getElementById('navBtn'), navBtnIcon = navBtn.querySelector('i');
    const checkboxes = document.querySelectorAll('.level-checkbox'), checkAll = document.getElementById('check-all');
    const searchBox = document.getElementById('search-box'), tenantFilter = document.getElementById('tenant-filter');
    const startDateFilter = document.getElementById('start-date-filter'), endDateFilter = document.getElementById('end-date-filter');
    const checkAllBtn = document.getElementById('checkAllBtn'), uncheckAllBtn = document.getElementById('uncheckAllBtn'), clearSearchBtn = document.getElementById('clearSearchBtn');
    const clearLogsBtn = document.getElementById('clearLogsBtn');
    const fullscreenToggle = document.getElementById('fullscreen-toggle');
    const toastEl = document.getElementById('copy-toast');
    const toast = new bootstrap.Toast(toastEl, { delay: 2000 });
    
    const errorKeywords = ['erro', 'error', 'exception', 'falha'];
    const errorLevels = ['ERRO', 'SQLERRO', 'SYSERR'];

    // --- FUNÇÕES AUXILIARES ---
    function parseLogTimestamp(timestampStr) {
        if (!timestampStr) return null;
        let parsableStr = timestampStr.replace(',', '.');
        if (parsableStr.includes('/')) {
            const parts = parsableStr.split(' ');
            if (parts.length >= 2) {
                const dateParts = parts[0].split('/');
                if (dateParts.length === 3) {
                   parsableStr = `${dateParts[2]}-${dateParts[1]}-${dateParts[0]}T${parts[1]}`;
                }
            }
        } else {
            parsableStr = parsableStr.replace(' ', 'T');
        }
        try {
            const date = new Date(parsableStr);
            return isNaN(date) ? null : date;
        } catch (e) {
            return null;
        }
    }
    
    function escapeHtml(unsafe) {
        if (typeof unsafe !== 'string') return '';
        return unsafe
             .replace(/&/g, "&amp;")
             .replace(/</g, "&lt;")
             .replace(/>/g, "&gt;")
             .replace(/"/g, "&quot;")
             .replace(/'/g, "&#039;");
    }

    function updateCheckAllState() {
        const allChecked = Array.from(checkboxes).every(cb => cb.checked);
        const someChecked = Array.from(checkboxes).some(cb => cb.checked);
        checkAll.checked = allChecked;
        checkAll.indeterminate = !allChecked && someChecked;
    }

    function updateNavButton() {
        const scrollPosition = window.scrollY || document.documentElement.scrollTop;
        const pageHeight = document.documentElement.scrollHeight - window.innerHeight;
        if (pageHeight > 100) {
            navBtn.style.display = 'block';
             if (scrollPosition > pageHeight - 50) {
                navBtn.setAttribute('data-action', 'up'); navBtnIcon.className = 'bi bi-arrow-up'; navBtn.title = 'Voltar ao topo';
            } else {
                navBtn.setAttribute('data-action', 'down'); navBtnIcon.className = 'bi bi-arrow-down'; navBtn.title = 'Ir para o fim';
            }
        } else {
            navBtn.style.display = 'none';
        }
    }

    // --- FUNÇÕES PRINCIPAIS DE RENDERIZAÇÃO E FILTRO ---
    function renderTablePage() {
        const totalPages = Math.ceil(state.filteredData.length / state.itemsPerPage) || 1;
        state.currentPage = Math.max(1, Math.min(state.currentPage, totalPages)); 

        const start = (state.currentPage - 1) * state.itemsPerPage;
        const end = start + state.itemsPerPage;
        const pageData = state.filteredData.slice(start, end);

        tableBody.innerHTML = pageData.map((entry, index) => {
            let isError = errorLevels.includes((entry.level || '').toUpperCase());
            if (!isError) {
                for (const keyword of errorKeywords) {
                    if ((entry.message || '').toLowerCase().includes(keyword)) {
                        isError = true;
                        break;
                    }
                }
            }
            const rowClass = isError ? 'is-error-row' : '';
            
            let messageHtml = '';
            const messageStr = entry.message || '';
            const first_line = messageStr.split('\n')[0];
            const stack_trace = messageStr.substring(first_line.length);
            const escaped_first_line = escapeHtml(first_line);

            if (['SQL', 'SQLERRO'].includes((entry.level || '').toUpperCase())) {
                const formattedSql = escaped_first_line
                    .replace(/^Query acima de \d+ ms: \d+ms :/, '')
                    .replace(/ (FROM|WHERE|LEFT JOIN|INNER JOIN|ORDER BY|GROUP BY|AND|OR|ON|LIMIT|SET|UPDATE|INSERT INTO|VALUES)/g, '\n$1');
                messageHtml = `<pre>${formattedSql}</pre>`;
            } else {
                messageHtml = `<pre>${escaped_first_line}</pre>`;
            }

            if (stack_trace && stack_trace.trim()) {
                const escaped_stack = escapeHtml(stack_trace.trim());
                messageHtml += `<a href="#" class="toggle-stack-trace" data-target="stack-${start + index}">[ + Mostrar detalhes ]</a><div id="stack-${start + index}" class="stack-trace"><pre>${escaped_stack}</pre></div>`;
            }
            
            const fullLogContent = `${entry.timestamp || ''}${entry.build ? ' [' + entry.build + ']' : ''} ${entry.level || ''} - ${entry.message || ''}`;

            return `
                <tr data-level="${escapeHtml(entry.level)}" data-tenant="${escapeHtml(entry.user)}" data-timestamp="${escapeHtml(entry.timestamp)}" class="${rowClass}">
                    <td>${escapeHtml(entry.timestamp)}</td>
                    <td><b>${escapeHtml(entry.level)}</b></td>
                    <td>${escapeHtml(entry.user)}</td>
                    <td class="log-message">
                        <a href="#" class="copy-link">[ Copiar Log ]</a>
                        <textarea class="d-none full-log-content">${escapeHtml(fullLogContent)}</textarea>
                        ${messageHtml}
                    </td>
                </tr>`;
        }).join('');

        attachRowListeners();
        renderPaginationControls();
    }

    function renderPaginationControls() {
        const totalEntries = state.filteredData.length;
        const totalPages = Math.ceil(totalEntries / state.itemsPerPage) || 1;
        
        if (totalEntries === 0) {
            pageInfo.textContent = "Nenhum resultado";
            tableBody.innerHTML = '<tr><td colspan="4" class="text-center p-4">Nenhuma entrada de log encontrada com os filtros aplicados.</td></tr>';
            prevPageBtn.disabled = true;
            nextPageBtn.disabled = true;
            return;
        }

        pageInfo.textContent = `Página ${state.currentPage} de ${totalPages}`;
        prevPageBtn.disabled = state.currentPage === 1;
        nextPageBtn.disabled = state.currentPage >= totalPages;
    }

    function applyFilters() {
        const selectedLevels = Array.from(checkboxes).filter(cb => cb.checked).map(cb => cb.value);
        const searchTerm = searchBox.value.toLowerCase();
        const selectedTenant = tenantFilter ? tenantFilter.value : '';
        const startDate = startDateFilter.value ? new Date(startDateFilter.value) : null;
        const endDate = endDateFilter.value ? new Date(endDateFilter.value) : null;
        
        state.filteredData = fullLogData.filter(entry => {
            const levelMatch = selectedLevels.includes(entry.level);
            const searchMatch = ((entry.message || '').toLowerCase() + (entry.user || '').toLowerCase()).includes(searchTerm);
            const tenantMatch = !selectedTenant || (entry.user === selectedTenant);
            
            const rowTimestamp = parseLogTimestamp(entry.timestamp);
            const dateMatch = !rowTimestamp ||
                            ((!startDate || rowTimestamp >= startDate) && (!endDate || rowTimestamp <= endDate));
            
            return levelMatch && searchMatch && tenantMatch && dateMatch;
        });
        
        state.currentPage = 1;
        renderTablePage();
        renderTablePage();
        logSummary.textContent = `Exibindo ${state.filteredData.length.toLocaleString()} de ${fullLogData.length.toLocaleString()} entradas.`;
        
        // If live monitor is on, go to last page
        if (liveToggle && liveToggle.checked) {
             const totalPages = Math.ceil(state.filteredData.length / state.itemsPerPage);
             if (state.currentPage !== totalPages) {
                 state.currentPage = totalPages;
                 renderTablePage();
             }
        }
    }
    
    function scrollToTableTop() {
        const tableContainer = document.querySelector('main .table-responsive');
        if (tableContainer) {
            tableContainer.scrollTo({ top: 0, behavior: 'smooth' });
        }
    }

    function attachRowListeners() {
        document.querySelectorAll('.toggle-stack-trace').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const targetDiv = document.getElementById(this.getAttribute('data-target'));
                if (targetDiv) {
                    const isVisible = targetDiv.style.display === 'block';
                    targetDiv.style.display = isVisible ? 'none' : 'block';
                    this.textContent = isVisible ? '[ + Mostrar detalhes ]' : '[ - Ocultar detalhes ]';
                }
            });
        });

        document.querySelectorAll('.copy-link').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const textarea = this.nextElementSibling; // Pega o elemento textarea escondido
                const contentToCopy = textarea.value.trim();

                // Tenta usar a API moderna se o contexto for seguro (HTTPS/localhost)
                if (navigator.clipboard && window.isSecureContext) {
                    navigator.clipboard.writeText(contentToCopy).then(() => {
                        toast.show();
                    }).catch(err => {
                        console.error('Falha ao copiar com a API moderna: ', err);
                        alert('Não foi possível copiar o texto.');
                    });
                } else {
                    // Método alternativo (fallback) para contextos não seguros (HTTP)
                    try {
                        textarea.classList.remove('d-none'); // Torna o textarea visível temporariamente
                        textarea.select();
                        document.execCommand('copy');
                        textarea.classList.add('d-none'); // Esconde novamente
                        window.getSelection().removeAllRanges(); // Remove a seleção do texto
                        toast.show();
                    } catch (err) {
                        console.error('Falha ao copiar com o método fallback: ', err);
                        alert('Não foi possível copiar o texto.');
                    }
                }
            });
        });
    }

    // --- INICIALIZAÇÃO E EVENT LISTENERS ---
    
    if (tenantFilter) {
        new Choices(tenantFilter, { searchPlaceholderValue: "Digite para buscar...", removeItemButton: true, itemSelectText: 'Pressionar para selecionar', noResultsText: 'Nenhum tenant encontrado' });
    }

    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', () => {
            document.body.classList.toggle('sidebar-collapsed');
            const icon = sidebarToggle.querySelector('i');
            const isCollapsed = document.body.classList.contains('sidebar-collapsed');
            icon.className = isCollapsed ? 'bi bi-chevron-double-right' : 'bi bi-chevron-double-left';
            sidebarToggle.title = isCollapsed ? 'Mostrar Filtros' : 'Recolher Filtros';
        });
    }

    if (fullscreenToggle) {
        fullscreenToggle.addEventListener('click', () => {
            document.body.classList.toggle('fullscreen-mode');
            const icon = fullscreenToggle.querySelector('i');
            if (document.body.classList.contains('fullscreen-mode')) {
                icon.classList.replace('bi-arrows-fullscreen', 'bi-fullscreen-exit');
                fullscreenToggle.title = 'Sair da Tela Cheia';
            } else {
                icon.classList.replace('bi-fullscreen-exit', 'bi-arrows-fullscreen');
                fullscreenToggle.title = 'Tela Cheia';
            }
        });
    }

    [startDateFilter, endDateFilter, searchBox].forEach(el => el.addEventListener('input', debounce(applyFilters, 300)));
    if (tenantFilter) tenantFilter.addEventListener('change', applyFilters);
    
    checkAll.addEventListener('change', () => { checkboxes.forEach(cb => cb.checked = checkAll.checked); applyFilters(); updateCheckAllState(); });
    checkboxes.forEach(cb => cb.addEventListener('change', () => { applyFilters(); updateCheckAllState(); }));
    checkAllBtn.addEventListener('click', () => { checkboxes.forEach(cb => cb.checked = true); checkAll.checked = true; applyFilters(); updateCheckAllState(); });
    uncheckAllBtn.addEventListener('click', () => { checkboxes.forEach(cb => cb.checked = false); checkAll.checked = false; applyFilters(); updateCheckAllState(); });
    
    clearSearchBtn.addEventListener('click', () => { searchBox.value = ''; applyFilters(); });
    clearLogsBtn.addEventListener('click', function() { if (confirm('Tem certeza que deseja apagar TODOS os arquivos de log enviados para o servidor?\n\nEsta ação não pode ser desfeita.')) { window.location.href = 'log-analyzer.php?action=clear_logs'; } });
    
    prevPageBtn.addEventListener('click', () => { if (state.currentPage > 1) { state.currentPage--; renderTablePage(); scrollToTableTop(); } });
    nextPageBtn.addEventListener('click', () => { 
        const totalPages = Math.ceil(state.filteredData.length / state.itemsPerPage);
        if (state.currentPage < totalPages) { state.currentPage++; renderTablePage(); scrollToTableTop(); } 
    });

    window.addEventListener('scroll', updateNavButton);
    navBtn.addEventListener('click', () => {
        const action = navBtn.getAttribute('data-action');
        window.scrollTo({ top: (action === 'up' ? 0 : document.body.scrollHeight), behavior: 'smooth' });
    });

    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
    
    // Inicialização
    applyFilters();

    // Remove loading overlay
    if (loadingOverlay) {
        setTimeout(() => {
            loadingOverlay.classList.add('fade-out');
            setTimeout(() => {
                loadingOverlay.style.display = 'none';
                document.body.classList.remove('loading');
            }, 400); // Wait for transition
        }, 300); // Small delay to ensure render is visible
    }

    // --- LIVES MONITOR LOGIC ---
    let pollInterval = null;
    let currentOffset = <?php echo isset($logFileSize) ? $logFileSize : 0; ?>;
    const currentFilename = '<?php echo isset($logFilename) ? $logFilename : ''; ?>';
    const currentModule = '<?php echo isset($logModule) ? $logModule : 'uniplus_desktop'; ?>';
    const liveToggle = document.getElementById('live-monitor-toggle');

    if (liveToggle) {
        liveToggle.addEventListener('change', function() {
            if (this.checked) {
                startPolling();
                // Disable automatic pagination resets while live
                state.currentPage = Math.ceil(fullLogData.length / state.itemsPerPage); // Go to last page
                renderTablePage();
                scrollToBottom();
            } else {
                stopPolling();
            }
        });
    }

    function startPolling() {
        if (pollInterval) clearInterval(pollInterval);
        navBtn.classList.remove('btn-primary');
        navBtn.classList.add('btn-danger', 'spinner-grow'); // Visual indicator
        
        pollInterval = setInterval(() => {
            fetch(`log-analyzer.php?action=poll&file=${encodeURIComponent(currentFilename)}&module=${currentModule}&offset=${currentOffset}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        console.error("Poll error:", data.error);
                        stopPolling();
                        liveToggle.checked = false;
                        alert("Erro ao monitorar arquivo: " + data.error);
                        return;
                    }

                    if (data.hasData && data.entries.length > 0) {
                        currentOffset = data.offset;
                        // Append new entries
                        fullLogData.push(...data.entries);
                        
                        // Recalculate stats? Mostly filters.
                        state.filteredData = fullLogData; // Assuming no filters active for Live, or re-apply.
                        applyFilters(); // Re-apply current filters to new data
                        
                        // Auto-scroll if near bottom or forced
                        if (liveToggle.checked) {
                             // Go to last page
                             const totalPages = Math.ceil(state.filteredData.length / state.itemsPerPage);
                             state.currentPage = totalPages;
                             renderTablePage();
                             scrollToBottom();
                        }
                    } else if (data.hasData === false && data.offset !== currentOffset) {
                         // File truncated/rotated
                         currentOffset = data.offset;
                    }
                })
                .catch(err => {
                    console.error("Poll request failed", err);
                });
        }, 3000); // 3 seconds
    }

    function stopPolling() {
        if (pollInterval) clearInterval(pollInterval);
        pollInterval = null;
        navBtn.classList.remove('btn-danger', 'spinner-grow');
        navBtn.classList.add('btn-primary');
    }

    function scrollToBottom() {
        const tableContainer = document.querySelector('main .table-responsive');
        if (tableContainer) {
            tableContainer.scrollTo({ top: tableContainer.scrollHeight, behavior: 'smooth' });
        }
    }
});
</script>
<?php endif; ?>
</body>
</html>
