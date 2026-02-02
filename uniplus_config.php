<?php
include 'includes/header.php';

// --- SESSION HANDLER FOR PATH ---
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Default path or session path
// Default path or session path
if (PHP_OS_FAMILY === 'Windows') {
    $defaultPath = 'c:/xampp/htdocs/utool/font/uniplus.properties';
} else {
    // Linux default (relative to script or standard location)
    // Assuming 'font' dir is peer to this script in deployment
    $defaultPath = __DIR__ . '/font/uniplus.properties';
}

$propFile = isset($_SESSION['uniplus_config_path']) ? $_SESSION['uniplus_config_path'] : $defaultPath;
$backupDir = dirname($propFile) . '/backups/';

// Handle Path Change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'set_path') {
    $newPath = trim($_POST['custom_path']);
    // Simple validation
    if (file_exists($newPath) && str_ends_with($newPath, '.properties')) {
        $_SESSION['uniplus_config_path'] = $newPath;
        $propFile = $newPath;
        $backupDir = dirname($propFile) . '/backups/';
    } elseif (is_dir($newPath)) {
        // If dir provided, assume uniplus.properties inside
        $newPath = rtrim($newPath, '/\\') . '/uniplus.properties';
        if (file_exists($newPath)) {
            $_SESSION['uniplus_config_path'] = $newPath;
            $propFile = $newPath;
            $backupDir = dirname($propFile) . '/backups/';
        } else {
            $_SESSION['path_error'] = "Arquivo uniplus.properties não encontrado na pasta informada.";
        }
    } else {
        $_SESSION['path_error'] = "Arquivo ou diretório inválido.";
    }
    // Redirect to clear post
    header('Location: uniplus_toolkit.php');
    exit;
}

// Ensure backup dir exists (only if we have a valid file to backup)
if (file_exists($propFile)) {
    if (!is_dir($backupDir)) {
        @mkdir($backupDir, 0777, true);
    }
}

// --- FUNCTIONS ---

function parseProperties($txtProperties) {
    $result = array();
    $lines = explode("\n", $txtProperties);
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || strpos($line, '#') === 0) continue; 
        
        $parts = explode('=', $line, 2);
        if (count($parts) === 2) {
            $key = trim($parts[0]);
            $value = trim($parts[1]);
            $result[$key] = $value;
        }
    }
    return $result;
}

function savePropertiesFile($filepath, $properties) {
    $content = "# Editado via Uniplus Toolkit em " . date('r') . "\n";
    foreach ($properties as $key => $value) {
        $content .= "$key=$value\n";
    }
    return file_put_contents($filepath, $content);
}

function classifyParams($props) {
    $categories = [
        'Banco de Dados' => [],
        'Rede & Portas' => [],
        'Sistema & Flags' => [],
        'Interface / UI' => [],
        'Hardware & Serviços' => [],
        'Financeiro & Fiscal' => [],
        'Outros' => []
    ];

    foreach ($props as $key => $val) {
        if (str_starts_with($key, 'base.') || str_starts_with($key, 'db.') || str_contains($key, 'sql')) {
            $categories['Banco de Dados'][$key] = $val;
        } elseif (str_starts_with($key, 'concentrador.') || str_starts_with($key, 'dashboard.') || str_starts_with($key, 'ftp.') || str_starts_with($key, 'ws.') || str_contains($key, 'port') || str_contains($key, 'url') || str_contains($key, 'ip')) {
            $categories['Rede & Portas'][$key] = $val;
        } elseif (str_starts_with($key, 'ativar') || str_contains($key, 'debug') || str_contains($key, 'log') || str_starts_with($key, 'extrair') || str_starts_with($key, 'filial')) {
            $categories['Sistema & Flags'][$key] = $val;
        } elseif (str_starts_with($key, 'consultapreco.') || str_starts_with($key, 'tabelapreco.') || str_contains($key, 'exibir') || str_contains($key, 'visual') || str_contains($key, 'janela')) {
            $categories['Interface / UI'][$key] = $val;
        } elseif (str_starts_with($key, 'yoda.') || str_starts_with($key, 'mscapi.') || str_starts_with($key, 'certificado') || str_contains($key, 'impressora')) {
            $categories['Hardware & Serviços'][$key] = $val;
        } elseif (str_contains($key, 'fiscal') || str_contains($key, 'nf') || str_contains($key, 'tribut') || str_contains($key, 'cfop')) {
            $categories['Financeiro & Fiscal'][$key] = $val;
        } else {
            $categories['Outros'][$key] = $val;
        }
    }
    return $categories;
}

