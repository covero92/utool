<?php
include 'includes/header.php';

// --- CONFIG ---
if (session_status() == PHP_SESSION_NONE) session_start();

// Default fallback
$defaultBase = 'c:/xampp/htdocs/utool/font/';
$logDir = $defaultBase . 'logs/';

// 1. Try to infer from Session Config Path (Best Guess)
if (isset($_SESSION['uniplus_config_path'])) {
    $configPath = $_SESSION['uniplus_config_path']; // e.g., D:\Uniplus\uniplus.properties
    $installDir = dirname($configPath); // e.g., D:\Uniplus
    
    // Common variants for log folder
    $candidates = [
        $installDir . '/log/',
        $installDir . '/logs/'
    ];

    foreach ($candidates as $dir) {
        if (is_dir($dir)) {
            $logDir = $dir;
            break;
        }
    }
}

// 2. Fallback to hardcoded common paths if session fails
if (!is_dir($logDir)) {
    $commonPaths = [
        'C:/Uniplus/log/',
        'D:/Uniplus/log/',
        'E:/Uniplus/log/',
        'C:/Uniplus/logs/',
        $defaultBase . 'logs/'
    ];
    
    foreach ($commonPaths as $path) {
        if (is_dir($path)) {
            $logDir = $path;
            break;
        }
    }
}

// 3. Last Resort: Create dummy if sticking to local default
if ($logDir === $defaultBase . 'logs/' && !is_dir($logDir)) {
    @mkdir($logDir, 0777, true);
    if (empty(glob($logDir . '*.log'))) {
        file_put_contents($logDir . 'system.log', "[INFO] " . date('Y-m-d H:i:s') . " - Log system initialized.\n");
    }
}

// --- AJAX HANDLER ---
if (isset($_GET['action']) && $_GET['action'] === 'poll') {
    header('Content-Type: application/json');
    $file = isset($_GET['file']) ? basename($_GET['file']) : '';
    $lastSize = isset($_GET['lastSize']) ? (int)$_GET['lastSize'] : 0;
    
    $fullPath = $logDir . $file;
    
    if ($file && file_exists($fullPath)) {
        clearstatcache(true, $fullPath);
        $currentSize = filesize($fullPath);
        
        if ($currentSize < $lastSize) {
            // File rotated/truncated
            $lastSize = 0;
        }
        
        if ($currentSize > $lastSize) {
            $fh = fopen($fullPath, 'r');
            fseek($fh, $lastSize);
            $newContent = fread($fh, $currentSize - $lastSize);
            fclose($fh);
            
            echo json_encode(['status' => 'ok', 'content' => $newContent, 'size' => $currentSize]);
        } else {
            echo json_encode(['status' => 'ok', 'content' => '', 'size' => $currentSize]);
        }
    } else {
        echo json_encode(['status' => 'error', 'msg' => 'File not found']);
    }
    exit;
}

// List Files
$files = glob($logDir . '*.{log,txt}', GLOB_BRACE);
$logFiles = [];
if ($files) {
    foreach ($files as $f) {
        $logFiles[] = basename($f);
    }
}
$currentLog = isset($_GET['log']) ? basename($_GET['log']) : ($logFiles[0] ?? '');
?>

<div class="container-fluid h-100 d-flex flex-column py-3 px-4" style="height: calc(100vh - 80px) !important;">
    
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="d-flex align-items-center gap-3">
            <h1 class="h4 fw-bold text-dark mb-0"><i class="bi bi-terminal me-2 text-danger"></i>Log Viewer</h1>
            
            <select class="form-select form-select-sm border-0 shadow-sm rounded-pill px-3 fw-bold" style="width: 250px;" id="logSelector">
                <?php foreach($logFiles as $f): ?>
                    <option value="<?php echo $f; ?>" <?php echo ($currentLog === $f) ? 'selected' : ''; ?>><?php echo $f; ?></option>
                <?php endforeach; ?>
            </select>
            
            <div class="badge bg-light text-muted border" id="statusBadge">Conectado</div>
        </div>
        
        <div class="d-flex gap-2">
            <div class="form-check form-switch pt-1">
                <input class="form-check-input" type="checkbox" id="autoScroll" checked>
                <label class="form-check-label small fw-bold text-muted" for="autoScroll">Auto-Scroll</label>
            </div>
            <button class="btn btn-sm btn-white border shadow-sm rounded-pill" onclick="clearLog()"><i class="bi bi-eraser me-1"></i> Limpar</button>
            <button class="btn btn-sm btn-white border shadow-sm rounded-pill" onclick="togglePause()" id="pauseBtn"><i class="bi bi-pause-fill me-1"></i> Pausar</button>
            <a href="uniplus_toolkit.php" class="btn btn-sm btn-outline-secondary rounded-pill ms-2">Voltar</a>
        </div>
    </div>

    <!-- Terminal Window -->
    <div class="card border-0 shadow-lg rounded-3 flex-grow-1 bg-dark text-light overflow-hidden position-relative terminal-window">
        <!-- Lines Container -->
        <div class="card-body p-3 font-monospace small overflow-auto h-100" id="logContainer" style="white-space: pre-wrap; word-break: break-all;">
            <div class="text-secondary opacity-50">--- Iniciando leitura de <?php echo $currentLog; ?> ---</div>
        </div>
    </div>
