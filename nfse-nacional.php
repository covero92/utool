<?php
session_start();
ob_start();

$schemasDir = __DIR__ . '/NFSE Nacional/Schemas/';
$jsonFile = __DIR__ . '/data/nfse_rules.json';
$isAdmin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;

// Helper to save CSV
function saveCsv($file, $data, $type) {
    if (!($fp = fopen($file, 'w'))) return false;
    // BOM for Excel
    fputs($fp, $bom =( chr(0xEF) . chr(0xBB) . chr(0xBF) ));
    // Header
    if ($type === 'nbs') fputcsv($fp, ['Código', 'Descrição'], ';');
    else fputcsv($fp, ['CTN', 'Item', 'Sub', 'Desd', 'Descrição'], ';');
    
    foreach ($data as $row) {
        if ($type === 'nbs') {
            fputcsv($fp, [$row['code'], $row['desc']], ';');
        } else {
            fputcsv($fp, [$row['ctn'], $row['item'], $row['sub'], $row['desd'], $row['desc']], ';');
        }
    }
    fclose($fp);
    return true;
}

// --- AJAX Handlers ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    
    // Login
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

    // Logout
    if ($_POST['action'] === 'logout') {
        unset($_SESSION['is_admin']);
        echo json_encode(['success' => true]);
        exit;
    }

    // Search Schemas
    if ($_POST['action'] === 'search_schemas') {
        $term = trim($_POST['term'] ?? '');
        $matches = [];
        if ($term !== '' && is_dir($schemasDir)) {
            $files = scandir($schemasDir);
            foreach ($files as $file) {
                if (pathinfo($file, PATHINFO_EXTENSION) === 'xsd') {
                    $content = file_get_contents($schemasDir . $file);
                    if (stripos($content, $term) !== false) {
                        $matches[] = $file;
                    }
                }
            }
        }
        echo json_encode(['success' => true, 'matches' => $matches]);
        exit;
    }

    // Save Rule (Admin)
    if ($_POST['action'] === 'save_rule' && $isAdmin) {
        $rules = [];
        if (file_exists($jsonFile)) $rules = json_decode(file_get_contents($jsonFile), true);
        
        $newRule = [
            'code' => $_POST['code'],
            'level' => $_POST['level'],
            'message' => $_POST['message'],
            'rule' => $_POST['rule'],
            'field' => $_POST['field'],
            'path' => $_POST['path'],
            'applicability' => $_POST['applicability'],
            'observations' => $_POST['observations']
        ];

        $isEdit = false;
        foreach ($rules as $k => $r) {
            if ($r['code'] === $newRule['code']) {
                $rules[$k] = $newRule;
                $isEdit = true;
                break;
            }
        }
        if (!$isEdit) $rules[] = $newRule;

        usort($rules, fn($a, $b) => strcmp($a['code'], $b['code']));
        file_put_contents($jsonFile, json_encode($rules, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        echo json_encode(['success' => true]);
        exit;
    }

    // Delete Rule (Admin)
    if ($_POST['action'] === 'delete_rule' && $isAdmin) {
        $code = $_POST['code'];
        $rules = [];
        if (file_exists($jsonFile)) $rules = json_decode(file_get_contents($jsonFile), true);
        $rules = array_filter($rules, fn($r) => $r['code'] !== $code);
        file_put_contents($jsonFile, json_encode(array_values($rules), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        echo json_encode(['success' => true]);
        exit;
    }

    // Validate XML
    if ($_POST['action'] === 'validate_xml') {
        $xmlContent = $_POST['xml'] ?? '';
        $schemaFile = $_POST['schema'] ?? '';
        $schemaPath = $schemasDir . $schemaFile;

        if (empty($xmlContent) || empty($schemaFile) || !file_exists($schemaPath)) {
            echo json_encode(['success' => false, 'errors' => ['Schema não encontrado ou XML vazio.']]);
            exit;
        }

        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        
        if (!$dom->loadXML($xmlContent)) {
            $errors = [];
            foreach (libxml_get_errors() as $error) $errors[] = "Erro de Sintaxe (Linha {$error->line}): {$error->message}";
            libxml_clear_errors();
            echo json_encode(['success' => false, 'errors' => $errors]);
            exit;
        }

        $isValid = $dom->schemaValidate($schemaPath);
        $errors = [];
        if (!$isValid) {
            foreach (libxml_get_errors() as $error) $errors[] = "Erro de Validação (Linha {$error->line}): {$error->message}";
            libxml_clear_errors();
        }
        echo json_encode(['success' => $isValid, 'errors' => $errors]);
        exit;
    }
    
    // Fetch Codes
     if ($_POST['action'] === 'fetch_codes') {
        $type = $_POST['type'] ?? 'nbs';
        $file = ($type === 'nbs') ? __DIR__ . '/NFSE Nacional/nbs.csv' : __DIR__ . '/NFSE Nacional/lista_servico_nacional.csv';
        if (!file_exists($file)) { echo json_encode(['success' => false, 'message' => 'Arquivo n&atilde;o encontrado']); exit; }
        
        $results = [];
        if (($handle = fopen($file, "r")) !== FALSE) {
            fgetcsv($handle, 1000, ";"); // Skip header
            while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
                $data = array_map('trim', $data);
                $data = array_map(fn($d) => mb_convert_encoding($d, 'UTF-8', 'ISO-8859-1, Windows-1252'), $data);
                if ($type === 'nbs') {
                    if ($data[0]) $results[] = ['code' => $data[0], 'desc' => $data[1] ?? ''];
                } else {
                     if ($data[1] || $data[0]) {
                        // Sometimes desc is last or 4th index
                        $desc = $data[4] ?? ($data[count($data)-1]);
                        $results[] = ['ctn' => $data[0], 'item' => $data[1], 'sub' => $data[2], 'desd' => $data[3], 'desc' => $desc];
                     }
                }
            }
            fclose($handle);
        }
        echo json_encode(['success' => true, 'results' => $results]);
        exit;
    }

    // Save NBS Data (Admin)
    if ($_POST['action'] === 'save_nbs_data' && $isAdmin) {
        $data = json_decode($_POST['data'], true);
        if (saveCsv(__DIR__ . '/NFSE Nacional/nbs.csv', $data, 'nbs')) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erro ao salvar arquivo.']);
        }
        exit;
    }

    // Save Service Data (Admin)
    if ($_POST['action'] === 'save_service_data' && $isAdmin) {
        $data = json_decode($_POST['data'], true);
        if (saveCsv(__DIR__ . '/NFSE Nacional/lista_servico_nacional.csv', $data, 'service')) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erro ao salvar arquivo.']);
        }
        exit;
    }
}

