<?php
session_start();
include 'includes/header.php';

// Glass Theme Styles
echo '<style>
    :root {
        /* Base Glass Vars - Light Mode */
        --glass-border: rgba(0, 0, 0, 0.1);
        --glass-highlight: rgba(255, 255, 255, 0.4);
        --glass-shadow: 0 4px 30px rgba(0, 0, 0, 0.05);
        
        /* Light Theme */
        --color-body-bg: #f8fafc; /* Plain White/Light Gray Background */

        --color-card-bg: rgba(255, 255, 255, 0.8);
        --color-card-header-bg: rgba(241, 245, 249, 0.8);
        --color-card-item-header-bg: rgba(241, 245, 249, 0.6);
        --color-card-subtle-bg: rgba(255, 255, 255, 0.5);

        --color-text-main: #0f172a;      /* Dark text */
        --color-text-primary: #1e293b;   /* Darker text */
        --color-text-secondary: #334155;
        --color-text-muted: #64748b;
        --color-text-placeholder: #94a3b8;
        --color-text-inverted: #f8fafc;

        --color-border: rgba(203, 213, 225, 0.6);
        --color-border-accent: rgba(14, 165, 233, 0.5);

        --color-input-bg: rgba(255, 255, 255, 0.9);

        --color-header-bg: rgba(255, 255, 255, 0.8);
        --color-header-border: rgba(226, 232, 240, 0.6);
        --color-header-text: #0f172a;

        --color-glass-border: rgba(0, 0, 0, 0.05);
    }

    /* GLOBAL STYLES */
    body {
        font-family: "Inter", sans-serif;
        background: var(--color-body-bg);
        color: var(--color-text-main);
        min-height: 100vh;
    }

    /* GLASS UTILITIES */
    .glass-panel, .card, .modal-content {
        background: var(--color-card-bg) !important;
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid var(--color-glass-border) !important;
        box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        color: var(--color-text-main);
    }

    .bg-light, .bg-white {
        background-color: transparent !important;
    }

    .text-dark {
        color: var(--color-text-main) !important;
    }

    .text-muted {
        color: var(--color-text-muted) !important;
    }

    /* INPUTS */
    .form-control, .form-select {
        background-color: var(--color-input-bg) !important;
        border: 1px solid var(--color-border);
        color: var(--color-text-main) !important;
    }
    .form-control::placeholder {
        color: var(--color-text-placeholder);
    }
    .form-control:focus, .form-select:focus {
        background-color: #ffffff !important;
        border-color: var(--color-border-accent);
        box-shadow: 0 0 0 0.25rem rgba(14, 165, 233, 0.15);
        color: var(--color-text-main) !important;
    }

    /* TABLES */
    .table {
        color: var(--color-text-main);
        --bs-table-bg: transparent;
        --bs-table-hover-bg: rgba(0, 0, 0, 0.03);
        --bs-table-border-color: var(--color-border);
    }
    .table thead {
        border-bottom: 2px solid var(--color-border);
        background-color: rgba(0, 0, 0, 0.02);
    }
    
    /* SIDEBAR */
    .dict-sidebar {
        border-right: 1px solid var(--color-border);
    }
    .dict-table-item {
        color: var(--color-text-secondary);
        padding: 8px 12px;
        cursor: pointer;
        border-radius: 6px;
        transition: all 0.2s;
    }
    .dict-table-item:hover {
        background: rgba(0, 0, 0, 0.05);
        color: var(--color-text-primary);
    }
    .dict-table-item.active {
        background: rgba(14, 165, 233, 0.1);
        color: #0ea5e9;
        border-left: 3px solid #0ea5e9;
    }
    
    /* MODALS */
    .modal-header, .modal-footer {
        border-color: var(--color-border);
    }
    .btn-close {
        filter: none; /* Reset invert for light mode */
    }

    /* NAV TABS */
    .nav-tabs .nav-link {
        color: var(--color-text-muted);
        border: none;
        background: transparent;
    }
    .nav-tabs .nav-link:hover {
        color: var(--color-text-main);
        border-color: transparent;
    }
    .nav-tabs .nav-link.active {
        color: #0ea5e9;
        background: transparent;
        border-bottom: 2px solid #0ea5e9;
    }
</style>';

$tables = [];
$stats = ['tables' => 0, 'columns' => 0, 'fks' => 0];
$hasData = false;
$defaultFile = 'dicionariodados.html';
$isAdmin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
$isTemp = false;
$content = null;

// Handle Actions (Login/Logout)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'login') {
        $user = trim($_POST['user'] ?? '');
        $pass = trim($_POST['pass'] ?? '');
        
        if ($user === 'administrador' && $pass === 'S9T"jR<@d78t') {
            $_SESSION['is_admin'] = true;
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Credenciais inválidas']);
        }
        exit;
    }

    if ($_POST['action'] === 'logout') {
        unset($_SESSION['is_admin']);
        header('Location: dictionary.php');
        exit;
    }
}

// Handle File Upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
    $uploadType = $_POST['upload_type'] ?? 'temp';
    
    if ($isAdmin && $uploadType === 'system') {
        move_uploaded_file($_FILES['file']['tmp_name'], $defaultFile);
        // Reload to pick up the new default file
        header('Location: dictionary.php');
        exit;
    } else {
        // Temporary Upload
        $content = file_get_contents($_FILES['file']['tmp_name']);
        $isTemp = true;
    }
}

// Load Dictionary Data
if ($content === null && file_exists($defaultFile)) {
    $content = file_get_contents($defaultFile);
}