</div>

<style>
    .terminal-window {
        background-color: #1e1e1e !important;
        font-family: 'Consolas', 'Monaco', monospace;
    }
    
    /* Syntax Highlighting */
    .log-line { border-left: 3px solid transparent; padding-left: 5px; }
    .log-line:hover { background-color: rgba(255,255,255,0.05); }

    .line-error { color: #ff6b6b; border-left-color: #ff6b6b; background-color: rgba(255, 107, 107, 0.1); }
    .line-warn { color: #fcc419; border-left-color: #fcc419; }
    .line-info { color: #74c0fc; }
    .line-debug { color: #868e96; }
    .line-success { color: #51cf66; }
    
    .timestamp { color: #6c757d; margin-right: 10px; }
</style>

<script>
    let currentFile = "<?php echo $currentLog; ?>";
    let lastSize = 0;
    let isPaused = false;
    let currentRequest = null;
    const logContainer = document.getElementById('logContainer');
    const statusBadge = document.getElementById('statusBadge');

    // Selector Change
    document.getElementById('logSelector').addEventListener('change', (e) => {
        currentFile = e.target.value;
        lastSize = 0;
        logContainer.innerHTML = '<div class="text-secondary opacity-50">--- Trocando para ' + currentFile + ' ---</div>';
        pollLog();
    });

    function togglePause() {
        isPaused = !isPaused;
        const btn = document.getElementById('pauseBtn');
        if (isPaused) {
            btn.innerHTML = '<i class="bi bi-play-fill me-1"></i> Retomar';
            statusBadge.textContent = 'Pausado';
            statusBadge.className = 'badge bg-warning text-dark border';
        } else {
            btn.innerHTML = '<i class="bi bi-pause-fill me-1"></i> Pausar';
            statusBadge.className = 'badge bg-light text-muted border';
            pollLog();
        }
    }

    function clearLog() {
        logContainer.innerHTML = '';
    }

    function formatLine(line) {
        if (!line.trim()) return '';
        
        let cssClass = '';
        if (line.match(/ERROR|EXCEPTION|FATAL|FAIL/i)) cssClass = 'line-error';
        else if (line.match(/WARN|ALERT/i)) cssClass = 'line-warn';
        else if (line.match(/INFO/i)) cssClass = 'line-info';
        else if (line.match(/DEBUG/i)) cssClass = 'line-debug';
        
        // Try extract timestamp (assuming ISO-ish or bracketed)
        // Simple heuristic: First 20 chars
        
        return `<div class="log-line ${cssClass}">${line}</div>`;
    }

    async function pollLog() {
        if (isPaused || !currentFile) return;

        try {
            statusBadge.textContent = 'Lendo...';
            const response = await fetch(`?action=poll&file=${encodeURIComponent(currentFile)}&lastSize=${lastSize}`);
            const data = await response.json();

            if (data.status === 'ok') {
                statusBadge.textContent = 'Ao Vivo';
                statusBadge.className = 'badge bg-success bg-opacity-75 text-white border-0';
                
                if (data.size !== lastSize) {
                    lastSize = data.size;
                    
                    if (data.content) {
                        const lines = data.content.split('\n');
                        lines.forEach(line => {
                            const html = formatLine(line);
                            if (html) logContainer.insertAdjacentHTML('beforeend', html);
                        });

                        // Auto-Scroll
                        if (document.getElementById('autoScroll').checked) {
                            logContainer.scrollTop = logContainer.scrollHeight;
                        }
                    }
                }
            } else {
                statusBadge.textContent = 'Erro';
                statusBadge.className = 'badge bg-danger text-white';
            }
        } catch (e) {
            console.error(e);
            statusBadge.textContent = 'Falha Rede';
        }

        // Schedule next poll
        if (!isPaused) setTimeout(pollLog, 2000);
    }

    // Start
    pollLog();
</script>

<?php include 'includes/footer.php'; ?>