// --- Schema Analysis Logic ---
function parseXsdStructure($xmlContent) {
    $dom = new DOMDocument();
    libxml_use_internal_errors(true);
    $dom->loadXML($xmlContent);
    libxml_clear_errors();
    $xpath = new DOMXPath($dom);
    $xpath->registerNamespace('xs', 'http://www.w3.org/2001/XMLSchema');
    
    $structure = [];
    $getDoc = function($node) use ($xpath) {
        $doc = $xpath->query('.//xs:annotation/xs:documentation', $node)->item(0);
        return $doc ? trim($doc->nodeValue) : '';
    };

    foreach ($xpath->query('//xs:complexType') as $ct) {
        $name = $ct->getAttribute('name');
        $elements = [];
        $children = $xpath->query('.//xs:sequence/xs:element | .//xs:choice/xs:element | .//xs:complexContent/xs:extension//xs:sequence/xs:element', $ct);
        foreach ($children as $child) {
            $elements[] = [
                'name' => $child->getAttribute('name') ?: $child->getAttribute('ref'),
                'type' => $child->getAttribute('type'),
                'doc' => $getDoc($child),
                'min' => $child->getAttribute('minOccurs') ?: '1',
                'max' => $child->getAttribute('maxOccurs') ?: '1'
            ];
        }
        $structure[] = ['type' => 'ComplexType', 'name' => $name, 'doc' => $getDoc($ct), 'children' => $elements];
    }
    return $structure;
}

$activeSchema = $_GET['schema'] ?? null;
$parsedStructure = [];
$selectedSchemaContent = '';

if ($activeSchema && file_exists($schemasDir . $activeSchema)) {
    $selectedSchemaContent = file_get_contents($schemasDir . $activeSchema);
    $parsedStructure = parseXsdStructure($selectedSchemaContent);
}

// Get Schemas List
$schemas = [];
if (is_dir($schemasDir)) {
    foreach (scandir($schemasDir) as $f) {
        if (pathinfo($f, PATHINFO_EXTENSION) === 'xsd') $schemas[] = $f;
    }
}

// Load Rules Data
$rulesData = [];
if (file_exists($jsonFile)) {
    $rulesData = json_decode(file_get_contents($jsonFile), true);
}

