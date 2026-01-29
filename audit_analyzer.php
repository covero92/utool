<?php
// audit_analyzer.php - Tool for analyzing system logs
// Uses SplFileObject for efficient file reading

session_start();

// Handle File Reset
if (isset($_GET['reset_file'])) {
    unset($_SESSION['active_log_file']);
    header('Location: audit_analyzer.php');
    exit;
}

// Handle File Upload
if (isset($_FILES['audit_file']) && $_FILES['audit_file']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = __DIR__ . '/uploads';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
    
    $fileTmpPath = $_FILES['audit_file']['tmp_name'];
    $fileName = 'custom_audit_' . time() . '.txt';
    $destPath = $uploadDir . '/' . $fileName;
    
    if (move_uploaded_file($fileTmpPath, $destPath)) {
        $_SESSION['active_log_file'] = $destPath;
        header('Location: audit_analyzer.php');
        exit;
    }
}


$logFile = isset($_SESSION['active_log_file']) && file_exists($_SESSION['active_log_file']) 
            ? $_SESSION['active_log_file'] 
            : null;

$usingCustomFile = ($logFile !== null);

$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$perPage = isset($_GET['per_page']) ? intval($_GET['per_page']) : 50;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$filterAction = isset($_GET['action']) ? trim($_GET['action']) : '';
$filterUser = isset($_GET['user']) ? trim($_GET['user']) : '';
$filterTable = isset($_GET['table']) ? trim($_GET['table']) : '';
$filterDateStart = isset($_GET['date_start']) ? trim($_GET['date_start']) : '';
$filterDateEnd = isset($_GET['date_end']) ? trim($_GET['date_end']) : '';

// Function to Parse a Line
function parseLogLine($line) {
    // 1. Clean up
    $line = trim($line);
    if (substr($line, -1) === ';') $line = substr($line, 0, -1); // Remove trailing delimiter
    if (!$line) return false;

    // 2. Regex for Uniplus Log Format
    // Example: 18/12/2025 03:16:51   Build: 6.12.22  Usuário: DIEGO  SQL       delete from...
    // Supports 4-digit years
    if (preg_match('/^(\d{2}\/\d{2}\/\d{4}\s+\d{2}:\d{2}:\d{2})\s+Build:\s*[\d\.]+\s+Usuário:\s*(.*?)\s+(INFO|SQL|SQLERRO|ERRO)\s+(.*)$/i', $line, $matches)) {
        $timestamp = $matches[1];
        $user = trim($matches[2]);
        $type = strtoupper($matches[3]);
        $details = trim($matches[4]);
        
        $action = ucfirst(strtolower($type)); // Default action is the Type (Info, Sql, Erro)
        $table = '-'; // Default table

        // Refine Action/Table for SQL types
        if ($type === 'SQL' || $type === 'SQLERRO') {
            // Check for "ID gerado ... | QUERY"
            $query = $details;
            if (strpos($details, '|') !== false) {
                $parts = explode('|', $details, 2);
                if (count($parts) > 1) $query = trim($parts[1]);
            }

            // Detect SQL Verb
            if (preg_match('/^(INSERT INTO|UPDATE|DELETE FROM|DELETE|SELECT)\s+([a-zA-Z0-9_]+)/i', $query, $sqlMatches)) {
                $verb = strtoupper($sqlMatches[1]);
                $tbl = $sqlMatches[2];
                
                if (strpos($verb, 'INSERT') !== false) { $action = 'Insert'; $table = $tbl; }
                elseif (strpos($verb, 'UPDATE') !== false) { $action = 'Update'; $table = $tbl; }
                elseif (strpos($verb, 'DELETE') !== false) { $action = 'Delete'; $table = $tbl; }
                elseif (strpos($verb, 'SELECT') !== false) { $action = 'Select'; $table = $tbl; }
            } elseif (preg_match('/^(BEGIN|COMMIT)/i', $query, $txMatches)) {
                $action = ucfirst(strtolower($txMatches[1]));
                $table = 'TRANSACTION';
            }
        }

        return [
            'timestamp' => $timestamp,
            'user' => $user ?: 'Sistema',
            'action' => $action,
            'table' => $table,
            'details' => $details
        ];
    }
    
    // Fallback or specific file header handling
    if ($line === ';') return false; // Skip single delimiter lines
    
    return false;
}