// --- AÇÕES DO FORMULÁRIO (SAVE) ---
$message = '';
if (isset($_SESSION['path_error'])) {
    $message = "<div class='alert alert-danger'>".$_SESSION['path_error']."</div>";
    unset($_SESSION['path_error']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'save') {
        if (file_exists($propFile)) {
            $currentContent = file_get_contents($propFile);
            $bkpName = 'uniplus.properties.' . date('Ymd_His') . '.bak';
            file_put_contents($backupDir . $bkpName, $currentContent);
            
            if (isset($_POST['props']) && is_array($_POST['props'])) {
                 if (savePropertiesFile($propFile, $_POST['props'])) {
                     $message = "<div class='alert alert-success'>Configurações salvas! Backup: $bkpName</div>";
                 } else {
                     $message = "<div class='alert alert-danger'>Erro ao salvar! Verifique permissões de escrita.</div>";
                 }
            }
        } else {
             $message = "<div class='alert alert-danger'>Arquivo não encontrado para salvar: $propFile</div>";
        }
    }
}

// --- CARREGAR DADOS ---
if (file_exists($propFile)) {
    $rawContent = file_get_contents($propFile);
    $properties = parseProperties($rawContent);
    $categorized = classifyParams($properties);
} else {
    $message .= "<div class='alert alert-warning'>Arquivo de configuração não encontrado.<br><small class='text-muted'>$propFile</small></div>";
    $categorized = [];
}
?>

<style>
    .prop-card {
        background: rgba(255, 255, 255, 0.4);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255,255,255,0.2);
        border-radius: 12px;
        transition: transform 0.2s;
    }
    .prop-card:hover {
        background: rgba(255, 255, 255, 0.6);
        border-color: #3b82f6;
    }
    .nav-pills .nav-link {
        color: #64748b;
        font-weight: 600;
        border-radius: 8px;
        margin-right: 5px;
    }
    .nav-pills .nav-link.active {
        background-color: #3b82f6;
        color: white;
        box-shadow: 0 4px 6px rgba(59, 130, 246, 0.3);
    }
    .form-label-key {
        font-family: 'Monaco', 'Consolas', monospace;
        font-size: 0.85rem;
        color: #475569;
    }
    .hidden-prop {
        display: none !important;
    }
</style>

