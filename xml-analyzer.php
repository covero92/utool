<?php
include 'includes/header.php';

// Load NFe Rules
$nfeRules = [];
$nfeRulesFile = __DIR__ . '/data/nfe_rules.json';
if (file_exists($nfeRulesFile)) {
    $nfeRules = json_decode(file_get_contents($nfeRulesFile), true);
}


function renderXmlNode($node) {
    $children = $node->children();
    $attributes = $node->attributes();
    $hasChildren = count($children) > 0;
    $nodeName = $node->getName();
    
    echo '<div class="xml-node ps-3 border-start border-muted-foreground/20">';
    
    // Opening Tag
    echo '<div class="d-flex align-items-center py-1">';
    if ($hasChildren) {
        echo '<span class="xml-toggle text-muted-foreground me-1 cursor-pointer" onclick="toggleNode(this)"><i class="bi bi-caret-down-fill text-xs"></i></span>';
    } else {
        echo '<span class="xml-toggle me-1" style="width: 12px;"></span>';
    }
    
    echo '<span class="text-primary font-monospace">&lt;' . htmlspecialchars($nodeName) . '</span>';
    
    foreach ($attributes as $name => $value) {
        echo ' <span class="text-info font-monospace">' . htmlspecialchars($name) . '</span>=<span class="text-success font-monospace">"' . htmlspecialchars($value) . '"</span>';
    }
    
    if ($hasChildren) {
        echo '<span class="text-primary font-monospace">&gt;</span>';
    } else {
        $content = trim((string)$node);
        if ($content !== '') {
            echo '<span class="text-primary font-monospace">&gt;</span>';
            echo '<span class="text-foreground font-monospace mx-1">' . htmlspecialchars($content) . '</span>';
            echo '<span class="text-primary font-monospace">&lt;/' . htmlspecialchars($nodeName) . '&gt;</span>';
        } else {
            echo '<span class="text-primary font-monospace"> /&gt;</span>';
        }
    }
    echo '</div>';
    
    // Children
    if ($hasChildren) {
        echo '<div class="xml-children">';
        foreach ($children as $child) {
            renderXmlNode($child);
        }
        echo '<div class="ps-4 py-1"><span class="text-primary font-monospace">&lt;/' . htmlspecialchars($nodeName) . '&gt;</span></div>';
        echo '</div>';
    }
    
    echo '</div>';
    echo '</div>';
}