// Load CST Data for IBS View
include 'reforma_tributaria_data.php';
$flatCsts = [];
if (isset($csts) && is_array($csts)) {
    foreach ($csts as $group) {
        if (isset($group['classificacoes'])) {
            foreach ($group['classificacoes'] as $cst) {
                 $flatCsts[$cst['codigo']] = $cst;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NFS-e Nacional | DevTools</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <!-- Prism & Theme -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism-tomorrow.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="assets/css/glass-theme.css">
    <!-- Legacy Libs -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.14.0/Sortable.min.js"></script>
    <script src="https://cdn.sheetjs.com/xlsx-latest/package/dist/xlsx.full.min.js"></script>
    
    <style>
        .sidebar { width: 280px; background: rgba(255, 255, 255, 0.65); backdrop-filter: blur(12px); border-right: 1px solid var(--glass-border); position: fixed; top: 0; bottom: 0; left: 0; z-index: 1000; overflow-y: auto; padding: 1.5rem; }
        .main-content { margin-left: 280px; padding: 2rem; }
        .nav-pills .nav-link { color: var(--color-text-secondary); border-radius: 12px; padding: 12px 16px; margin-bottom: 4px; transition: all 0.2s ease; font-weight: 500; }
        .nav-pills .nav-link:hover { background: rgba(255, 255, 255, 0.5); color: var(--color-accent); transform: translateX(4px); }
        .nav-pills .nav-link.active { background: var(--color-accent); color: white; box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3); }
        .nav-pills .nav-link i { margin-right: 10px; font-size: 1.1em; }
        .glass-card { background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(16px); border: 1px solid rgba(255, 255, 255, 0.5); border-radius: 20px; padding: 1.5rem; box-shadow: 0 8px 32px rgba(0, 0, 0, 0.05); }
        .code-view { background: #1e1e1e; border-radius: 12px; max-height: 600px; }
    </style>
</head>
<body>

<aside class="sidebar d-flex flex-column">
    <div class="mb-5 px-2">
        <h4 class="fw-bold text-dark mb-0"><i class="bi bi-code-square text-accent me-2"></i>NFS-e Tool</h4>
        <small class="text-muted">NFS-e Nacional</small>
    </div>

    <ul class="nav nav-pills flex-column flex-grow-1" id="mainTab" role="tablist">
        <li class="nav-item">
            <button class="nav-link active w-100 text-start" data-bs-toggle="pill" data-bs-target="#tab-schemas">
                <i class="bi bi-file-earmark-code"></i>Schemas XSD
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link w-100 text-start" data-bs-toggle="pill" data-bs-target="#tab-validator">
                <i class="bi bi-check-circle"></i>Validador NFS-e
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link w-100 text-start" data-bs-toggle="pill" data-bs-target="#tab-rules">
                <i class="bi bi-list-check"></i>Regras de Validação
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link w-100 text-start" data-bs-toggle="pill" data-bs-target="#tab-consultas">
                <i class="bi bi-search"></i>Tabelas
            </button>
        </li>
         <li class="nav-item">
            <button class="nav-link w-100 text-start" data-bs-toggle="pill" data-bs-target="#tab-ibs">
                <i class="bi bi-bank"></i>IBS/NBS (XLSX)
            </button>
        </li>
    </ul>

    <div class="mt-auto pt-4 border-top">
        <div class="d-flex align-items-center justify-content-between px-2">
            <?php if ($isAdmin): ?>
                <span class="badge bg-success-gradient rounded-pill"><i class="bi bi-shield-check me-1"></i>Admin</span>
                <button class="btn btn-sm btn-icon text-danger" onclick="doLogout()" title="Sair"><i class="bi bi-box-arrow-right"></i></button>
            <?php else: ?>
                <span class="text-muted small">Modo Leitura</span>
                <button class="btn btn-sm btn-outline-primary rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#loginModal">Login</button>
            <?php endif; ?>
        </div>
        <a href="index.php" class="btn btn-light w-100 mt-3 rounded-pill text-muted">
            <i class="bi bi-arrow-left me-2"></i>Voltar
        </a>
    </div>
</aside>

<main class="main-content">
    <div class="tab-content">
        <!-- SCHEMAS -->
        <div class="tab-pane fade show active" id="tab-schemas">
             <div class="row g-4">
                <div class="col-md-3">
                    <div class="glass-card h-100 p-0 overflow-hidden d-flex flex-column">
                        <div class="p-3 border-bottom bg-white-50">
                            <h6 class="fw-bold mb-3">XSD</h6>
                            <input type="text" class="form-control form-control-sm" placeholder="Search..." id="schemaFilter">
                        </div>
                        <div class="list-group list-group-flush overflow-auto custom-scrollbar flex-grow-1" style="max-height: 70vh;">
                            <?php foreach ($schemas as $schema): ?>
                                <a href="?schema=<?php echo urlencode($schema); ?>" class="list-group-item list-group-item-action border-0 py-3 <?php echo ($activeSchema === $schema) ? 'active-schema bg-light' : ''; ?>">
                                    <div class="d-flex align-items-center">
                                        <div class="icon-box bg-white text-primary rounded-circle shadow-sm me-3" style="width:32px;height:32px;font-size:1rem;"><i class="bi bi-filetype-xml"></i></div>
                                        <div>
                                            <div class="fw-bold text-dark text-truncate" style="max-width:140px;"><?php echo $schema; ?></div>
                                            <small class="text-muted"><?php echo date("d/m/Y", filemtime($schemasDir . $schema)); ?></small>
                                        </div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-9">
                    <?php if ($activeSchema): ?>
                        <div class="glass-card h-100 d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div><h4 class="fw-bold mb-1"><?php echo $activeSchema; ?></h4><span class="badge bg-light text-secondary border">Schema Definition</span></div>
                                <div class="btn-group">
                                    <input type="radio" class="btn-check" name="viewMode" id="viewStruct" checked onclick="toggleView('struct')">
                                    <label class="btn btn-outline-primary" for="viewStruct">Estrutura</label>
                                    <input type="radio" class="btn-check" name="viewMode" id="viewCode" onclick="toggleView('code')">
                                    <label class="btn btn-outline-primary" for="viewCode">Código</label>
                                </div>
                            </div>
                            <div id="structView" class="flex-grow-1 overflow-auto custom-scrollbar pe-2">
                                <?php if(empty($parsedStructure)): ?><div class="text-center py-5 text-muted">Nenhuma estrutura complexa.</div><?php else: ?>
                                    <?php foreach ($parsedStructure as $ct): ?>
                                        <div class="card border-0 shadow-sm mb-4">
                                            <div class="card-header bg-white py-3"><h5 class="mb-0 font-monospace text-primary"><?php echo $ct['name']; ?></h5><small class="text-muted"><?php echo $ct['doc']; ?></small></div>
                                            <div class="card-body p-0">
                                                <table class="table table-hover mb-0 align-middle">
                                                    <thead class="table-light small"><tr><th class="ps-4">Campo</th><th>Tipo</th><th>Ocorr.</th><th>Descrição</th></tr></thead>
                                                    <tbody>
                                                        <?php foreach ($ct['children'] as $child): ?>
                                                            <tr>
                                                                <td class="ps-4 font-monospace fw-bold text-dark"><?php echo $child['name']; ?></td>
                                                                <td><span class="badge bg-light text-secondary border font-monospace"><?php echo $child['type']; ?></span></td>
                                                                <td class="small font-monospace"><?php echo $child['min']; ?>..<?php echo $child['max']; ?></td>
                                                                <td class="small text-muted"><?php echo $child['doc']; ?></td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                            <div id="codeView" class="d-none h-100 position-relative">
                                <pre class="code-view h-100 m-0"><code class="language-xml"><?php echo htmlspecialchars($selectedSchemaContent); ?></code></pre>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="h-100 d-flex flex-column align-items-center justify-content-center text-muted opacity-50"><h4>Selecione um Schema</h4></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- VALIDATOR -->
         <div class="tab-pane fade" id="tab-validator">
             <div class="glass-card mb-4 text-center py-5">
                 <h2 class="fw-bold text-accent mb-2">Validador NFS-e</h2>
                 <p class="text-muted">Valide seus arquivos XML contra os Schemas da Receita Federal (v1.01)</p>
             </div>
             <div class="row justify-content-center">
                 <div class="col-lg-10">
                     <div class="glass-card">
                         <div class="row g-4">
                             <div class="col-md-4 border-end">
                                 <label class="form-label fw-bold mb-3">1. Selecione o Schema</label>
                                 <select id="validatorSchema" class="form-select form-select-lg mb-4">
                                     <option value="">Escolher...</option>
                                     <?php foreach ($schemas as $s): ?>
                                         <option value="<?php echo $s; ?>" <?php echo strpos($s, 'DPS')!==false ? 'selected':''; ?>><?php echo $s; ?></option>
                                     <?php endforeach; ?>
                                 </select>
                             </div>
                             <div class="col-md-8">
                                 <label class="form-label fw-bold mb-3">2. Cole seu XML</label>
                                 <div class="position-relative">
                                     <textarea id="xmlInput" class="form-control font-monospace border-0 bg-light" rows="12" placeholder="<DPS>...</DPS>"></textarea>
                                     <div class="position-absolute bottom-0 end-0 m-3 d-flex gap-2">
                                         <button class="btn btn-primary shadow-sm" onclick="validateXml()">Check</button>
                                     </div>
                                 </div>
                             </div>
                         </div>
                         <div id="validationResult" class="mt-4 d-none">
                             <div id="validationAlert" class="alert d-flex align-items-center"><i id="validationIcon" class="bi me-2"></i><div id="validationBody"></div></div>
                         </div>
                     </div>
                 </div>
             </div>
        </div>

        <!-- RULES -->
        <div class="tab-pane fade" id="tab-rules">
            <div class="glass-card h-100 d-flex flex-column">
                <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
                    <h4 class="fw-bold mb-0">Regras de Validação</h4>
                    <?php if($isAdmin): ?>
                        <button class="btn btn-primary btn-sm" onclick="openRuleModal()"><i class="bi bi-plus-lg me-2"></i>Nova Regra</button>
                    <?php endif; ?>
                </div>
                <div class="row g-3 mb-4">
                    <div class="col-md-6"><input type="text" id="ruleSearch" class="form-control" placeholder="Pesquisar regra, mensagem ou campo..."></div>
                    <div class="col-md-3">
                        <select id="filterLevel" class="form-select"><option value="">Nível (Todos)</option><option value="1">1 - Schema</option><option value="2">2 - Nacional</option><option value="3">3 - Municipal</option></select>
                    </div>
                </div>
                <div class="flex-grow-1 overflow-auto custom-scrollbar">
                    <table class="table table-hover align-middle small">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th style="width: 50px;">Nível</th>
                                <th style="width: 80px;">Cód</th>
                                <th>Mensagem / Regra</th>
                                <th>Aplicabilidade</th>
                                <th>Campo / Caminho</th>
                                <?php if($isAdmin): ?><th style="width: 100px;">Ações</th><?php endif; ?>
                            </tr>
                        </thead>
                        <tbody id="rulesTableBody"></tbody>
                    </table>
                    <div id="noRulesFound" class="text-center py-5 d-none"><p class="text-muted">Nenhuma regra encontrada.</p></div>
                </div>
            </div>
        </div>

        <!-- CODES -->
        <div class="tab-pane fade" id="tab-consultas">
             <div class="glass-card h-100 d-flex flex-column">
                 <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
                     <div><h4 class="fw-bold mb-1">Tabelas de Códigos</h4><p class="text-muted small mb-0">NBS / Lista de Serviços</p></div>
                     <div class="d-flex gap-2">
                         <?php if($isAdmin): ?>
                             <div class="form-check form-switch pt-1">
                                 <input class="form-check-input" type="checkbox" id="editModeSwitch" onchange="toggleEditMode()">
                                 <label class="form-check-label fw-bold text-danger" for="editModeSwitch">Editor</label>
                             </div>
                             <div id="editorActions" class="d-none">
                                <button class="btn btn-sm btn-success" onclick="addNewItem()"><i class="bi bi-plus-lg"></i></button>
                                <button class="btn btn-sm btn-primary" onclick="saveNbsChanges()"><i class="bi bi-save"></i></button>
                             </div>
                         <?php endif; ?>
                         <a href="reforma_tributaria.php" target="_blank" class="btn btn-outline-primary btn-sm">Reforma Tributária</a>
                     </div>
                 </div>

                 <div class="row g-3 mb-4">
                     <div class="col-md-4">
                         <div class="btn-group w-100">
                             <input type="radio" class="btn-check" name="searchType" id="searchNbs" value="nbs" checked>
                             <label class="btn btn-outline-primary" for="searchNbs">NBS</label>
                             <input type="radio" class="btn-check" name="searchType" id="searchService" value="service">
                             <label class="btn btn-outline-primary" for="searchService">Serviços</label>
                         </div>
                     </div>
                     <div class="col-md-8">
                         <input type="text" id="codeSearchInput" class="form-control" placeholder="Buscar...">
                     </div>
                 </div>

                 <div class="flex-grow-1 overflow-auto custom-scrollbar border rounded bg-white position-relative">
                     <div id="codeResults" class="d-none">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light sticky-top" style="z-index: 5;"><tr id="codesHeaderRow"></tr></thead>
                            <tbody id="codeResultsBody"></tbody>
                        </table>
                     </div>
                     <div id="codesEmptyState" class="text-center py-5">
                         <div class="spinner-border text-primary d-none" id="codesLoading"></div>
                         <div id="codesHelp"><i class="bi bi-search display-4 text-muted opacity-25"></i></div>
                     </div>
                 </div>
             </div>
        </div>

        <!-- IBS/NBS -->
        <div class="tab-pane fade" id="tab-ibs">
            <div class="glass-card h-100 d-flex flex-column">
                <h4 class="fw-bold mb-3">Tabela de Correlação IBS/NBS</h4>
                <div class="input-group mb-3">
                    <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                    <input type="text" id="ibsSearch" class="form-control" placeholder="Pesquisar..." disabled>
                </div>
                <div id="ibsLoading" class="text-center py-5"><div class="spinner-border text-primary"></div><p class="mt-2">Carregando Excel...</p></div>
                <div id="ibsContent" class="d-none flex-grow-1 overflow-auto custom-scrollbar">
                     <table class="table table-hover table-striped mb-0 small">
                         <thead class="table-light sticky-top"><tr><th>Item</th><th>NBS</th><th>Descrição</th><th>P/S</th><th>Adq.Ext</th><th>CST</th></tr></thead>
                         <tbody id="ibsTableBody"></tbody>
                     </table>
                     <p class="text-muted xsmall mt-2">* Fonte: relacaoibsnbs.xlsx</p>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- MODALS -->
<!-- Login -->
<div class="modal fade" id="loginModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content glass-card border-0">
            <div class="modal-body p-4 text-center">
                <h5 class="fw-bold mb-3">Login</h5>
                <form id="loginForm">
                    <input type="text" name="user" class="form-control mb-2" placeholder="User">
                    <input type="password" name="pass" class="form-control mb-3" placeholder="Pass">
                    <button type="submit" class="btn btn-primary w-100 rounded-pill">Entrar</button>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- Rule Editor -->
<div class="modal fade" id="ruleModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title" id="ruleModalTitle">Regra</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <form id="ruleForm">
                    <div class="row g-3">
                        <div class="col-md-3"><label>Código</label><input type="text" name="code" class="form-control" required></div>
                        <div class="col-md-3"><label>Nível</label><select name="level" class="form-select"><option value="1">1</option><option value="2">2</option><option value="3">3</option></select></div>
                        <div class="col-md-6"><label>Aplicabilidade</label><input type="text" name="applicability" class="form-control"></div>
                        <div class="col-12"><label>Mensagem</label><input type="text" name="message" class="form-control" required></div>
                        <div class="col-12"><label>Regra Técnica</label><textarea name="rule" class="form-control"></textarea></div>
                    </div>
                </form>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-primary" onclick="saveRule()">Salvar</button></div>
        </div>
    </div>
</div>
<!-- Item Editor (Codes) -->
<div class="modal fade" id="editItemModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Editar Item</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <form id="editItemForm">
                    <input type="hidden" id="editItemIndex">
                    <div id="fieldsNbs" class="mb-3"><label>Código NBS</label><input type="text" id="editNbsCode" class="form-control"></div>
                    <div id="fieldsService" class="d-none row g-2 mb-3">
                        <div class="col-3"><input type="text" id="editServiceCtn" class="form-control" placeholder="CTN"></div>
                        <div class="col-3"><input type="text" id="editServiceItem" class="form-control" placeholder="Item"></div>
                        <div class="col-3"><input type="text" id="editServiceSub" class="form-control" placeholder="Sub"></div>
                        <div class="col-3"><input type="text" id="editServiceDesd" class="form-control" placeholder="Desd"></div>
                    </div>
                    <div class="mb-3"><label>Descrição</label><textarea id="editDesc" class="form-control" rows="3"></textarea></div>
                </form>
            </div>
            <div class="modal-footer"><button class="btn btn-primary" onclick="saveItemFromModal()">Salvar</button></div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/prism.min.js"></script>
<script>
    // --- Global Data & Init ---
    const rulesData = <?php echo json_encode($rulesData); ?>;
    const cstData = <?php echo json_encode($flatCsts); ?>;
    const isAdmin = <?php echo json_encode($isAdmin); ?>;
    
    // Tabs
    document.querySelectorAll('#mainTab button').forEach(el => new bootstrap.Tab(el));

    // --- XML Validator ---
    function validateXml() {
        // ... (Same logic, compacted) ...
        const xml=document.getElementById('xmlInput').value, schema=document.getElementById('validatorSchema').value;
        if(!xml||!schema){alert('Preencha os campos');return;}
        document.querySelector('button[onclick="validateXml()"]').disabled=true;
        const fd=new FormData(); fd.append('action','validate_xml'); fd.append('xml',xml); fd.append('schema',schema);
        fetch('nfse-nacional.php',{method:'POST',body:fd}).then(r=>r.json()).then(d=>{
            const res=document.getElementById('validationResult'); res.classList.remove('d-none');
            const alert=document.getElementById('validationAlert'), icon=document.getElementById('validationIcon'), body=document.getElementById('validationBody');
            if(d.success){ alert.className='alert alert-success'; icon.className='bi bi-check-circle-fill text-success'; body.innerText='XML Válido!'; }
            else{ alert.className='alert alert-danger'; icon.className='bi bi-x-circle-fill text-danger'; body.innerHTML=d.errors.join('<br>'); }
        }).finally(()=>document.querySelector('button[onclick="validateXml()"]').disabled=false);
    }
    function toggleView(m){ document.getElementById('structView').className=m==='struct'?'':'d-none'; document.getElementById('codeView').className=m==='code'?'':'d-none'; }

    // --- RULES Logic ---
    function renderRules(list) {
        const tbody=document.getElementById('rulesTableBody'); tbody.innerHTML='';
        if(list.length===0) { document.getElementById('noRulesFound').classList.remove('d-none'); return; }
        document.getElementById('noRulesFound').classList.add('d-none');
        list.slice(0,100).forEach(r => {
            const tr=document.createElement('tr');
            // Combine Field and Path for better display
            const fieldDisplay = r.field ? `<div class="fw-bold text-dark font-monospace">${r.field}</div>` : '';
            const pathDisplay = r.path ? `<div class="text-muted font-monospace text-truncate" style="max-width: 200px; font-size: 0.75em;" title="${r.path}">${r.path}</div>` : '';
            
            // Message and Technical Rule
            const msgDisplay = `<div class="fw-medium text-dark">${r.message}</div>`;
            const techRuleDisplay = r.rule ? `<div class="text-muted small mt-1 border-start ps-2 border-3" style="font-size: 0.85em;">${r.rule}</div>` : '';
            
            // Level Badge Color
            let badgeClass = 'bg-secondary';
            if(r.level == '1') badgeClass = 'bg-info text-dark';
            if(r.level == '2') badgeClass = 'bg-primary';
            if(r.level == '3') badgeClass = 'bg-warning text-dark';

            tr.innerHTML=`
                <td class="text-center"><span class="badge ${badgeClass} rounded-pill">${r.level}</span></td>
                <td class="font-monospace text-danger fw-bold">${r.code}</td>
                <td>
                    ${msgDisplay}
                    ${techRuleDisplay}
                </td>
                <td class="small text-secondary">${r.applicability || '-'}</td>
                <td>
                    ${fieldDisplay}
                    ${pathDisplay}
                </td>
                ${isAdmin?`<td><button class="btn btn-sm btn-outline-primary me-1" onclick='openRuleModal(${JSON.stringify(r)})'><i class="bi bi-pencil"></i></button><button class="btn btn-sm btn-outline-danger" onclick="deleteRule('${r.code}')"><i class="bi bi-trash"></i></button></td>`:''}
            `;
            tbody.appendChild(tr);
        });
    }
    document.getElementById('ruleSearch').addEventListener('input', filterRules);
    document.getElementById('filterLevel').addEventListener('change', filterRules);
    function filterRules(){
        const term=document.getElementById('ruleSearch').value.toLowerCase(), level=document.getElementById('filterLevel').value;
        renderRules(rulesData.filter(r => (r.code.toLowerCase().includes(term)||r.message.toLowerCase().includes(term)) && (level===''||r.level==level)));
    }
    renderRules(rulesData);
    
    // Rules CRUD
    function openRuleModal(r=null){
        const f=document.getElementById('ruleForm'); f.reset();
        document.getElementById('ruleModalTitle').innerText=r?'Editar Regra':'Nova Regra';
        if(r){
            f.code.value=r.code; f.level.value=r.level; f.message.value=r.message; f.rule.value=r.rule; f.field.value=r.field;
            f.applicability.value=r.applicability;
        }
        new bootstrap.Modal(document.getElementById('ruleModal')).show();
    }
    function saveRule(){
        const fd=new FormData(document.getElementById('ruleForm')); fd.append('action','save_rule');
        fetch('nfse-nacional.php',{method:'POST',body:fd}).then(()=>location.reload());
    }
    function deleteRule(c){
        if(confirm('Excluir?')){ const fd=new FormData(); fd.append('action','delete_rule'); fd.append('code',c); fetch('nfse-nacional.php',{method:'POST',body:fd}).then(()=>location.reload()); }
    }

    // --- CODES Logic ---
    let codesData=[], codesType='nbs', sortable=null;
    function loadCodes(t){
        codesType=t; document.getElementById('codesLoading').classList.remove('d-none'); document.getElementById('codeResults').classList.add('d-none');
        const fd=new FormData(); fd.append('action','fetch_codes'); fd.append('type',t);
        fetch('nfse-nacional.php',{method:'POST',body:fd}).then(r=>r.json()).then(d=>{
            codesData=d.results; filterCodes(); document.getElementById('codesLoading').classList.add('d-none'); document.getElementById('codeResults').classList.remove('d-none');
        });
    }
    function filterCodes(){
        const term=document.getElementById('codeSearchInput').value.toLowerCase();
        const res=codesData.filter(i => (JSON.stringify(i).toLowerCase().includes(term)));
        renderCodes(res.slice(0, 100)); // Limit render
    }
    function renderCodes(data){
        const tb=document.getElementById('codeResultsBody'); tb.innerHTML='';
        const head=document.getElementById('codesHeaderRow');
        const isEdit=document.getElementById('editModeSwitch')?.checked;
        
        if(codesType==='nbs') head.innerHTML=`${isEdit?'<th width="30"></th>':''}<th>Código</th><th>Descrição</th>${isEdit?'<th>Edição</th>':''}`;
        else head.innerHTML=`${isEdit?'<th width="30"></th>':''}<th>CTN</th><th>Item</th><th>Descrição</th>${isEdit?'<th>Edição</th>':''}`;

        data.forEach((r,idx) => {
            const tr=document.createElement('tr'); tr.dataset.index=codesData.indexOf(r); // Use real index
            if(isEdit) tr.innerHTML+=`<td class="drag-handle"><i class="bi bi-grip-vertical text-muted"></i></td>`;
            if(codesType==='nbs') tr.innerHTML+=`<td><span class="badge bg-light text-dark border font-monospace">${r.code}</span></td><td>${r.desc}</td>`;
            else tr.innerHTML+=`<td>${r.ctn}</td><td>${r.item}</td><td>${r.desc}</td>`;
            if(isEdit) tr.innerHTML+=`<td><button class="btn btn-sm btn-outline-primary" onclick="editItem(${codesData.indexOf(r)})"><i class="bi bi-pencil"></i></button> <button class="btn btn-sm btn-outline-danger" onclick="removeItem(${codesData.indexOf(r)})"><i class="bi bi-trash"></i></button></td>`;
            tb.appendChild(tr);
        });
    }
    function toggleEditMode(){
        const on=document.getElementById('editModeSwitch').checked;
        document.getElementById('editorActions').classList.toggle('d-none',!on);
        if(on && !sortable) sortable=new Sortable(document.getElementById('codeResultsBody'), { handle:'.drag-handle', animation:150, onEnd: onSortEnd });
        if(!on && sortable) { sortable.destroy(); sortable=null; }
        filterCodes();
    }
    function onSortEnd(evt){
        // Reordering logic: logic is complex for filtered lists. warning user:
        if(document.getElementById('codeSearchInput').value) { alert('Limpe a busca para reordenar com segurança'); filterCodes(); return; }
        const item = codesData.splice(evt.oldIndex, 1)[0];
        codesData.splice(evt.newIndex, 0, item);
    }
    // Codes CRUD
    function addNewItem(){ openItemModal(); }
    function editItem(i){ openItemModal(codesData[i], i); }
    function openItemModal(item=null, idx='new'){
        document.getElementById('editItemIndex').value=idx; document.getElementById('editItemForm').reset();
        if(codesType==='nbs'){
            document.getElementById('fieldsNbs').classList.remove('d-none'); document.getElementById('fieldsService').classList.add('d-none');
            if(item){ document.getElementById('editNbsCode').value=item.code; }
        } else {
            document.getElementById('fieldsNbs').classList.add('d-none'); document.getElementById('fieldsService').classList.remove('d-none');
            if(item){ document.getElementById('editServiceCtn').value=item.ctn; document.getElementById('editServiceItem').value=item.item; document.getElementById('editServiceSub').value=item.sub; document.getElementById('editServiceDesd').value=item.desd; }
        }
        if(item) document.getElementById('editDesc').value=item.desc;
        new bootstrap.Modal(document.getElementById('editItemModal')).show();
    }
    function saveItemFromModal(){
         const idx=document.getElementById('editItemIndex').value;
         const desc=document.getElementById('editDesc').value;
         let obj={desc:desc};
         if(codesType==='nbs') obj.code=document.getElementById('editNbsCode').value;
         else { obj.ctn=document.getElementById('editServiceCtn').value; obj.item=document.getElementById('editServiceItem').value; obj.sub=document.getElementById('editServiceSub').value; obj.desd=document.getElementById('editServiceDesd').value; }
         
         if(idx==='new') codesData.unshift(obj);
         else codesData[idx]=obj;
         filterCodes();
         bootstrap.Modal.getInstance(document.getElementById('editItemModal')).hide();
    }
    function removeItem(i){ if(confirm('Excluir?')){ codesData.splice(i,1); filterCodes(); } }
    function saveNbsChanges(){
        if(!confirm('Salvar alterações em disco?')) return;
        const fd=new FormData(); fd.append('action', codesType==='nbs'?'save_nbs_data':'save_service_data'); fd.append('data', JSON.stringify(codesData));
        fetch('nfse-nacional.php',{method:'POST',body:fd}).then(r=>r.json()).then(d=>{ if(d.success) alert('Salvo!'); else alert('Erro'); });
    }
    document.querySelectorAll('input[name="searchType"]').forEach(el=>el.addEventListener('change',e=>loadCodes(e.target.value)));
    document.getElementById('codeSearchInput').addEventListener('input',filterCodes);
    document.querySelector('button[data-bs-target="#tab-consultas"]').addEventListener('shown.bs.tab',()=> { if(codesData.length===0) loadCodes('nbs'); });

    // --- IBS Logic ---
    let ibsDataLoaded=false;
    document.querySelector('button[data-bs-target="#tab-ibs"]').addEventListener('shown.bs.tab', function(){
        if(ibsDataLoaded) return;
        document.getElementById('ibsSearch').disabled=false;
        fetch('NFSE Nacional/relacaoibsnbs.xlsx').then(r=>r.arrayBuffer()).then(ab=>{
            const wb=XLSX.read(ab,{type:'array'});
            const sn=wb.SheetNames.find(n=>n.toLowerCase().includes('geral'))||wb.SheetNames[0];
            const data=XLSX.utils.sheet_to_json(wb.Sheets[sn], {header:1});
            const tbody=document.getElementById('ibsTableBody'); tbody.innerHTML='';
            // Skip header (row 0)
            data.slice(1).slice(0,500).forEach(row=>{
                if(row.length===0)return;
                const tr=document.createElement('tr');
                // Maps to old visual: Item, NBS, Desc, P/S, Adq, ClassTrib
                // Cols: A=0(item), C=2(nbs), D=3(desc), E=4(ps), F=5(adq), I=8(cst)
                tr.innerHTML=`<td>${row[0]||''} ${row[1]||''}</td><td class="font-monospace text-primary">${row[2]||''}</td><td>${row[3]||''}</td><td>${row[4]||''}</td><td>${row[5]||''}</td><td>${row[8]||''}</td>`;
                tbody.appendChild(tr);
            });
            document.getElementById('ibsLoading').classList.add('d-none');
            document.getElementById('ibsContent').classList.remove('d-none');
            ibsDataLoaded=true;
        });
    });
    // Login
    document.getElementById('loginForm').addEventListener('submit', function(e){ e.preventDefault(); const fd=new FormData(this); fd.append('action','login'); fetch('nfse-nacional.php',{method:'POST',body:fd}).then(r=>r.json()).then(d=>{ if(d.success) location.reload(); else alert(d.message); }); });
    function doLogout(){ const fd=new FormData(); fd.append('action','logout'); fetch('nfse-nacional.php',{method:'POST',body:fd}).then(()=>location.reload()); }
</script>
</body>
</html>
