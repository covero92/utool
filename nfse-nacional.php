<?php
session_start();
ob_start();

$schemasDir = __DIR__ . '/NFSE Nacional/Schemas/';
$jsonFile = __DIR__ . '/data/nfse_rules.json';
$jsonFile = __DIR__ . '/data/nfse_rules.json';
$isAdmin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;

// Handle Actions (AJAX)
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
        header('Location: nfse-nacional.php#tab-rules');
        exit;
    }

    // Save Rule (Add/Edit)
    if ($_POST['action'] === 'save_rule' && $isAdmin) {
        $rules = [];
        if (file_exists($jsonFile)) {
            $rules = json_decode(file_get_contents($jsonFile), true);
        }
        
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

        // Check if editing existing (by code)
        $isEdit = false;
        foreach ($rules as $k => $r) {
            if ($r['code'] === $newRule['code']) {
                $rules[$k] = $newRule; // Update
                $isEdit = true;
                break;
            }
        }
        
        if (!$isEdit) {
            $rules[] = $newRule;
        }

        // Sort by code
        usort($rules, function($a, $b) {
            return strcmp($a['code'], $b['code']);
        });

        file_put_contents($jsonFile, json_encode($rules, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        echo json_encode(['success' => true]);
        exit;
    }

    // Delete Rule
    if ($_POST['action'] === 'delete_rule' && $isAdmin) {
        $code = $_POST['code'];
        $rules = [];
        if (file_exists($jsonFile)) {
            $rules = json_decode(file_get_contents($jsonFile), true);
        }

        $rules = array_filter($rules, function($r) use ($code) {
            return $r['code'] !== $code;
        });

        file_put_contents($jsonFile, json_encode(array_values($rules), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
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
    // Validate XML
    if ($_POST['action'] === 'validate_xml') {
        $xmlContent = $_POST['xml'] ?? '';
        $schemaFile = $_POST['schema'] ?? '';
        $schemaPath = $schemasDir . $schemaFile;

        if (empty($xmlContent) || empty($schemaFile) || !file_exists($schemaPath)) {
            echo json_encode(['success' => false, 'errors' => ['Parâmetros inválidos ou schema não encontrado.']]);
            exit;
        }

        $dom = new DOMDocument();
        // Libxml setup to capture errors
        libxml_use_internal_errors(true);
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        
        $loaded = $dom->loadXML($xmlContent);
        
        if (!$loaded) {
            $errors = [];
            foreach (libxml_get_errors() as $error) {
                $errors[] = "Erro de Sintaxe (Linha {$error->line}): {$error->message}";
            }
            libxml_clear_errors();
            echo json_encode(['success' => false, 'errors' => $errors]);
            exit;
        }

        // Validate against Schema
        $isValid = $dom->schemaValidate($schemaPath);
        $errors = [];

        if (!$isValid) {
            foreach (libxml_get_errors() as $error) {
                $errors[] = "Erro de Validação (Linha {$error->line}): {$error->message}";
            }
            libxml_clear_errors();
        }

        echo json_encode(['success' => $isValid, 'errors' => $errors]);
        exit;
    }
    // Helper to send JSON response
    function sendNfseJson($data) {
        // Clear any previous output (warnings, notices, etc.)
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_INVALID_UTF8_IGNORE);
        exit;
    }

    // Fetch Codes (NBS / Service List) - Full Load
    if ($_POST['action'] === 'fetch_codes') {
        file_put_contents(__DIR__ . '/debug_fetch.log', "Fetch Start: " . date('Y-m-d H:i:s') . "\n", FILE_APPEND);
        
        $type = $_POST['type'] ?? 'nbs'; // 'nbs' or 'service'
        
        $results = [];
        $file = ($type === 'nbs') ? __DIR__ . '/NFSE Nacional/nbs.csv' : __DIR__ . '/NFSE Nacional/lista_servico_nacional.csv';

        if (!file_exists($file)) {
             file_put_contents(__DIR__ . '/debug_fetch.log', "File Not Found: $file\n", FILE_APPEND);
             sendNfseJson(['success' => false, 'message' => 'Arquivo base não encontrado: ' . basename($file)]);
        }

        if (($handle = fopen($file, "r")) !== FALSE) {
            $header = fgetcsv($handle, 1000, ";"); // Skip header
            
            while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
                // remove empty values caused by leading/trailing delimiters if any
                $data = array_map('trim', $data);
                
                // FORCE UTF-8
                $data = array_map(function($d) {
                    return mb_convert_encoding($d, 'UTF-8', 'ISO-8859-1, Windows-1252');
                }, $data);

                $item = [];

                if ($type === 'nbs') {
                    // CÓDIGO NBS;DESCRIÇÃO
                    $code = $data[0] ?? '';
                    $desc = $data[1] ?? '';
                    if ($code) {
                        $item = ['code' => $code, 'desc' => $desc];
                        $results[] = $item;
                    }
                } else {
                     // Service Logic (same as before)
                    $ctn = $data[0] ?? '';
                    $itemVal = $data[1] ?? '';
                    $sub = $data[2] ?? '';
                    $desd = $data[3] ?? '';
                    $desc = $data[4] ?? '';
                    
                    if (empty($desc)) $desc = end($data);

                    if ($itemVal || $ctn) {
                        $item = [
                            'ctn' => $ctn,
                            'item' => $itemVal,
                            'sub' => $sub,
                            'desd' => $desd,
                            'desc' => $desc
                        ];
                        $results[] = $item;
                    }
                }
            }
            fclose($handle);
        }
        
        file_put_contents(__DIR__ . '/debug_fetch.log', "Fetch End. Results count: " . count($results) . "\n", FILE_APPEND);
        sendNfseJson(['success' => true, 'results' => $results]);
    }

    // Save NBS Data (Admin Only)
    if ($_POST['action'] === 'save_nbs_data' && $isAdmin) {
        $data = json_decode($_POST['data'], true);
        
        if (!is_array($data)) {
            sendNfseJson(['success' => false, 'message' => 'Dados inválidos']);
        }

        $file = __DIR__ . '/NFSE Nacional/nbs.csv';
        
        // Backup
        if (file_exists($file)) {
            copy($file, $file . '.bak.' . date('YmdHis'));
        }

        if (($handle = fopen($file, "w")) !== FALSE) {
            // Write Header
            fputcsv($handle, ['CÓDIGO NBS', 'DESCRIÇÃO'], ';');
            
            foreach ($data as $item) {
                $code = $item['code'] ?? '';
                $desc = $item['desc'] ?? '';
                
                $fields = [$code, $desc];
                $fields = array_map(function($f) {
                     return mb_convert_encoding($f, 'Windows-1252', 'UTF-8');
                }, $fields);

                fputcsv($handle, $fields, ';');
            }
            fclose($handle);
            sendNfseJson(['success' => true]);
        } else {
            sendNfseJson(['success' => false, 'message' => 'Erro ao escrever no arquivo']);
        }
    }

    // Save Service List Data (Admin Only)
    if ($_POST['action'] === 'save_service_data' && $isAdmin) {
        $data = json_decode($_POST['data'], true);
        
        if (!is_array($data)) {
            sendNfseJson(['success' => false, 'message' => 'Dados inválidos']);
        }

        $file = __DIR__ . '/NFSE Nacional/lista_servico_nacional.csv';
        
        // Backup
        if (file_exists($file)) {
            copy($file, $file . '.bak.' . date('YmdHis'));
        }

        if (($handle = fopen($file, "w")) !== FALSE) {
            // Write Header
            fputcsv($handle, ['CÓDIGO DE TRIBUTAÇÃO NACIONAL', 'ITEM', 'SUBITEM', 'DESD', 'DESCRIÇÃO'], ';');
            
            foreach ($data as $item) {
                $ctn  = $item['ctn'] ?? '';
                $it   = $item['item'] ?? '';
                $sub  = $item['sub'] ?? '';
                $desd = $item['desd'] ?? '';
                $desc = $item['desc'] ?? '';
                
                $fields = [$ctn, $it, $sub, $desd, $desc];
                $fields = array_map(function($f) {
                     return mb_convert_encoding($f, 'Windows-1252', 'UTF-8');
                }, $fields);

                fputcsv($handle, $fields, ';');
            }
            fclose($handle);
            sendNfseJson(['success' => true]);
        } else {
            sendNfseJson(['success' => false, 'message' => 'Erro ao escrever no arquivo']);
        }
    }
}

include 'includes/header.php';

$activeTab = 'schemas';

// Get List of Schemas
$schemas = [];
if (is_dir($schemasDir)) {
    $files = scandir($schemasDir);
    foreach ($files as $file) {
        if (pathinfo($file, PATHINFO_EXTENSION) === 'xsd') {
            $schemas[] = $file;
        }
    }
}

// Handle Schema View
// Handle Schema View
$selectedSchemaContent = '';
$selectedSchema = '';
$parsedStructure = [];

function parseXsdStructure($xmlContent) {
    $dom = new DOMDocument();
    libxml_use_internal_errors(true);
    $dom->loadXML($xmlContent);
    libxml_clear_errors();
    
    $xpath = new DOMXPath($dom);
    $xpath->registerNamespace('xs', 'http://www.w3.org/2001/XMLSchema');
    
    $structure = [];

    // Helper to extract documentation
    $getDoc = function($node) use ($xpath) {
        $doc = '';
        $docNode = $xpath->query('.//xs:annotation/xs:documentation', $node)->item(0);
        if ($docNode) {
            $doc = trim($docNode->nodeValue);
        }
        return $doc;
    };

    // Find Top-Level Elements
    $elements = $xpath->query('/xs:schema/xs:element');
    foreach ($elements as $element) {
        $name = $element->getAttribute('name');
        $type = $element->getAttribute('type');
        $doc = $getDoc($element);
        
        $structure[] = [
            'type' => 'Element',
            'name' => $name,
            'dataType' => $type,
            'doc' => $doc
        ];
    }
    
    // Find all ComplexTypes
    $complexTypes = $xpath->query('//xs:complexType');
    foreach ($complexTypes as $ct) {
        $name = $ct->getAttribute('name');
        $elements = [];
        $baseType = null;
        
        // Handle Extension/Inheritance (complexContent)
        $extension = $xpath->query('.//xs:complexContent/xs:extension', $ct)->item(0);
        if ($extension) {
            $baseType = $extension->getAttribute('base');
            // Children within extension
             $children = $xpath->query('.//xs:sequence/xs:element | .//xs:choice/xs:element', $extension);
        } else {
             // Standard sequence/choice
            $children = $xpath->query('.//xs:sequence/xs:element | .//xs:choice/xs:element', $ct);
        }

        foreach ($children as $child) {
            $elemName = $child->getAttribute('name');
            $ref = $child->getAttribute('ref');
            $type = $child->getAttribute('type');
            $min = $child->getAttribute('minOccurs') ?: '1';
            $max = $child->getAttribute('maxOccurs') ?: '1';
            $doc = $getDoc($child);
            
            $elements[] = [
                'name' => $elemName ?: $ref,
                'type' => $type,
                'min' => $min,
                'max' => $max,
                'doc' => $doc
            ];
        }
        
        $doc = $getDoc($ct);

        $structure[] = [
            'type' => 'ComplexType',
            'name' => $name,
            'base' => $baseType,
            'doc' => $doc,
            'children' => $elements
        ];
    }
    
    // Find all SimpleTypes
    $simpleTypes = $xpath->query('//xs:simpleType');
    foreach ($simpleTypes as $st) {
        $name = $st->getAttribute('name');
        $enums = [];
        $restrictions = [];
        
        // Get Restriction Base
        $resNode = $xpath->query('.//xs:restriction', $st)->item(0);
        if ($resNode) {
            $base = $resNode->getAttribute('base');
            if ($base) $restrictions['base'] = $base;
            
            // Get other restrictions
            $resChildren = $xpath->query('*', $resNode);
            foreach ($resChildren as $child) {
                $nodeName = $child->localName;
                if ($nodeName === 'enumeration') {
                    $val = $child->getAttribute('value');
                    $doc = $getDoc($child);
                    $enums[] = ['value' => $val, 'doc' => $doc];
                } elseif ($nodeName !== 'whiteSpace' && $nodeName !== 'annotation') {
                    $restrictions[$nodeName] = $child->getAttribute('value');
                }
            }
        }
        
        $stDoc = $getDoc($st);
        
        if (!empty($enums) || !empty($restrictions)) {
            $structure[] = [
                'type' => 'SimpleType',
                'name' => $name ?: 'Sem Nome',
                'doc' => $stDoc,
                'restrictions' => $restrictions,
                'enums' => $enums
            ];
        }
    }

    return $structure;
}

if (isset($_GET['view_schema'])) {
    $activeTab = 'schemas';
    $selectedSchema = basename($_GET['view_schema']);
    $schemaPath = $schemasDir . $selectedSchema;
    if (file_exists($schemaPath)) {
        $selectedSchemaContent = file_get_contents($schemaPath);
        $parsedStructure = parseXsdStructure($selectedSchemaContent);
    }
}
?>


</div> <!-- Close container from header -->
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 fw-bold text-foreground">NFS-e Nacional</h1>
            <p class="text-muted-foreground">Ferramentas para validação e consulta da Nota Fiscal de Serviço Eletrônica Nacional.</p>
        </div>
        <div class="d-flex gap-2">
            <?php if ($isAdmin): ?>
                <form method="POST" class="d-inline">
                    <input type="hidden" name="action" value="logout">
                    <button type="submit" class="btn btn-outline-danger btn-sm">
                        <i class="bi bi-box-arrow-right"></i> Sair (Admin)
                    </button>
                </form>
            <?php else: ?>
                <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#loginModal">
                    <i class="bi bi-shield-lock"></i> Admin
                </button>
            <?php endif; ?>
            <a href="index.php" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white border-bottom-0 pt-4 px-4 pb-0">
            <ul class="nav nav-tabs nav-tabs-custom card-header-tabs" role="tablist">
                <li class="nav-item">
                    <button class="nav-link <?php echo $activeTab === 'schemas' ? 'active' : ''; ?>" data-bs-toggle="tab" data-bs-target="#tab-schemas">
                        <i class="bi bi-file-earmark-code me-2"></i>Schemas XSD
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-rules">
                        <i class="bi bi-list-check me-2"></i>Regras de Validação
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-ibs-nbs">
                        <i class="bi bi-table me-2"></i>Correlação IBS/NBS
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-validator">
                        <i class="bi bi-check-circle me-2"></i>Validador XML
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-consultas">
                        <i class="bi bi-search me-2"></i>Consultas (NBS/LC)
                    </button>
                </li>
            </ul>
        </div>
        <div class="card-body p-4">
            <div class="tab-content">
                <!-- Schemas Tab -->
                <div class="tab-pane fade <?php echo $activeTab === 'schemas' ? 'show active' : ''; ?>" id="tab-schemas">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="mb-3">
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                                    <input type="text" id="schemaSearchInput" class="form-control border-start-0 ps-0" placeholder="Filtrar..." onkeyup="filterSchemaList()">
                                </div>
                                <div class="d-grid mt-2">
                                    <button class="btn btn-sm btn-outline-primary" onclick="searchSchemaContent()">
                                        <i class="bi bi-file-earmark-text me-1"></i>Buscar no Conteúdo
                                    </button>
                                </div>
                            </div>
                            <div class="list-group custom-scrollbar" style="max-height: 600px; overflow-y: auto;" id="schemaListGroup">
                                <?php foreach ($schemas as $schema): ?>
                                    <a href="?view_schema=<?php echo urlencode($schema); ?>#tab-schemas" class="list-group-item list-group-item-action <?php echo $selectedSchema === $schema ? 'active' : ''; ?> schema-item" data-name="<?php echo strtolower($schema); ?>">
                                        <i class="bi bi-file-earmark-code me-2"></i><?php echo htmlspecialchars($schema); ?>
                                        <span class="badge bg-warning text-dark float-end d-none match-badge">Match</span>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="col-md-9">
                            <?php if ($selectedSchemaContent): ?>
                                <div class="card border-0 bg-dark text-light">
                                    <div class="card-header bg-transparent border-secondary d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center gap-3">
                                            <span class="font-monospace small text-light"><?php echo htmlspecialchars($selectedSchema); ?></span>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <input type="radio" class="btn-check" name="viewMode" id="viewCode" autocomplete="off" checked onclick="toggleSchemaView('code')">
                                                <label class="btn btn-outline-light" for="viewCode"><i class="bi bi-code-slash me-1"></i>Código</label>

                                                <input type="radio" class="btn-check" name="viewMode" id="viewStruct" autocomplete="off" onclick="toggleSchemaView('struct')">
                                                <label class="btn btn-outline-light" for="viewStruct"><i class="bi bi-table me-1"></i>Estrutura</label>
                                            </div>
                                        </div>
                                        <div class="d-flex gap-2 align-items-center" id="codeControls">
                                            <div class="input-group input-group-sm" style="width: 300px;">
                                                <input type="text" id="localSearchInput" class="form-control bg-dark text-light border-secondary" placeholder="Buscar neste arquivo..." onkeydown="handleLocalSearch(event)">
                                                <button class="btn btn-outline-secondary text-light" onclick="findNext()" title="Próximo (Enter)">
                                                    <i class="bi bi-arrow-down"></i>
                                                </button>
                                                <button class="btn btn-outline-secondary text-light" onclick="findPrev()" title="Anterior (Shift+Enter)">
                                                    <i class="bi bi-arrow-up"></i>
                                                </button>
                                                <span class="input-group-text bg-dark text-light border-secondary small" id="searchCount">0/0</span>
                                            </div>
                                            <button class="btn btn-sm btn-outline-light" onclick="copySchema()">
                                                <i class="bi bi-clipboard"></i> Copiar
                                            </button>
                                        </div>
                                        <!-- Structure Filter (Hidden in Code View) -->
                                        <!-- Edit Item Modal (Custom) -->
<div class="modal fade" id="editItemModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title" id="editItemModalTitle">Editar Item</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="editItemForm">
                    <input type="hidden" id="editItemIndex">
                    
                    <!-- NBS Fields -->
                    <div id="fieldsNbs" class="d-none">
                        <div class="mb-3">
                            <label for="editNbsCode" class="form-label fw-bold">Código NBS</label>
                            <input type="text" class="form-control form-control-lg font-monospace" id="editNbsCode" placeholder="Ex: 1.0101.1">
                        </div>
                    </div>

                    <!-- Service List Fields -->
                    <div id="fieldsService" class="d-none">
                         <div class="row g-2 mb-3">
                            <div class="col-12">
                                <label for="editServiceCtn" class="form-label fw-bold">Cód. Trib. Nac.</label>
                                <input type="text" class="form-control font-monospace" id="editServiceCtn" placeholder="Ex: 01.01.01">
                            </div>
                            <div class="col-4">
                                <label for="editServiceItem" class="form-label fw-bold">Item</label>
                                <input type="text" class="form-control font-monospace" id="editServiceItem" placeholder="01">
                            </div>
                            <div class="col-4">
                                <label for="editServiceSub" class="form-label fw-bold">Sub</label>
                                <input type="text" class="form-control font-monospace" id="editServiceSub" placeholder="01">
                            </div>
                            <div class="col-4">
                                <label for="editServiceDesd" class="form-label fw-bold">Desd</label>
                                <input type="text" class="form-control font-monospace" id="editServiceDesd" placeholder="00">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="editDesc" class="form-label fw-bold">Descrição</label>
                        <textarea class="form-control" id="editDesc" rows="3" placeholder="Descrição do serviço..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary px-4" onclick="saveItemFromModal()">
                    <i class="bi bi-check-lg me-2"></i>Salvar
                </button>
            </div>
        </div>
    </div>
</div>

<div class="d-none" id="logoutData"></div>                                        <div class="d-none" id="structControls">
                                            <div class="input-group input-group-sm" style="width: 250px;">
                                                <span class="input-group-text bg-dark text-light border-secondary"><i class="bi bi-funnel"></i></span>
                                                <input type="text" id="structFilterInput" class="form-control bg-dark text-light border-secondary" placeholder="Filtrar estrutura..." onkeyup="filterStructure()">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body p-0 position-relative">
                                        <!-- Code View -->
                                        <div id="schemaCodeView">
                                            <pre class="m-0" style="max-height: 600px; overflow: auto;"><code class="language-xml" id="schemaContent"><?php echo htmlspecialchars($selectedSchemaContent); ?></code></pre>
                                        </div>
                                        
                                        <!-- Structure View -->
                                        <div id="schemaStructureView" class="d-none bg-light text-dark p-3" style="max-height: 600px; overflow: auto;">
                                            <?php if (empty($parsedStructure)): ?>
                                                <div class="alert alert-warning">Não foi possível identificar estruturas complexas ou simples neste arquivo.</div>
                                            <?php else: ?>
                                                <?php foreach ($parsedStructure as $item): ?>
                                                    <div class="card mb-4 shadow-sm border-0 struct-card" data-name="<?php echo strtolower($item['name']); ?>">
                                                        <div class="card-header bg-white border-bottom py-3">
                                                            <div class="d-flex align-items-center">
                                                                <?php
                                                                    $badgeClass = 'bg-secondary';
                                                                    $typeName = 'Elemento';
                                                                    if ($item['type'] === 'ComplexType') { $badgeClass = 'bg-primary'; $typeName = 'Tipo Complexo'; }
                                                                    if ($item['type'] === 'SimpleType') { $badgeClass = 'bg-info text-dark'; $typeName = 'Tipo Simples'; }
                                                                ?>
                                                                <span class="badge <?php echo $badgeClass; ?> me-2"><?php echo $typeName; ?></span>
                                                                <h5 class="mb-0 font-monospace text-primary"><?php echo $item['name']; ?></h5>
                                                            </div>
                                                            <?php if ($item['type'] === 'Element' && $item['doc']): ?>
                                                                <div class="mt-2 text-muted small"><?php echo $item['doc']; ?></div>
                                                            <?php endif; ?>
                                                        </div>
                                                        <div class="card-body p-0">
                                                            <?php if ($item['type'] === 'Element'): ?>
                                                                <div class="p-3">
                                                                    <strong>Tipo:</strong> <span class="font-monospace text-secondary"><?php echo $item['dataType']; ?></span>
                                                                </div>
                                                            <?php elseif ($item['type'] === 'ComplexType'): ?>
                                                                <div class="table-responsive">
                                                                    <?php if (!empty($item['base'])): ?>
                                                                        <div class="px-3 pt-3 pb-2 bg-light border-bottom">
                                                                            <small class="text-muted fw-bold">HERANÇA (Base):</small> 
                                                                            <span class="badge bg-secondary font-monospace"><?php echo $item['base']; ?></span>
                                                                        </div>
                                                                    <?php endif; ?>
                                                                    <?php if ($item['doc']): ?>
                                                                        <div class="px-3 py-2 bg-white border-bottom">
                                                                            <small class="d-block text-primary fw-bold mb-1">Descrição</small>
                                                                            <div class="small text-muted"><?php echo $item['doc']; ?></div>
                                                                        </div>
                                                                    <?php endif; ?>
                                                                    <table class="table table-hover table-striped mb-0 align-middle">
                                                                        <thead class="table-light text-muted small text-uppercase">
                                                                            <tr>
                                                                                <th class="ps-3">Campo</th>
                                                                                <th>Tipo</th>
                                                                                <th class="text-center">Ocorrências</th>
                                                                                <th>Descrição</th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                            <?php foreach ($item['children'] as $child): ?>
                                                                                <tr class="struct-row" data-text="<?php echo strtolower($child['name'] . ' ' . $child['type']); ?>">
                                                                                    <td class="ps-3 font-monospace fw-bold text-dark"><?php echo $child['name']; ?></td>
                                                                                    <td><span class="badge bg-light text-secondary border font-monospace"><?php echo $child['type']; ?></span></td>
                                                                                    <td class="text-center small text-muted font-monospace"><?php echo $child['min']; ?>..<?php echo $child['max']; ?></td>
                                                                                    <td class="small text-muted"><?php echo $child['doc'] ?: '-'; ?></td>
                                                                                </tr>
                                                                            <?php endforeach; ?>
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                            <?php else: ?>
                                                                <div class="p-3 border-bottom bg-light">
                                                                    <?php if (!empty($item['restrictions'])): ?>
                                                                        <h6 class="small fw-bold text-muted text-uppercase mb-2">Restrições</h6>
                                                                        <div class="d-flex flex-wrap gap-2 mb-3">
                                                                            <?php foreach ($item['restrictions'] as $key => $val): ?>
                                                                                <span class="badge bg-white text-secondary border">
                                                                                    <span class="fw-bold text-dark"><?php echo $key; ?>:</span> <?php echo $val; ?>
                                                                                </span>
                                                                            <?php endforeach; ?>
                                                                        </div>
                                                                    <?php endif; ?>
                                                                </div>
                                                                <?php if (!empty($item['enums'])): ?>
                                                                    <div class="table-responsive">
                                                                        <table class="table table-hover table-striped mb-0 align-middle">
                                                                            <thead class="table-light text-muted small text-uppercase">
                                                                                <tr>
                                                                                    <th class="ps-3">Valor</th>
                                                                                    <th>Descrição</th>
                                                                                </tr>
                                                                            </thead>
                                                                            <tbody>
                                                                                <?php foreach ($item['enums'] as $enum): ?>
                                                                                    <tr class="struct-row" data-text="<?php echo strtolower($enum['value']); ?>">
                                                                                        <td class="ps-3 font-monospace fw-bold text-dark"><?php echo $enum['value']; ?></td>
                                                                                        <td class="small text-muted"><?php echo $enum['doc'] ?: '-'; ?></td>
                                                                                    </tr>
                                                                                <?php endforeach; ?>
                                                                            </tbody>
                                                                        </table>
                                                                    </div>
                                                                <?php endif; ?>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="h-100 d-flex align-items-center justify-content-center text-muted p-5 border rounded bg-light">
                                    <p>Selecione um schema para visualizar seu conteúdo.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Rules Tab -->
                <div class="tab-pane fade" id="tab-rules">
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                                <input type="text" id="ruleSearch" class="form-control border-start-0 ps-0" placeholder="Pesquisar...">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <select id="filterLevel" class="form-select">
                                <option value="">Todos Níveis</option>
                                <option value="1">1 - Layout</option>
                                <option value="2">2 - Nacional</option>
                                <option value="3">3 - Municipal</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select id="filterApp" class="form-select">
                                <option value="">Todas Aplicações</option>
                                <option value="Recepção DPS">Recepção DPS</option>
                                <option value="Emissão NFS-e">Emissão NFS-e</option>
                                <option value="ADN Recepção">ADN Recepção</option>
                                <option value="ADN Emissão">ADN Emissão</option>
                            </select>
                        </div>
                        <div class="col-md-4 text-end">
                            <button class="btn btn-outline-info me-2" data-bs-toggle="modal" data-bs-target="#helpModal" title="Legenda">
                                <i class="bi bi-question-circle"></i>
                            </button>
                            <?php if ($isAdmin): ?>
                                <button class="btn btn-primary me-2" onclick="openRuleModal()">
                                    <i class="bi bi-plus-lg"></i> Nova
                                </button>
                            <?php endif; ?>
                            <a href="NFSE Nacional/ANEXO_I-SEFIN_ADN-DPS_NFSe-SNNFSe.xlsx" class="btn btn-outline-success" title="Baixar XLSX Original">
                                <i class="bi bi-download"></i>
                            </a>
                        </div>
                    </div>

                    <div class="table-responsive custom-scrollbar" style="max-height: 600px;">
                        <table class="table table-hover align-middle">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th style="width: 80px; cursor: pointer;" onclick="sortRules('level')">Nível <i class="bi bi-arrow-down-up small text-muted"></i></th>
                                    <th style="width: 100px; cursor: pointer;" onclick="sortRules('code')">Código <i class="bi bi-arrow-down-up small text-muted"></i></th>
                                    <th>Mensagem / Regra</th>
                                    <th>Campo / Caminho</th>
                                    <th>Aplicabilidade</th>
                                    <?php if ($isAdmin): ?><th style="width: 100px;">Ações</th><?php endif; ?>
                                </tr>
                            </thead>
                            <tbody id="rulesTableBody">
                                <!-- Populated by JS -->
                            </tbody>
                        </table>
                    </div>
                    <div id="noRulesFound" class="text-center py-5 d-none">
                        <p class="text-muted">Nenhuma regra encontrada para sua pesquisa.</p>
                    </div>
                </div>

                <!-- Services Tab (Removed) -->

                 <!-- IBS/NBS Tab -->
                 <div class="tab-pane fade" id="tab-ibs-nbs">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                                <input type="text" id="ibsSearch" class="form-control border-start-0 ps-0" placeholder="Pesquisar por Código, Descrição ou Item...">
                            </div>
                        </div>
                        <div class="col-md-6 text-end">
                             <a href="reforma_tributaria.php" class="btn btn-outline-primary">
                                <i class="bi bi-bank me-2"></i>Ver Reforma Tributária
                             </a>
                        </div>
                    </div>
                    
                    <div id="ibsLoading" class="text-center py-5">
                         <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Carregando...</span>
                        </div>
                        <p class="mt-2 text-muted">Carregando tabela de correlação...</p>
                    </div>

                    <div id="ibsContent" class="d-none">
                        <div class="table-responsive custom-scrollbar" style="max-height: 600px;">
                            <table class="table table-hover table-striped align-middle" id="ibsTable">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th>Item/Subitem</th>
                                        <th>Código NBS</th>
                                        <th>Descrição NBS</th>
                                        <th>P/S Onerosa</th>
                                        <th>Adq. Exterior</th>
                                        <th>cIndOp</th>
                                        <th>Local Incidência</th>
                                        <th>cClassTrib</th>
                                        <th>Desc. cClassTrib</th>
                                    </tr>
                                </thead>
                                <tbody id="ibsTableBody">
                                    <!-- Populated by JS -->
                                </tbody>
                            </table>
                        </div>
                        <p class="text-muted small mt-2">
                            * Dados carregados de <a href="https://www.gov.br/nfse/pt-br/biblioteca/documentacao-tecnica/rtc/anexoviii-correlacaoitemnbsindopcclasstrib_ibscbs_v1-00-00.xlsx" target="_blank">relacaoibsnbs.xlsx (Fonte Oficial)</a>
                        </p>
                    </div>
                 </div>

                 <!-- Validator Tab -->
                 <div class="tab-pane fade" id="tab-validator">
                    <div class="p-3">
                        <div class="row g-3">
                            <!-- Controls Area -->
                            <div class="col-md-9">
                                <div class="form-floating">
                                    <select id="validatorSchema" class="form-select">
                                        <option value="">Selecione um schema...</option>
                                        <?php foreach ($schemas as $schema): ?>
                                            <option value="<?php echo htmlspecialchars($schema); ?>" <?php echo (strpos($schema, 'DPS') !== false) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($schema); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <label for="validatorSchema">Schema XSD</label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <button class="btn btn-primary w-100 h-100" onclick="validateXml()">
                                    <i class="bi bi-check-lg me-2"></i>Validar XML
                                </button>
                            </div>

                            <!-- Editor Area -->
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center mb-2 mt-2">
                                    <span class="text-muted small fw-bold text-uppercase">XML para Validação</span>
                                    <button class="btn btn-sm btn-link text-decoration-none" onclick="document.getElementById('xmlInput').value = ''">Limpar</button>
                                </div>
                                <textarea id="xmlInput" class="form-control font-monospace mb-3" style="min-height: 400px; background-color: #f8f9fa;" placeholder="Cole o conteúdo do arquivo XML aqui..."></textarea>
                            </div>

                            <!-- Results Area -->
                            <div class="col-12">
                                <div id="validationResult" class="d-none">
                                    <div class="card shadow-sm">
                                        <div class="card-header fw-bold" id="validationHeader">Resultado da Validação</div>
                                        <div class="card-body p-0" id="validationBody">
                                            <!-- JS populates this -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                 </div>

                 <!-- Consultas Tab -->
                 <div class="tab-pane fade" id="tab-consultas">
                    <div class="row mb-4">
                        <div class="col-md-9 mx-auto">
                            <div class="card shadow-sm border-0 bg-light">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <!-- Search Type -->
                                        <div class="d-flex gap-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="searchType" id="searchNbs" value="nbs" checked>
                                                <label class="form-check-label fw-bold" for="searchNbs">Código NBS</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="searchType" id="searchService" value="service">
                                                <label class="form-check-label fw-bold" for="searchService">Lista de Serviços</label>
                                            </div>
                                        </div>

                                        <!-- Admin Controls -->
                                        <div>
                                            <?php if (!$isAdmin): ?>
                                                <button class="btn btn-sm btn-outline-dark" data-bs-toggle="modal" data-bs-target="#loginModal">
                                                    <i class="bi bi-lock me-1"></i>Admin
                                                </button>
                                            <?php else: ?>
                                                <div class="form-check form-switch d-inline-block me-2" id="editModeContainer">
                                                    <input class="form-check-input" type="checkbox" id="editModeSwitch" onchange="toggleEditMode()">
                                                    <label class="form-check-label fw-bold text-danger" for="editModeSwitch">Modo Edição</label>
                                                </div>
                                                <button class="btn btn-sm btn-outline-danger" onclick="logout()">Sair</button>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <div class="input-group input-group-lg">
                                        <input type="text" id="codeSearchInput" class="form-control" placeholder="Digite código ou descrição..." onkeyup="filterCodes()">
                                        <button class="btn btn-primary px-4" onclick="filterCodes()">
                                            <i class="bi bi-search me-2"></i>Pesquisar
                                        </button>
                                    </div>
                                    
                                    <!-- Editor Toolbar (Hidden by default) -->
                                    <div id="editorToolbar" class="d-none mt-3 p-2 bg-white border rounded">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="text-danger small fw-bold"><i class="bi bi-pencil-square me-1"></i>Editando NBS</span>
                                            <div class="d-flex gap-2">
                                                <button class="btn btn-sm btn-success" onclick="addNewItem()">
                                                    <i class="bi bi-plus-lg me-1"></i>Adicionar Novo
                                                </button>
                                                <button class="btn btn-sm btn-primary" onclick="saveNbsChanges()">
                                                    <i class="bi bi-save me-1"></i>Salvar Alterações
                                                </button>
                                            </div>
                                        </div>
                                        <div class="alert alert-warning small mb-0 mt-2 py-1">
                                            <i class="bi bi-exclamation-triangle me-1"></i>
                                            As alterações são salvas apenas ao clicar em "Salvar Alterações". A ordem na tabela define a estrutura.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div id="codeResults" class="d-none">
                        <div class="table-responsive custom-scrollbar" style="max-height: 500px;">
                            <table class="table table-hover table-striped align-middle" id="codesTable">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <!-- Header injected by JS -->
                                    </tr>
                                </thead>
                                <tbody id="codeResultsBody"></tbody>
                            </table>
                        </div>
                    </div>
                 </div>

            </div>
        </div>
    </div>
</div>
</div>

<!-- Login Modal -->
<div class="modal fade" id="loginModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Acesso Admin</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="loginForm">
                    <div class="mb-3">
                        <label class="form-label">Usuário</label>
                        <input type="text" name="user" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Senha</label>
                        <input type="password" name="pass" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Entrar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Rule Editor Modal -->
<div class="modal fade" id="ruleModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="ruleModalTitle">Nova Regra</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="ruleForm">
                    <input type="hidden" name="action" value="save_rule">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Código</label>
                            <input type="text" name="code" class="form-control font-monospace" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Nível</label>
                            <select name="level" class="form-select">
                                <option value="1">1 - Layout</option>
                                <option value="2">2 - Nacional</option>
                                <option value="3">3 - Municipal</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Aplicabilidade (separar por vírgula)</label>
                            <input type="text" name="applicability" class="form-control" placeholder="Recepção DPS, Emissão NFS-e...">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Mensagem de Erro</label>
                            <input type="text" name="message" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Descrição da Regra</label>
                            <textarea name="rule" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Campo</label>
                            <input type="text" name="field" class="form-control font-monospace">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Caminho XML</label>
                            <input type="text" name="path" class="form-control font-monospace">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Observações</label>
                            <textarea name="observations" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="saveRule()">Salvar</button>
            </div>
        </div>
    </div>
</div>

<!-- Help Modal -->
<div class="modal fade" id="helpModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-info-circle me-2"></i>Legenda das Regras</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <h6 class="fw-bold">Níveis de Validação</h6>
                <ul class="list-group mb-3">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        1 - Schema / Layout
                        <span class="badge bg-secondary">Nível 1</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        2 - Regra de Negócio Nacional
                        <span class="badge bg-primary">Nível 2</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        3 - Regra de Negócio Municipal
                        <span class="badge bg-warning text-dark">Nível 3</span>
                    </li>
                </ul>

                <h6 class="fw-bold">Aplicabilidade</h6>
                <p class="small text-muted">Indica em qual momento a regra é validada.</p>
                <div class="mb-3">
                    <span class="badge bg-info text-dark mb-1">Recepção DPS</span>
                    <span class="badge bg-primary mb-1">Emissão NFS-e</span>
                    <span class="badge bg-secondary mb-1">ADN Recepção</span>
                    <span class="badge bg-secondary mb-1">ADN Emissão</span>
                </div>

                <div class="alert alert-light border small">
                    <p class="mb-2"><strong><i class="bi bi-building me-1"></i>Emissores Públicos Nacionais (Recepção DPS / Emissão NFS-e):</strong><br>
                    Define as regras aplicadas quando a nota é gerada diretamente pelos sistemas da Receita Federal (Portal Web, Aplicativo Mobile ou via API nacional). Ocorre no momento em que o sistema recebe a DPS (Declaração de Prestação de Serviço).</p>

                    <p class="mb-2"><strong><i class="bi bi-share me-1"></i>ADN NFS-e - Compartilhamento (ADN Recepção / Emissão):</strong><br>
                    Refere-se a quando o Município possui sistema próprio de emissão e apenas envia (compartilha) os dados com o Ambiente de Dados Nacional (ADN). O sistema nacional valida se os dados recebidos da prefeitura estão corretos.</p>
                    
                    <hr class="my-2">
                    
                    <p class="mb-2"><strong><i class="bi bi-gavel me-1"></i>Decisão Judicial/Administrativa - Nacional:</strong><br>
                    Define quais regras se aplicam quando a nota é emitida pelo sistema nacional, mas forçada por uma ordem judicial ou administrativa (Status cStat = 102). Geralmente, validações são relaxadas aqui para cumprir a ordem legal.</p>

                    <p class="mb-0"><strong><i class="bi bi-gavel me-1"></i>Decisão Judicial/Administrativa - Compartilhamento:</strong><br>
                    Igual à anterior, mas para casos onde a prefeitura enviou uma nota gerada por ordem judicial.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Load Rules Data
$rulesData = [];
if (file_exists($jsonFile)) {
    $rulesData = json_decode(file_get_contents($jsonFile), true);
}

// Load CST Data from refroma_tributaria_data.php for usage in IBS/NBS tab
include 'reforma_tributaria_data.php';
// Flatten CSTs to simple array for easy JS lookup: code => details
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




<!-- View Detail Modal -->
<div class="modal fade" id="viewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-light border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold text-primary" id="viewModalTitle">Detalhes</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 pt-2">
                <div id="viewModalContent"></div>
            </div>
            <div class="modal-footer border-top-0 pt-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>


<!-- Edit Item Modal (NBS/Service) -->
<div class="modal fade" id="editItemModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editItemModalTitle">Editar Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editItemForm">
                    <input type="hidden" id="editItemIndex" value="">
                    
                    <div id="fieldsNbs">
                        <div class="mb-3">
                            <label class="form-label">Código NBS</label>
                            <input type="text" id="editNbsCode" class="form-control font-monospace" placeholder="1.01.01">
                            <div class="form-text text-muted small"><i class="bi bi-info-circle me-1"></i>A hierarquia é automática pelo código (ex: <strong>1.01.01</strong> será filho de <strong>1.01</strong>).</div>
                        </div>
                    </div>

                    <div id="fieldsService" class="d-none">
                        <div class="row g-2 mb-3">
                            <div class="col-3">
                                <label class="form-label">CTN</label>
                                <input type="text" id="editServiceCtn" class="form-control font-monospace" placeholder="1.01">
                            </div>
                            <div class="col-3">
                                <label class="form-label">Item</label>
                                <input type="text" id="editServiceItem" class="form-control font-monospace" placeholder="01">
                            </div>
                            <div class="col-3">
                                <label class="form-label">Sub</label>
                                <input type="text" id="editServiceSub" class="form-control font-monospace" placeholder="01">
                            </div>
                            <div class="col-3">
                                <label class="form-label">Desd</label>
                                <input type="text" id="editServiceDesd" class="form-control font-monospace" placeholder="00">
                            </div>
                            <div class="col-12 form-text text-muted small"><i class="bi bi-info-circle me-1"></i>Preencha Sub/Desd para criar níveis inferiores.</div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Descrição</label>
                        <textarea id="editDesc" class="form-control" rows="3" required></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="saveItemFromModal()">Salvar</button>
            </div>
        </div>
    </div>
</div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- SortableJS for Drag & Drop -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.14.0/Sortable.min.js"></script>
<script src="https://cdn.sheetjs.com/xlsx-latest/package/dist/xlsx.full.min.js"></script>
<script>
const rulesData = <?php echo json_encode($rulesData); ?>;
const cstData = <?php echo json_encode($flatCsts); ?>;
const isAdmin = <?php echo json_encode($isAdmin); ?>;

// --- Schema View Logic ---
function toggleSchemaView(mode) {
    const codeView = document.getElementById('schemaCodeView');
    const structView = document.getElementById('schemaStructureView');
    const codeControls = document.getElementById('codeControls');
    const structControls = document.getElementById('structControls');
    
    if (mode === 'code') {
        codeView.classList.remove('d-none');
        structView.classList.add('d-none');
        codeControls.classList.remove('d-none');
        structControls.classList.add('d-none');
    } else {
        codeView.classList.add('d-none');
        structView.classList.remove('d-none');
        codeControls.classList.add('d-none');
        structControls.classList.remove('d-none');
    }
}

function filterSchemaList() {
    const term = document.getElementById('schemaSearchInput').value.toLowerCase();
    const items = document.querySelectorAll('.schema-item');
    
    items.forEach(item => {
        const name = item.getAttribute('data-name');
        if (name.includes(term)) {
            item.classList.remove('d-none');
        } else {
            item.classList.add('d-none');
        }
    });
}

function filterStructure() {
    const term = document.getElementById('structFilterInput').value.toLowerCase();
    const cards = document.querySelectorAll('.struct-card');
    
    cards.forEach(card => {
        const name = card.getAttribute('data-name');
        const rows = card.querySelectorAll('.struct-row');
        let hasRowMatch = false;
        
        rows.forEach(row => {
            const text = row.getAttribute('data-text');
            if (text.includes(term)) {
                row.style.display = '';
                hasRowMatch = true;
            } else {
                row.style.display = 'none';
            }
        });
        
        if (name.includes(term) || hasRowMatch) {
            card.classList.remove('d-none');
        } else {
            card.classList.add('d-none');
        }
    });
}

function copySchema() {
    const content = document.getElementById('schemaContent').textContent;
    navigator.clipboard.writeText(content).then(() => {
        alert('Conteúdo copiado!');
    });
}

function searchSchemaContent() {
    const term = document.getElementById('schemaSearchInput').value;
    if (!term) return;
    
    const formData = new FormData();
    formData.append('action', 'search_schemas');
    formData.append('term', term);
    
    fetch('nfse-nacional.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            document.querySelectorAll('.schema-item').forEach(item => {
                const badge = item.querySelector('.match-badge');
                // Simple check if filename is in matches
                const filename = item.textContent.trim(); 
                // Note: textContent includes the badge text, but badge is hidden usually.
                // Better to check if any match contains the data-name?
                // The PHP returns exact filenames.
                // Let's just reset badges first
                badge.classList.add('d-none');
                
                data.matches.forEach(match => {
                    if (item.getAttribute('href').includes(match)) {
                        badge.classList.remove('d-none');
                    }
                });
            });
        }
    });
}