$xmlError = '';
$brError = []; // Business Rule Errors
$xsdError = '';
$validationSuccess = false;
$xmlContent = '';
$xsdContent = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle XML Input
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $xmlContent = file_get_contents($_FILES['file']['tmp_name']);
    } elseif (isset($_POST['content'])) {
        $xmlContent = $_POST['content'];
    }

    // Handle XSD Input
    if (isset($_FILES['xsd_file']) && $_FILES['xsd_file']['error'] === UPLOAD_ERR_OK) {
        $xsdContent = file_get_contents($_FILES['xsd_file']['tmp_name']);
    } elseif (isset($_POST['xsd_content']) && trim($_POST['xsd_content']) !== '') {
        $xsdContent = trim($_POST['xsd_content']);
    } elseif (isset($_POST['schema_lib']) && !empty($_POST['schema_lib'])) {
        // Load from Library
        $libPath = '';
        if ($_POST['schema_lib'] === 'nfe') {
            $libPath = __DIR__ . '/NF-e/';
        } elseif ($_POST['schema_lib'] === 'nfse') {
            $libPath = __DIR__ . '/NFSE Nacional/Schemas/';
        }

        if ($libPath && $xmlContent) {
            // Auto-detect schema based on root element
            $xml = simplexml_load_string($xmlContent);
            if ($xml) {
                $root = $xml->getName();
                $schemaFile = '';

                // NF-e / NFC-e Detection
                if ($_POST['schema_lib'] === 'nfe') {
                    if ($root === 'NFe' || $root === 'nfeProc') $schemaFile = 'nfe_v4.00.xsd';
                    // Add more mappings if needed
                }
                // NFS-e Detection
                elseif ($_POST['schema_lib'] === 'nfse') {
                    if ($root === 'DPS') $schemaFile = 'DPS_v1.00.xsd';
                    elseif ($root === 'NFSe') $schemaFile = 'NFSe_v1.00.xsd';
                }

                if ($schemaFile && file_exists($libPath . $schemaFile)) {
                    $xsdPath = $libPath . $schemaFile;
                    $xsdContent = file_get_contents($xsdPath); // Keep content for display if needed
                }
            }
        }
    }

    if ($xmlContent) {
        libxml_use_internal_errors(true);
        
        // Parse XML for display
        $xml = simplexml_load_string($xmlContent);
        if ($xml === false) {
            $errors = libxml_get_errors();
            foreach ($errors as $error) {
                $xmlError .= "Erro XML na linha {$error->line}: {$error->message}<br>";
            }
            libxml_clear_errors();
        } else {
            // Validate against XSD if provided
            if ($xsdContent || isset($xsdPath)) {
                $dom = new DOMDocument();
                $dom->loadXML($xmlContent);
                
                $isValid = false;

                // If we have a file path (from library), use schemaValidate
                if (isset($xsdPath) && file_exists($xsdPath)) {
                    // Temporarily change directory to handle relative imports in XSD
                    $currentDir = getcwd();
                    $schemaDir = dirname($xsdPath);
                    chdir($schemaDir);
                    
                    // Enable detailed error handling
                    libxml_use_internal_errors(true);
                    libxml_clear_errors();

                    if (!@$dom->schemaValidate($xsdPath)) {
                        $errors = libxml_get_errors();
                    } else {
                        $isValid = true;
                    }
                    
                    // Debug: Check if there were any warnings even if valid
                    // $debugErrors = libxml_get_errors();
                    // if (!empty($debugErrors)) { var_dump($debugErrors); }

                    chdir($currentDir);
                } 
                // Otherwise use schemaValidateSource (for pasted content or uploaded file)
                elseif ($xsdContent) {
                    // Enable detailed error handling
                    libxml_use_internal_errors(true);
                    libxml_clear_errors();
                    
                    // Use a custom error handler to catcharnings that DOMDocument::schemaValidateSource emits
                    $internalErrors = [];
                    set_error_handler(function($errno, $errstr, $errfile, $errline) use (&$internalErrors) {
                         $internalErrors[] = $errstr;
                    });
                    
                    try {
                        $validateResult = $dom->schemaValidateSource($xsdContent);
                    } catch (Exception $e) {
                         $internalErrors[] = $e->getMessage();
                         $validateResult = false;
                    }
                    
                    restore_error_handler();

                    if (!$validateResult) {
                        $errors = libxml_get_errors();
                        // If libxml didn't catch it but we have internal PHP warnings (like "Invalid Schema")
                        if (empty($errors) && !empty($internalErrors)) {
                             foreach ($internalErrors as $intErr) {
                                 // Create a mock error object to fit existing structure
                                 $mockErr = new stdClass();
                                 $mockErr->message = "Erro Crítico no Schema XSD: " . $intErr;
                                 $mockErr->line = 0;
                                 $errors[] = $mockErr;
                             }
                        }
                    } else {
                        $isValid = true;
                    }
                }
                
                if (!$isValid) {
                     // Parse existing XSD errors (Keep existing logic for XSD display)
                     if (!isset($errors)) $errors = libxml_get_errors(); 
                     // ... (XSD Error Parsing Logic remains same) ...
                     $parsedErrors = [];
                     foreach ($errors as $error) {
                        $parsedErrors[] = $error->message . (isset($error->line) ? " (Linha {$error->line})" : "");
                     }
                     libxml_clear_errors();
                     
                     foreach ($parsedErrors as $error) {
                         // ... (XSD Error Formatting remains) ...
                         $friendlyMsg = $error;
                         // ...
                         $xsdError .= '<div class="card mb-2 border-danger border-opacity-25 shadow-sm">';
                         $xsdError .= '<div class="card-body py-2 px-3">';
                                $xsdError .= '<strong class="text-danger small font-monospace"><i class="bi bi-x-circle me-1"></i>Erro XSD</strong>';
                                $xsdError .= '<p class="mb-0 text-muted xsmall font-monospace text-break opacity-75">' . htmlspecialchars($error) . '</p>';
                         $xsdError .= '</div></div>';
                     }
                }

                // 2. Business Rules Validation (Using new Engine)
                // Always run if XML is parseable, even if XSD fails (Requirement 8 partially - user wants unified report)
                // But typically logic rules need valid structure. Given requirement "Report final unified", we try.
                
                require_once 'includes/NFeValidator.php';
                
                try {
                    $validator = new NFeValidator($xmlContent);
                    $brErrors = $validator->validate();
                    
                    if (!empty($brErrors)) {
                        $validationSuccess = false;
                    } elseif ($isValid) {
                        $validationSuccess = true;
                    }
                } catch (Exception $e) {
                    // Validator loading failed (e.g. malformed XML that simplexml loaded but DOM failed?)
                    // Or normalization error
                    $brErrors[] = ['code' => 'SYS', 'severity' => 'error', 'msg' => 'Falha no Motor de Regras', 'detail' => $e->getMessage()];
                    $validationSuccess = false;
                }

                
                // Restore dir
                if (isset($currentDir)) chdir($currentDir);
            }
        }
    }
}
?>

<?php
// Load Layout Data
$nfeLayout = [];
$nfeLayoutFile = __DIR__ . '/data/nfe_layout.json';
if (file_exists($nfeLayoutFile)) {
    $nfeLayout = json_decode(file_get_contents($nfeLayoutFile), true);
}
?>

