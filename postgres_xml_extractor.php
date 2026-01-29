<?php
session_start();
require_once 'vendor/autoload.php';

$error = '';
$success = '';
$xmlContent = '';
$hexInput = '';
$activeTab = 'manual'; // 'manual' or 'db'

// Database Connection & Query Logic
$dbResult = null;
$dbError = null;
$tables = []; // For dictionary

// Load Dictionary for suggestions
if (file_exists('dicionariodados.html')) {
    $content = file_get_contents('dicionariodados.html');
    libxml_use_internal_errors(true);
    $dom = new DOMDocument();
    $dom->loadHTML('<?xml encoding="utf-8" ?>' . $content);
    libxml_clear_errors();
    $allTables = $dom->getElementsByTagName('table');
    foreach ($allTables as $table) {
        $rows = $table->getElementsByTagName('tr');
        if ($rows->length < 2) continue;
        $rawName = trim($rows->item(0)->nodeValue);
        $tableName = trim(str_ireplace('Nome da tabela', '', $rawName));
        
        $fields = [];
        foreach ($rows as $row) {
            $tds = $row->getElementsByTagName('td');
            if ($tds->length === 4) { // Assuming field row
                 $fields[] = trim($tds->item(0)->nodeValue);
            }
        }
        if (!empty($fields)) {
            $tables[$tableName] = $fields;
        }
    }
}