// Local Search (Simple implementation)
function handleLocalSearch(e) {
    if (e.key === 'Enter') {
        findNext();
    }
}

function findNext() {
    const term = document.getElementById('localSearchInput').value;
    if (window.find && term) {
        window.find(term);
    }
}

function findPrev() {
    const term = document.getElementById('localSearchInput').value;
    if (window.find && term) {
        window.find(term, false, true); // backwards
    }
}

function renderRules(rules) {
    const tbody = document.getElementById('rulesTableBody');
    const noFound = document.getElementById('noRulesFound');
    tbody.innerHTML = '';
    
    if (rules.length === 0) {
        noFound.classList.remove('d-none');
        return;
    }
    
    noFound.classList.add('d-none');
    
    // Limit to first 100 for performance if no search
    const displayRules = rules.slice(0, 100);
    
    displayRules.forEach(rule => {
        const tr = document.createElement('tr');
        
        // Format Applicability badges
        let appBadges = '';
        if (rule.applicability) {
            const apps = rule.applicability.split(', ');
            apps.forEach(app => {
                let badgeClass = 'bg-secondary';
                if (app.includes('Recepção')) badgeClass = 'bg-info text-dark';
                if (app.includes('Emissão')) badgeClass = 'bg-primary';
                appBadges += `<span class="badge ${badgeClass} me-1 mb-1" style="font-size: 0.7em;">${app}</span>`;
            });
        }

        // Level Badge Color
        let levelBadge = 'bg-secondary';
        if (rule.level == 2) levelBadge = 'bg-primary';
        if (rule.level == 3) levelBadge = 'bg-warning text-dark';

        tr.style.cursor = 'pointer';
        tr.onclick = (e) => {
            // Prevent if clicking action buttons
            if (e.target.closest('button')) return;
            
            const detailContent = `
                <div class="mb-3">
                    <span class="badge ${levelBadge} mb-2">Nível ${rule.level}</span>
                    <span class="badge bg-danger mb-2 font-monospace">${rule.code}</span>
                </div>
                
                <div class="mb-4">
                    <h6 class="fw-bold text-dark border-bottom pb-2">Mensagem</h6>
                    <div class="p-3 bg-light rounded border">${rule.message}</div>
                </div>
                
                <div class="mb-4">
                    <h6 class="fw-bold text-dark border-bottom pb-2">Regra Técnica</h6>
                    <div class="p-3 bg-light rounded border font-monospace small text-muted">${rule.rule}</div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <h6 class="fw-bold text-dark border-bottom pb-2">Campo</h6>
                        <div class="font-monospace text-primary">${rule.field}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <h6 class="fw-bold text-dark border-bottom pb-2">Caminho (XPath)</h6>
                        <div class="font-monospace text-muted small text-break">${rule.path}</div>
                    </div>
                </div>

                ${rule.observations ? `
                <div class="alert alert-warning mt-3">
                    <i class="bi bi-exclamation-triangle me-2"></i><strong>Observações:</strong><br>
                    ${rule.observations}
                </div>` : ''}
                
                <div class="mt-4">
                     <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">Aplicabilidade</h6>
                     ${appBadges}
                </div>
            `;
            openViewModal(`Regra ${rule.code}`, detailContent);
        };

        tr.innerHTML = `
            <td class="text-center"><span class="badge ${levelBadge} border">${rule.level}</span></td>
            <td><span class="badge bg-danger font-monospace">${rule.code}</span></td>
            <td>
                <div class="fw-bold small mb-1">${rule.message}</div>
                <div class="text-muted small fst-italic text-truncate" style="max-width: 400px;">${rule.rule}</div>
                ${rule.observations ? `<div class="mt-1 small text-warning"><i class="bi bi-exclamation-triangle me-1"></i>Obs. disponível</div>` : ''}
            </td>
            <td>
                <div class="small fw-bold font-monospace text-primary text-truncate" style="max-width: 150px;">${rule.field}</div>
                <div class="text-muted xsmall font-monospace text-truncate" style="max-width: 200px;">${rule.path}</div>
            </td>
            <td>${appBadges}</td>
            ${isAdmin ? `
                <td class="text-end">
                    <button class="btn btn-sm btn-outline-primary me-1" onclick='editRule(${JSON.stringify(rule)})'><i class="bi bi-pencil"></i></button>
                    <button class="btn btn-sm btn-outline-danger" onclick="deleteRule('${rule.code}')"><i class="bi bi-trash"></i></button>
                </td>
            ` : ''}
        `;
        tbody.appendChild(tr);
    });
}