</div> <!-- Close header container -->
<div class="container-fluid px-3 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 fw-bold text-foreground">Ferramentas NFC-e / NF-e</h1>
            <p class="text-muted-foreground">Regras de validação, Layout e Webservices para Documentos Fiscais.</p>
        </div>
        <a href="index.php" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
    </div>

    <!-- Top Level Tabs -->
    <ul class="nav nav-tabs nav-tabs-custom mb-4" role="tablist">
        <li class="nav-item">
            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-main-rules">
                <i class="bi bi-list-check me-2"></i>Regras de Validação
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-main-layouts">
                <i class="bi bi-layout-text-window-reverse me-2"></i>Layouts
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-main-webservices">
                <i class="bi bi-hdd-network me-2"></i>Webservices
            </button>
        </li>
    </ul>

    <div class="tab-content">
        <!-- Tab 1: Validador XML (Existing Content) -->
        <!-- Tab 2: Regras de Validação -->
        <div class="tab-pane fade show active" id="tab-main-rules">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                                <input type="text" id="ruleSearch" class="form-control border-start-0 ps-0" placeholder="Pesquisar por código, mensagem ou descrição...">
                            </div>
                        </div>
                        <div class="col-md-6 text-end">
                            <span class="text-muted small">Total de Regras: <span id="rulesCount">0</span></span>
                        </div>
                    </div>

                    <div class="table-responsive custom-scrollbar" style="max-height: 600px;">
                        <table class="table table-hover align-middle border">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th style="width: 80px;">Código</th>
                                    <th>Mensagem / Descrição</th>
                                    <th>Aplicabilidade</th>
                                    <th>Efeito</th>
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
            </div>
        </div>

        <!-- Tab 3: Layouts -->
        <div class="tab-pane fade" id="tab-main-layouts">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                                <input type="text" id="layoutSearch" class="form-control border-start-0 ps-0" placeholder="Pesquisar por campo, descrição ou ID...">
                            </div>
                        </div>
                        <div class="col-md-6 text-end">
                            <div class="mb-2">
                                <button class="btn btn-sm btn-outline-secondary me-1" onclick="toggleAllGroups(true)">
                                    <i class="bi bi-arrows-expand me-1"></i>Expandir Todos
                                </button>
                                <button class="btn btn-sm btn-outline-secondary" onclick="toggleAllGroups(false)">
                                    <i class="bi bi-arrows-collapse me-1"></i>Recolher Todos
                                </button>
                            </div>
                            <span class="text-muted small">Total de Campos: <span id="layoutCount">0</span></span>
                        </div>
                    </div>

                    <div class="table-responsive custom-scrollbar" style="height: calc(100vh - 280px); max-height: none;">
                        <table class="table table-hover align-middle border table-sm">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th style="width: 50px;">#</th>
                                    <th>ID</th>
                                    <th>Campo</th>
                                    <th>Descrição</th>
                                    <th class="text-center">Ele</th>
                                    <th class="text-center">Pai</th>
                                    <th class="text-center">Tipo</th>
                                    <th class="text-center">Tam.</th>
                                    <th class="text-center">Ocor.</th>
                                    <th>Observação</th>
                                    <th class="text-center">Ações</th>
                                </tr>
                            </thead>
                            <tbody id="layoutTableBody">
                                <!-- Populated by JS -->
                            </tbody>
                        </table>
                    </div>
                    <div id="noLayoutFound" class="text-center py-5 d-none">
                        <p class="text-muted">Nenhum campo encontrado para sua pesquisa.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab 4: Webservices -->
        <div class="tab-pane fade" id="tab-main-webservices">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4">Webservices Estaduais e Nacionais</h5>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="list-group custom-scrollbar" id="ws-sidebar" role="tablist" style="max-height: 600px; overflow-y: auto;">
                                <!-- Sidebar items will be injected here -->
                            </div>
                        </div>
                        <div class="col-md-9">
                            <div class="tab-content" id="ws-content">
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle me-2"></i>Selecione uma UF ou Ambiente para visualizar os webservices.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .xml-tree {
        font-size: 0.9rem;
        background-color: #f8f9fa;
    }
    .xml-node {
        white-space: nowrap; /* Prevent wrapping of tags */
    }
    /* Custom Scrollbar for better aesthetics */
    .custom-scrollbar::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: #f1f1f1; 
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #c1c1c1; 
        border-radius: 4px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8; 
    }
</style>