if ($content) {
    libxml_use_internal_errors(true);
    $dom = new DOMDocument();
    // Hack to handle UTF-8 correctly in loadHTML
    $dom->loadHTML('<?xml encoding="utf-8" ?>' . $content);
    libxml_clear_errors();

    $allTables = $dom->getElementsByTagName('table');
    
    foreach ($allTables as $table) {
        $rows = $table->getElementsByTagName('tr');
        if ($rows->length < 2) continue;

        // Clean up Table Name and Description
        $rawName = trim($rows->item(0)->nodeValue);
        $tableName = trim(str_ireplace('Nome da tabela', '', $rawName));
        
        $rawDesc = trim($rows->item(1)->nodeValue);
        $tableDesc = trim(str_ireplace('Descrição', '', $rawDesc));
        
        $fields = [];
        $foreignKeys = [];
        $section = '';

        foreach ($rows as $row) {
            $ths = $row->getElementsByTagName('th');
            $tds = $row->getElementsByTagName('td');

            if ($ths->length > 0) {
                $headerText = trim($ths->item(0)->nodeValue);
                if ($headerText === 'Lista de campos') {
                    $section = 'fields';
                    continue;
                }
                if ($headerText === 'Chaves Estrangeiras') {
                    $section = 'fks';
                    continue;
                }
            }

            if ($tds->length === 4) {
                if ($section === 'fields') {
                    $fields[] = [
                        'name' => trim($tds->item(0)->nodeValue),
                        'type' => trim($tds->item(1)->nodeValue),
                        'size' => trim(str_replace('&nbsp;', '', $tds->item(2)->nodeValue)),
                        'desc' => trim($tds->item(3)->nodeValue),
                    ];
                } elseif ($section === 'fks') {
                    $foreignKeys[] = [
                        'name' => trim($tds->item(0)->nodeValue),
                        'col'  => trim($tds->item(1)->nodeValue),
                        'refTable' => trim($tds->item(2)->nodeValue),
                        'refCol' => trim($tds->item(3)->nodeValue),
                    ];
                }
            }
        }

        if (!empty($fields)) {
            $tables[] = [
                'name' => $tableName,
                'desc' => $tableDesc,
                'fields' => $fields,
                'fks' => $foreignKeys
            ];
            $stats['columns'] += count($fields);
            $stats['fks'] += count($foreignKeys);
        }
    }
    $stats['tables'] = count($tables);
    $hasData = !empty($tables);
}
?>
</div>
<div class="container-fluid px-4 py-4 bg-light min-vh-100">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h4 fw-bold text-dark mb-0">Analisador de Dicionário de Dados</h1>
            <?php if ($isTemp): ?>
                <span class="badge bg-warning text-dark"><i class="bi bi-exclamation-triangle me-1"></i>Versão Temporária</span>
            <?php else: ?>
                <p class="text-muted small mb-0">Versão Base: 6.12.24</p>
            <?php endif; ?>
        </div>
        <div class="d-flex gap-2">
            <?php if ($hasData): ?>
                <button class="btn btn-outline-info btn-sm bg-white" data-bs-toggle="modal" data-bs-target="#helpModal">
                    <i class="bi bi-question-circle me-2"></i>Ajuda
                </button>
            <?php endif; ?>

            <?php if ($isTemp): ?>
                <a href="dictionary.php" class="btn btn-outline-danger btn-sm bg-white">
                    <i class="bi bi-arrow-counterclockwise me-2"></i>Restaurar Padrão
                </a>
            <?php else: ?>
                <button class="btn btn-outline-secondary btn-sm bg-white" onclick="document.getElementById('uploadForm').style.display = document.getElementById('uploadForm').style.display === 'none' ? 'block' : 'none';">
                    <i class="bi bi-cloud-upload me-2"></i>Carregar Arquivo
                </button>
            <?php endif; ?>

            <?php if ($isAdmin): ?>
                <form method="POST" class="d-inline">
                    <input type="hidden" name="action" value="logout">
                    <button type="submit" class="btn btn-outline-danger btn-sm bg-white">
                        <i class="bi bi-box-arrow-right me-2"></i>Sair (Admin)
                    </button>
                </form>
            <?php else: ?>
                <button class="btn btn-outline-primary btn-sm bg-white" data-bs-toggle="modal" data-bs-target="#loginModal">
                    <i class="bi bi-shield-lock me-2"></i>Admin
                </button>
            <?php endif; ?>
            
            <a href="index.php" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
        </div>
    </div>

    <!-- Upload Form & Instructions -->
    <div id="uploadForm" class="card shadow-sm mb-4" style="display:none;">
        <div class="card-body p-4">
            <div class="row">
                <div class="col-md-6 border-end">
                    <h5 class="fw-bold mb-3"><i class="bi bi-info-circle me-2 text-primary"></i>Como Gerar o Dicionário</h5>
                    <p class="text-muted small">Para gerar a versão mais recente do dicionário de dados do Uniplus:</p>
                    <ol class="text-muted small">
                        <li>Acesse a pasta raiz do sistema (onde está o <code>uniplus.jar</code>).</li>
                        <li>Abra o CMD (Prompt de Comando) nesta pasta.</li>
                        <li>Execute o seguinte comando:</li>
                    </ol>
                    <div class="bg-dark text-light p-2 rounded small font-monospace mb-3 user-select-all">
                        java -Dfile.encoding=UTF8 -cp uniplus.jar br.intelidata.commons.core.sql.GerarDicionarioUnicoEmHTML
                    </div>
                    <p class="text-muted small mb-0">Isso criará um arquivo HTML na pasta.</p>
                </div>
                <div class="col-md-6 d-flex flex-column justify-content-center text-center">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <i class="bi bi-cloud-arrow-up display-4 text-primary mb-3 d-block"></i>
                            <h5 class="fw-bold">Carregar Dicionário</h5>
                            <p class="text-muted small">Selecione o arquivo HTML gerado.</p>
                            <input class="form-control w-75 mx-auto mb-3" type="file" name="file" accept=".html" required>
                            
                            <?php if ($isAdmin): ?>
                                <div class="form-check form-switch d-inline-block text-start bg-light p-2 rounded border mb-3">
                                    <input class="form-check-input" type="checkbox" name="upload_type" value="system" id="updateSystemCheck">
                                    <label class="form-check-label small fw-bold" for="updateSystemCheck">Atualizar Sistema (Definitivo)</label>
                                    <div class="text-muted xsmall">Se marcado, substitui a versão padrão para todos.</div>
                                </div>
                            <?php else: ?>
                                <input type="hidden" name="upload_type" value="temp">
                                <div class="alert alert-info py-1 px-2 small d-inline-block mb-3">
                                    <i class="bi bi-eye me-1"></i>Modo de Visualização Temporária
                                </div>
                            <?php endif; ?>
                        </div>
                        <button type="submit" class="btn btn-primary px-4">
                            Carregar Arquivo
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php if ($hasData): ?>
        <!-- Stats Row -->
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="dict-stats-card">
                    <div class="d-flex justify-content-between align-items-start">
                        <span class="dict-stats-label">Total de Tabelas</span>
                        <i class="bi bi-bar-chart text-muted"></i>
                    </div>
                    <div class="dict-stats-value"><?php echo $stats['tables']; ?></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="dict-stats-card">
                    <div class="d-flex justify-content-between align-items-start">
                        <span class="dict-stats-label">Total de Colunas</span>
                        <i class="bi bi-layout-three-columns text-muted"></i>
                    </div>
                    <div class="dict-stats-value"><?php echo $stats['columns']; ?></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="dict-stats-card">
                    <div class="d-flex justify-content-between align-items-start">
                        <span class="dict-stats-label">Chaves Estrangeiras</span>
                        <i class="bi bi-link text-muted"></i>
                    </div>
                    <div class="dict-stats-value"><?php echo $stats['fks']; ?></div>
                </div>
            </div>
        </div>

        <!-- Search & Filters -->
        <div class="dict-search-container mb-4">
            <div class="row g-3">
                <div class="col-md-10">
                    <div class="position-relative">
                        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                        <input type="text" id="searchInput" class="form-control dict-search-input ps-5" placeholder="Buscar em todas as tabelas, colunas, tipos..." onkeyup="filterTables()">
                    </div>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-search w-100" onclick="filterTables()">
                        <i class="bi bi-search me-2"></i>Buscar
                    </button>
                </div>
            </div>
            <div class="d-flex gap-4 mt-3 align-items-center flex-wrap">
                <div class="d-flex align-items-center gap-2">
                    <input type="radio" name="searchScope" id="scopeAll" value="all" checked onchange="filterTables()">
                    <label for="scopeAll" class="text-sm text-muted-foreground cursor-pointer">Tudo</label>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <input type="radio" name="searchScope" id="scopeTable" value="table" onchange="filterTables()">
                    <label for="scopeTable" class="text-sm text-muted-foreground cursor-pointer">Nomes de Tabela</label>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <input type="radio" name="searchScope" id="scopeColumn" value="column" onchange="filterTables()">
                    <label for="scopeColumn" class="text-sm text-muted-foreground cursor-pointer">Nomes de Coluna</label>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <input type="checkbox" id="exactMatch" onchange="filterTables()">
                    <label for="exactMatch" class="text-sm text-muted-foreground cursor-pointer">Busca Exata</label>
                </div>
                <button class="btn btn-link text-decoration-none text-muted p-0 ms-auto small" onclick="clearFilters()">
                    <i class="bi bi-x me-1"></i> Limpar Filtros
                </button>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="card shadow-sm border-0 overflow-hidden">
            <div class="row g-0">
                <!-- Sidebar -->
                <div class="col-md-3 border-end">
                    <div class="p-3 border-bottom bg-white">
                        <h6 class="fw-bold mb-2">Tabelas (<?php echo $stats['tables']; ?>)</h6>
                        <input type="text" class="form-control form-control-sm bg-light border-0" placeholder="Filtrar tabelas..." id="sidebarFilter" onkeyup="filterSidebar()">
                    </div>
                    <div class="dict-sidebar" id="tableList">
                        <?php foreach ($tables as $index => $table): ?>
                            <div class="dict-table-item" 
                                 onclick="showTable(<?php echo $index; ?>, this)"
                                 data-index="<?php echo $index; ?>"
                                 data-name="<?php echo strtolower($table['name']); ?>"
                                 data-desc="<?php echo strtolower($table['desc']); ?>"
                                 data-columns="<?php echo strtolower(implode(' ', array_column($table['fields'], 'name'))); ?>">
                                <div class="fw-bold text-dark mb-1 text-truncate"><?php echo htmlspecialchars($table['name']); ?></div>
                                <div class="small text-muted text-truncate"><?php echo htmlspecialchars($table['desc']); ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Content -->
                <div class="col-md-9 bg-white">
                    <div id="emptyState" class="h-100 d-flex flex-column align-items-center justify-content-center text-muted">
                        <i class="bi bi-table display-4 mb-3 opacity-25"></i>
                        <p>Selecione uma tabela para ver os detalhes.</p>
                    </div>

                    <?php foreach ($tables as $index => $table): ?>
                        <div id="table-details-<?php echo $index; ?>" class="table-details h-100 d-none flex-column">
                            <div class="p-4 border-bottom d-flex justify-content-between align-items-start">
                                <div>
                                    <h2 class="h4 fw-bold text-dark mb-1"><?php echo htmlspecialchars($table['name']); ?></h2>
                                    <p class="text-muted mb-0"><?php echo htmlspecialchars($table['desc']); ?></p>
                                </div>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-outline-success btn-sm" onclick="exportSchema(<?php echo $index; ?>)">
                                        <i class="bi bi-filetype-json me-2"></i>Exportar Schema
                                    </button>
                                    <button class="btn btn-outline-primary btn-sm" onclick="openSqlGenerator(<?php echo $index; ?>)">
                                        <i class="bi bi-code-slash me-2"></i>Gerador SQL
                                    </button>
                                </div>
                            </div>
                            
                            <div class="px-4 pt-3">
                                <ul class="nav nav-tabs nav-tabs-custom" role="tablist">
                                    <li class="nav-item">
                                        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#cols-<?php echo $index; ?>">
                                            Colunas
                                        </button>
                                    </li>
                                    <li class="nav-item">
                                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#fks-<?php echo $index; ?>">
                                            Chaves Estrangeiras (<?php echo count($table['fks']); ?>)
                                        </button>
                                    </li>
                                </ul>
                            </div>

                            <div class="tab-content flex-grow-1 overflow-auto p-4">
                                <!-- Columns Tab -->
                                <div class="tab-pane fade show active" id="cols-<?php echo $index; ?>">
                                    <div class="d-flex justify-content-end mb-3">
                                        <input type="text" class="form-control form-control-sm w-25" placeholder="Filtrar colunas..." onkeyup="filterColumns(this, 'cols-<?php echo $index; ?>')">
                                    </div>
                                    <div class="border rounded">
                                        <table class="table table-hover mb-0">
                                            <thead class="bg-light">
                                                <tr>
                                                    <th class="border-bottom-0 text-muted small font-weight-bold">Nome</th>
                                                    <th class="border-bottom-0 text-muted small font-weight-bold">Tipo</th>
                                                    <th class="border-bottom-0 text-muted small font-weight-bold">Tamanho</th>
                                                    <th class="border-bottom-0 text-muted small font-weight-bold">Descrição</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($table['fields'] as $field): ?>
                                                    <tr>
                                                        <td class="fw-medium"><?php echo htmlspecialchars($field['name']); ?></td>
                                                        <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($field['type']); ?></span></td>
                                                        <td class="text-muted small"><?php echo htmlspecialchars($field['size']); ?></td>
                                                        <td class="text-muted small"><?php echo htmlspecialchars($field['desc']); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- FKs Tab -->
                                <div class="tab-pane fade" id="fks-<?php echo $index; ?>">
                                    <?php if (empty($table['fks'])): ?>
                                        <p class="text-muted text-center py-5">Nenhuma chave estrangeira encontrada.</p>
                                    <?php else: ?>
                                        <div class="d-flex justify-content-end mb-3">
                                            <input type="text" class="form-control form-control-sm w-25" placeholder="Filtrar chaves..." onkeyup="filterForeignKeys(this, 'fks-<?php echo $index; ?>')">
                                        </div>
                                        <div class="border rounded">
                                            <table class="table table-hover mb-0">
                                                <thead class="bg-light">
                                                    <tr>
                                                        <th class="border-bottom-0 text-muted small font-weight-bold">Nome</th>
                                                        <th class="border-bottom-0 text-muted small font-weight-bold">Coluna</th>
                                                        <th class="border-bottom-0 text-muted small font-weight-bold">Tabela Relacionada</th>
                                                        <th class="border-bottom-0 text-muted small font-weight-bold">Coluna Relacionada</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($table['fks'] as $fk): ?>
                                                        <tr>
                                                            <td class="text-muted small"><?php echo htmlspecialchars($fk['name']); ?></td>
                                                            <td class="font-monospace small"><?php echo htmlspecialchars($fk['col']); ?></td>
                                                            <td>
                                                                <a href="#" class="text-primary text-decoration-none" 
                                                                   onclick="openTableModal('<?php echo htmlspecialchars($fk['refTable']); ?>'); return false;">
                                                                    <?php echo htmlspecialchars($fk['refTable']); ?>
                                                                </a>
                                                            </td>
                                                            <td class="font-monospace small"><?php echo htmlspecialchars($fk['refCol']); ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Toast -->
        <div id="toast" class="toast-custom">
            <h6 class="fw-bold mb-1">Dicionário carregado</h6>
            <p class="mb-0 text-muted small"><?php echo $stats['tables']; ?> tabelas foram carregadas com sucesso.</p>
        </div>

        <!-- Table Details Modal -->
        <div class="modal fade" id="tableModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold" id="modalTitle">Detalhes da Tabela</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body" id="modalBody">
                        <!-- Content injected by JS -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Help Modal -->
        <div class="modal fade" id="helpModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">Guia de Uso</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <ul class="nav nav-tabs nav-tabs-custom mb-4" role="tablist">
                            <li class="nav-item">
                                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#help-dict">
                                    Analisador
                                </button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#help-sql">
                                    Gerador SQL
                                </button>
                            </li>
                        </ul>
                        
                        <div class="tab-content">
                            <!-- Dictionary Help -->
                            <div class="tab-pane fade show active" id="help-dict">
                                <h6 class="fw-bold mb-3">Como usar o Analisador</h6>
                                <ol class="list-group list-group-numbered mb-4">
                                    <li class="list-group-item border-0 ps-0">
                                        <span class="fw-bold">Dicionário Padrão:</span> O sistema carrega automaticamente a versão oficial do dicionário (<code>dicionariodados.html</code>).
                                    </li>
                                    <li class="list-group-item border-0 ps-0">
                                        <span class="fw-bold">Visualização Temporária:</span> Qualquer usuário pode clicar em "Carregar Arquivo" para visualizar uma versão diferente temporariamente. Isso não afeta outros usuários.
                                    </li>
                                    <li class="list-group-item border-0 ps-0">
                                        <span class="fw-bold">Atualização do Sistema:</span> Apenas administradores podem atualizar a versão padrão do dicionário. Clique em "Admin" para fazer login.
                                    </li>
                                    <li class="list-group-item border-0 ps-0">
                                        <span class="fw-bold">Navegar e Buscar:</span> Use a barra lateral e a busca superior para explorar tabelas e colunas.
                                    </li>
                                </ol>
                            </div>

                            <!-- SQL Generator Help -->
                            <div class="tab-pane fade" id="help-sql">
                                <h6 class="fw-bold mb-3">Como usar o Gerador SQL</h6>
                                <p class="text-muted mb-4">O Gerador SQL permite criar comandos rápidos para facilitar o dia a dia.</p>
                                
                                <div class="mb-4">
                                    <h6 class="fw-bold small text-uppercase text-muted">Passo a Passo</h6>
                                    <ol class="list-group list-group-numbered">
                                        <li class="list-group-item border-0 ps-0">
                                            Abra o gerador clicando no botão <span class="badge bg-primary">Gerador SQL</span> na tela da tabela.
                                        </li>
                                        <li class="list-group-item border-0 ps-0">
                                            Escolha a operação: <code>SELECT</code>, <code>INSERT</code>, <code>UPDATE</code> ou <code>DELETE</code>.
                                        </li>
                                        <li class="list-group-item border-0 ps-0">
                                            <span class="fw-bold">Relacionamentos (Joins):</span> Se a tabela tiver chaves estrangeiras, você pode marcar outras tabelas para fazer um <code>INNER JOIN</code> automático.
                                        </li>
                                        <li class="list-group-item border-0 ps-0">
                                            <span class="fw-bold">Colunas:</span> Selecione as colunas que deseja incluir. Se houver Joins, as colunas das outras tabelas também estarão disponíveis.
                                        </li>
                                        <li class="list-group-item border-0 ps-0">
                                            <span class="fw-bold">Filtros:</span> Use os campos de texto para filtrar rapidamente a lista de colunas ou relacionamentos.
                                        </li>
                                        <li class="list-group-item border-0 ps-0">
                                            <span class="fw-bold">Condições:</span> Adicione cláusulas <code>WHERE</code> clicando no botão <strong>+</strong>.
                                        </li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- SQL Generator Modal -->
        <div class="modal fade" id="sqlModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content h-100">
                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title fw-bold" id="sqlModalTitle">Gerador de SQL</h5>
                            <p class="text-muted small mb-0" id="sqlModalSubtitle">Gerar comandos para a tabela</p>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-0">
                        <div class="row g-0 h-100">
                            <!-- Left: Configuration -->
                            <div class="col-md-4 border-end bg-light p-4 overflow-auto" style="max-height: 70vh;">
                                <form id="sqlForm">
                                    <div class="mb-4">
                                        <label class="form-label fw-bold small text-uppercase text-muted">Operação</label>
                                        <select class="form-select" id="sqlOperation" onchange="updateSqlForm()">
                                            <option value="SELECT">SELECT (Consultar)</option>
                                            <option value="INSERT">INSERT (Inserir)</option>
                                            <option value="UPDATE">UPDATE (Atualizar)</option>
                                            <option value="DELETE">DELETE (Excluir)</option>
                                        </select>
                                    </div>

                                    <div class="mb-4" id="columnsSection">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <label class="form-label fw-bold small text-uppercase text-muted mb-0">Colunas</label>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="selectAllCols" onchange="toggleAllColumns()">
                                                <label class="form-check-label small" for="selectAllCols">Todas</label>
                                            </div>
                                        </div>
                                        <input type="text" class="form-control form-control-sm mb-2" placeholder="Filtrar colunas..." onkeyup="filterSqlList(this, 'columnList')">
                                        <div class="card">
                                            <div class="card-body p-2" id="columnList" style="max-height: 200px; overflow-y: auto;">
                                                <!-- Columns injected by JS -->
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-4" id="joinsSection">
                                        <label class="form-label fw-bold small text-uppercase text-muted mb-2">Relacionamentos (Joins)</label>
                                        <input type="text" class="form-control form-control-sm mb-2" placeholder="Filtrar relacionamentos..." onkeyup="filterSqlList(this, 'joinList')">
                                        <div class="card">
                                            <div class="card-body p-2" id="joinList" style="max-height: 150px; overflow-y: auto;">
                                                <!-- Joins injected by JS -->
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-4" id="conditionsSection">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <label class="form-label fw-bold small text-uppercase text-muted mb-0">Condições (WHERE)</label>
                                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="addCondition()">
                                                <i class="bi bi-plus"></i>
                                            </button>
                                        </div>
                                        <div id="conditionsList">
                                            <!-- Conditions injected by JS -->
                                        </div>
                                    </div>
                                </form>
                            </div>

                            <!-- Right: Preview -->
                            <div class="col-md-8 p-4 bg-white d-flex flex-column">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="fw-bold mb-0">Preview do SQL</h6>
                                    <button class="btn btn-sm btn-outline-primary" onclick="copySql()">
                                        <i class="bi bi-clipboard me-2"></i>Copiar
                                    </button>
                                </div>
                                <div class="position-relative flex-grow-1">
                                    <textarea id="sqlOutput" class="form-control font-monospace h-100 bg-light border-0 p-3" readonly style="resize: none;"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>



        <script>
            // Inject PHP data into JS
            const dictData = <?php echo json_encode($tables); ?>;




            // Show toast on load
            document.addEventListener('DOMContentLoaded', function() {
                const toast = document.getElementById('toast');
                toast.style.display = 'block';
                setTimeout(() => {
                    toast.style.display = 'none';
                }, 5000);
            });

            function showTable(index, element) {
                document.querySelectorAll('.table-details').forEach(el => {
                    el.classList.remove('d-flex');
                    el.classList.add('d-none');
                });
                document.getElementById('emptyState').classList.remove('d-flex');
                document.getElementById('emptyState').classList.add('d-none');

                const detail = document.getElementById('table-details-' + index);
                detail.classList.remove('d-none');
                detail.classList.add('d-flex');

                document.querySelectorAll('.dict-table-item').forEach(el => el.classList.remove('active'));
                if (element) {
                    element.classList.add('active');
                    element.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }
            }

            function openTableModal(tableName) {
                const table = dictData.find(t => t.name.toLowerCase() === tableName.toLowerCase());
                
                if (!table) {
                    alert('Tabela não encontrada: ' + tableName);
                    return;
                }

                document.getElementById('modalTitle').textContent = table.name;
                
                let html = `
                    <p class="text-muted mb-4">${table.desc}</p>
                    <h6 class="fw-bold mb-3">Colunas</h6>
                    <div class="table-responsive border rounded">
                        <table class="table table-hover table-sm mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="border-bottom-0 text-muted small font-weight-bold">Nome</th>
                                    <th class="border-bottom-0 text-muted small font-weight-bold">Tipo</th>
                                    <th class="border-bottom-0 text-muted small font-weight-bold">Tamanho</th>
                                    <th class="border-bottom-0 text-muted small font-weight-bold">Descrição</th>
                                </tr>
                            </thead>
                            <tbody>
                `;

                table.fields.forEach(field => {
                    html += `
                        <tr>
                            <td class="fw-medium">${field.name}</td>
                            <td><span class="badge bg-light text-dark border">${field.type}</span></td>
                            <td class="text-muted small">${field.size}</td>
                            <td class="text-muted small">${field.desc}</td>
                        </tr>
                    `;
                });

                html += `
                            </tbody>
                        </table>
                    </div>
                `;

                document.getElementById('modalBody').innerHTML = html;
                
                const modal = new bootstrap.Modal(document.getElementById('tableModal'));
                modal.show();
            }

            function filterTables() {
                const input = document.getElementById('searchInput').value.toLowerCase();
                const scope = document.querySelector('input[name="searchScope"]:checked').value;
                const exactMatch = document.getElementById('exactMatch').checked;
                const items = document.querySelectorAll('.dict-table-item');
                let firstVisible = null;

                items.forEach(item => {
                    const name = item.getAttribute('data-name');
                    const desc = item.getAttribute('data-desc');
                    const columns = item.getAttribute('data-columns');
                    
                    let match = false;

                    if (scope === 'all') {
                        if (exactMatch) {
                            match = name === input || desc === input || columns.split(' ').includes(input);
                        } else {
                            match = name.includes(input) || desc.includes(input) || columns.includes(input);
                        }
                    } else if (scope === 'table') {
                        if (exactMatch) {
                            match = name === input;
                        } else {
                            match = name.includes(input);
                        }
                    } else if (scope === 'column') {
                        if (exactMatch) {
                            match = columns.split(' ').includes(input);
                        } else {
                            match = columns.includes(input);
                        }
                    }

                    if (match) {
                        item.classList.remove('d-none');
                        if (!firstVisible) firstVisible = item;
                    } else {
                        item.classList.add('d-none');
                    }
                });
            }

            function filterSidebar() {
                const input = document.getElementById('sidebarFilter').value.toLowerCase();
                const items = document.querySelectorAll('.dict-table-item');
                items.forEach(item => {
                    const name = item.getAttribute('data-name');
                    if (name.includes(input)) {
                        item.classList.remove('d-none');
                    } else {
                        item.classList.add('d-none');
                    }
                });
            }

            function filterColumns(input, containerId) {
                const filter = input.value.toLowerCase();
                const rows = document.querySelectorAll('#' + containerId + ' tbody tr');
                rows.forEach(row => {
                    const name = row.cells[0].textContent.toLowerCase();
                    if (name.includes(filter)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            }

            function filterForeignKeys(input, containerId) {
                const filter = input.value.toLowerCase();
                const rows = document.querySelectorAll('#' + containerId + ' tbody tr');
                rows.forEach(row => {
                    // Search in Name (0), Column (1), Ref Table (2), Ref Column (3)
                    const text = row.innerText.toLowerCase();
                    if (text.includes(filter)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            }

            function clearFilters() {
                document.getElementById('searchInput').value = '';
                document.getElementById('scopeAll').checked = true;
                document.getElementById('exactMatch').checked = false;
                filterTables();
            }


            let currentTable = null;
            let joinedTables = []; // Array of { tableName: string, alias: string, joinCondition: string }

            function openSqlGenerator(index) {
                currentTable = dictData[index];
                joinedTables = []; // Reset joins
                
                document.getElementById('sqlModalTitle').textContent = 'Gerador SQL: ' + currentTable.name;
                document.getElementById('sqlModalSubtitle').textContent = currentTable.desc;
                
                // Reset form
                document.getElementById('sqlOperation').value = 'SELECT';
                document.getElementById('selectAllCols').checked = true;
                document.getElementById('conditionsList').innerHTML = '';
                
                renderColumns();
                renderJoins();
                updateSqlForm();
                
                const modal = new bootstrap.Modal(document.getElementById('sqlModal'));
                modal.show();
            }

            function exportSchema(index) {
                const table = dictData[index];
                if (!table) return;

                const schema = {
                    tableName: table.name,
                    description: table.desc,
                    columns: table.fields.map(f => ({
                        name: f.name,
                        type: f.type,
                        size: f.size,
                        description: f.desc
                    })),
                    relationships: table.fks.map(fk => ({
                        name: fk.name,
                        column: fk.col,
                        referencedTable: fk.refTable,
                        referencedColumn: fk.refCol
                    })),
                    generatedAt: new Date().toISOString(),
                    tool: "uTool Dictionary Analyzer"
                };

                const jsonString = JSON.stringify(schema, null, 2);
                const blob = new Blob([jsonString], { type: "application/json" });
                const url = URL.createObjectURL(blob);
                
                const a = document.createElement('a');
                a.href = url;
                a.download = `${table.name}_schema.json`;
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                URL.revokeObjectURL(url);
            }

            function renderColumns() {
                const colList = document.getElementById('columnList');
                colList.innerHTML = '';
                
                // Main table columns
                addColumnsToUI(currentTable, currentTable.name, true);

                // Joined tables columns
                joinedTables.forEach(join => {
                    const table = dictData.find(t => t.name.toLowerCase() === join.tableName.toLowerCase());
                    if (table) {
                        addColumnsToUI(table, join.tableName, false);
                    }
                });
            }

            function addColumnsToUI(table, tableName, isMain) {
                const colList = document.getElementById('columnList');
                
                // Header for table if it's a join or if we have joins
                if (!isMain || joinedTables.length > 0) {
                    const header = document.createElement('div');
                    header.className = 'fw-bold small text-muted mt-2 mb-1 border-bottom pb-1';
                    header.textContent = tableName;
                    colList.appendChild(header);
                }

                table.fields.forEach((field, i) => {
                    const uniqueId = `col_${tableName}_${field.name}`.replace(/[^a-zA-Z0-9]/g, '_');
                    const div = document.createElement('div');
                    div.className = 'form-check';
                    // Default checked only for main table
                    const isChecked = isMain ? 'checked' : ''; 
                    
                    div.innerHTML = `
                        <input class="form-check-input sql-col-check" type="checkbox" value="${tableName}.${field.name}" id="${uniqueId}" ${isChecked} onchange="generateSql()">
                        <label class="form-check-label small text-truncate d-block" for="${uniqueId}" title="${field.name} (${field.type})">
                            ${field.name} <span class="text-muted ms-1" style="font-size: 0.75em;">${field.type}</span>
                        </label>
                    `;
                    colList.appendChild(div);
                });
            }

            function renderJoins() {
                const joinList = document.getElementById('joinList');
                joinList.innerHTML = '';
                
                if (!currentTable.fks || currentTable.fks.length === 0) {
                    joinList.innerHTML = '<p class="text-muted small mb-0 fst-italic">Nenhum relacionamento encontrado.</p>';
                    return;
                }

                currentTable.fks.forEach((fk, i) => {
                    const div = document.createElement('div');
                    div.className = 'form-check';
                    div.innerHTML = `
                        <input class="form-check-input" type="checkbox" id="join_${i}" onchange="toggleJoin(${i}, this.checked)">
                        <label class="form-check-label small text-truncate d-block" for="join_${i}" title="Join with ${fk.refTable}">
                            <span class="fw-bold text-primary">JOIN</span> ${fk.refTable} 
                            <span class="text-muted" style="font-size: 0.8em;">(via ${fk.col})</span>
                        </label>
                    `;
                    joinList.appendChild(div);
                });
            }

            function toggleJoin(fkIndex, isChecked) {
                const fk = currentTable.fks[fkIndex];
                
                if (isChecked) {
                    // Add to joinedTables
                    joinedTables.push({
                        tableName: fk.refTable,
                        alias: fk.refTable, // Simple alias for now
                        joinCondition: `${currentTable.name}.${fk.col} = ${fk.refTable}.${fk.refCol}`
                    });
                } else {
                    // Remove from joinedTables
                    joinedTables = joinedTables.filter(j => j.tableName !== fk.refTable);
                }
                
                renderColumns();
                generateSql();
            }

            function updateSqlForm() {
                const op = document.getElementById('sqlOperation').value;
                const colsSection = document.getElementById('columnsSection');
                const joinsSection = document.getElementById('joinsSection');
                
                if (op === 'DELETE') {
                    colsSection.style.display = 'none';
                    joinsSection.style.display = 'none'; // Usually DELETE doesn't use JOINs in simple generators
                } else if (op === 'INSERT') {
                    colsSection.style.display = 'block';
                    joinsSection.style.display = 'none'; // INSERT usually doesn't use JOINs
                } else {
                    colsSection.style.display = 'block';
                    joinsSection.style.display = 'block';
                }
                
                generateSql();
            }

            function toggleAllColumns() {
                const checked = document.getElementById('selectAllCols').checked;
                document.querySelectorAll('.sql-col-check').forEach(el => el.checked = checked);
                generateSql();
            }

            function addCondition() {
                const div = document.createElement('div');
                div.className = 'input-group mb-2 condition-row';
                
                // Build column options including joined tables
                let colOptions = `<option value="" selected disabled>Coluna</option>`;
                
                // Main table
                colOptions += `<optgroup label="${currentTable.name}">`;
                currentTable.fields.forEach(f => {
                    colOptions += `<option value="${currentTable.name}.${f.name}">${f.name}</option>`;
                });
                colOptions += `</optgroup>`;

                // Joined tables
                joinedTables.forEach(join => {
                    const table = dictData.find(t => t.name.toLowerCase() === join.tableName.toLowerCase());
                    if (table) {
                        colOptions += `<optgroup label="${join.tableName}">`;
                        table.fields.forEach(f => {
                            colOptions += `<option value="${join.tableName}.${f.name}">${f.name}</option>`;
                        });
                        colOptions += `</optgroup>`;
                    }
                });

                div.innerHTML = `
                    <select class="form-select form-select-sm" onchange="generateSql()">${colOptions}</select>
                    <select class="form-select form-select-sm" style="max-width: 80px;" onchange="generateSql()">
                        <option value="=">=</option>
                        <option value=">">></option>
                        <option value="<"><</option>
                        <option value=">=">>=</option>
                        <option value="<="><=</option>
                        <option value="<>"><></option>
                        <option value="LIKE">LIKE</option>
                        <option value="IN">IN</option>
                        <option value="IS NULL">IS NULL</option>
                    </select>
                    <input type="text" class="form-control form-control-sm" placeholder="Valor" onkeyup="generateSql()">
                    <button class="btn btn-outline-danger btn-sm" type="button" onclick="removeCondition(this)">
                        <i class="bi bi-trash"></i>
                    </button>
                `;
                
                document.getElementById('conditionsList').appendChild(div);
            }

            function removeCondition(btn) {
                btn.closest('.condition-row').remove();
                generateSql();
            }

            function generateSql() {
                if (!currentTable) return;

                const op = document.getElementById('sqlOperation').value;
                const tableName = currentTable.name;
                let sql = '';

                // Get selected columns
                const selectedCols = Array.from(document.querySelectorAll('.sql-col-check:checked')).map(el => el.value);
                
                // Get conditions
                const conditions = [];
                document.querySelectorAll('.condition-row').forEach(row => {
                    const selects = row.querySelectorAll('select');
                    const input = row.querySelector('input');
                    const col = selects[0].value;
                    const operator = selects[1].value;
                    let val = input.value;
                    
                    if (col) {
                        if (operator === 'IS NULL') {
                            conditions.push(`${col} IS NULL`);
                        } else {
                            if (val !== '') {
                                if (isNaN(val) && operator !== 'IN') {
                                    val = `'${val}'`;
                                }
                                conditions.push(`${col} ${operator} ${val}`);
                            }
                        }
                    }
                });

                const whereClause = conditions.length > 0 ? `\nWHERE ${conditions.join(' AND ')}` : '';

                // Build Join Clause
                let joinClause = '';
                if (joinedTables.length > 0) {
                    joinClause = joinedTables.map(j => `\nINNER JOIN ${j.tableName} ON ${j.joinCondition}`).join('');
                }

                switch (op) {
                    case 'SELECT':
                        const cols = selectedCols.length > 0 ? selectedCols.join(', ') : '*';
                        sql = `SELECT ${cols}\nFROM ${tableName}${joinClause}${whereClause};`;
                        break;
                    
                    case 'INSERT':
                        // INSERT usually doesn't support JOINs in standard SQL in the same way, keeping it simple
                        // Strip table names from columns for INSERT
                        const insertCols = selectedCols.map(c => c.split('.')[1]);
                        if (insertCols.length > 0) {
                            const vals = insertCols.map(() => '?').join(', ');
                            sql = `INSERT INTO ${tableName} (${insertCols.join(', ')})\nVALUES (${vals});`;
                        } else {
                            sql = `-- Selecione pelo menos uma coluna para gerar o INSERT`;
                        }
                        break;

                    case 'UPDATE':
                        // Standard SQL UPDATE with JOINs varies by DB. 
                        // For PostgreSQL (which user mentioned): UPDATE t1 SET ... FROM t2 WHERE ...
                        // For simplicity, we'll stick to basic UPDATE or simple FROM syntax if joins exist.
                        
                        const updateCols = selectedCols.filter(c => c.startsWith(tableName + '.'));
                        
                        if (updateCols.length > 0) {
                            const setClause = updateCols.map(c => `${c.split('.')[1]} = 'valor'`).join(',\n    ');
                            
                            if (joinedTables.length > 0) {
                                // Postgres style update with join
                                const fromList = joinedTables.map(j => j.tableName).join(', ');
                                const joinConds = joinedTables.map(j => j.joinCondition).join(' AND ');
                                
                                // Merge join conditions into WHERE for Postgres UPDATE syntax
                                // UPDATE t1 SET ... FROM t2 WHERE t1.id = t2.id AND ...
                                const combinedWhere = whereClause 
                                    ? whereClause + ` AND ${joinConds}` 
                                    : `\nWHERE ${joinConds}`;
                                    
                                sql = `UPDATE ${tableName}\nSET ${setClause}\nFROM ${fromList}${combinedWhere};`;
                            } else {
                                sql = `UPDATE ${tableName}\nSET ${setClause}${whereClause};`;
                            }
                        } else {
                            sql = `-- Selecione colunas da tabela principal (${tableName}) para atualizar`;
                        }
                        break;

                    case 'DELETE':
                        // DELETE FROM t1 USING t2 WHERE ...
                        if (joinedTables.length > 0) {
                             const usingList = joinedTables.map(j => j.tableName).join(', ');
                             const joinConds = joinedTables.map(j => j.joinCondition).join(' AND ');
                             const combinedWhere = whereClause 
                                    ? whereClause + ` AND ${joinConds}` 
                                    : `\nWHERE ${joinConds}`;
                             
                             sql = `DELETE FROM ${tableName}\nUSING ${usingList}${combinedWhere};`;
                        } else {
                            sql = `DELETE FROM ${tableName}${whereClause};`;
                        }
                        break;
                }

                document.getElementById('sqlOutput').value = sql;
            }

            function copySql() {
                const copyText = document.getElementById("sqlOutput");
                copyText.select();
                copyText.setSelectionRange(0, 99999); 
                navigator.clipboard.writeText(copyText.value);
                
                // Visual feedback
                const btn = document.querySelector('button[onclick="copySql()"]');
                const originalHtml = btn.innerHTML;
                btn.innerHTML = '<i class="bi bi-check me-2"></i>Copiado!';
                btn.classList.remove('btn-outline-primary');
                btn.classList.add('btn-success');
                
                setTimeout(() => {
                    btn.innerHTML = originalHtml;
                    btn.classList.remove('btn-success');
                    btn.classList.add('btn-outline-primary');
                }, 2000);
            }

            function filterSqlList(input, listId) {
                const filter = input.value.toLowerCase();
                const list = document.getElementById(listId);
                const items = list.querySelectorAll('.form-check');
                
                items.forEach(item => {
                    const label = item.querySelector('label').textContent.toLowerCase();
                    if (label.includes(filter)) {
                        item.classList.remove('d-none');
                    } else {
                        item.classList.add('d-none');
                    }
                });
                
                // Also handle headers in column list
                if (listId === 'columnList') {
                    const headers = list.querySelectorAll('.fw-bold.small.text-muted');
                    headers.forEach(header => {
                        // Simple logic: if all siblings until next header are hidden, hide header? 
                        // Or simpler: just keep headers visible for context, or hide if no visible children.
                        // Let's try to hide header if no visible children in that group.
                        // Since structure is flat (header, div, div, header, div...), this is tricky without grouping.
                        // For now, let's just leave headers visible or implement a smarter render.
                        // Actually, let's keep it simple: filter only hides items. Headers stay.
                    });
                }
            }
        </script>
    <?php endif; ?>
</div>


<!-- Login Modal -->
<div class="modal fade" id="loginModal" tabindex="-1">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Login Admin</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="loginForm" onsubmit="handleLogin(event)">
                    <div class="mb-3">
                        <label class="form-label small text-muted">Usuário</label>
                        <input type="text" name="user" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-muted">Senha</label>
                        <input type="password" name="pass" class="form-control" required>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Entrar</button>
                    </div>
                    <div id="loginError" class="text-danger small text-center mt-2" style="display:none;"></div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function handleLogin(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    formData.append('action', 'login');

    fetch('dictionary.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            const err = document.getElementById('loginError');
            err.textContent = data.message;
            err.style.display = 'block';
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}
</script>

<?php include 'includes/footer.php'; ?>