// Handle Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    // DB Connection
    if ($action === 'connect') {
        $activeTab = 'db';
        $_SESSION['db_host'] = $_POST['db_host'];
        $_SESSION['db_port'] = $_POST['db_port'];
        $_SESSION['db_name'] = $_POST['db_name'];
        $_SESSION['db_user'] = $_POST['db_user'];
        $_SESSION['db_pass'] = $_POST['db_pass']; // Warning: Storing pass in session
        
        try {
            $dsn = "pgsql:host={$_SESSION['db_host']};port={$_SESSION['db_port']};dbname={$_SESSION['db_name']}";
            $pdo = new PDO($dsn, $_SESSION['db_user'], $_SESSION['db_pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            $success = "Conexão estabelecida com sucesso!";
        } catch (PDOException $e) {
            $dbError = "Erro na conexão: " . $e->getMessage();
            // Clear invalid session data
            unset($_SESSION['db_host'], $_SESSION['db_port'], $_SESSION['db_name'], $_SESSION['db_user'], $_SESSION['db_pass']);
        }
    }

    // DB Disconnect
    if ($action === 'disconnect') {
        $activeTab = 'db';
        unset($_SESSION['db_host'], $_SESSION['db_port'], $_SESSION['db_name'], $_SESSION['db_user'], $_SESSION['db_pass']);
        $success = "Desconectado com sucesso.";
    }
    
    // Execute Query
    if ($action === 'query') {
        $activeTab = 'db';
        $query = $_POST['sql_query'] ?? '';
        if (!empty($query) && isset($_SESSION['db_host'])) {
            try {
                $dsn = "pgsql:host={$_SESSION['db_host']};port={$_SESSION['db_port']};dbname={$_SESSION['db_name']}";
                $pdo = new PDO($dsn, $_SESSION['db_user'], $_SESSION['db_pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
                
                $stmt = $pdo->query($query);
                $dbResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                $dbError = "Erro na query: " . $e->getMessage();
            }
        } elseif (!isset($_SESSION['db_host'])) {
             $dbError = "Você não está conectado.";
        }
    }

    // Manual Extraction (Existing Logic)
    if (in_array($action, ['preview', 'download_xml', 'view_danfe', 'view_dacte'])) {
        $activeTab = 'manual';
        $hexInput = $_POST['hex_content'] ?? '';
        
        if (!empty($hexInput)) {
            // Cleaning
            $cleanHex = str_replace(['\x', ' ', "\n", "\r"], '', $hexInput);
            
            if (!ctype_xdigit($cleanHex)) {
                $error = "A entrada contém caracteres inválidos. Certifique-se de que é uma string hexadecimal.";
            } else {
                try {
                    $decoded = hex2bin($cleanHex);
                    
                    // Basic XML Validation
                    if (strpos(trim($decoded), '<') !== 0) {
                        $error = "O conteúdo decodificado não parece ser um XML válido (não começa com '<').";
                    } else {
                        // Try parsing to ensure it's valid XML
                        libxml_use_internal_errors(true);
                        $xmlObj = simplexml_load_string($decoded);
                        
                        if ($xmlObj === false) {
                            $error = "Erro ao analisar o XML decodificado.";
                            foreach(libxml_get_errors() as $e) {
                                $error .= "<br>Line $e->line: $e->message";
                            }
                            libxml_clear_errors();
                        } else {
                            $xmlContent = $decoded;
                            
                            // Detect Type
                            $isCte = false;
                            if (isset($xmlObj->cteProc) || $xmlObj->getName() == 'CTe') {
                                $isCte = true;
                            }

                            // Handle Export Actions
                            if ($action === 'download_xml') {
                                header('Content-Description: File Transfer');
                                header('Content-Type: application/xml');
                                header('Content-Disposition: attachment; filename="documento.xml"');
                                header('Expires: 0');
                                header('Cache-Control: must-revalidate');
                                header('Pragma: public');
                                header('Content-Length: ' . strlen($xmlContent));
                                echo $xmlContent;
                                exit;
                            } elseif ($action === 'view_danfe') {
                                try {
                                    $danfe = new \NFePHP\DA\NFe\Danfe($xmlContent);
                                    $danfe->debugMode(false);
                                    $danfe->creditsIntegratorFooter('Gerado por uTool');
                                    $pdf = $danfe->render();
                                    
                                    header('Content-Type: application/pdf');
                                    header('Content-Disposition: inline; filename="danfe.pdf"');
                                    echo $pdf;
                                    exit;
                                } catch (\Exception $e) {
                                    $error = "Erro ao gerar DANFE: " . $e->getMessage();
                                }
                            } elseif ($action === 'view_dacte') {
                                try {
                                    // DACTE Generation
                                    $dacte = new \NFePHP\DA\CTe\Dacte($xmlContent, 'P', 'A4', '', 'I', null);
                                    $dacte->creditsIntegratorFooter('Gerado por uTool');
                                    $dacte->monta();
                                    $pdf = $dacte->render();
                                    
                                    header('Content-Type: application/pdf');
                                    header('Content-Disposition: inline; filename="dacte.pdf"');
                                    echo $pdf;
                                    exit;
                                } catch (\Exception $e) {
                                    $error = "Erro ao gerar DACTE: " . $e->getMessage();
                                }
                            } else {
                                $typeLabel = $isCte ? 'CT-e' : 'NF-e/Outro';
                                $success = "XML ($typeLabel) decodificado com sucesso! Tamanho: " . strlen($xmlContent) . " bytes.";
                            }
                        }
                    }
                } catch (\Exception $e) {
                    $error = "Erro ao processar: " . $e->getMessage();
                }
            }
        }
    }
}

include 'includes/header.php';
?>

</div>
<div class="container-fluid px-4 py-5">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div class="d-flex align-items-center">
            <div class="icon-box bg-primary-gradient text-white rounded-3 me-3">
                <i class="bi bi-database-fill-gear fs-4"></i>
            </div>
            <div>
                <h1 class="fw-bold text-dark mb-0">Extrator XML Postgres</h1>
                <p class="text-muted mb-0">Extraia e visualize XMLs diretamente do banco de dados ou via texto.</p>
            </div>
        </div>
        <a href="index.php" class="btn btn-outline-secondary rounded-pill px-4">
            <i class="bi bi-arrow-left me-2"></i>Voltar
        </a>
    </div>

    <ul class="nav nav-tabs nav-tabs-custom mb-4" id="myTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link <?= $activeTab === 'manual' ? 'active' : '' ?>" id="manual-tab" data-bs-toggle="tab" data-bs-target="#manual" type="button" role="tab">Extrator Manual</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link <?= $activeTab === 'db' ? 'active' : '' ?>" id="db-tab" data-bs-toggle="tab" data-bs-target="#db" type="button" role="tab">Conexão Direta</button>
        </li>
    </ul>

    <div class="tab-content" id="myTabContent">
        <!-- Manual Tab -->
        <div class="tab-pane fade <?= $activeTab === 'manual' ? 'show active' : '' ?>" id="manual" role="tabpanel">
            <div class="row">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-body p-4">
                            <?php if ($error && $activeTab === 'manual'): ?>
                                <div class="alert alert-danger rounded-3 mb-4">
                                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                    <?= $error ?>
                                </div>
                            <?php endif; ?>

                            <?php if ($success && $activeTab === 'manual'): ?>
                                <div class="alert alert-success rounded-3 mb-4">
                                    <i class="bi bi-check-circle-fill me-2"></i>
                                    <?= $success ?>
                                </div>
                            <?php endif; ?>

                            <form method="POST" action="" id="extractorForm">
                                <input type="hidden" name="action" id="formAction" value="preview">
                                
                                <div class="mb-4">
                                    <label for="hex_content" class="form-label fw-bold">Conteúdo Hexadecimal</label>
                                    <textarea class="form-control font-monospace" id="hex_content" name="hex_content" rows="8" placeholder="\x3c3f786d6c..."><?= htmlspecialchars($hexInput) ?></textarea>
                                    <div class="form-text">Cole a string hexadecimal exportada do Postgres (com ou sem o prefixo \x).</div>
                                </div>

                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary px-4" onclick="document.getElementById('formAction').value='preview'">
                                        <i class="bi bi-gear-fill me-2"></i>Processar
                                    </button>
                                    
                                    <?php if ($xmlContent): ?>
                                        <button type="submit" class="btn btn-success px-4" onclick="document.getElementById('formAction').value='download_xml'">
                                            <i class="bi bi-download me-2"></i>Baixar XML
                                        </button>
                                        
                                        <?php 
                                            // Check if it looks like a CT-e to show DACTE button, otherwise DANFE
                                            $showDacte = false;
                                            if (strpos($xmlContent, '<cteProc') !== false || strpos($xmlContent, '<CTe') !== false) {
                                                $showDacte = true;
                                            }
                                        ?>
                                        
                                        <?php if ($showDacte): ?>
                                            <button type="submit" class="btn btn-info text-white px-4" onclick="document.getElementById('formAction').value='view_dacte'">
                                                <i class="bi bi-truck me-2"></i>Visualizar DACTE
                                            </button>
                                        <?php else: ?>
                                            <button type="submit" class="btn btn-danger px-4" onclick="document.getElementById('formAction').value='view_danfe'">
                                                <i class="bi bi-file-pdf-fill me-2"></i>Visualizar DANFE
                                            </button>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm rounded-4 bg-light">
                        <div class="card-body p-4">
                            <h5 class="fw-bold mb-3">Instruções</h5>
                            <ol class="ps-3 mb-0 text-muted small">
                                <li class="mb-2">Execute sua query no Postgres selecionando a coluna <code>bytea</code> ou <code>text</code>.</li>
                                <li class="mb-2">Copie o resultado completo (geralmente começa com <code>\x</code>).</li>
                                <li class="mb-2">Cole no campo ao lado e clique em <strong>Processar</strong>.</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- DB Tab -->
        <div class="tab-pane fade <?= $activeTab === 'db' ? 'show active' : '' ?>" id="db" role="tabpanel">
            <div class="row">
                <!-- Connection & Query -->
                <div class="col-lg-3">
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-header bg-white border-bottom-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                            <h5 class="fw-bold mb-0">Conexão</h5>
                            <?php if (isset($_SESSION['db_host'])): ?>
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">
                                    <i class="bi bi-check-circle-fill me-1"></i>Ativa
                                </span>
                            <?php else: ?>
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill">
                                    Offline
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="card-body p-4">
                            <?php if ($success && $activeTab === 'db'): ?>
                                <div class="alert alert-success py-2 small mb-3">
                                    <i class="bi bi-check-circle me-1"></i><?= $success ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (isset($_SESSION['db_host'])): ?>
                                <div class="mb-3">
                                    <label class="form-label small text-muted">Conectado em:</label>
                                    <div class="fw-bold text-dark">
                                        <?= htmlspecialchars($_SESSION['db_user']) ?>@<?= htmlspecialchars($_SESSION['db_host']) ?>:<?= htmlspecialchars($_SESSION['db_port']) ?>
                                    </div>
                                    <div class="small text-muted"><?= htmlspecialchars($_SESSION['db_name']) ?></div>
                                </div>
                                <form method="POST">
                                    <input type="hidden" name="action" value="disconnect">
                                    <button type="submit" class="btn btn-outline-danger btn-sm w-100">
                                        <i class="bi bi-power me-2"></i>Desconectar
                                    </button>
                                </form>
                            <?php else: ?>
                                <form method="POST">
                                    <input type="hidden" name="action" value="connect">
                                    <div class="row g-2 mb-2">
                                        <div class="col-8">
                                            <label class="form-label small text-muted">Host</label>
                                            <input type="text" name="db_host" class="form-control form-control-sm" value="<?= $_SESSION['db_host'] ?? 'localhost' ?>" required>
                                        </div>
                                        <div class="col-4">
                                            <label class="form-label small text-muted">Porta</label>
                                            <input type="text" name="db_port" class="form-control form-control-sm" value="<?= $_SESSION['db_port'] ?? '5432' ?>" required>
                                        </div>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label small text-muted">Database</label>
                                        <input type="text" name="db_name" class="form-control form-control-sm" value="<?= $_SESSION['db_name'] ?? '' ?>" required>
                                    </div>
                                    <div class="row g-2 mb-3">
                                        <div class="col-6">
                                            <label class="form-label small text-muted">Usuário</label>
                                            <input type="text" name="db_user" class="form-control form-control-sm" value="<?= $_SESSION['db_user'] ?? 'postgres' ?>" required>
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label small text-muted">Senha</label>
                                            <input type="password" name="db_pass" class="form-control form-control-sm" value="<?= $_SESSION['db_pass'] ?? '' ?>">
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-sm w-100">
                                        <i class="bi bi-plug-fill me-2"></i>Conectar / Testar
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Dictionary Helper -->
                    <?php if (!empty($tables)): ?>
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-header bg-white border-bottom-0 pt-4 px-4">
                            <h5 class="fw-bold mb-0">Assistente (Dicionário)</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="mb-3">
                                <label class="form-label small text-muted">Buscar Tabela</label>
                                <input type="text" id="tableSearch" class="form-control form-control-sm" placeholder="Ex: cte, mdfe..." onkeyup="filterDictTables()">
                            </div>
                            <div class="list-group list-group-flush border rounded overflow-auto" style="max-height: 200px;" id="dictTableList">
                                <?php foreach ($tables as $tName => $cols): ?>
                                    <button type="button" class="list-group-item list-group-item-action small py-2" onclick="selectDictTable('<?= $tName ?>', <?= htmlspecialchars(json_encode($cols)) ?>)">
                                        <?= $tName ?>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                            <div id="selectedTableInfo" class="mt-3 d-none">
                                <h6 class="fw-bold small mb-2" id="selectedTableName"></h6>
                                <div class="d-grid gap-2">
                                    <button class="btn btn-outline-primary btn-sm" onclick="generateSelect()">
                                        Gerar SELECT
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Query & Results -->
                <div class="col-lg-9">
                    <div class="card border-0 shadow-sm rounded-4 mb-4 h-100">
                        <div class="card-body p-4 d-flex flex-column">
                            <?php if ($dbError): ?>
                                <div class="alert alert-danger py-2 small">
                                    <?= $dbError ?>
                                </div>
                            <?php endif; ?>

                            <form method="POST" class="mb-4">
                                <input type="hidden" name="action" value="query">
                                <label class="form-label fw-bold">Consulta SQL</label>
                                <div class="position-relative">
                                    <textarea name="sql_query" id="sqlQuery" class="form-control font-monospace bg-light" rows="5" placeholder="SELECT * FROM ..."><?= $_POST['sql_query'] ?? '' ?></textarea>
                                    <button type="submit" class="btn btn-primary position-absolute bottom-0 end-0 m-3">
                                        <i class="bi bi-play-fill me-1"></i>Executar
                                    </button>
                                </div>
                            </form>

                            <?php if ($dbResult !== null): ?>
                                <h6 class="fw-bold mb-3">Resultados (<?= count($dbResult) ?>)</h6>
                                <div class="table-responsive border rounded flex-grow-1" style="max-height: 600px; overflow-y: auto;">
                                    <table class="table table-hover table-sm mb-0 small font-monospace table-striped" style="white-space: nowrap;">
                                        <thead class="bg-light position-sticky top-0 shadow-sm" style="z-index: 1;">
                                            <tr>
                                                <?php if (!empty($dbResult)): ?>
                                                    <?php foreach (array_keys($dbResult[0]) as $col): ?>
                                                        <th><?= $col ?></th>
                                                    <?php endforeach; ?>
                                                    <th class="text-end bg-light position-sticky end-0" style="z-index: 2;">Ação</th>
                                                <?php endif; ?>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($dbResult as $row): ?>
                                                <tr>
                                                    <?php foreach ($row as $col => $val): ?>
                                                        <td title="<?= htmlspecialchars($val) ?>">
                                                            <?= strlen($val) > 50 ? substr(htmlspecialchars($val), 0, 50) . '...' : htmlspecialchars($val) ?>
                                                        </td>
                                                    <?php endforeach; ?>
                                                    <td class="text-end position-sticky end-0 bg-white shadow-sm">
                                                        <?php 
                                                            // Find potential XML column
                                                            $xmlCol = '';
                                                            foreach ($row as $k => $v) {
                                                                if (strpos($v, '\x') === 0 || strpos($v, '<') === 0) {
                                                                    $xmlCol = $v;
                                                                    break;
                                                                }
                                                            }
                                                        ?>
                                                        <?php if ($xmlCol): ?>
                                                            <button type="button" class="btn btn-xs btn-outline-success" onclick="loadToExtractor('<?= htmlspecialchars($xmlCol, ENT_QUOTES) ?>')" title="Carregar no Extrator">
                                                                <i class="bi bi-box-arrow-up-right"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                    <?php if (empty($dbResult)): ?>
                                        <div class="text-center py-4 text-muted">Nenhum resultado encontrado.</div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function filterDictTables() {
    const input = document.getElementById('tableSearch').value.toLowerCase();
    const buttons = document.getElementById('dictTableList').getElementsByTagName('button');
    for (let btn of buttons) {
        if (btn.textContent.toLowerCase().includes(input)) {
            btn.classList.remove('d-none');
        } else {
            btn.classList.add('d-none');
        }
    }
}

let selectedTable = '';
let selectedColumns = [];

function selectDictTable(name, cols) {
    selectedTable = name;
    selectedColumns = cols;
    document.getElementById('selectedTableName').textContent = name;
    document.getElementById('selectedTableInfo').classList.remove('d-none');
    
    // Highlight active
    const buttons = document.getElementById('dictTableList').getElementsByTagName('button');
    for (let btn of buttons) {
        btn.classList.remove('active');
        if (btn.textContent.trim() === name) btn.classList.add('active');
    }
}

function generateSelect() {
    if (!selectedTable) return;
    
    // Try to find XML columns
    const xmlCols = selectedColumns.filter(c => c.toLowerCase().includes('xml') || c.toLowerCase().includes('arquivo') || c.toLowerCase().includes('conteudo'));
    const colsToSelect = xmlCols.length > 0 ? ['*', ...xmlCols] : ['*'];
    
    const query = `SELECT * FROM ${selectedTable} LIMIT 10;`;
    document.getElementById('sqlQuery').value = query;
}

function loadToExtractor(content) {
    // Switch tab
    const tab = new bootstrap.Tab(document.getElementById('manual-tab'));
    tab.show();
    
    // Set content
    document.getElementById('hex_content').value = content;
    
    // Scroll to top
    window.scrollTo(0, 0);
    
    // Optional: Auto submit
    // document.getElementById('extractorForm').submit();
}

// Inactivity Timer
let inactivityTime = function () {
    let time;
    const timeout = 300000; // 5 minutes

    function logout() {
        // Only if connected (check for disconnect button)
        const disconnectBtn = document.querySelector('input[name="action"][value="disconnect"]');
        if (disconnectBtn) {
            console.log("Auto-disconnecting due to inactivity...");
            disconnectBtn.closest('form').submit();
        }
    }

    function resetTimer() {
        clearTimeout(time);
        time = setTimeout(logout, timeout);
    }

    // Events to reset timer
    window.onload = resetTimer;
    document.onmousemove = resetTimer;
    document.onkeypress = resetTimer;
    document.ontouchstart = resetTimer; // Touchscreen
    document.onclick = resetTimer;      // Touchpad clicks
    document.onscroll = resetTimer;     // Scrolling
};

// Start timer if on DB tab
if (document.getElementById('db-tab')) {
    inactivityTime();
}
</script>

<style>
.bg-primary-gradient {
    background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
}
.nav-tabs-custom .nav-link {
    color: #6c757d;
    border: none;
    border-bottom: 2px solid transparent;
    padding: 1rem 1.5rem;
    font-weight: 500;
}
.nav-tabs-custom .nav-link.active {
    color: #0d6efd;
    border-bottom: 2px solid #0d6efd;
    background: transparent;
}
.nav-tabs-custom .nav-link:hover {
    border-color: transparent;
    color: #0d6efd;
}
.btn-xs {
    padding: 0.1rem 0.4rem;
    font-size: 0.75rem;
}
</style>

<?php include 'includes/footer.php'; ?>