<script>
    function toggleNode(element) {
        const childrenContainer = element.parentElement.nextElementSibling;
        const icon = element.querySelector('i');
        
        if (childrenContainer.style.display === 'none') {
            childrenContainer.style.display = 'block';
            icon.classList.remove('bi-plus-square');
            icon.classList.add('bi-dash-square');
        } else {
            childrenContainer.style.display = 'none';
            icon.classList.remove('bi-dash-square');
            icon.classList.add('bi-plus-square');
        }
    }

    function expandAll() {
        document.querySelectorAll('.xml-children').forEach(el => el.style.display = 'block');
        document.querySelectorAll('.xml-toggle i').forEach(el => {
            el.classList.remove('bi-plus-square');
            el.classList.add('bi-dash-square');
        });
    }

    // Rules Logic
    const nfeRules = <?php echo json_encode($nfeRules); ?>;
    const nfeLayout = <?php echo json_encode($nfeLayout); ?>;

    const groupTitles = {
        'A': 'Grupo A. Dados da Nota Fiscal eletrônica',
        'B': 'Grupo B. Identificação da Nota Fiscal eletrônica',
        'C': 'Grupo C. Identificação do Emitente da Nota Fiscal eletrônica',
        'D': 'Grupo D. Identificação do Fisco Emitente da NF-e',
        'E': 'Grupo E. Identificação do Destinatário da Nota Fiscal eletrônica',
        'F': 'Grupo F. Identificação do Local de Retirada',
        'G': 'Grupo G. Identificação do Local de Entrega',
        'H': 'Grupo H. Detalhamento de Produtos e Serviços da NF-e',
        'I': 'Grupo I. Produtos e Serviços da NF-e',
        'J': 'Grupo J. Detalhamento Específico de Veículos novos',
        'K': 'Grupo K. Detalhamento Específico de Medicamentos e de matérias-primas farmacêuticas',
        'L': 'Grupo L. Detalhamento Específico de Armamentos',
        'M': 'Grupo M. Tributos incidentes no Produto ou Serviço',
        'N': 'Grupo N. ICMS Normal e ST',
        'O': 'Grupo O. Imposto sobre Produtos Industrializados',
        'P': 'Grupo P. Imposto de Importação',
        'Q': 'Grupo Q. PIS',
        'R': 'Grupo R. PIS ST',
        'S': 'Grupo S. COFINS',
        'T': 'Grupo T. COFINS ST',
        'U': 'Grupo U. ISSQN',
        'V': 'Grupo V. Informações adicionais',
        'W': 'Grupo W. Total da NF-e',
        'X': 'Grupo X. Transporte da NF-e',
        'Y': 'Grupo Y. Dados da Cobrança',
        'Z': 'Grupo Z. Informações Adicionais da NF-e',
    };

    function renderRules(rules) {
        const tbody = document.getElementById('rulesTableBody');
        const noFound = document.getElementById('noRulesFound');
        const countSpan = document.getElementById('rulesCount');
        
        tbody.innerHTML = '';
        countSpan.innerText = rules.length;
        
        if (rules.length === 0) {
            noFound.classList.remove('d-none');
            return;
        }
        
        noFound.classList.add('d-none');
        
        // Limit to first 200 for performance
        const displayRules = rules.slice(0, 200);
        
        displayRules.forEach(rule => {
            const tr = document.createElement('tr');
            
            tr.innerHTML = `
                <td class="text-center"><span class="badge bg-secondary font-monospace">${rule.code}</span></td>
                <td>
                    <div class="fw-bold small mb-1 text-primary">${rule.description || rule.efeito || rule.msg}</div>
                    <div class="text-muted xsmall">${rule.modelo || ''}</div>
                </td>
                <td><span class="badge bg-light text-dark border">${rule.aplic || '-'}</span></td>
                <td><span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25">${rule.msg || rule.efeito || '-'}</span></td>
            `;
            tbody.appendChild(tr);
        });
    }

    function renderLayout(items) {
        const tbody = document.getElementById('layoutTableBody');
        const noFound = document.getElementById('noLayoutFound');
        const countSpan = document.getElementById('layoutCount');
        
        tbody.innerHTML = '';
        countSpan.innerText = items.length;
        
        if (items.length === 0) {
            noFound.classList.remove('d-none');
            return;
        }
        
        noFound.classList.add('d-none');
        
        // Limit to first 500 for performance, but we need to process all for grouping if not searching
        const isFiltered = document.getElementById('layoutSearch').value.length > 0;
        const displayItems = isFiltered ? items.slice(0, 200) : items;
        
        let lastGroup = '';

        displayItems.forEach((item, index) => {
            // Determine Group
            let currentGroup = '';
            if (item.id && item.id.length > 0) {
                const firstChar = item.id.charAt(0).toUpperCase();
                if (firstChar.match(/[A-Z]/)) {
                    currentGroup = firstChar;
                } else {
                    currentGroup = 'Outros';
                }
            }

            // Insert Header if Group Changed
            if (!isFiltered && currentGroup !== lastGroup && groupTitles[currentGroup]) {
                const headerRow = document.createElement('tr');
                headerRow.className = 'table-light border-bottom border-top group-header';
                headerRow.style.cursor = 'pointer';
                headerRow.setAttribute('onclick', `toggleGroup('${currentGroup}')`);
                headerRow.innerHTML = `
                    <td colspan="11" class="fw-bold py-2 px-3 text-dark bg-light">
                        <i class="bi bi-chevron-down me-2 group-icon-${currentGroup}"></i>
                        ${groupTitles[currentGroup] || 'Grupo ' + currentGroup}
                    </td>
                `;
                tbody.appendChild(headerRow);
                lastGroup = currentGroup;
            }

            const tr = document.createElement('tr');
            if (!isFiltered) {
                tr.classList.add(`group-item-${currentGroup}`);
            }
            
            // Format Observation: Replace ; with <br> for better readability
            let obsFormatted = item.obs || '';
            if (obsFormatted) {
                obsFormatted = obsFormatted.replace(/;/g, ';<br>');
            }

            tr.innerHTML = `
                <td class="text-center text-muted small">${index + 1}</td>
                <td class="font-monospace small fw-bold">${item.id}</td>
                <td class="fw-bold text-primary font-monospace">${item.campo}</td>
                <td class="small text-muted">${item.descricao}</td>
                <td class="text-center small">${item.ele || ''}</td>
                <td class="text-center small text-muted">${item.pai || ''}</td>
                <td class="text-center small">${item.tipo}</td>
                <td class="text-center small">${item.tam}</td>
                <td class="text-center small">${item.ocor}</td>
                <td class="small text-muted" style="font-size: 0.85rem;">${obsFormatted}</td>
                <td class="text-center">
                    <button class="btn btn-xs btn-outline-info" onclick="findRulesForField('${item.campo}')" title="Ver Regras">
                        <i class="bi bi-search"></i>
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    function toggleGroup(groupName) {
        const items = document.querySelectorAll(`.group-item-${groupName}`);
        const icon = document.querySelector(`.group-icon-${groupName}`);
        let isHidden = false;

        items.forEach(item => {
            if (item.style.display === 'none') {
                item.style.display = '';
                isHidden = false;
            } else {
                item.style.display = 'none';
                isHidden = true;
            }
        });

        if (icon) {
            if (isHidden) {
                icon.classList.remove('bi-chevron-down');
                icon.classList.add('bi-chevron-right');
            } else {
                icon.classList.remove('bi-chevron-right');
                icon.classList.add('bi-chevron-down');
            }
        }
    }

    function toggleAllGroups(expand) {
        const allItems = document.querySelectorAll('[class*="group-item-"]');
        const allIcons = document.querySelectorAll('[class*="group-icon-"]');

        allItems.forEach(item => {
            item.style.display = expand ? '' : 'none';
        });

        allIcons.forEach(icon => {
            if (expand) {
                icon.classList.remove('bi-chevron-right');
                icon.classList.add('bi-chevron-down');
            } else {
                icon.classList.remove('bi-chevron-down');
                icon.classList.add('bi-chevron-right');
            }
        });
    }

    function findRulesForField(fieldName) {
        // Switch to Rules Tab
        const rulesTabBtn = document.querySelector('[data-bs-target="#tab-main-rules"]');
        const tab = new bootstrap.Tab(rulesTabBtn);
        tab.show();
        
        // Set Search
        const searchInput = document.getElementById('ruleSearch');
        searchInput.value = fieldName;
        
        // Trigger Search Event
        const event = new Event('input');
        searchInput.dispatchEvent(event);
    }

    document.getElementById('ruleSearch').addEventListener('input', function(e) {
        const term = e.target.value.toLowerCase();
        const filtered = nfeRules.filter(rule => {
            return (rule.code && rule.code.toLowerCase().includes(term)) ||
                   (rule.description && rule.description.toLowerCase().includes(term)) ||
                   (rule.efeito && rule.efeito.toLowerCase().includes(term)) ||
                   (rule.modelo && rule.modelo.toLowerCase().includes(term));
        });
        renderRules(filtered);
    });

    document.getElementById('layoutSearch').addEventListener('input', function(e) {
        const term = e.target.value.toLowerCase();
        const filtered = nfeLayout.filter(item => {
            return (item.id && item.id.toLowerCase().includes(term)) ||
                   (item.campo && item.campo.toLowerCase().includes(term)) ||
                   (item.descricao && item.descricao.toLowerCase().includes(term));
        });
        renderLayout(filtered);
    });

    // Webservices Logic
    const webservicesData = {
    "AM": [
        {
            "ambiente": "Produção",
            "servico": "NfeInutilizacao",
            "url": "https://nfe.sefaz.am.gov.br/services2/services/NfeInutilizacao4"
        },
        {
            "ambiente": "Produção",
            "servico": "NfeConsultaProtocolo",
            "url": "https://nfe.sefaz.am.gov.br/services2/services/NfeConsulta4"
        },
        {
            "ambiente": "Produção",
            "servico": "NfeStatusServico",
            "url": "https://nfe.sefaz.am.gov.br/services2/services/NfeStatusServico4"
        },
        {
            "ambiente": "Produção",
            "servico": "RecepcaoEvento",
            "url": "https://nfe.sefaz.am.gov.br/services2/services/RecepcaoEvento4"
        },
        {
            "ambiente": "Produção",
            "servico": "NFeAutorizacao",
            "url": "https://nfe.sefaz.am.gov.br/services2/services/NFeAutorizacao4"
        },
        {
            "ambiente": "Produção",
            "servico": "NFeRetAutorizacao",
            "url": "https://nfe.sefaz.am.gov.br/services2/services/NFeRetAutorizacao4"
        }
    ],
    "BA": [
        {
            "ambiente": "Produção",
            "servico": "NfeInutilizacao",
            "url": "https://nfe.sefaz.ba.gov.br/NFeInutilizacao4/NFeInutilizacao4.asmx"
        },
        {
            "ambiente": "Produção",
            "servico": "NfeConsultaProtocolo",
            "url": "https://nfe.sefaz.ba.gov.br/NFeConsultaProtocolo4/NFeConsultaProtocolo4.asmx"
        },
        {
            "ambiente": "Produção",
            "servico": "NfeStatusServico",
            "url": "https://nfe.sefaz.ba.gov.br/NFeStatusServico4/NFeStatusServico4.asmx"
        },
        {
            "ambiente": "Produção",
            "servico": "RecepcaoEvento",
            "url": "https://nfe.sefaz.ba.gov.br/NFeRecepcaoEvento4/NFeRecepcaoEvento4.asmx"
        },
        {
            "ambiente": "Produção",
            "servico": "NFeAutorizacao",
            "url": "https://nfe.sefaz.ba.gov.br/NFeAutorizacao4/NFeAutorizacao4.asmx"
        },
        {
            "ambiente": "Produção",
            "servico": "NFeRetAutorizacao",
            "url": "https://nfe.sefaz.ba.gov.br/NFeRetAutorizacao4/NFeRetAutorizacao4.asmx"
        },
        {
            "ambiente": "Produção",
            "servico": "NfeConsultaCadastro",
            "url": "https://nfe.sefaz.ba.gov.br/NFeConsultaCadastro4/NFeConsultaCadastro4.asmx"
        }
    ],
    "GO": [
        {
            "ambiente": "Produção",
            "servico": "NfeInutilizacao",
            "url": "https://nfe.sefaz.go.gov.br/nfe/services/NFeInutilizacao4?wsdl"
        },
        {
            "ambiente": "Produção",
            "servico": "NfeConsultaProtocolo",
            "url": "https://nfe.sefaz.go.gov.br/nfe/services/NFeConsultaProtocolo4?wsdl"
        },
        {
            "ambiente": "Produção",
            "servico": "NfeStatusServico",
            "url": "https://nfe.sefaz.go.gov.br/nfe/services/NFeStatusServico4?wsdl"
        },
        {
            "ambiente": "Produção",
            "servico": "RecepcaoEvento",
            "url": "https://nfe.sefaz.go.gov.br/nfe/services/NFeRecepcaoEvento4?wsdl"
        },
        {
            "ambiente": "Produção",
            "servico": "NFeAutorizacao",
            "url": "https://nfe.sefaz.go.gov.br/nfe/services/NFeAutorizacao4?wsdl"
        },
        {
            "ambiente": "Produção",
            "servico": "NFeRetAutorizacao",
            "url": "https://nfe.sefaz.go.gov.br/nfe/services/NFeRetAutorizacao4?wsdl"
        },
        {
            "ambiente": "Produção",
            "servico": "NfeConsultaCadastro",
            "url": "https://nfe.sefaz.go.gov.br/nfe/services/CadConsultaCadastro4?wsdl"
        }
    ],
    "MG": [
        {
            "ambiente": "Produção",
            "servico": "NfeInutilizacao",
            "url": "https://nfe.fazenda.mg.gov.br/nfe2/services/NFeInutilizacao4"
        },
        {
            "ambiente": "Produção",
            "servico": "NfeConsultaProtocolo",
            "url": "https://nfe.fazenda.mg.gov.br/nfe2/services/NFeConsultaProtocolo4"
        },
        {
            "ambiente": "Produção",
            "servico": "NfeStatusServico",
            "url": "https://nfe.fazenda.mg.gov.br/nfe2/services/NFeStatusServico4"
        },
        {
            "ambiente": "Produção",
            "servico": "RecepcaoEvento",
            "url": "https://nfe.fazenda.mg.gov.br/nfe2/services/NFeRecepcaoEvento4"
        },
        {
            "ambiente": "Produção",
            "servico": "NFeAutorizacao",
            "url": "https://nfe.fazenda.mg.gov.br/nfe2/services/NFeAutorizacao4"
        },
        {
            "ambiente": "Produção",
            "servico": "NFeRetAutorizacao",
            "url": "https://nfe.fazenda.mg.gov.br/nfe2/services/NFeRetAutorizacao4"
        },
        {
            "ambiente": "Produção",
            "servico": "NfeConsultaCadastro",
            "url": "https://nfe.fazenda.mg.gov.br/nfe2/services/CadConsultaCadastro4"
        }
    ],
    "MS": [
        {
            "ambiente": "Produção",
            "servico": "NfeInutilizacao",
            "url": "https://nfe.sefaz.ms.gov.br/ws/NFeInutilizacao4"
        },
        {
            "ambiente": "Produção",
            "servico": "NfeConsultaProtocolo",
            "url": "https://nfe.sefaz.ms.gov.br/ws/NFeConsultaProtocolo4"
        },
        {
            "ambiente": "Produção",
            "servico": "NfeStatusServico",
            "url": "https://nfe.sefaz.ms.gov.br/ws/NFeStatusServico4"
        },
        {
            "ambiente": "Produção",
            "servico": "RecepcaoEvento",
            "url": "https://nfe.sefaz.ms.gov.br/ws/NFeRecepcaoEvento4"
        },
        {
            "ambiente": "Produção",
            "servico": "NFeAutorizacao",
            "url": "https://nfe.sefaz.ms.gov.br/ws/NFeAutorizacao4"
        },
        {
            "ambiente": "Produção",
            "servico": "NFeRetAutorizacao",
            "url": "https://nfe.sefaz.ms.gov.br/ws/NFeRetAutorizacao4"
        },
        {
            "ambiente": "Produção",
            "servico": "NfeConsultaCadastro",
            "url": "https://nfe.sefaz.ms.gov.br/ws/CadConsultaCadastro4"
        }
    ],
    "MT": [
        {
            "ambiente": "Produção",
            "servico": "NfeInutilizacao",
            "url": "https://nfe.sefaz.mt.gov.br/nfews/v2/services/NfeInutilizacao4?wsdl"
        },
        {
            "ambiente": "Produção",
            "servico": "NfeConsultaProtocolo",
            "url": "https://nfe.sefaz.mt.gov.br/nfews/v2/services/NfeConsulta4?wsdl"
        },
        {
            "ambiente": "Produção",
            "servico": "NfeStatusServico",
            "url": "https://nfe.sefaz.mt.gov.br/nfews/v2/services/NfeStatusServico4?wsdl"
        },
        {
            "ambiente": "Produção",
            "servico": "RecepcaoEvento",
            "url": "https://nfe.sefaz.mt.gov.br/nfews/v2/services/RecepcaoEvento4?wsdl"
        },
        {
            "ambiente": "Produção",
            "servico": "NFeAutorizacao",
            "url": "https://nfe.sefaz.mt.gov.br/nfews/v2/services/NfeAutorizacao4?wsdl"
        },
        {
            "ambiente": "Produção",
            "servico": "NFeRetAutorizacao",
            "url": "https://nfe.sefaz.mt.gov.br/nfews/v2/services/NfeRetAutorizacao4?wsdl"
        },
        {
            "ambiente": "Produção",
            "servico": "NfeConsultaCadastro",
            "url": "https://nfe.sefaz.mt.gov.br/nfews/v2/services/CadConsultaCadastro4?wsdl"
        }
    ],
    "PE": [
        {
            "ambiente": "Produção",
            "servico": "NfeInutilizacao",
            "url": "https://nfe.sefaz.pe.gov.br/nfe-service/services/NFeInutilizacao4?wsdl"
        },
        {
            "ambiente": "Produção",
            "servico": "NfeConsultaProtocolo",
            "url": "https://nfe.sefaz.pe.gov.br/nfe-service/services/NFeConsultaProtocolo4?wsdl"
        },
        {
            "ambiente": "Produção",
            "servico": "NfeStatusServico",
            "url": "https://nfe.sefaz.pe.gov.br/nfe-service/services/NFeStatusServico4?wsdl"
        },
        {
            "ambiente": "Produção",
            "servico": "RecepcaoEvento",
            "url": "https://nfe.sefaz.pe.gov.br/nfe-service/services/NFeRecepcaoEvento4?wsdl"
        },
        {
            "ambiente": "Produção",
            "servico": "NFeAutorizacao",
            "url": "https://nfe.sefaz.pe.gov.br/nfe-service/services/NFeAutorizacao4?wsdl"
        },
        {
            "ambiente": "Produção",
            "servico": "NFeRetAutorizacao",
            "url": "https://nfe.sefaz.pe.gov.br/nfe-service/services/NFeRetAutorizacao4?wsdl"
        },
        {
            "ambiente": "Produção",
            "servico": "NfeConsultaCadastro",
            "url": "https://nfe.sefaz.pe.gov.br/nfe-service/services/CadConsultaCadastro4?wsdl"
        }
    ],
    "PR": [
        {
            "ambiente": "Produção",
            "servico": "NfeInutilizacao",
            "url": "https://nfe.sefa.pr.gov.br/nfe/NFeInutilizacao4?wsdl"
        },
        {
            "ambiente": "Produção",
            "servico": "NfeConsultaProtocolo",
            "url": "https://nfe.sefa.pr.gov.br/nfe/NFeConsultaProtocolo4?wsdl"
        },
        {
            "ambiente": "Produção",
            "servico": "NfeStatusServico",
            "url": "https://nfe.sefa.pr.gov.br/nfe/NFeStatusServico4?wsdl"
        },
        {
            "ambiente": "Produção",
            "servico": "RecepcaoEvento",
            "url": "https://nfe.sefa.pr.gov.br/nfe/NFeRecepcaoEvento4?wsdl"
        },
        {
            "ambiente": "Produção",
            "servico": "NFeAutorizacao",
            "url": "https://nfe.sefa.pr.gov.br/nfe/NFeAutorizacao4?wsdl"
        },
        {
            "ambiente": "Produção",
            "servico": "NFeRetAutorizacao",
            "url": "https://nfe.sefa.pr.gov.br/nfe/NFeRetAutorizacao4?wsdl"
        },
        {
            "ambiente": "Produção",
            "servico": "NfeConsultaCadastro",
            "url": "https://nfe.sefa.pr.gov.br/nfe/CadConsultaCadastro4?wsdl"
        }
    ],
    "RS": [
        {
            "ambiente": "Produção",
            "servico": "NfeInutilizacao",
            "url": "https://nfe.sefazrs.rs.gov.br/ws/nfeinutilizacao/nfeinutilizacao4.asmx"
        },
        {
            "ambiente": "Produção",
            "servico": "NfeConsultaProtocolo",
            "url": "https://nfe.sefazrs.rs.gov.br/ws/NfeConsulta/NfeConsulta4.asmx"
        },
        {
            "ambiente": "Produção",
            "servico": "NfeStatusServico",
            "url": "https://nfe.sefazrs.rs.gov.br/ws/NfeStatusServico/NfeStatusServico4.asmx"
        },
        {
            "ambiente": "Produção",
            "servico": "RecepcaoEvento",
            "url": "https://nfe.sefazrs.rs.gov.br/ws/recepcaoevento/recepcaoevento4.asmx"
        },
        {
            "ambiente": "Produção",
            "servico": "NFeAutorizacao",
            "url": "https://nfe.sefazrs.rs.gov.br/ws/NfeAutorizacao/NFeAutorizacao4.asmx"
        },
        {
            "ambiente": "Produção",
            "servico": "NFeRetAutorizacao",
            "url": "https://nfe.sefazrs.rs.gov.br/ws/NfeRetAutorizacao/NFeRetAutorizacao4.asmx"
        },
        {
            "ambiente": "Produção",
            "servico": "NfeConsultaCadastro",
            "url": "https://nfe.sefazrs.rs.gov.br/ws/CadConsultaCadastro/CadConsultaCadastro4.asmx"
        }
    ],
    "SP": [
        {
            "ambiente": "Produção",
            "servico": "NfeInutilizacao",
            "url": "https://nfe.fazenda.sp.gov.br/ws/nfeinutilizacao4.asmx"
        },
        {
            "ambiente": "Produção",
            "servico": "NfeConsultaProtocolo",
            "url": "https://nfe.fazenda.sp.gov.br/ws/nfeconsultaprotocolo4.asmx"
        },
        {
            "ambiente": "Produção",
            "servico": "NfeStatusServico",
            "url": "https://nfe.fazenda.sp.gov.br/ws/nfestatusservico4.asmx"
        },
        {
            "ambiente": "Produção",
            "servico": "RecepcaoEvento",
            "url": "https://nfe.fazenda.sp.gov.br/ws/nferecepcaoevento4.asmx"
        },
        {
            "ambiente": "Produção",
            "servico": "NFeAutorizacao",
            "url": "https://nfe.fazenda.sp.gov.br/ws/nfeautorizacao4.asmx"
        },
        {
            "ambiente": "Produção",
            "servico": "NFeRetAutorizacao",
            "url": "https://nfe.fazenda.sp.gov.br/ws/nferetautorizacao4.asmx"
        },
        {
            "ambiente": "Produção",
            "servico": "NfeConsultaCadastro",
            "url": "https://nfe.fazenda.sp.gov.br/ws/cadconsultacadastro4.asmx"
        }
    ],
    "SVAN": [
        {
            "ambiente": "Produção",
            "servico": "NfeInutilizacao",
            "url": "https://www.sefazvirtual.fazenda.gov.br/NFeInutilizacao4/NFeInutilizacao4.asmx"
        },
        {
            "ambiente": "Produção",
            "servico": "NfeConsultaProtocolo",
            "url": "https://www.sefazvirtual.fazenda.gov.br/NFeConsultaProtocolo4/NFeConsultaProtocolo4.asmx"
        },
        {
            "ambiente": "Produção",
            "servico": "NfeStatusServico",
            "url": "https://www.sefazvirtual.fazenda.gov.br/NFeStatusServico4/NFeStatusServico4.asmx"
        },
        {
            "ambiente": "Produção",
            "servico": "RecepcaoEvento",
            "url": "https://www.sefazvirtual.fazenda.gov.br/NFeRecepcaoEvento4/NFeRecepcaoEvento4.asmx"
        },
        {
            "ambiente": "Produção",
            "servico": "NFeAutorizacao",
            "url": "https://www.sefazvirtual.fazenda.gov.br/NFeAutorizacao4/NFeAutorizacao4.asmx"
        },
        {
            "ambiente": "Produção",
            "servico": "NFeRetAutorizacao",
            "url": "https://www.sefazvirtual.fazenda.gov.br/NFeRetAutorizacao4/NFeRetAutorizacao4.asmx"
        }
    ],
    "SVRS": [
        {
            "ambiente": "Produção",
            "servico": "NfeInutilizacao",
            "url": "https://nfe.svrs.rs.gov.br/ws/nfeinutilizacao/nfeinutilizacao4.asmx"
        },
        {
            "ambiente": "Produção",
            "servico": "NfeConsultaProtocolo",
            "url": "https://nfe.svrs.rs.gov.br/ws/NfeConsulta/NfeConsulta4.asmx"
        },
        {
            "ambiente": "Produção",
            "servico": "NfeStatusServico",
            "url": "https://nfe.svrs.rs.gov.br/ws/NfeStatusServico/NfeStatusServico4.asmx"
        },
        {
            "ambiente": "Produção",
            "servico": "RecepcaoEvento",
            "url": "https://nfe.svrs.rs.gov.br/ws/recepcaoevento/recepcaoevento4.asmx"
        },
        {
            "ambiente": "Produção",
            "servico": "NFeAutorizacao",
            "url": "https://nfe.svrs.rs.gov.br/ws/NfeAutorizacao/NFeAutorizacao4.asmx"
        },
        {
            "ambiente": "Produção",
            "servico": "NFeRetAutorizacao",
            "url": "https://nfe.svrs.rs.gov.br/ws/NfeRetAutorizacao/NFeRetAutorizacao4.asmx"
        },
        {
            "ambiente": "Produção",
            "servico": "NfeConsultaCadastro",
            "url": "https://nfe.svrs.rs.gov.br/ws/CadConsultaCadastro/CadConsultaCadastro4.asmx"
        },
        {
            "ambiente": "Produção",
            "servico": "Consulta GTIN",
            "url": "https://dfe-servico.svrs.rs.gov.br/ws/ccgConsGTIN/ccgConsGTIN.asmx"
        }
    ],
    "SVC-AN": [
        {
            "ambiente": "Produção",
            "servico": "NfeInutilizacao",
            "url": "https://www.sefazvirtual.fazenda.gov.br/NFeInutilizacao4/NFeInutilizacao4.asmx"
        },
        {
            "ambiente": "Produção",
            "servico": "NfeConsultaProtocolo",
            "url": "https://www.sefazvirtual.fazenda.gov.br/NFeConsultaProtocolo4/NFeConsultaProtocolo4.asmx"
        },
        {
            "ambiente": "Produção",
            "servico": "NfeStatusServico",
            "url": "https://www.sefazvirtual.fazenda.gov.br/NFeStatusServico4/NFeStatusServico4.asmx"
        },
        {
            "ambiente": "Produção",
            "servico": "RecepcaoEvento",
            "url": "https://www.sefazvirtual.fazenda.gov.br/NFeRecepcaoEvento4/NFeRecepcaoEvento4.asmx"
        },
        {
            "ambiente": "Produção",
            "servico": "NFeAutorizacao",
            "url": "https://www.sefazvirtual.fazenda.gov.br/NFeAutorizacao4/NFeAutorizacao4.asmx"
        },
        {
            "ambiente": "Produção",
            "servico": "NFeRetAutorizacao",
            "url": "https://www.sefazvirtual.fazenda.gov.br/NFeRetAutorizacao4/NFeRetAutorizacao4.asmx"
        }
    ],
    "SVC-RS": [
        {
            "ambiente": "Produção",
            "servico": "NfeConsultaProtocolo",
            "url": "https://nfe.svrs.rs.gov.br/ws/NfeConsulta/NfeConsulta4.asmx"
        },
        {
            "ambiente": "Produção",
            "servico": "NfeStatusServico",
            "url": "https://nfe.svrs.rs.gov.br/ws/NfeStatusServico/NfeStatusServico4.asmx"
        },
        {
            "ambiente": "Produção",
            "servico": "RecepcaoEvento",
            "url": "https://nfe.svrs.rs.gov.br/ws/recepcaoevento/recepcaoevento4.asmx"
        },
        {
            "ambiente": "Produção",
            "servico": "NFeAutorizacao",
            "url": "https://nfe.svrs.rs.gov.br/ws/NfeAutorizacao/NFeAutorizacao4.asmx"
        },
        {
            "ambiente": "Produção",
            "servico": "NFeRetAutorizacao",
            "url": "https://nfe.svrs.rs.gov.br/ws/NfeRetAutorizacao/NFeRetAutorizacao4.asmx"
        }
    ],
    "AN": [
        {
            "ambiente": "Produção",
            "servico": "NFeDistribuicaoDFe",
            "url": "https://www1.nfe.fazenda.gov.br/NFeDistribuicaoDFe/NFeDistribuicaoDFe.asmx"
        },
        {
            "ambiente": "Produção",
            "servico": "RecepcaoEvento",
            "url": "https://www.nfe.fazenda.gov.br/NFeRecepcaoEvento4/NFeRecepcaoEvento4.asmx"
        }
    ]
};

    function renderWebservices() {
        const sidebar = document.getElementById('ws-sidebar');
        const content = document.getElementById('ws-content');
        
        // Sort UFs
        const ufs = Object.keys(webservicesData).sort();
        
        // Clear existing content
        sidebar.innerHTML = '';
        content.innerHTML = '';

        ufs.forEach((uf, index) => {
            // Sidebar Item
            const link = document.createElement('a');
            link.className = `list-group-item list-group-item-action ${index === 0 ? 'active' : ''}`;
            link.id = `list-${uf}-list`;
            link.setAttribute('data-bs-toggle', 'list');
            link.href = `#list-${uf}`;
            link.role = 'tab';
            link.innerHTML = `<div class="d-flex w-100 justify-content-between"><h6 class="mb-0 fw-bold">${uf}</h6></div>`;
            sidebar.appendChild(link);
            
            // Content Pane
            const pane = document.createElement('div');
            pane.className = `tab-pane fade ${index === 0 ? 'show active' : ''}`;
            pane.id = `list-${uf}`;
            pane.role = 'tabpanel';
            
            let tableHtml = `
                <h5 class="mb-3 border-bottom pb-2 text-primary"><i class="bi bi-globe me-2"></i>${uf} - Webservices</h5>
                <div class="table-responsive shadow-sm rounded">
                    <table class="table table-bordered table-hover table-striped small mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Ambiente</th>
                                <th>Serviço</th>
                                <th>URL</th>
                            </tr>
                        </thead>
                        <tbody>
            `;
            
            webservicesData[uf].forEach(ws => {
                tableHtml += `
                    <tr>
                        <td style="width: 100px;"><span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">${ws.ambiente}</span></td>
                        <td style="width: 200px;" class="fw-bold text-dark">${ws.servico}</td>
                        <td class="text-break font-monospace text-muted user-select-all">${ws.url}</td>
                    </tr>
                `;
            });
            
            tableHtml += `</tbody></table></div>`;
            pane.innerHTML = tableHtml;
            content.appendChild(pane);
        });
    }

    // Initial Render
    document.addEventListener('DOMContentLoaded', () => {
        renderRules(nfeRules);
        renderLayout(nfeLayout);
        renderWebservices();
    });
</script>

<?php include 'includes/footer.php'; ?>