<div class="container-fluid py-4 px-5">
    
    <!-- HEADER & ACTIONS -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="display-6 fw-bold text-dark"><i class="bi bi-sliders me-2 text-primary"></i>Editor de Configuração</h1>
            <p class="text-muted mb-0">Edite o arquivo <code>uniplus.properties</code> com segurança.</p>
        </div>
        <div>
            <a href="uniplus_toolkit.php" class="btn btn-outline-secondary rounded-pill"><i class="bi bi-arrow-left me-1"></i> Voltar</a>
        </div>
    </div>

    <!-- CURRENT PATH INFO -->
    <div class="alert alert-light border shadow-sm d-flex justify-content-between align-items-center py-2 mb-4">
        <div class="text-truncate">
            <i class="bi bi-file-earmark-code text-primary me-2"></i>
            <span class="text-muted small text-uppercase fw-bold me-2">Arquivo Atual:</span>
            <span class="font-monospace text-dark"><?php echo $propFile; ?></span>
        </div>
        <?php if(!file_exists($propFile)): ?>
            <span class="badge bg-danger">Não Encontrado</span>
        <?php else: ?>
            <span class="badge bg-success bg-opacity-10 text-success">Carregado</span>
        <?php endif; ?>
    </div>

    <?php echo $message; ?>

    <!-- MAIN FORM -->
    <form method="POST" action="">
        <input type="hidden" name="action" value="save">

        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 mb-4">
                <div class="card border-0 shadow-sm rounded-4 p-3 sticky-top" style="top: 20px;">
                    
                    <!-- SEARCH BAR -->
                    <div class="mb-3 position-relative">
                        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                        <input type="text" id="propSearch" class="form-control rounded-pill ps-5 bg-light border-0" placeholder="Buscar parâmetro...">
                    </div>

                    <h6 class="text-uppercase text-muted fw-bold small mb-3 px-2">Categorias</h6>
                    <ul class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                        <?php 
                        $first = true;
                        foreach ($categorized as $catName => $props): 
                            if (empty($props)) continue;
                            $slug = md5($catName);
                            // Only first active if no search active (search overrides structure concept visually)
                            $activeClass = $first ? 'active' : '';
                        ?>
                            <li class="nav-item mb-1">
                                <button class="nav-link w-100 text-start <?php echo $activeClass; ?>" id="v-pills-<?php echo $slug; ?>-tab" data-bs-toggle="pill" data-bs-target="#v-pills-<?php echo $slug; ?>" type="button" role="tab">
                                    <?php echo $catName; ?> 
                                    <span class="badge bg-secondary bg-opacity-25 text-secondary float-end rounded-pill count-badge"><?php echo count($props); ?></span>
                                </button>
                            </li>
                        <?php 
                            $first = false; 
                        endforeach; 
                        ?>
                    </ul>
                    
                    <hr class="my-4">
                    
                    <button type="submit" class="btn btn-primary w-100 rounded-pill py-2 fw-bold shadow-sm">
                        <i class="bi bi-save me-2"></i> Salvar Alterações
                    </button>
                    <div class="text-center mt-2">
                        <small class="text-muted" style="font-size: 0.75rem;">Backup gerado em <?php echo basename($backupDir); ?></small>
                    </div>
                </div>
            </div>

            <!-- Content Area -->
            <div class="col-md-9">
                <div class="tab-content" id="v-pills-tabContent">
                    <?php 
                    $first = true;
                    foreach ($categorized as $catName => $props): 
                        if (empty($props)) continue;
                        $slug = md5($catName);
                        $activeClass = $first ? 'show active' : '';
                        $first = false;
                    ?>
                        <div class="tab-pane fade <?php echo $activeClass; ?>" id="v-pills-<?php echo $slug; ?>" role="tabpanel">
                            <div class="d-flex align-items-center mb-3 category-header">
                                <h4 class="fw-bold text-dark mb-0"><?php echo $catName; ?></h4>
                                <span class="ms-3 badge bg-light text-dark border"><?php echo count($props); ?> parâmetros</span>
                            </div>
                            
                            <div class="row g-3 prop-grid">
                                <?php foreach ($props as $key => $val): ?>
                                    <div class="col-md-6 col-xl-4 prop-item-col" data-key="<?php echo strtolower($key); ?>" data-val="<?php echo strtolower($val); ?>">
                                        <div class="prop-card p-3 h-100">
                                            <div class="mb-2">
                                                <label class="form-label d-block text-truncate form-label-key fw-bold" title="<?php echo $key; ?>">
                                                    <?php echo $key; ?>
                                                </label>
                                            </div>
                                            
                                            <?php if ($val === '0' || $val === '1'): ?>
                                                <!-- Boolean Switch -->
                                                <div class="form-check form-switch">
                                                    <input type="hidden" name="props[<?php echo $key; ?>]" value="0">
                                                    <input class="form-check-input" type="checkbox" role="switch" name="props[<?php echo $key; ?>]" value="1" <?php echo ($val === '1') ? 'checked' : ''; ?>>
                                                    <label class="form-check-label small text-muted">
                                                        <?php echo ($val === '1') ? 'Ativado (1)' : 'Desativado (0)'; ?>
                                                    </label>
                                                </div>
                                            <?php else: ?>
                                                <!-- Standard Input -->
                                                <input type="text" class="form-control form-control-sm bg-white border-0 shadow-sm" name="props[<?php echo $key; ?>]" value="<?php echo htmlspecialchars($val); ?>">
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Search Empty State -->
                <div id="search-no-results" class="text-center py-5 d-none">
                    <i class="bi bi-search fs-1 text-muted opacity-50"></i>
                    <p class="text-muted mt-2">Nenhum parâmetro encontrado para sua busca.</p>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Path Configuration Modal -->