// Auth & CRUD Functions
document.getElementById('loginForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    formData.append('action', 'login');
    
    fetch('nfse-nacional.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.text()) // Get text first to debug
    .then(text => {
        try {
            const data = JSON.parse(text);
            if (data.success) {
                location.reload();
            } else {
                alert(data.message);
            }
        } catch (e) {
            console.error('Invalid JSON response:', text);
            alert('Erro no servidor. Verifique o console.');
        }
    });
});

function openViewModal(title, content) {
    const modalEl = document.getElementById('viewModal');
    if(modalEl) {
        document.getElementById('viewModalTitle').innerHTML = title;
        document.getElementById('viewModalContent').innerHTML = content;
        new bootstrap.Modal(modalEl).show();
    } else {
        console.error('View Modal not found');
    }
}

function showCstDetails(code) {
    const data = cstData[code];
    if (!data) return;

    let indicators = '';
    // Helper icons
    const check = '<i class="bi bi-check-circle-fill text-success"></i>';
    const dash = '<i class="bi bi-dash-circle-fill text-secondary opacity-25"></i>';
    
    // Build indicator list or table
    indicators += `<div class="d-flex flex-wrap gap-2 mb-3">`;
    if(data.indicadores) {
        indicators += `<span class="badge bg-light text-dark border">${data.indicadores.tributacao_regular ? check : dash} Trib. Regular</span>`;
        indicators += `<span class="badge bg-light text-dark border">${data.indicadores.credito_presumido ? check : dash} Créd. Presumido</span>`;
        indicators += `<span class="badge bg-light text-dark border">${data.indicadores.estorno_credito ? check : dash} Estorno Créd.</span>`;
    }
    indicators += `</div>`;

    let dfes = '';
    if(data.dfes && data.dfes.length) {
        dfes = data.dfes.map(d => `<span class="badge bg-secondary me-1">${d}</span>`).join('');
    }

    const content = `
        <div class="mb-3">
             <span class="badge bg-primary fs-6 mb-2">${code}</span>
             <h5 class="fw-bold">${data.descricao}</h5>
        </div>
        
        <div class="row mb-3">
            <div class="col-6">
                <div class="p-2 border rounded bg-light text-center">
                    <small class="text-muted d-block">Redução IBS</small>
                    <span class="fs-5 fw-bold text-success">${data.reducao_ibs || '0.00'}%</span>
                </div>
            </div>
            <div class="col-6">
                 <div class="p-2 border rounded bg-light text-center">
                    <small class="text-muted d-block">Redução CBS</small>
                    <span class="fs-5 fw-bold text-success">${data.reducao_cbs || '0.00'}%</span>
                </div>
            </div>
        </div>

        <h6 class="fw-bold border-bottom pb-2">Indicadores</h6>
        ${indicators}

        <h6 class="fw-bold border-bottom pb-2">Documentos Fiscais</h6>
        <div class="mb-3">${dfes || '<span class="text-muted small">Nenhum especificado</span>'}</div>
        
        <h6 class="fw-bold border-bottom pb-2">Tipo de Alíquota</h6>
        <p>${data.tipo_aliquota || '-'}</p>

        ${data.legislacao ? 
            `<div class="mt-3 pt-2 border-top">
                <a href="${data.legislacao}" target="_blank" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-link-45deg me-1"></i>Ver Legislação
                </a>
             </div>` : ''}
    `;

    openViewModal(`Classificação Tributária: ${code}`, content);
}