function formatSql($sql) {
    // Simple naive formatter for PHP side if needed (though JS handles the modal)
    // We will rely on JS for the modal, this is just a placeholder or basic cleanup
    return trim($sql);
}

// Read File logic
$records = [];
$uniqueTables = [];
$uniqueUsers = [];
$uniqueActions = []; // New: Collect Actions dynamically

$totalRecords = 0;
$totalPages = 0;

if ($logFile && file_exists($logFile)) {
    try {
        $file = new SplFileObject($logFile);
        $file->setFlags(SplFileObject::DROP_NEW_LINE | SplFileObject::SKIP_EMPTY);
        
        // Skip Header? The new file doesn't seem to have a complex header, maybe just line 1 is ';'.
        // We'll trust parseLogLine to ignore invalid lines.
        // $file->seek(0); 

        $currentRecord = null;

        while (!$file->eof()) {
            $line = $file->fgets();
            $parsed = parseLogLine($line);
            
            if ($parsed) {
                if ($currentRecord) {
                    $records[] = $currentRecord;
                }
                $currentRecord = $parsed;
                
                // Collect for filters
                if ($parsed['table'] !== '-') $uniqueTables[$parsed['table']] = true;
                $uniqueUsers[$parsed['user']] = true;
                $uniqueActions[$parsed['action']] = true;
            } else {
                // Append Detail Line (Multi-line support)
                if ($currentRecord && trim($line) !== '' && trim($line) !== ';') {
                   // $currentRecord['details'] .= "\n" . trim($line); // Careful with appending garbage
                }
            }
        }
        if ($currentRecord) $records[] = $currentRecord;

        // Apply Filters
        if ($search || $filterAction || $filterTable || $filterDateStart || $filterDateEnd || $filterUser) {
            $records = array_filter($records, function($r) use ($search, $filterAction, $filterTable, $filterDateStart, $filterDateEnd, $filterUser) {
                if ($filterAction && strcasecmp($r['action'], $filterAction) !== 0) return false;
                if ($filterTable && strcasecmp($r['table'], $filterTable) !== 0) return false;
                if ($filterUser && strcasecmp($r['user'], $filterUser) !== 0) return false;
                
                // Date Filter (Format d/m/Y H:i:s)
                if ($filterDateStart || $filterDateEnd) {
                    $logDt = DateTime::createFromFormat('d/m/Y H:i:s', $r['timestamp']);
                    if ($logDt) {
                        $logDate = $logDt->format('Y-m-d');
                        if ($filterDateStart && $logDate < $filterDateStart) return false;
                        if ($filterDateEnd && $logDate > $filterDateEnd) return false;
                    }
                }

                if ($search) {
                    $term = strtolower($search);
                    $fullRow = strtolower($r['timestamp'] . ' ' . $r['user'] . ' ' . $r['action'] . ' ' . $r['table'] . ' ' . $r['details']);
                    return strpos($fullRow, $term) !== false;
                }
                return true;
            });
        }
        
        // Sort: Newest First (Reverse)
        $records = array_reverse($records);
        
        $totalRecords = count($records);
        $totalPages = ceil($totalRecords / $perPage);
        $offset = ($page - 1) * $perPage;
        $displayRecords = array_slice($records, $offset, $perPage);
        
        ksort($uniqueTables);
        ksort($uniqueUsers);
        ksort($uniqueActions);

    } catch (Exception $e) {
        $error = "Erro ao ler arquivo: " . $e->getMessage();
    }
} else {
    // Auto-detect latest file in auditoria/ if not specified
    if (!$logFile) {
        $files = glob(__DIR__ . '/auditoria/uviewer*.txt');
        if ($files) {
            // Sort by modification time, newest first
            usort($files, function($a, $b) {
                return filemtime($b) - filemtime($a);
            });
            $defaultFile = $files[0];
            if (file_exists($defaultFile)) {
                $logFile = $defaultFile;
                $_SESSION['active_log_file'] = $defaultFile;
                // Auto-loaded
            }
        }
    }
    
    if (!$logFile) $error = "Nenhum arquivo de log encontrado ou selecionado.";
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Auditor de Logs - Uniplus</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <!-- SQL Formatter & Prism -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sql-formatter/4.0.2/sql-formatter.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.24.1/themes/prism-coy.min.css" rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.24.1/prism.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.24.1/components/prism-sql.min.js"></script>
    
    <!-- SQL Formatter & Prism -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.24.1/themes/prism-coy.min.css" rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.24.1/prism.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.24.1/components/prism-sql.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sql-formatter/4.0.2/sql-formatter.min.js"></script>
    
    <style>
        /* ... (existing styles) */
        :root {
            /* Light Theme */
            --color-body-bg: #f8fafc;
            --color-card-bg: #ffffff;
            --color-text-main: #334155;
            --color-text-muted: #64748b;
            --color-border: #e2e8f0;
            --color-accent: #38bdf8;
            --glass-border: #cbd5e1;
            --glass-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        /* Compact Layout Tweaks */
        body {
            font-family: 'Inter', sans-serif;
            background: var(--color-body-bg);
            color: var(--color-text-main);
            min-height: 100vh;
            font-size: 0.9rem; /* Smaller global font */
        }

        .glass-panel {
            background: var(--color-card-bg);
            border: 1px solid var(--glass-border);
            box-shadow: var(--glass-shadow);
            border-radius: 8px; /* Tighter radius */
        }

        .code-block {
            font-family: 'Fira Code', monospace;
            font-size: 0.8rem;
            background: #f1f5f9 !important;
            padding: 8px;
            border-radius: 4px;
            color: #334155;
            white-space: pre-wrap;
            max-height: 200px;
            overflow-y: auto;
            border: 1px solid var(--color-border);
        }
        
        pre[class*="language-"] { margin: 0 !important; background: transparent !important; border: none !important; box-shadow: none !important; }
        code[class*="language-"], pre[class*="language-"] { color: #0f172a !important; text-shadow: none !important; }

        /* Uniform Inputs */
        .form-control, .form-select, .input-group-text {
            background-color: #ffffff !important;
            border-color: #cbd5e1 !important; /* Uniform slate-300 */
            color: #334155 !important;
        }
        .form-control:focus, .form-select:focus {
            border-color: #38bdf8 !important; /* Sky-400 */
            box-shadow: 0 0 0 0.2rem rgba(56, 189, 248, 0.15);
        }
        .input-group-text {
            background-color: #f8fafc !important; /* Slight gray for addons */
            color: #64748b !important;
        }

        /* Table Compact */
        .table {
            color: var(--color-text-main);
            --bs-table-bg: transparent;
            --bs-table-border-color: var(--color-border);
            font-size: 0.85rem;
        }
        .table > :not(caption) > * > * {
            padding: 0.4rem 0.6rem;
        }
        .table-hover tbody tr:hover td {
            background-color: #f1f5f9;
        }
        
        .badge { font-weight: 500; font-size: 0.75rem; }
        .badge-action-Insert { background-color: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
        .badge-action-Update { background-color: #dbeafe; color: #1d4ed8; border: 1px solid #bfdbfe; }
        .badge-action-Delete { background-color: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }
        .badge-action-Select { background-color: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }
        .badge-action-Info { background-color: #e0f2fe; color: #0284c7; border: 1px solid #bae6fd; }
        .badge-action-Erro, .badge-action-Error { background-color: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
        .badge-action-Sql, .badge-action-SqlErro { background-color: #f3f4f6; color: #4b5563; border: 1px solid #e5e7eb; }
        .badge-action-Begin, .badge-action-Commit, .badge-action-Transaction { background-color: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
        
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        
        .filters-sticky {
            position: sticky;
            top: 10px;
            z-index: 100;
            padding: 0.8rem !important;
        }
        
        h2 { font-size: 1.5rem; }
        h4, h5 { color: #0f172a; }
        .text-info { color: #0ea5e9 !important; }
        
        .modal-body-custom {
            max-height: 80vh;
            overflow-y: auto;
        }
    </style>
</head>
<body class="p-4">

<div class="container-fluid">
    <?php if (!$logFile): ?>
        <div class="d-flex flex-column align-items-center justify-content-center vh-100 pb-5">
             <div class="glass-panel p-5 text-center shadow-sm" style="max-width: 500px; width: 100%;">
                <i class="bi bi-cloud-upload text-primary mb-3" style="font-size: 4rem;"></i>
                <h2 class="fw-bold mb-3 text-dark">Carregar Auditoria</h2>
                <p class="text-muted mb-4">
                    Nenhum arquivo carregado. Selecione um arquivo de log (.txt) para iniciar a análise.
                </p>
                <button type="button" class="btn btn-primary btn-lg w-100 mb-3" data-bs-toggle="modal" data-bs-target="#uploadModal">
                    <i class="bi bi-file-earmark-arrow-up me-2"></i>Selecionar Arquivo
                </button>
                <a href="index.php" class="btn btn-link text-secondary text-decoration-none">Voltar ao Hub</a>
             </div>
        </div>
    <?php else: ?>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center gap-3">
            <div>
                <h2 class="fw-bold mb-0"><i class="bi bi-shield-check me-2"></i>Auditor de Logs</h2>
                <div class="d-flex align-items-center gap-2">
                    <p class="text-muted small mb-0">Análise do arquivo: 
                        <span class="fw-bold text-dark font-monospace"><?php echo basename($logFile); ?></span>
                    </p>
                    <?php if ($usingCustomFile): ?>
                        <a href="?reset_file=1" class="badge bg-secondary text-decoration-none" title="Voltar ao log padrão"><i class="bi bi-arrow-counterclockwise me-1"></i>Resetar</a>
                    <?php endif; ?>
                </div>
            </div>
            
            <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#uploadModal">
                <i class="bi bi-upload me-1"></i> Carregar .txt
            </button>
        </div>
        <a href="index.php" class="btn btn-secondary btn-sm"><i class="bi bi-house-door-fill me-1"></i>Voltar ao Hub</a>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger glass-panel border-0 text-white d-flex align-items-center">
            <i class="bi bi-exclamation-triangle-fill me-2 fs-4"></i>
            <div><?php echo $error; ?></div>
        </div>
    <?php else: ?>

        <!-- Filters -->
        <div class="glass-panel mb-4 filters-sticky">
            <form method="GET" class="row g-2 align-items-center">
                <div class="col-md-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Buscar em tudo..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                </div>
                 <div class="col-md-2">
                    <div class="input-group input-group-sm" title="Data Início">
                        <span class="input-group-text">De</span>
                        <input type="date" name="date_start" class="form-control" value="<?php echo htmlspecialchars($filterDateStart); ?>">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="input-group input-group-sm" title="Data Fim">
                        <span class="input-group-text">Até</span>
                        <input type="date" name="date_end" class="form-control" value="<?php echo htmlspecialchars($filterDateEnd); ?>">
                    </div>
                </div>
                <div class="col-md-2">
                    <select name="user" class="form-select form-select-sm">
                        <option value="">Usuário: Todos</option>
                        <?php foreach (array_keys($uniqueUsers) as $usr): ?>
                            <option value="<?php echo htmlspecialchars($usr); ?>" <?php echo $filterUser === $usr ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($usr); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <!-- ... -->
                <div class="col-md-1">
                    <select name="action" class="form-select form-select-sm">
                        <option value="">Ação: Todas</option>
                        <?php foreach (array_keys($uniqueActions) as $act): ?>
                            <option value="<?php echo htmlspecialchars($act); ?>" <?php echo $filterAction === $act ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($act); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-1">
                    <select name="table" class="form-select form-select-sm">
                        <option value="">Tabela: Todas</option>
                        <?php foreach (array_keys($uniqueTables) as $tbl): ?>
                            <option value="<?php echo htmlspecialchars($tbl); ?>" <?php echo $filterTable === $tbl ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($tbl); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-1 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm w-100"><i class="bi bi-funnel-fill"></i></button>
                    <?php if ($search || $filterAction || $filterTable || $filterDateStart || $filterDateEnd || $filterUser): ?>
                        <a href="audit_analyzer.php" class="btn btn-outline-secondary btn-sm" title="Limpar"><i class="bi bi-x-lg"></i></a>
                    <?php endif; ?>
                </div>
                <!-- Navigation Bar -->
                <hr class="my-2 border-secondary opacity-50">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted small fw-bold">
                        <i class="bi bi-list-ol me-1"></i> Total: <?php echo number_format($totalRecords, 0, ',', '.'); ?>
                    </div>
                    
                    <div class="d-flex align-items-center gap-2">
                        <!-- Rows Per Page -->
                        <div class="input-group input-group-sm" style="width: auto;">
                            <span class="input-group-text bg-light text-muted">Linhas</span>
                            <select name="per_page" class="form-select" onchange="this.form.submit()" style="max-width: 80px;">
                                <?php foreach([50, 100, 200, 500, 1000] as $lim): ?>
                                    <option value="<?php echo $lim; ?>" <?php echo $perPage == $lim ? 'selected' : ''; ?>><?php echo $lim; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Pagination Controls -->
                        <div class="btn-group btn-group-sm">
                            <!-- Prev -->
                            <?php if ($page > 1): ?>
                                <button type="submit" name="page" value="1" class="btn btn-outline-secondary" title="Primeira Página"><i class="bi bi-chevron-double-left"></i></button>
                                <button type="submit" name="page" value="<?php echo $page - 1; ?>" class="btn btn-outline-secondary" title="Anterior"><i class="bi bi-chevron-left"></i></button>
                            <?php else: ?>
                                <button type="button" class="btn btn-outline-secondary disabled"><i class="bi bi-chevron-double-left"></i></button>
                                <button type="button" class="btn btn-outline-secondary disabled"><i class="bi bi-chevron-left"></i></button>
                            <?php endif; ?>

                            <!-- Current Page Input -->
                            <div class="input-group input-group-sm" style="width: 130px;">
                                <input type="number" name="page" class="form-control text-center border-secondary" min="1" max="<?php echo $totalPages; ?>" value="<?php echo $page; ?>">
                                <span class="input-group-text bg-light text-muted">de <?php echo $totalPages; ?></span>
                            </div>

                            <!-- Next -->
                            <?php if ($page < $totalPages): ?>
                                <button type="submit" name="page" value="<?php echo $page + 1; ?>" class="btn btn-outline-secondary" title="Próxima"><i class="bi bi-chevron-right"></i></button>
                                <button type="submit" name="page" value="<?php echo $totalPages; ?>" class="btn btn-outline-secondary" title="Última Página"><i class="bi bi-chevron-double-right"></i></button>
                            <?php else: ?>
                                <button type="button" class="btn btn-outline-secondary disabled"><i class="bi bi-chevron-right"></i></button>
                                <button type="button" class="btn btn-outline-secondary disabled"><i class="bi bi-chevron-double-right"></i></button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Table -->
        <?php if (empty($displayRecords)): ?>
            <div class="glass-panel p-5 text-center text-muted">
                <i class="bi bi-inbox fs-1 mb-3 d-block"></i>
                Nenhum registro encontrado.
            </div>
        <?php else: ?>
            <div class="glass-panel overflow-hidden">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="bg-light text-secondary">
                            <tr>
                                <th class="ps-4">Data/Hora</th>
                                <th>Usuário</th>
                                <th>Ação</th>
                                <th>Tabela</th>
                                <th>Detalhes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($displayRecords as $rec): ?>
                                <?php 
                                    $rowClass = '';
                                    if ($rec['action'] === 'Info') $rowClass = 'table-info bg-opacity-10';
                                    elseif (in_array($rec['action'], ['Erro', 'Error', 'SqlErro'])) $rowClass = 'table-danger bg-opacity-10';
                                    elseif (in_array($rec['action'], ['Begin', 'Commit', 'Transaction'])) $rowClass = 'table-success bg-opacity-10';
                                ?>
                                <tr class="<?php echo $rowClass; ?>">
                                    <td class="ps-4 text-nowrap font-monospace text-secondary small fw-bold">
                                        <?php echo htmlspecialchars($rec['timestamp']); ?>
                                    </td>
                                    <td class="fw-bold"><?php echo htmlspecialchars($rec['user']); ?></td>
                                    <td>
                                        <span class="badge rounded-pill badge-action-<?php echo $rec['action']; ?>">
                                            <?php echo htmlspecialchars($rec['action']); ?>
                                        </span>
                                    </td>
                                    <td class="font-monospace text-info small"><?php echo htmlspecialchars($rec['table']); ?></td>
                                    <td class="small">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="text-truncate me-2" style="max-width: 400px;" title="<?php echo htmlspecialchars($rec['details']); ?>">
                                                <?php 
                                                    // For INFO, show text clearly. For others, truncate more.
                                                    $displayText = $rec['details'];
                                                    if ($rec['action'] === 'Info') {
                                                        echo '<i class="bi bi-cursor-fill me-1 text-muted"></i>' . htmlspecialchars($displayText);
                                                    } else {
                                                        echo htmlspecialchars(substr($displayText, 0, 80)) . (strlen($displayText) > 80 ? '...' : '');
                                                    }
                                                ?>
                                            </div>
                                            <button class="btn btn-sm btn-outline-primary btn-details flex-shrink-0"
                                                    data-action="<?php echo htmlspecialchars($rec['action']); ?>" 
                                                    data-table="<?php echo htmlspecialchars($rec['table']); ?>"
                                                    data-timestamp="<?php echo htmlspecialchars($rec['timestamp']); ?>"
                                                    data-user="<?php echo htmlspecialchars($rec['user']); ?>"
                                                    data-details="<?php echo htmlspecialchars($rec['details']); ?>">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>


        <?php endif; ?>
    <?php endif; ?>

</div>

<!-- Details Modal -->
<div class="modal fade" id="detailsModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header border-bottom-0" style="background: #f1f5f9;">
        <div>
            <h5 class="modal-title fw-bold text-dark" id="modalTitle">Detalhes do Registro</h5>
            <small class="text-muted" id="modalSubtitle">Visualização complet</small>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-0 modal-body-custom">
        <div class="d-flex border-bottom bg-white">
            <button class="btn btn-link text-decoration-none py-3 px-4 border-bottom border-primary border-2 fw-bold text-primary active-tab-btn" id="btnTabVisual">
                <i class="bi bi-table me-2"></i>Visualização
            </button>
            <button class="btn btn-link text-decoration-none py-3 px-4 text-muted" id="btnTabSql">
                <i class="bi bi-code-slash me-2"></i>SQL
            </button>
        </div>
        
        <div class="p-4 bg-white" id="tabVisual">
            <div id="visualContent"></div>
        </div>
        <div class="p-4 bg-light d-none" id="tabSql">
            <div class="d-flex justify-content-end mb-2">
                <button class="btn btn-sm btn-outline-secondary bg-white" id="btnCopySql">
                    <i class="bi bi-clipboard me-1"></i> Copiar SQL
                </button>
            </div>
            <div class="code-block bg-white border position-relative">
                <code class="language-sql" id="sqlContent">...</code>
            </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Details Modal ... (existing) -->

<!-- Upload Modal -->
<div class="modal fade" id="uploadModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-bottom-0">
                <h5 class="modal-title fw-bold">Carregar Arquivo de Auditoria</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="POST" enctype="multipart/form-data" action="audit_analyzer.php">
                    <div class="mb-3">
                        <label for="audit_file" class="form-label text-muted small text-uppercase fw-bold">Selecione o arquivo .txt</label>
                        <input class="form-control" type="file" id="audit_file" name="audit_file" accept=".txt" required>
                    </div>
                    <div class="alert alert-info d-flex align-items-center small">
                        <i class="bi bi-info-circle-fill me-2 fs-5"></i>
                        <div>O arquivo será analisado e usado temporariamente nesta sessão.</div>
                    </div>
                    <div class="d-flex justify-content-end">
                        <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-upload me-1"></i> Carregar e Analisar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    // SQL Formatter
    const formatSql = (sql) => {
        try { return sqlFormatter.format(sql, { language: 'postgresql' }); } catch (e) { return sql; }
    };

    // Reconstruct SQL Helper
    const reconstructSql = (action, table, details) => {
        let sql = '';
        const lines = details.split('\n');
        
        if (action === 'Insert') {
            const cols = []; const vals = [];
            const regex = /(\w+)=('[^']*'|[^,\s]+)/g;
            let match;
            while ((match = regex.exec(details)) !== null) {
                cols.push(match[1]); vals.push(match[2]);
            }
            if (cols.length > 0) sql = `INSERT INTO ${table} (${cols.join(', ')}) VALUES (${vals.join(', ')})`;
            else sql = `INSERT INTO ${table} ... (Parsing Failed)`;
            
        } else if (action === 'Update') {
            const whereClause = lines[0].replace(/(\w+)=('[^']*'|[^,\s]+)/g, "$1 = $2");
            const setPairs = [];
            for (let i = 1; i < lines.length; i++) {
                const regex = /(\w+)=('[^']*'|[^,\s]+)/g;
                let match;
                while ((match = regex.exec(lines[i])) !== null) setPairs.push(`${match[1]} = ${match[2]}`);
            }
            if (setPairs.length > 0) sql = `UPDATE ${table} SET ${setPairs.join(', ')} WHERE ${whereClause}`;
            else sql = `UPDATE ${table} SET ... WHERE ${whereClause}`;

        } else if (action === 'Delete') {
             sql = `DELETE FROM ${table} WHERE ${details.replace(/\n/g, ' ')}`;
        } else if (['Begin', 'Commit', 'Transaction'].includes(action)) {
             sql = details; // Already SQL
        } else if (['Info', 'Erro', 'Error', 'SqlErro'].includes(action)) {
             sql = `-- [${action.toUpperCase()}] Log Message:\n${details}`;
        } else {
            sql = `-- Unknown Action: ${action}\n${details}`;
        }
        return sql;
    };
    
    // Parse to Table
    const parseToTable = (action, details) => {
        // Simple View for non-structured data
        if (['Info', 'Erro', 'Error', 'SqlErro', 'Begin', 'Commit', 'Transaction'].includes(action)) {
            let colorClass = 'text-secondary';
            if (action.includes('Erro')) colorClass = 'text-danger fw-bold';
            if (['Begin', 'Commit', 'Transaction'].includes(action)) colorClass = 'text-success fw-bold font-monospace';
            
            return `<div class="p-3 ${colorClass}" style="white-space: pre-wrap;">${details}</div>`;
        }

        let html = '<table class="table table-bordered table-striped table-sm mb-0"><thead><tr class="table-light"><th style="width:30%">Campo</th><th>Valor</th></tr></thead><tbody>';
        let hasRows = false;
        
        const row = (k, v) => `<tr><td class="fw-bold text-secondary font-monospace">${k}</td><td class="font-monospace text-primary text-break">${v}</td></tr>`;

        if (action === 'Insert') {
            const regex = /(\w+)=('[^']*'|[^,\s]+)/g;
            let match;
            while ((match = regex.exec(details)) !== null) {
                html += row(match[1], match[2]);
                hasRows = true;
            }
        } else if (action === 'Update') {
            const lines = details.split('\n');
            // Where or All Fields
            const headerLabel = lines.length > 1 ? 'Critério (Where)' : 'Dados / Campos';
            const headerIcon = lines.length > 1 ? 'bi-funnel' : 'bi-list-check';
            html += `<tr><td colspan="2" class="bg-warning bg-opacity-10 fw-bold border-warning text-warning-emphasis"><i class="bi ${headerIcon} me-1"></i>${headerLabel}</td></tr>`;
            
            const regexWhere = /(\w+)=('[^']*'|[^,\s]+)/g;
            let matchW;
            while ((matchW = regexWhere.exec(lines[0])) !== null) {
                html += row(matchW[1], matchW[2]);
            }

            html += `<tr><td colspan="2" class="bg-info bg-opacity-10 fw-bold border-info text-info-emphasis"><i class="bi bi-pencil me-1"></i>Alterações (Set)</td></tr>`;
            for (let i = 1; i < lines.length; i++) {
                const regex = /(\w+)=('[^']*'|[^,\s]+)/g;
                let match;
                while ((match = regex.exec(lines[i])) !== null) {
                    html += row(match[1], match[2]);
                    hasRows = true;
                }
            }
        } else if (action === 'Delete') {
             html += `<tr><td colspan="2" class="bg-danger bg-opacity-10 fw-bold border-danger text-danger-emphasis"><i class="bi bi-trash me-1"></i>Critério de Exclusão</td></tr>`;
             html += `<tr><td colspan="2" class="font-monospace">${details.replace(/\n/g, '<br>')}</td></tr>`;
             hasRows = true;
        }

        if (!hasRows && action !== 'Delete') {
             html += `<tr><td colspan="2" class="text-muted text-center py-3">Não foi possível extrair campos estruturados.</td></tr>`;
        }
        
        html += '</tbody></table>';
        return html;
    };

    // Modal Handling
    const modalEl = document.getElementById('detailsModal');
    const modal = new bootstrap.Modal(modalEl);
    const btnTabVisual = document.getElementById('btnTabVisual');
    const btnTabSql = document.getElementById('btnTabSql');
    const tabVisual = document.getElementById('tabVisual');
    const tabSql = document.getElementById('tabSql');

    // Tab Switching
    btnTabVisual.addEventListener('click', () => {
        tabVisual.classList.remove('d-none');
        tabSql.classList.add('d-none');
        btnTabVisual.classList.add('border-bottom', 'border-primary', 'border-2', 'fw-bold', 'text-primary');
        btnTabVisual.classList.remove('text-muted');
        btnTabSql.classList.remove('border-bottom', 'border-primary', 'border-2', 'fw-bold', 'text-primary');
        btnTabSql.classList.add('text-muted');
    });

    btnTabSql.addEventListener('click', () => {
        tabSql.classList.remove('d-none');
        tabVisual.classList.add('d-none');
        btnTabSql.classList.add('border-bottom', 'border-primary', 'border-2', 'fw-bold', 'text-primary');
        btnTabSql.classList.remove('text-muted');
        btnTabVisual.classList.remove('border-bottom', 'border-primary', 'border-2', 'fw-bold', 'text-primary');
        btnTabVisual.classList.add('text-muted');
    });

    // Open Modal
    document.querySelectorAll('.btn-details').forEach(btn => {
        btn.addEventListener('click', () => {
            const action = btn.dataset.action;
            const table = btn.dataset.table;
            const time = btn.dataset.timestamp;
            const user = btn.dataset.user;
            const details = btn.dataset.details;

            document.getElementById('modalTitle').innerHTML = `<span class="badge badge-action-${action} me-2">${action}</span> ${table}`;
            document.getElementById('modalSubtitle').innerText = `${time} - Por: ${user}`;

            // Populate SQL
            const sql = reconstructSql(action, table, details);
            const formatted = formatSql(sql);
            const sqlContainer = document.getElementById('sqlContent');
            sqlContainer.textContent = formatted;
            Prism.highlightElement(sqlContainer);

            // Populate Visual
            document.getElementById('visualContent').innerHTML = parseToTable(action, details);

            // Reset Tabs
            btnTabVisual.click();
            
            modal.show();
        });
    });

    // Copy SQL Handler
    document.getElementById('btnCopySql').addEventListener('click', () => {
        const sqlContent = document.getElementById('sqlContent').innerText;
        navigator.clipboard.writeText(sqlContent).then(() => {
            const btn = document.getElementById('btnCopySql');
            const originalHtml = btn.innerHTML;
            
            btn.innerHTML = '<i class="bi bi-check-lg me-1"></i> Copiado!';
            btn.classList.remove('btn-outline-secondary', 'bg-white');
            btn.classList.add('btn-success', 'text-white');
            
            setTimeout(() => {
                btn.innerHTML = originalHtml;
                btn.classList.remove('btn-success', 'text-white');
                btn.classList.add('btn-outline-secondary', 'bg-white');
            }, 2000);
        }).catch(err => {
            console.error('Failed to copy: ', err);
            alert('Erro ao copiar para a área de transferência.');
        });
    });
});
</script>
</body>
</html>