<div class="modal fade" id="pathModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="" class="modal-content border-0 shadow">
            <input type="hidden" name="action" value="set_path">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Localizar Arquivo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label small text-muted fw-bold">Caminho do Arquivo ou Pasta de Instalação</label>
                    <input type="text" class="form-control" name="custom_path" value="<?php echo htmlspecialchars($propFile); ?>" placeholder="Ex: C:\Uniplus\font\uniplus.properties">
                    <div class="form-text">Insira o caminho completo para o arquivo <code>uniplus.properties</code> ou a pasta onde ele reside.</div>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary rounded-pill px-4">Carregar</button>
            </div>
        </form>
    </div>
</div>

<script>
    // Search Functionality
    const searchInput = document.getElementById('propSearch');
    const propItems = document.querySelectorAll('.prop-item-col');
    const noResults = document.getElementById('search-no-results');
    const tabPanes = document.querySelectorAll('.tab-pane');
    const sidebarTabs = document.getElementById('v-pills-tab');
    const tabButtons = document.querySelectorAll('.nav-link[data-bs-toggle="pill"]');
    
    // Store original active tab to restore when clearing search
    let originalActiveTabId = document.querySelector('.nav-link.active')?.id;

    searchInput.addEventListener('keyup', function() {
        const term = this.value.toLowerCase().trim();
        let globalVisibleCount = 0;

        if (term.length > 0) {
            // SEARCH MODE
            
            // 1. Hide Sidebar Categories (optional, but cleaner)
            sidebarTabs.style.opacity = '0.5';
            sidebarTabs.style.pointerEvents = 'none';

            // 2. Filter Items and Count
            propItems.forEach(item => {
                const key = item.getAttribute('data-key');
                const val = item.getAttribute('data-val');
                
                if (key.includes(term) || val.includes(term)) {
                    item.classList.remove('hidden-prop');
                    globalVisibleCount++;
                } else {
                    item.classList.add('hidden-prop');
                }
            });

            // 3. Manage Category Visibility
            tabPanes.forEach(pane => {
                // Check if this pane has any visible items
                const hasVisibleItems = pane.querySelectorAll('.prop-item-col:not(.hidden-prop)').length > 0;
                
                if (hasVisibleItems) {
                    pane.classList.add('show', 'active');
                    pane.classList.remove('fade');
                    pane.style.display = 'block';
                } else {
                    pane.style.display = 'none';
                    pane.classList.remove('show', 'active');
                }
            });

        } else {
            // RESTORE VIEW (Clear Search)
            sidebarTabs.style.opacity = '1';
            sidebarTabs.style.pointerEvents = 'auto';

            propItems.forEach(item => item.classList.remove('hidden-prop'));

            tabPanes.forEach(pane => {
                pane.style.display = ''; // Clear inline display
                pane.classList.add('fade'); // Restore fade animation
                
                // Only keep actual active tab shown
                if (originalActiveTabId && pane.id === originalActiveTabId.replace('-tab', '')) {
                    pane.classList.add('show', 'active');
                } else {
                    pane.classList.remove('show', 'active');
                }
            });
            
            if(originalActiveTabId) {
                 const originalTab = document.getElementById(originalActiveTabId);
                 if(originalTab) originalTab.classList.add('active');
            }
            
            globalVisibleCount = 1; // Prevent No Results showing
        }

        // Show/Hide Empty State
        if (globalVisibleCount === 0 && term.length > 0) {
            noResults.classList.remove('d-none');
        } else {
            noResults.classList.add('d-none');
        }
    });

    // Capture tab change to update "original" for restore
    tabButtons.forEach(btn => {
        btn.addEventListener('shown.bs.tab', function (e) {
            if (searchInput.value.length === 0) {
                originalActiveTabId = e.target.id;
            }
        });
    });
</script>

<?php include 'includes/footer.php'; ?>