function openRuleModal(rule = null) {
    const modal = new bootstrap.Modal(document.getElementById('ruleModal'));
    const form = document.getElementById('ruleForm');
    document.getElementById('ruleModalTitle').innerText = rule ? 'Editar Regra' : 'Nova Regra';
    
    if (rule) {
        form.code.value = rule.code;
        form.level.value = rule.level;
        form.message.value = rule.message;
        form.rule.value = rule.rule;
        form.field.value = rule.field;
        form.path.value = rule.path;
        form.applicability.value = rule.applicability;
        form.observations.value = rule.observations;
        form.code.readOnly = true; // Cannot change code when editing
    } else {
        form.reset();
        form.code.readOnly = false;
    }
    
    modal.show();
}

function openViewModal(title, content) {
    document.getElementById('viewModalTitle').textContent = title;
    document.getElementById('viewModalContent').innerHTML = content;
    new bootstrap.Modal(document.getElementById('viewModal')).show();
}

function editRule(rule) {
    openRuleModal(rule);
}

function saveRule() {
    const form = document.getElementById('ruleForm');
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    const formData = new FormData(form);
    
    fetch('nfse-nacional.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Erro ao salvar regra');
        }
    });
}

function deleteRule(code) {
    if (!confirm('Tem certeza que deseja excluir a regra ' + code + '?')) return;
    
    const formData = new FormData();
    formData.append('action', 'delete_rule');
    formData.append('code', code);
    
    fetch('nfse-nacional.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Erro ao excluir regra');
        }
    });
}



document.getElementById('filterLevel').addEventListener('change', filterRules);
document.getElementById('filterApp').addEventListener('change', filterRules);
document.getElementById('ruleSearch').addEventListener('input', filterRules);

let currentSort = { col: 'code', asc: true };

function filterRules() {
    const term = document.getElementById('ruleSearch').value.toLowerCase();
    const level = document.getElementById('filterLevel').value;
    const app = document.getElementById('filterApp').value;

    let filtered = rulesData.filter(rule => {
        const matchesTerm = (rule.code && rule.code.toLowerCase().includes(term)) ||
                            (rule.message && rule.message.toLowerCase().includes(term)) ||
                            (rule.field && rule.field.toLowerCase().includes(term)) ||
                            (rule.rule && rule.rule.toLowerCase().includes(term)) ||
                            (rule.observations && rule.observations.toLowerCase().includes(term));
        
        const matchesLevel = level === '' || rule.level == level;
        const matchesApp = app === '' || (rule.applicability && rule.applicability.includes(app));

        return matchesTerm && matchesLevel && matchesApp;
    });

    // Apply Sort
    filtered.sort((a, b) => {
        let valA = a[currentSort.col] || '';
        let valB = b[currentSort.col] || '';
        
        if (currentSort.col === 'level') {
            valA = parseInt(valA) || 0;
            valB = parseInt(valB) || 0;
        }

        if (valA < valB) return currentSort.asc ? -1 : 1;
        if (valA > valB) return currentSort.asc ? 1 : -1;
        return 0;
    });

    renderRules(filtered);
}

function sortRules(col) {
    if (currentSort.col === col) {
        currentSort.asc = !currentSort.asc;
    } else {
        currentSort.col = col;
        currentSort.asc = true;
    }
    filterRules();
}



// Initial Render
document.addEventListener('DOMContentLoaded', () => {
    // Existing rule render
    renderRules(rulesData);
    
    // Load IBS/NBS Data if tab is active or clicked
    const ibsTab = document.querySelector('button[data-bs-target="#tab-ibs-nbs"]');
    if (ibsTab) {
        ibsTab.addEventListener('shown.bs.tab', loadIbsNbsData);
    }
});

let ibsDataLoaded = false;
let ibsRawData = [];

function loadIbsNbsData() {
    if (ibsDataLoaded) return;
    
    const filePath = 'NFSE Nacional/relacaoibsnbs.xlsx';
    
    fetch(filePath)
        .then(response => response.arrayBuffer())
        .then(data => {
            const workbook = XLSX.read(data, { type: 'array' });
            
            // User specified 'tabela geral' as the main sheet
            let sheetName = 'tabela geral';
            if (!workbook.Sheets[sheetName]) {
                // Fallback to second sheet if specific name fails or just use the one with most data
                // Image showed "tabela geral" as the 2nd sheet usually, but user said "ficou a tabela geral".
                // Let's try to find it, otherwise pick the second one or the largest.
                sheetName = workbook.SheetNames.find(n => n.toLowerCase().includes('geral')) || workbook.SheetNames[1] || workbook.SheetNames[0];
            }
            
            const worksheet = workbook.Sheets[sheetName];
            
            // Convert to JSON
            const jsonData = XLSX.utils.sheet_to_json(worksheet, { header: 1 });
            
            // Remove header row (Row 1)
            ibsRawData = jsonData.slice(1); 
            
            renderIbsTable(ibsRawData);
            
            document.getElementById('ibsLoading').classList.add('d-none');
            document.getElementById('ibsContent').classList.remove('d-none');
            ibsDataLoaded = true;
        })
        .catch(error => {
            console.error('Error loading XLSX:', error);
            document.getElementById('ibsLoading').innerHTML = '<p class="text-danger">Erro ao carregar arquivo XLSX. Verifique se o arquivo existe em <code>NFSE Nacional/relacaoibsnbs.xlsx</code>.</p>';
        });
}

function renderIbsTable(data) {
    const tbody = document.getElementById('ibsTableBody');
    tbody.innerHTML = '';
    
    // Limit render for performance
    const displayData = data.slice(0, 500); 
    
    displayData.forEach(row => {
        if (row.length === 0) return;
        
        // Mapping based on Images provided:
        // Col A(0): Item/Subitem? (Let's assume A=0, B=1, C=2...)
        // Image shows:
        // Col D(3): Descrição NBS
        // Col E(4): P/S Onerosa
        // Col F(5): Adq Exterior
        // Col G(6): INDOP (cIndOp)
        // Col H(7): Local Incidência
        // Col I(8): cClassTrib
        // Col J(9): nome cClassTrib
        
        // We assume A(0), B(1), C(2) contain Item, Subitem, Code NBS
        
        const tr = document.createElement('tr');
        tr.style.cursor = 'pointer';
        
        const itemSub = (row[0] || '') + ' ' + (row[1] || '').toString(); // Item + Subitem
        const nbsCode = row[2] || '';
        const nbsDesc = row[3] || '';
        const psOnerosa = row[4] || '';
        const adqExt = row[5] || '';
        const cIndOp = row[6] || '';
        const localInc = row[7] || '';
        const cClassTrib = row[8] || '';
        const descClass = row[9] || '';

        // Prepare Detail View Content
        tr.onclick = () => {
             const detailContent = `
                <table class="table table-bordered">
                    <tr><th class="bg-light" style="width: 30%">Item/Subitem</th><td>${itemSub}</td></tr>
                    <tr><th class="bg-light">Código NBS</th><td class="font-monospace text-primary fw-bold">${nbsCode}</td></tr>
                    <tr><th class="bg-light">Descrição NBS</th><td>${nbsDesc}</td></tr>
                    <tr><th class="bg-light">P/S Onerosa</th><td>${psOnerosa}</td></tr>
                    <tr><th class="bg-light">Adq. Exterior</th><td>${adqExt}</td></tr>
                    <tr><th class="bg-light">cIndOp</th><td class="font-monospace">${cIndOp}</td></tr>
                    <tr><th class="bg-light">Local Incidência</th><td>${localInc}</td></tr>
                    <tr>
                        <th class="bg-light">cClassTrib</th>
                        <td class="font-monospace">
                            ${cClassTrib}
                            ${cstData[cClassTrib] ? 
                                `<br><button class="btn btn-sm btn-outline-info mt-1" onclick="event.stopPropagation(); showCstDetails('${cClassTrib}')">
                                    <i class="bi bi-info-circle me-1"></i>Ver Detalhes Tributação
                                 </button>` : ''}
                        </td>
                    </tr>
                    <tr><th class="bg-light">Desc. cClassTrib</th><td>${descClass}</td></tr>
                </table>
            `;
            openViewModal(`Detalhes IBS/NBS: ${nbsCode}`, detailContent);
        };

        const cols = [
            itemSub, 
            nbsCode, 
            nbsDesc, 
            psOnerosa, 
            adqExt, 
            cIndOp, 
            localInc, 
            // Wrap cClassTrib in link/button if valid code
            (cstData[cClassTrib] ? `<a href="javascript:void(0)" onclick="event.stopPropagation(); showCstDetails('${cClassTrib}')" class="fw-bold text-decoration-underline text-primary" title="Ver Detalhes da Classificação">${cClassTrib}</a>` : cClassTrib),
            descClass
        ];
        
        cols.forEach((cell, index) => {
             const td = document.createElement('td');
             // Use innerHTML for cClassTrib (index 7) to render the link
             if (index === 7 && typeof cell === 'string' && cell.startsWith('<a')) {
                 td.innerHTML = cell;
             } else {
                 td.textContent = cell !== undefined ? cell : '';
             }
             // Removed text-truncate and max-width as requested
             // "textos fiquem completos e não cortados"
             tr.appendChild(td);
        });
        
        tbody.appendChild(tr);
    });
}

// IBS Search
document.getElementById('ibsSearch').addEventListener('input', function(e) {
    const term = e.target.value.toLowerCase();
    
    if (!ibsDataLoaded) return;
    
    const filtered = ibsRawData.filter(row => {
        return row.some(cell => String(cell).toLowerCase().includes(term));
    });
    
    renderIbsTable(filtered);
    renderIbsTable(filtered);
});

function validateXml() {
    const xml = document.getElementById('xmlInput').value;
    const schema = document.getElementById('validatorSchema').value;
    const resultDiv = document.getElementById('validationResult');
    const header = document.getElementById('validationHeader');
    const body = document.getElementById('validationBody');

    if (!xml || !schema) {
        alert('Por favor, cole o XML e selecione um Schema.');
        return;
    }

    resultDiv.classList.add('d-none');
    
    // Show Loading
    const btn = document.querySelector('button[onclick="validateXml()"]');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Validando...';

    const formData = new FormData();
    formData.append('action', 'validate_xml');
    formData.append('xml', xml);
    formData.append('schema', schema);

    fetch('nfse-nacional.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        resultDiv.classList.remove('d-none');
        body.innerHTML = '';

        if (data.success) {
            header.className = 'card-header bg-success text-white fw-bold';
            header.innerHTML = '<i class="bi bi-check-circle-fill me-2"></i>XML Válido!';
            body.innerHTML = '<div class="alert alert-success mb-0">O XML está em conformidade com o Schema XSD selecionado.</div>';
        } else {
            header.className = 'card-header bg-danger text-white fw-bold';
            header.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-2"></i>Erros Encontrados';
            
            let html = '<div class="list-group list-group-flush">';
            if (data.errors && data.errors.length > 0) {
                data.errors.forEach(err => {
                    html += `<div class="list-group-item list-group-item-danger text-small"><i class="bi bi-x me-2"></i>${err}</div>`;
                });
            } else {
                html += '<div class="list-group-item list-group-item-danger">Erro desconhecido na validação.</div>';
            }
            html += '</div>';
            body.innerHTML = html;
        }
    })
    .catch(err => {
        console.error(err);
        alert('Erro na requisição: ' + err);
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
}
let codesData = [];
let codesType = 'nbs';

document.addEventListener('DOMContentLoaded', () => {
    // Other initializations...
    
    // Auto-load codes if tab is accessed
    const consultasTab = document.querySelector('button[data-bs-target="#tab-consultas"]');
    if (consultasTab) {
        consultasTab.addEventListener('shown.bs.tab', () => loadCodesData('nbs'));
    }

    // Radio change listener
    document.querySelectorAll('input[name="searchType"]').forEach(radio => {
        radio.addEventListener('change', (e) => {
            loadCodesData(e.target.value);
        });
    });

    // Search Input Listener
    document.getElementById('codeSearchInput').addEventListener('input', filterCodes);
});

    // Helper to robustly parse JSON even with PHP Warnings
    function parseJsonResponse(text) {
        try {
            return JSON.parse(text);
        } catch (e) {
            // Try to extract JSON from garbage (PHP warnings prefix)
            const jsonStart = text.indexOf('{');
            const jsonEnd = text.lastIndexOf('}');
            if (jsonStart !== -1 && jsonEnd !== -1) {
                const jsonStr = text.substring(jsonStart, jsonEnd + 1);
                try {
                    return JSON.parse(jsonStr);
                } catch (e2) {
                    console.error('Failed to parse (extracted):', jsonStr);
                    // Alert user for debugging
                    alert('Erro Crítico de Parse JSON:\n' + jsonStr.substring(0, 200) + '...');
                    throw e2;
                }
            }
            alert('Falha ao identificar JSON válido na resposta. Resposta bruta:\n' + text.substring(0, 200) + '...');
            throw e;
        }
    }

function loadCodesData(type) {
    codesType = type;
    const tbody = document.getElementById('codeResultsBody');
    const container = document.getElementById('codeResults');
    
    // Show loading state in table or verify if already loaded (optional cache)
    tbody.innerHTML = '<tr><td colspan="3" class="text-center py-5"><div class="spinner-border text-primary"></div><p class="mt-2 text-muted">Carregando lista completa...</p></td></tr>';
    container.classList.remove('d-none');

    const formData = new FormData();
    formData.append('action', 'fetch_codes');
    formData.append('type', type);

    fetch('nfse-nacional.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.text()) // Get raw text first
    .then(text => {
        const data = parseJsonResponse(text);
        if (data.success) {
            codesData = data.results;
            filterCodes(); // Initial render
        } else {
            tbody.innerHTML = `<tr><td colspan="3" class="text-center text-danger py-3">Erro: ${data.message}</td></tr>`;
        }
    })
    .catch(err => {
        console.error(err);
        tbody.innerHTML = `<tr><td colspan="3" class="text-center text-danger py-3">
            <strong>Erro na requisição:</strong> ${err.message}<br>
            <small class="text-muted">Verifique o console para mais detalhes.</small>
        </td></tr>`;
    });
}
// ... (skip lines)
function saveNbsChanges() {
    if (!confirm('Deseja salvar todas as alterações em ' + (codesType==='nbs'?'NBS':'Lista de Serviços') + '? Isso irá sobrescrever o arquivo original.')) return;

    const btn = document.querySelector('button[onclick="saveNbsChanges()"]');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Salvando...';

    const formData = new FormData();
    // Choose action based on type
    const action = (codesType === 'nbs') ? 'save_nbs_data' : 'save_service_data';
    formData.append('action', action);
    formData.append('data', JSON.stringify(codesData));

    fetch('nfse-nacional.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.text())
    .then(text => {
        const data = parseJsonResponse(text);
        if (data.success) {
            alert('Alterações salvas com sucesso!');
        } else {
            alert('Erro ao salvar: ' + (data.message || 'Erro desconhecido'));
        }
    })
    .catch(err => {
        console.error(err);
        alert('Erro na requisição. Verifique o console.');
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
}

let isEditMode = false;
let sortableInstance = null;
let editModalInstance = null;

function toggleEditMode() {
    const switchEl = document.getElementById('editModeSwitch');
    isEditMode = switchEl.checked;
    const toolbar = document.getElementById('editorToolbar');
    
    if (isEditMode) {
        toolbar.classList.remove('d-none');
        // Enable editing for both types now
        document.getElementById('searchNbs').disabled = false;
        document.getElementById('searchService').disabled = false;
        
        // Reload to show drag handles
        filterCodes();
    } else {
        toolbar.classList.add('d-none');
        document.getElementById('searchNbs').disabled = false;
        document.getElementById('searchService').disabled = false;
        
        // Destroy Sortable if exists
        if (sortableInstance) {
            sortableInstance.destroy();
            sortableInstance = null;
        }
        filterCodes();
    }
}

function initSortable() {
    const el = document.getElementById('codeResultsBody');
    if (sortableInstance) sortableInstance.destroy();

    sortableInstance = new Sortable(el, {
        animation: 150,
        handle: '.drag-handle',
        onEnd: function (evt) {
            // Reorder codesData based on visual change
            // Problem: codesData is big, and table might be filtered.
            // Drag & Drop only makes sense if we are viewing the FULL list or a contiguous block?
            // User requirement: "criar a hierarquia". Usually implies full control.
            // If filtered, moving an item might be ambiguous in the main list.
            // For safety, let's only strictly allow simple visual swap if filtered list == visible list?
            // OR: We map the visual index back to `originalIndex`.
            
            // However, SortableJS mutates the DOM. We need to sync the model.
            // Get the new order of indices from the rows
            const rows = Array.from(el.querySelectorAll('tr'));
            const newOrderIndices = rows.map(tr => parseInt(tr.dataset.index));
            
            // We need to reconstruct codesData.
            // WARN: If we are filtering, we only see a subset. Moving item A to pos B in subset
            // should probably move it relative to those items in the main list?
            // This is complex. Simplest MVP: Reorder is only robust if "Search" is empty or we warn user.
            
            // Implementation: We'll rebuild the FULL 'codesData' array.
            // 1. Extract the moved items from codesData based on newOrderIndices
            // 2. But we might have hidden items not in 'newOrderIndices'.
            // Strategy: Reorder "in place" relative to the visible subset?
            // Let's assume user clears search to reorder.
            
            // To make it robust:
            // Find the item defined by oldIndex in the filtered subset.
            // Move it to newIndex in the filtered subset.
            // Reflect this move in the GLOBAL codesData array?
            // If the subset is contiguous in global, easy. If scattered, hard.
            
            // Let's rely on mapping.
            // For now, simpler approach:
            // Just update the visual order in DOM (Sortable does this).
            // Then manually re-read the order from DOM to update `codesData`.
            // Limitation: Only works well if showing ALL or careful.
            
            // Let's update `codesData` based on the DOM order of the *visible* items, 
            // assuming they are a block? No, risky.
            
            // Better: We track the item moved.
            // Item `evt.item` (check dataset.index) moved from `evt.oldIndex` to `evt.newIndex`.
            // We need to move it in `codesData`.
            // Since `dataset.index` holds the REAL index in `codesData` (before move),
            // we can just splice it out and insert it at the position of the neighbor.
            
            const itemIndex = parseInt(evt.item.dataset.index); // Index in codesData
            const targetRow = rows[evt.newIndex]; // The row currently at the new position
            // But wait, the DOM is already updated by Sortable.
            
            // Let's use the list of indices derived from current DOM state
            // If we are filtering, this list is partial.
            // If we save partial list as the new list, we LOSE data. DANGER.
            
            // Safe bet: Alert user if filter is active.
            const searchInput = document.getElementById('codeSearchInput');
            if (searchInput.value.trim() !== '') {
                alert('A reordenação só é salva com segurança sem filtros de pesquisa ativos. Limpe a pesquisa para reordenar.');
                // Revert DOM?
                return;
            }
            
            // If no filter, the DOM rows represent the Full Data (paginated? we limit to 100).
            // If we limit to 100, we can't see/sort all.
            // This UI pattern (Sortable Table) usually requires "Show All" or "Paginated Sort".
            // Given "NBS" has ~500 items, verify render limit.
            // In `filterCodes`, I set limit = 100.
            // I should increase this limit or implement "Load All" for Edit Mode to allow sorting.
            
            // Let's update `filterCodes` to show ALL items when in Edit Mode (or a larger limit).
             rebuildDataFromDOM();
        }
    });
}

function rebuildDataFromDOM() {
    const el = document.getElementById('codeResultsBody');
    const rows = Array.from(el.querySelectorAll('tr'));
    
    // Create new array based on DOM order
    // But we need to keep the DATA of each item.
    // We can fetch it from codesData using the dataset.index
    // WAIT: If we limit render to 100, rows only has 100 items. 
    // If we save this, we lose the rest (item 101+).
    // FIX: logic below in filterCodes to showing ALL in edit mode.
    
    const newData = [];
    rows.forEach(tr => {
        const originalIndex = parseInt(tr.dataset.index);
        if (!isNaN(originalIndex) && codesData[originalIndex]) {
            newData.push(codesData[originalIndex]);
        }
    });

    if (rows.length === codesData.length) {
         codesData = newData; // Update global state
    } else {
        console.warn('DOM row count mismatches data count. Not updating order to prevent data loss.');
    }
}

function openEditModal(index = null, prefill = null) {
    // Reset form
    document.getElementById('editItemForm').reset();
    document.getElementById('editItemIndex').value = (index !== null) ? index : 'new';
    
    const title = document.getElementById('editItemModalTitle');
    const fieldsNbs = document.getElementById('fieldsNbs');
    const fieldsService = document.getElementById('fieldsService');
    
    // Toggle Fields based on type
    if (codesType === 'nbs') {
        fieldsNbs.classList.remove('d-none');
        fieldsService.classList.add('d-none');
    } else {
        fieldsNbs.classList.add('d-none');
        fieldsService.classList.remove('d-none');
    }

    if (index !== null) {
        title.innerText = 'Editar Item';
        const item = codesData[index];
        document.getElementById('editDesc').value = item.desc;
        
        if (codesType === 'nbs') {
            document.getElementById('editNbsCode').value = item.code;
        } else {
            document.getElementById('editServiceCtn').value = item.ctn || '';
            document.getElementById('editServiceItem').value = item.item || '';
            document.getElementById('editServiceSub').value = item.sub || '';
            document.getElementById('editServiceDesd').value = item.desd || '';
        }
    } else {
        title.innerText = 'Adicionar Novo Item';
    }

    // Prefill logic (for Sublevel)
    if (prefill) {
        title.innerText = 'Adicionar Subnível';
        if (codesType === 'nbs') {
             if (prefill.code) document.getElementById('editNbsCode').value = prefill.code;
        } else {
             if (prefill.ctn) document.getElementById('editServiceCtn').value = prefill.ctn;
             if (prefill.item) document.getElementById('editServiceItem').value = prefill.item;
             if (prefill.sub) document.getElementById('editServiceSub').value = prefill.sub;
             if (prefill.desd) document.getElementById('editServiceDesd').value = prefill.desd;
        }
        // Focus description or next empty field?
        setTimeout(() => document.getElementById('editDesc').focus(), 100);
    }

    if (!editModalInstance) {
        editModalInstance = new bootstrap.Modal(document.getElementById('editItemModal'));
    }
    editModalInstance.show();
}

function saveItemFromModal() {
    const indexStr = document.getElementById('editItemIndex').value;
    const desc = document.getElementById('editDesc').value.trim();
    
    let newItem = { desc: desc };

    if (codesType === 'nbs') {
        newItem.code = document.getElementById('editNbsCode').value.trim();
        if (!newItem.code) { alert('Código é obrigatório'); return; }
    } else {
        newItem.ctn = document.getElementById('editServiceCtn').value.trim();
        newItem.item = document.getElementById('editServiceItem').value.trim();
        newItem.sub = document.getElementById('editServiceSub').value.trim();
        newItem.desd = document.getElementById('editServiceDesd').value.trim();
        // Item is strictly required? Maybe not, but usually yes.
    }

    if (indexStr === 'new') {
        codesData.unshift(newItem); // Add to top
    } else {
        const index = parseInt(indexStr);
        codesData[index] = newItem;
    }

    editModalInstance.hide();
    filterCodes();
}

function addNewItem() {
    openEditModal(null);
}

function editItem(index) {
    openEditModal(index);
}

function addSubLevel(index) {
    const parent = codesData[index];
    let prefill = {};

    if (codesType === 'nbs') {
        // NBS Logic: Add dot
        // If parent is 1.01, child is 1.01.
        prefill.code = parent.code + '.';
    } else {
        // Service List Logic
        // Determine next available slot
        prefill.ctn = parent.ctn || '';
        prefill.item = parent.item || '';
        prefill.sub = parent.sub || '';
        prefill.desd = parent.desd || '';

        // Heuristic to clear "lower" levels for new entry? 
        // No, usually we want to fill the "next" level.
        // If parent has CTN only '1.01', we keep CTN '1.01' and user types Item.
        // If parent has Item '1.01 01', we keep both and user types Sub.
        
        // Actually Uniplus logic likely copies everything and focuses the next field.
        // We will just Copy the parents structure as base.
    }
    
    openEditModal(null, prefill);
}

function removeItem(index) {
    if (confirm('Tem certeza que deseja excluir este item?')) {
        codesData.splice(index, 1);
        filterCodes();
    }
}

function saveNbsChanges() {
    if (!confirm('Deseja salvar todas as alterações em ' + (codesType==='nbs'?'NBS':'Lista de Serviços') + '? Isso irá sobrescrever o arquivo original.')) return;

    const btn = document.querySelector('button[onclick="saveNbsChanges()"]');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Salvando...';

    const formData = new FormData();
    // Choose action based on type
    const action = (codesType === 'nbs') ? 'save_nbs_data' : 'save_service_data';
    formData.append('action', action);
    formData.append('data', JSON.stringify(codesData));

    fetch('nfse-nacional.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert('Alterações salvas com sucesso!');
        } else {
            alert('Erro ao salvar: ' + (data.message || 'Erro desconhecido'));
        }
    })
    .catch(err => {
        console.error(err);
        alert('Erro na requisição. Verifique o console.');
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
}

function filterCodes() {
    const rawTerm = document.getElementById('codeSearchInput').value.toLowerCase();
    const cleanTerm = rawTerm.replace(/[^a-z0-9]/g, ''); 
    
    // Header Setup
    const thead = document.querySelector('#codeResults table thead tr');
    let actionHeader = 'Ação';
    if (isEditMode) {
        actionHeader = 'Editor';
    }

    if (codesType === 'nbs') {
        thead.innerHTML = `
            ${isEditMode ? '<th style="width: 30px;"></th>' : ''}
            <th style="width: 150px;">Código</th>
            <th>Descrição</th>
            <th style="width: ${isEditMode ? '160px' : '100px'};">${actionHeader}</th>
        `;
    } else {
        // Service
        if (isEditMode) {
             thead.innerHTML = `
                <th style="width: 30px;"></th>
                <th style="width: 120px;">CTN</th>
                <th style="width: 60px;">Item</th>
                <th style="width: 60px;">Sub</th>
                <th style="width: 60px;">Desd</th>
                <th>Descrição</th>
                <th style="width: 160px;">Editor</th>
            `;
        } else {
            thead.innerHTML = `
                <th style="width: 150px;">Cód. Trib. Nac.</th>
                <th style="width: 80px;">Item</th>
                <th style="width: 80px;">Sub</th>
                <th style="width: 80px;">Desd</th>
                <th>Descrição</th>
            `;
        }
    }

    const tbody = document.getElementById('codeResultsBody');
    tbody.innerHTML = '';
    
    // Map data to preserve index
    const mappedData = codesData.map((item, index) => ({ ...item, originalIndex: index }));

    // FILTER logic
    let filtered = mappedData.filter(item => {
        const desc = (item.desc || '').toLowerCase();
        if (codesType === 'nbs') {
            const cleanCode = (item.code || '').replace(/[^a-z0-9]/g, '');
            return cleanCode.includes(cleanTerm) || desc.includes(rawTerm);
        } else {
             const fullStr = (item.ctn || '') + (item.item || '') + (item.sub || '') + (item.desd || '');
             const cleanCode = fullStr.replace(/[^a-z0-9]/g, '');
             return cleanCode.includes(cleanTerm) || desc.includes(rawTerm);
        }
    });
    
    // EDIT MODE: If search is empty, SHOW ALL (Limit extremely high)
    // If search is NOT empty, we can show filtered list but D&D is disabled/unsafe.
    let limit = 100;
    if (isEditMode && cleanTerm === '') {
        limit = 5000; // Show all for full reordering support
    }

    const subset = filtered.slice(0, limit);

    if (filtered.length === 0) {
        tbody.innerHTML = `<tr><td colspan="${(codesType === 'nbs' ? 3 : 5) + (isEditMode?1:0)}" class="text-center text-muted py-3">Nenhum resultado encontrado.</td></tr>`;
        return;
    }

    subset.forEach(item => {
        const tr = document.createElement('tr');
        tr.dataset.index = item.originalIndex; // Crucial for mapping back
        
        // Draggable class
        if (isEditMode) tr.classList.add('draggable-item');

        const dragHandle = isEditMode ? '<td class="drag-handle text-center text-muted" style="cursor: grab;"><i class="bi bi-grip-vertical"></i></td>' : '';
        
        let actions = '';
        if (isEditMode) {
            // Edit Buttons
            actions = `
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-success" onclick="addSubLevel(${item.originalIndex})" title="Incluir Subnível"><i class="bi bi-node-plus"></i></button>
                    <button class="btn btn-outline-primary" onclick="editItem(${item.originalIndex})" title="Editar"><i class="bi bi-pencil"></i></button>
                    <button class="btn btn-outline-danger" onclick="removeItem(${item.originalIndex})" title="Excluir"><i class="bi bi-trash"></i></button>
                </div>
            `;
        } else {
            // Copy Button (Only for NBS usually, maybe for LC too?)
            actions = `
                 <button class="btn btn-sm btn-outline-secondary" onclick="navigator.clipboard.writeText('${item.code || item.ctn}')" title="Copiar">
                    <i class="bi bi-clipboard"></i>
                </button>
            `;
        }

        // Calculate Hierarchy Level for Indentation
        let level = 0;
        let isParent = false;
        
        if (codesType === 'nbs') {
            // Estimate level by dot count
            // 1.01 -> 1 dot (Level 0 base?)
            // 1.01.01 -> 2 dots (Level 1)
            // But some are loose.
            const dots = (item.code.match(/\./g) || []).length;
            level = dots > 0 ? dots - 1 : 0;
            if (level < 0) level = 0;
        } else {
            // Service List Logic
            // CTN only: Level 0
            // Item: Level 1
            // Sub: Level 2
            // Desd: Level 3
            if (item.desd && item.desd !== '00') level = 3;
            else if (item.sub && item.sub !== '00') level = 2;
            else if (item.item && item.item !== '00') level = 1;
            else level = 0;
        }

        const indentPx = level * 20; // 20px per level
        const folderIcon = level < 2 ? '<i class="bi bi-folder2-open me-1 text-warning"></i>' : '<i class="bi bi-file-text me-1 text-secondary"></i>';
        
        // Apply Indentation
        // Use a wrapper div/span to apply padding comfortably
        const descHtml = `<div style="padding-left: ${indentPx}px;">${folderIcon} ${item.desc}</div>`;

        if (codesType === 'nbs') {
            tr.innerHTML = `
                ${dragHandle}
                <td class="font-monospace fw-bold text-primary">${item.code}</td>
                <td>${descHtml}</td>
                ${isEditMode ? `<td>${actions}</td>` : `<td>${actions}</td>`}
            `;
        } else {
            // Service List
             if (!item.ctn && !isEditMode) {
                tr.classList.add('table-warning', 'fw-bold');
            }

            tr.innerHTML = `
                ${dragHandle}
                <td class="font-monospace fw-bold text-primary">${item.ctn || ''}</td>
                <td class="font-monospace">${item.item || ''}</td>
                <td class="font-monospace">${item.sub || ''}</td>
                <td class="font-monospace">${item.desd || ''}</td>
                <td>${descHtml}</td>
                ${isEditMode ? `<td>${actions}</td>` : ''}
            `;
        }
        tbody.appendChild(tr);
    });

    if (filtered.length > limit) {
        const tr = document.createElement('tr');
        const colSpan = (codesType === 'nbs' ? 3 : 5) + (isEditMode?1:0);
        tr.innerHTML = `<td colspan="${colSpan}" class="text-center text-muted small bg-light">Exibindo ${limit} de ${filtered.length} resultados. Para editar a ordem, limpe a pesquisa.</td>`;
        tbody.appendChild(tr);
    }
    
    // Init Sortable if in Edit Mode
    if (isEditMode && cleanTerm === '') {
        initSortable();
    }
}
</script>

<?php include 'includes/footer.php'; ?>
