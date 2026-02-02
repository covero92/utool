<?php
include 'includes/header.php';

// Helper to remove BOM and clean content
function cleanContent($content) {
    // Remove BOM
    $bom = pack('H*','EFBBBF');
    $content = preg_replace("/^$bom/", '', $content);
    
    // Ensure UTF-8
    if (!mb_check_encoding($content, 'UTF-8')) {
        $content = mb_convert_encoding($content, 'UTF-8', 'ISO-8859-1');
    }
    
    return $content;
}

// Define CL fields dynamically to avoid huge array in code
$clFields = [
    ['name' => "Identificação", 'type' => 'C', 'maxLength' => 2, 'required' => true],
    ['name' => "Código", 'type' => 'C', 'maxLength' => 14, 'required' => true],
    ['name' => "Nome", 'type' => 'C', 'maxLength' => 50, 'required' => true],
    ['name' => "Razão Social", 'type' => 'C', 'maxLength' => 50, 'required' => true],
];
for ($i = 1; $i <= 60; $i++) {
    $clFields[] = ['name' => "Campo Extra $i", 'type' => 'C', 'maxLength' => 50, 'required' => false];
}

// Validation Rules
$validationRules = [
    'OP' => [
        'name' => 'Operação',
        'fieldCount' => 34,
        'fields' => [
            ['name' => "Identificação", 'type' => 'C', 'maxLength' => 2, 'required' => true],
            ['name' => "Filial", 'type' => 'C', 'maxLength' => 4, 'required' => true],
            ['name' => "Tipo Operação", 'type' => 'C', 'maxLength' => 3, 'required' => true],
            ['name' => "Data", 'type' => 'N', 'maxLength' => 8, 'required' => true],
            // ... (Simplified for demo, would be full list in prod)
        ]
    ],
    'IT' => [
        'name' => 'Item',
        'fieldCount' => 22,
        'fields' => [
            ['name' => "Identificação", 'type' => 'C', 'maxLength' => 2, 'required' => true],
            ['name' => "Sequência", 'type' => 'N', 'maxLength' => 6, 'required' => true],
            ['name' => "Produto", 'type' => 'C', 'maxLength' => 14, 'required' => true],
        ]
    ],
    'CL' => [
        'name' => 'Cliente',
        'fieldCount' => 64,
        'fields' => $clFields
    ],
    'PR' => [
        'name' => 'Produto',
        'fieldCount' => 20,
        'fields' => [
            ['name' => "Identificação", 'type' => 'C', 'maxLength' => 2, 'required' => true],
            ['name' => "Código", 'type' => 'C', 'maxLength' => 14, 'required' => true],
            ['name' => "Descrição", 'type' => 'C', 'maxLength' => 50, 'required' => true],
            ['name' => "Unidade", 'type' => 'C', 'maxLength' => 2, 'required' => true],
            ['name' => "Preço", 'type' => 'N', 'maxLength' => 10, 'required' => true],
            ['name' => "Campo Extra 1", 'type' => 'C', 'maxLength' => 50, 'required' => false],
            ['name' => "Campo Extra 2", 'type' => 'C', 'maxLength' => 50, 'required' => false],
            ['name' => "Campo Extra 3", 'type' => 'C', 'maxLength' => 50, 'required' => false],
            ['name' => "Campo Extra 4", 'type' => 'C', 'maxLength' => 50, 'required' => false],
            ['name' => "Campo Extra 5", 'type' => 'C', 'maxLength' => 50, 'required' => false],
            ['name' => "Campo Extra 6", 'type' => 'C', 'maxLength' => 50, 'required' => false],
            ['name' => "Campo Extra 7", 'type' => 'C', 'maxLength' => 50, 'required' => false],
            ['name' => "Campo Extra 8", 'type' => 'C', 'maxLength' => 50, 'required' => false],
            ['name' => "Campo Extra 9", 'type' => 'C', 'maxLength' => 50, 'required' => false],
            ['name' => "Campo Extra 10", 'type' => 'C', 'maxLength' => 50, 'required' => false],
        ]
    ]
];

$initialData = [
    'lines' => [],
    'stats' => ['total' => 0, 'valid' => 0, 'errors' => 0, 'corrected' => 0],
    'recordTypes' => []
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $content = '';
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $content = file_get_contents($_FILES['file']['tmp_name']);
        $initialData['filename'] = $_FILES['file']['name'];
    } elseif (isset($_POST['content'])) {
        $content = $_POST['content'];
        $initialData['filename'] = 'pasted_content.txt';
    }

    if ($content) {
        $content = cleanContent($content);
        $lines = explode("\n", $content);
        $initialData['stats']['total'] = count($lines);
        
        foreach ($lines as $index => $line) {
            $line = trim($line);
            if (empty($line)) continue;

            $lineNumber = $index + 1;
            // Sanitize record type
            $recordType = mb_substr($line, 0, 2);
            $recordType = preg_replace('/[^A-Z0-9]/', '', $recordType);

            $lineErrors = [];
            $fields = explode(";", $line); 

            if (!in_array($recordType, $initialData['recordTypes'])) {
                $initialData['recordTypes'][] = $recordType;
            }

            if (!isset($validationRules[$recordType])) {
                $lineErrors[] = "Tipo de registro desconhecido '$recordType'.";
            } else {
                $rule = $validationRules[$recordType];
                
                // Validate Fields
                foreach ($rule['fields'] as $i => $fieldRule) {
                    if (isset($fields[$i])) {
                        $value = trim($fields[$i]);
                        
                        // Required
                        if ($fieldRule['required'] && $value === '') {
                            $lineErrors[] = "Campo '{$fieldRule['name']}' é obrigatório.";
                        }

                        // Max Length
                        if (mb_strlen($value) > $fieldRule['maxLength']) {
                            $lineErrors[] = "Campo '{$fieldRule['name']}' excede tamanho {$fieldRule['maxLength']}.";
                        }
                        
                        // Type Check (Simple)
                        if ($fieldRule['type'] === 'N' && !is_numeric(str_replace(',', '.', $value)) && $value !== '') {
                             $lineErrors[] = "Campo '{$fieldRule['name']}' deve ser numérico.";
                        }
                    } elseif ($fieldRule['required']) {
                        $lineErrors[] = "Campo '{$fieldRule['name']}' é obrigatório e está ausente.";
                    }
                }
            }

            if (!empty($lineErrors)) {
                $initialData['stats']['errors']++;
            } else {
                $initialData['stats']['valid']++;
            }

            $initialData['lines'][] = [
                'id' => $lineNumber,
                'content' => $line,
                'originalContent' => $line,
                'recordType' => $recordType,
                'errors' => $lineErrors,
                'status' => empty($lineErrors) ? 'valid' : 'error',
                'fields' => $fields
            ];
        }
    }
}
?>

<!-- JSPDF for PDF Export -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

<style>
    /* Validator Specific Styles */
    .val-stats-card {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 0.5rem;
        padding: 1rem;
    }
    .val-stats-label { font-size: 0.75rem; color: #64748b; font-weight: 500; }
    .val-stats-value { font-size: 1.5rem; font-weight: 700; color: #0f172a; }
    .val-stats-value.error { color: #dc2626; }
    .val-stats-value.valid { color: #16a34a; }
    .val-stats-value.corrected { color: #2563eb; }

    .val-list-item {
        border: 1px solid #e2e8f0;
        border-radius: 0.5rem;
        margin-bottom: 0.5rem;
        padding: 1rem;
        cursor: pointer;
        transition: all 0.2s;
    }
    .val-list-item:hover { border-color: #cbd5e1; }
    .val-list-item.active { border-color: #3b82f6; background-color: #eff6ff; }
    .val-list-item.error { background-color: #fef2f2; border-color: #fecaca; }
    .val-list-item.valid { background-color: #f0fdf4; border-color: #bbf7d0; }
    
    .val-editor {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 0.5rem;
        height: calc(100vh - 300px);
        display: flex;
        flex-direction: column;
    }
    
    .error-group-header {
        background-color: #f8fafc;
        padding: 0.5rem 1rem;
        font-weight: 600;
        font-size: 0.875rem;
        border-bottom: 1px solid #e2e8f0;
        color: #475569;
    }
    
    .transition-all { transition: all 0.3s ease; }
    .transition-icon { transition: transform 0.2s ease; }
</style>

<div class="container-fluid px-4 py-4 bg-light min-vh-100">
    <?php if (empty($initialData['lines'])): ?>
        <!-- Upload Screen -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h4 fw-bold text-dark mb-0">Validador R2D2</h1>
            <a href="index.php" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-5 text-center">
                <form method="POST" enctype="multipart/form-data" onsubmit="return validateUploadForm()">
                    <div class="mb-4">
                        <i class="bi bi-file-earmark-text display-4 text-primary mb-3 d-block"></i>
                        <h5 class="fw-bold">Carregar Arquivo R2D2</h5>
                        <p class="text-muted">Selecione o arquivo de texto posicional para validar.</p>
                        <input class="form-control w-50 mx-auto" type="file" name="file" id="fileInput">
                    </div>
                    <div class="mb-4">
                        <span class="text-muted small">OU</span>
                    </div>
                    <div class="mb-4">
                        <textarea class="form-control w-75 mx-auto font-monospace small" name="content" id="contentInput" rows="5" placeholder="Cole o conteúdo aqui..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-play-circle me-2"></i>Validar Arquivo
                    </button>
                </form>
                <script>
                    function validateUploadForm() {
                        const file = document.getElementById('fileInput').value;
                        const content = document.getElementById('contentInput').value.trim();
                        
                        if (!file && !content) {
                            alert('Por favor, selecione um arquivo OU cole o conteúdo para validar.');
                            return false;
                        }
                        
                        // Show Loader
                        document.getElementById('loadingOverlay').style.display = 'flex';
                        return true;
                    }
                </script>
            </div>
        </div>
    <?php else: ?>
        <!-- Loading Overlay -->
        <div id="loadingOverlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255, 255, 255, 0.9); z-index: 9999; display: flex; flex-direction: column; justify-content: center; align-items: center;">
            <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                <span class="visually-hidden">Carregando...</span>
            </div>
            <h5 class="mt-3 text-muted fw-bold">Processando arquivo...</h5>
            <p class="text-muted small">Isso pode levar alguns segundos para arquivos grandes.</p>
        </div>

        <!-- Results Screen -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
            <div class="d-flex align-items-center gap-3">
                <a href="validator.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i></a>
                <div>
                    <h1 class="h4 fw-bold text-dark mb-0">Resultado da Validação</h1>
                    <div class="text-muted small">
                        Arquivo: <span class="fw-bold text-primary"><?php echo htmlspecialchars($initialData['filename']); ?></span>
                    </div>
                </div>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <button class="btn btn-outline-secondary bg-white" onclick="window.location.href='validator.php'">
                    <i class="bi bi-cloud-upload me-2"></i>Novo Arquivo
                </button>
                <button class="btn btn-outline-secondary bg-white" onclick="autoCorrectAll()">
                    <i class="bi bi-magic me-2"></i>Corrigir Tudo
                </button>
                <button class="btn btn-outline-secondary bg-white" onclick="exportPDF()">
                    <i class="bi bi-file-pdf me-2"></i>PDF
                </button>
                <button class="btn btn-primary" onclick="downloadCorrected()">
                    <i class="bi bi-download me-2"></i>Baixar Corrigido
                </button>
            </div>
        </div>

        <!-- Stats -->
        <div class="row g-3 mb-4">
            <div class="col">
                <div class="val-stats-card">
                    <div class="val-stats-label">Total de Linhas</div>
                    <div class="val-stats-value" id="statTotal"><?php echo $initialData['stats']['total']; ?></div>
                </div>
            </div>
            <div class="col">
                <div class="val-stats-card">
                    <div class="val-stats-label">Linhas Válidas</div>
                    <div class="val-stats-value valid" id="statValid"><?php echo $initialData['stats']['valid']; ?></div>
                </div>
            </div>
            <div class="col">
                <div class="val-stats-card">
                    <div class="val-stats-label">Linhas com Erro</div>
                    <div class="val-stats-value error" id="statErrors"><?php echo $initialData['stats']['errors']; ?></div>
                </div>
            </div>
            <div class="col">
                <div class="val-stats-card">
                    <div class="val-stats-label">Linhas Corrigidas</div>
                    <div class="val-stats-value corrected" id="statCorrected">0</div>
                </div>
            </div>
            <div class="col">
                <div class="val-stats-card">
                    <div class="val-stats-label">Tipos de Registro</div>
                    <div class="fw-bold text-dark mt-1"><?php echo implode(", ", $initialData['recordTypes']); ?></div>
                </div>
            </div>
        </div>

        <!-- Main Interface -->
        <div class="bg-white border rounded p-2 mb-3 d-flex gap-2 overflow-auto">
            <button class="btn btn-light btn-sm active fw-medium text-nowrap" onclick="setTab('errors', this)">Linhas com Erro (<span id="tabErrorCount"><?php echo $initialData['stats']['errors']; ?></span>)</button>
            <button class="btn btn-light btn-sm fw-medium text-nowrap" onclick="setTab('valid', this)">Linhas Válidas (<span id="tabValidCount"><?php echo $initialData['stats']['valid']; ?></span>)</button>
            <button class="btn btn-light btn-sm fw-medium text-nowrap" onclick="setTab('corrected', this)">Linhas Corrigidas (<span id="tabCorrectedCount">0</span>)</button>
            <button class="btn btn-light btn-sm fw-medium text-nowrap" onclick="setTab('original', this)">Arquivo Original</button>
        </div>

        <div class="row g-4 h-100">
            <!-- Left Panel: Error List -->
            <div class="col-md-6 d-flex flex-column transition-all" id="listPanel" style="height: calc(100vh - 320px);">
                <div class="bg-white border rounded-top p-2 d-flex justify-content-between align-items-center flex-shrink-0">
                    <div class="btn-group btn-group-sm" id="viewControls">
                        <button class="btn btn-outline-secondary active" id="viewListBtn" onclick="setViewMode('list')"><i class="bi bi-list"></i> Visão por Linhas</button>
                        <button class="btn btn-outline-secondary" id="viewGroupBtn" onclick="setViewMode('group')"><i class="bi bi-layers"></i> Agrupar Erros</button>
                    </div>
                    <button class="btn btn-outline-secondary btn-sm ms-auto" onclick="toggleEditor()"><i class="bi bi-layout-sidebar-inset-reverse"></i> <span id="toggleEditorText">Ocultar Editor</span></button>
                </div>
                
                <div class="flex-grow-1 overflow-auto bg-white border-start border-end border-bottom p-3" id="linesContainer">
                    <!-- Lines will be rendered here by JS -->
                </div>
            </div>

            <!-- Right Panel: Editor -->
            <div class="col-md-6 transition-all" id="editorPanel" style="height: calc(100vh - 320px);">
                <div class="val-editor h-100 d-flex flex-column">
                    <div class="p-3 border-bottom d-flex justify-content-between align-items-center bg-white rounded-top flex-shrink-0">
                        <h6 class="fw-bold mb-0">Editor</h6>
                        <div class="d-flex align-items-center gap-2">
                             <span class="text-muted small" id="editorLineInfo">Selecione uma linha...</span>
                             <button class="btn btn-outline-danger btn-sm py-0 px-2 d-none" id="btnDeleteLine" onclick="deleteLine()" title="Excluir Linha">
                                <i class="bi bi-trash"></i>
                             </button>
                        </div>
                    </div>
                    
                    <div id="editorEmptyState" class="flex-grow-1 d-flex flex-column align-items-center justify-content-center text-muted">
                        <i class="bi bi-pencil-square display-4 mb-3 opacity-25"></i>
                        <p>Clique em uma linha na lista ao lado para editá-la aqui.</p>
                    </div>

                    <div id="editorContent" class="d-none flex-column flex-grow-1 h-100" style="min-height: 0;">
                        <div class="p-2 bg-light border-bottom d-flex flex-wrap justify-content-center gap-2 flex-shrink-0">
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-white border shadow-sm active" id="btnDetailed" onclick="setEditMode('detailed')"><i class="bi bi-layout-text-sidebar-reverse"></i> Detalhada</button>
                                <button class="btn btn-white border shadow-sm text-muted" id="btnQuick" onclick="setEditMode('quick')"><i class="bi bi-pencil"></i> Rápida</button>
                            </div>
                            <div class="vr mx-1"></div>
                            <button class="btn btn-primary btn-sm" onclick="saveChanges()">
                                <i class="bi bi-save me-1"></i>Salvar
                            </button>
                            <button class="btn btn-outline-secondary btn-sm bg-white" onclick="autoCorrectCurrent()">
                                <i class="bi bi-magic me-1"></i>Corrigir
                            </button>
                        </div>
                        
                        <div class="flex-grow-1 overflow-auto p-4" id="editorFields">
                            <!-- Fields rendered here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
            // Initialize Data from PHP
            const validationData = <?php echo json_encode($initialData, JSON_INVALID_UTF8_SUBSTITUTE | JSON_PARTIAL_OUTPUT_ON_ERROR); ?>;
            const validationRules = <?php echo json_encode($validationRules, JSON_INVALID_UTF8_SUBSTITUTE | JSON_PARTIAL_OUTPUT_ON_ERROR); ?>;
            
            let currentTab = 'errors';
            let viewMode = 'list'; // 'list' or 'group'
            let editMode = 'detailed'; // 'detailed' or 'quick'
            let selectedLineId = null;

            // --- Validation Logic (JS Mirror) ---
            function validateLine(line) {
                const rule = validationRules[line.recordType];
                const errors = [];
                
                if (!rule) {
                    return ['Tipo de registro desconhecido.'];
                }

                rule.fields.forEach((fieldRule, index) => {
                    const value = (line.fields[index] || '').trim();
                    
                    // Required
                    if (fieldRule.required && value === '') {
                        errors.push(`Campo '${fieldRule.name}' é obrigatório.`);
                    }
                    
                    // Max Length
                    if (value.length > fieldRule.maxLength) {
                        errors.push(`Campo '${fieldRule.name}' excede tamanho ${fieldRule.maxLength}.`);
                    }
                    
                    // Numeric
                    if (fieldRule.type === 'N' && value !== '' && isNaN(Number(value.replace(',', '.')))) {
                        errors.push(`Campo '${fieldRule.name}' deve ser numérico.`);
                    }
                });

                return errors;
            }

            // --- Rendering Logic ---

            function renderLines() {
                const container = document.getElementById('linesContainer');
                container.innerHTML = '';

                if (currentTab === 'original') {
                    const content = validationData.lines.map(l => l.originalContent).join('\n');
                    const lineNumbers = validationData.lines.map(l => l.id).join('\n');
                    
                    container.innerHTML = `
                        <div class="h-100 d-flex flex-column">
                            <div class="text-muted small mb-2"><i class="bi bi-info-circle me-1"></i> Visualização do arquivo cru (somente leitura).</div>
                            <div class="d-flex flex-grow-1 border rounded bg-light overflow-hidden">
                                <textarea class="bg-light border-0 text-end text-muted pe-2 py-2" 
                                          style="width: 60px; resize: none; overflow: hidden; font-family: monospace; font-size: 0.875rem; line-height: 1.5; outline: none;" 
                                          readonly disabled>${lineNumbers}</textarea>
                                <div class="vr"></div>
                                <textarea class="form-control border-0 bg-light ps-2 py-2" 
                                          style="resize: none; overflow: auto; white-space: pre; font-family: monospace; font-size: 0.875rem; line-height: 1.5; outline: none;" 
                                          readonly 
                                          onscroll="this.previousElementSibling.previousElementSibling.scrollTop = this.scrollTop">${content}</textarea>
                            </div>
                        </div>
                    `;
                    return;
                }

                const lines = validationData.lines.filter(l => {
                    if (currentTab === 'errors') return l.status === 'error';
                    if (currentTab === 'valid') return l.status === 'valid';
                    if (currentTab === 'corrected') return l.status === 'corrected';
                    return true;
                });

                if (lines.length === 0) {
                    container.innerHTML = '<div class="text-center py-5 text-muted">Nenhum registro encontrado nesta visualização.</div>';
                    return;
                }

                if (viewMode === 'group' && currentTab === 'errors') {
                    renderGroupedLines(lines, container);
                } else {
                    renderListLines(lines, container);
                }
            }

            function renderListLines(lines, container) {
                lines.forEach(line => {
                    const div = document.createElement('div');
                    div.className = `val-list-item ${line.status} ${line.id === selectedLineId ? 'active' : ''}`;
                    div.onclick = () => selectLine(line.id);
                    
                    let html = `<div class="d-flex justify-content-between mb-2">
                        <span class="badge bg-secondary">Linha ${line.id}: ${line.recordType}</span>
                        ${line.status === 'error' ? `<span class="badge bg-danger">${line.errors.length} erro(s)</span>` : ''}
                        ${line.status === 'corrected' ? `<span class="badge bg-primary">Corrigido</span>` : ''}
                    </div>
                    <div class="font-monospace small text-muted text-break mb-2 bg-white p-2 border rounded">${line.content}</div>`;

                    if (line.errors && line.errors.length > 0) {
                        html += `<div class="small text-danger"><i class="bi bi-exclamation-circle me-1"></i> ${line.errors[0]}</div>`;
                        if (line.errors.length > 1) {
                            html += `<div class="small text-danger mt-1">+ mais ${line.errors.length - 1} erro(s)</div>`;
                        }
                    }

                    div.innerHTML = html;
                    container.appendChild(div);
                });
            }

            function renderGroupedLines(lines, container) {
                const groups = {};
                lines.forEach(line => {
                    const errorKey = line.errors.length > 0 ? line.errors[0] : 'Outros Erros';
                    if (!groups[errorKey]) groups[errorKey] = [];
                    groups[errorKey].push(line);
                });

                Object.keys(groups).forEach((error, index) => {
                    const groupId = `group-${index}`;
                    const header = document.createElement('div');
                    header.className = 'error-group-header d-flex justify-content-between align-items-center cursor-pointer';
                    header.style.cursor = 'pointer';
                    header.onclick = () => toggleGroup(groupId);
                    header.innerHTML = `
                        <span><i class="bi bi-folder me-2"></i>${error} (${groups[error].length})</span>
                        <i class="bi bi-chevron-down transition-icon" id="${groupId}-icon"></i>
                    `;
                    container.appendChild(header);

                    const groupContainer = document.createElement('div');
                    groupContainer.id = groupId;
                    groupContainer.className = 'group-content'; // Default visible
                    
                    renderListLines(groups[error], groupContainer);
                    container.appendChild(groupContainer);
                });
            }

            function toggleGroup(groupId) {
                const content = document.getElementById(groupId);
                const icon = document.getElementById(`${groupId}-icon`);
                
                if (content.style.display === 'none') {
                    content.style.display = 'block';
                    icon.classList.remove('bi-chevron-right');
                    icon.classList.add('bi-chevron-down');
                } else {
                    content.style.display = 'none';
                    icon.classList.remove('bi-chevron-down');
                    icon.classList.add('bi-chevron-right');
                }
            }

            // --- Interaction Logic ---

            function selectLine(id) {
                selectedLineId = id;
                renderLines(); // Re-render to update active state
                
                const line = validationData.lines.find(l => l.id === id);
                if (!line) return;

                document.getElementById('editorEmptyState').classList.add('d-none');
                document.getElementById('editorContent').classList.remove('d-none');
                document.getElementById('editorContent').classList.add('d-flex');
                document.getElementById('btnDeleteLine').classList.remove('d-none');
                document.getElementById('editorLineInfo').textContent = `Editando Linha ${line.id} (${line.recordType})`;

                renderEditor();
            }

            function deleteLine() {
                if (!confirm('Tem certeza que deseja excluir esta linha?')) return;
                
                const index = validationData.lines.findIndex(l => l.id === selectedLineId);
                if (index > -1) {
                    const line = validationData.lines[index];
                    
                    // Update stats
                    if (line.status === 'error') validationData.stats.errors--;
                    if (line.status === 'valid') validationData.stats.valid--;
                    if (line.status === 'corrected') validationData.stats.corrected--;
                    validationData.stats.total--;

                    // Remove line
                    validationData.lines.splice(index, 1);
                    
                    // Reset Editor
                    selectedLineId = null;
                    document.getElementById('editorContent').classList.remove('d-flex');
                    document.getElementById('editorContent').classList.add('d-none');
                    document.getElementById('editorEmptyState').classList.remove('d-none');
                    document.getElementById('btnDeleteLine').classList.add('d-none');
                    document.getElementById('editorLineInfo').textContent = 'Selecione uma linha...';

                    updateStats();
                    renderLines();
                }
            }

            function getInvalidIndices(line) {
                const rule = validationRules[line.recordType];
                if (!rule) return [];
                const indices = [];
                
                rule.fields.forEach((fieldRule, index) => {
                    const value = (line.fields[index] || '').trim();
                    let isInvalid = false;

                    if (fieldRule.required && value === '') isInvalid = true;
                    if (value.length > fieldRule.maxLength) isInvalid = true;
                    if (fieldRule.type === 'N' && value !== '' && isNaN(Number(value.replace(',', '.')))) isInvalid = true;

                    if (isInvalid) indices.push(index);
                });
                return indices;
            }

            function renderEditor() {
                const line = validationData.lines.find(l => l.id === selectedLineId);
                const container = document.getElementById('editorFields');
                container.innerHTML = '';

                const invalidIndices = getInvalidIndices(line);

                if (editMode === 'quick') {
                    // Visual Preview for Quick Edit
                    let previewHtml = '<div class="mb-3 p-2 border rounded bg-light overflow-auto text-nowrap">';
                    previewHtml += '<small class="d-block text-muted mb-2">Referência Visual (Campos com erro em vermelho):</small>';
                    
                    line.fields.forEach((field, index) => {
                        const isError = invalidIndices.includes(index);
                        const style = isError ? 'bg-danger text-white border-danger' : 'bg-white border-secondary text-muted';
                        previewHtml += `<span class="d-inline-block border rounded px-2 py-1 me-1 mb-1 small ${style}" title="Campo ${index + 1}">${field || '&nbsp;'}</span>`;
                    });
                    previewHtml += '</div>';

                    container.innerHTML = previewHtml + `
                        <label class="form-label fw-bold">Conteúdo da Linha</label>
                        <textarea class="form-control font-monospace" rows="5" id="quickEditInput">${line.content}</textarea>
                        <div class="form-text text-muted mt-2">Edite a linha inteira diretamente. Use a referência acima para identificar os erros.</div>
                    `;
                    return;
                }

                // Detailed Mode
                const rule = validationRules[line.recordType];
                if (!rule) {
                    container.innerHTML = '<div class="alert alert-warning">Regras não definidas para este tipo de registro. Mude para Edição Rápida.</div>';
                    return;
                }

                rule.fields.forEach((fieldRule, index) => {
                    const value = line.fields[index] || '';
                    const isError = invalidIndices.includes(index);
                    const errorMsg = line.errors.find(e => e.includes(`'${fieldRule.name}'`));
                    
                    const div = document.createElement('div');
                    div.className = `mb-3 p-2 rounded ${isError ? 'bg-danger bg-opacity-10 border border-danger border-opacity-25' : ''}`;
                    div.innerHTML = `
                        <label class="form-label small fw-bold mb-1 ${isError ? 'text-danger' : ''}">${fieldRule.name} (Campo ${index + 1})</label>
                        <input type="text" class="form-control form-control-sm ${isError ? 'is-invalid border-danger' : ''}" 
                               value="${value}" 
                               id="field_${index}"
                               maxlength="${fieldRule.maxLength}"
                               oninput="updateFieldPreview(${index}, this.value)">
                        <div class="d-flex justify-content-between mt-1">
                            <span class="text-muted extra-small">Tipo: ${fieldRule.type} | Tamanho: ${fieldRule.maxLength} | ${fieldRule.required ? 'Obrigatório' : 'Opcional'}</span>
                            <span class="${isError ? 'text-danger fw-bold' : 'text-muted'} extra-small" id="charCount_${index}">(caracteres: ${value.length} / ${fieldRule.maxLength})</span>
                        </div>
                        ${errorMsg ? `<div class="invalid-feedback d-block fw-bold">${errorMsg}</div>` : ''}
                    `;
                    container.appendChild(div);
                });
            }

            function updateFieldPreview(index, value) {
                const line = validationData.lines.find(l => l.id === selectedLineId);
                const rule = validationRules[line.recordType].fields[index];
                
                const input = document.getElementById(`field_${index}`);
                const charCount = document.getElementById(`charCount_${index}`);
                const container = input.closest('.mb-3');
                const errorMsgDiv = container.querySelector('.invalid-feedback');
                const label = container.querySelector('label');

                let isValid = true;

                // 1. Required
                if (rule.required && value.trim() === '') isValid = false;

                // 2. Max Length
                if (value.length > rule.maxLength) isValid = false;

                // 3. Numeric
                if (rule.type === 'N' && value.trim() !== '' && isNaN(Number(value.replace(',', '.')))) isValid = false;

                charCount.textContent = `(caracteres: ${value.length} / ${rule.maxLength})`;

                if (isValid) {
                    // Valid State (Green)
                    input.classList.remove('is-invalid', 'border-danger');
                    input.classList.add('is-valid', 'border-success');
                    
                    charCount.classList.remove('text-danger');
                    charCount.classList.add('text-success');
                    
                    if (container) {
                        container.classList.remove('bg-danger', 'bg-opacity-10', 'border', 'border-danger', 'border-opacity-25');
                        container.classList.add('bg-success', 'bg-opacity-10', 'border', 'border-success', 'border-opacity-25');
                    }
                    
                    if (label) {
                        label.classList.remove('text-danger');
                        label.classList.add('text-success');
                    }

                    if (errorMsgDiv) errorMsgDiv.style.display = 'none';
                } else {
                    // Invalid State (Red)
                    input.classList.remove('is-valid', 'border-success');
                    input.classList.add('is-invalid', 'border-danger');
                    
                    charCount.classList.remove('text-success');
                    charCount.classList.add('text-danger');

                    if (container) {
                        container.classList.remove('bg-success', 'bg-opacity-10', 'border', 'border-success', 'border-opacity-25');
                        container.classList.add('bg-danger', 'bg-opacity-10', 'border', 'border-danger', 'border-opacity-25');
                    }

                    if (label) {
                        label.classList.remove('text-success');
                        label.classList.add('text-danger');
                    }

                    if (errorMsgDiv) errorMsgDiv.style.display = 'block';
                }
            }

            function saveChanges(isAutoCorrect = false) {
                const line = validationData.lines.find(l => l.id === selectedLineId);
                
                if (editMode === 'quick') {
                    line.content = document.getElementById('quickEditInput').value;
                    line.fields = line.content.split(';');
                } else {
                    const rule = validationRules[line.recordType];
                    if (rule) {
                        const newFields = [];
                        rule.fields.forEach((_, index) => {
                            newFields.push(document.getElementById(`field_${index}`).value);
                        });
                        line.fields = newFields;
                        line.content = newFields.join(';');
                    }
                }

                // Re-validate
                const errors = validateLine(line);
                
                if (errors.length === 0) {
                    line.status = 'corrected';
                    line.errors = [];
                } else {
                    line.status = 'error';
                    line.errors = errors;
                }
                
                // Update stats
                validationData.stats.errors = validationData.lines.filter(l => l.status === 'error').length;
                validationData.stats.corrected = validationData.lines.filter(l => l.status === 'corrected').length;
                validationData.stats.valid = validationData.lines.filter(l => l.status === 'valid').length;
                
                updateStats();
                renderLines();
                
                if (isAutoCorrect) {
                    if (errors.length === 0) {
                        alert('Linha corrigida automaticamente com sucesso!');
                    } else {
                        alert('Correção automática aplicada (espaços/tamanhos), mas a linha ainda contém erros (ex: campos obrigatórios vazios). Verifique manualmente.');
                    }
                } else {
                    alert('Alterações salvas!');
                }
            }

            function autoCorrectCurrent() {
                const line = validationData.lines.find(l => l.id === selectedLineId);
                if (!line) return;
                
                // 1. Get current values from DOM to ensure we capture any manual edits
                let currentFields = [];
                if (editMode === 'quick') {
                    const content = document.getElementById('quickEditInput').value;
                    currentFields = content.split(';');
                } else {
                    const rule = validationRules[line.recordType];
                    if (rule) {
                         rule.fields.forEach((_, index) => {
                            const el = document.getElementById(`field_${index}`);
                            currentFields.push(el ? el.value : '');
                        });
                    } else {
                        currentFields = line.fields;
                    }
                }

                // 2. Apply Logic (Trim, Truncate, Remove Extra)
                const rule = validationRules[line.recordType];
                
                // Trim
                currentFields = currentFields.map(f => f.trim());

                if (rule) {
                    // Remove Extra
                    if (currentFields.length > rule.fields.length) {
                        currentFields = currentFields.slice(0, rule.fields.length);
                    }
                    // Truncate
                    currentFields = currentFields.map((val, idx) => {
                        const fieldRule = rule.fields[idx];
                        if (fieldRule && val.length > fieldRule.maxLength) {
                            return val.substring(0, fieldRule.maxLength);
                        }
                        return val;
                    });
                }

                // 3. Update DOM
                if (editMode === 'quick') {
                    document.getElementById('quickEditInput').value = currentFields.join(';');
                } else {
                    if (rule) {
                        rule.fields.forEach((_, index) => {
                            const input = document.getElementById(`field_${index}`);
                            if (input) {
                                input.value = currentFields[index] || '';
                                updateFieldPreview(index, input.value);
                            }
                        });
                    }
                }
                
                saveChanges(true);
            }

            function autoCorrectAll() {
                if (!confirm('Isto tentará corrigir automaticamente erros estruturais (tamanho, campos extras, espaços). \n\nATENÇÃO: Campos obrigatórios vazios NÃO serão preenchidos automaticamente.\n\nContinuar?')) return;
                
                let correctedCount = 0;
                let log = [];

                try {
                    validationData.lines.forEach(line => {
                        if (line.status === 'error') {
                            let lineChanges = [];
                            const rule = validationRules[line.recordType];
                            let originalContent = line.content;

                            // 1. Trim
                            line.fields = line.fields.map(f => f.trim());

                            if (rule) {
                                // 2. Remove Extra Fields
                                if (line.fields.length > rule.fields.length) {
                                    const diff = line.fields.length - rule.fields.length;
                                    line.fields = line.fields.slice(0, rule.fields.length);
                                    lineChanges.push(`Removidos ${diff} campos excedentes`);
                                }

                                // 3. Truncate Fields
                                line.fields = line.fields.map((val, idx) => {
                                    const fieldRule = rule.fields[idx];
                                    if (fieldRule && val.length > fieldRule.maxLength) {
                                        lineChanges.push(`Campo '${fieldRule.name}' truncado`);
                                        return val.substring(0, fieldRule.maxLength);
                                    }
                                    return val;
                                });
                            }

                            line.content = line.fields.join(';');
                            
                            // Re-validate
                            const errors = validateLine(line);
                            if (errors.length === 0) {
                                line.status = 'corrected';
                                line.errors = [];
                                correctedCount++;
                                log.push({ id: line.id, status: 'success', changes: lineChanges });
                            } else {
                                line.status = 'error';
                                line.errors = errors;
                                // Log partial fixes or if we tried but failed
                                if (lineChanges.length > 0 || originalContent !== line.content) {
                                     log.push({ id: line.id, status: 'partial', changes: lineChanges, remaining: errors });
                                }
                            }
                        }
                    });

                    validationData.stats.errors = validationData.lines.filter(l => l.status === 'error').length;
                    validationData.stats.corrected = validationData.lines.filter(l => l.status === 'corrected').length;
                    
                    updateStats();
                    renderLines();
                    showCorrectionModal(correctedCount, log);
                } catch (e) {
                    console.error(e);
                    alert('Erro ao executar correção automática: ' + e.message);
                }
            }

            function showCorrectionModal(count, log) {
                const summary = document.getElementById('correctionSummaryText');
                const container = document.getElementById('correctionLog');
                
                if (!summary || !container) {
                    alert(`${count} linhas corrigidas.`);
                    return;
                }

                summary.textContent = `${count} linhas foram totalmente corrigidas.`;
                container.innerHTML = '';
                
                if (log.length === 0 && count === 0) {
                    container.innerHTML = '<div class="list-group-item text-muted">Nenhuma alteração realizada. As linhas com erro possuem problemas que requerem intervenção manual (ex: campos obrigatórios vazios).</div>';
                } else if (log.length === 0) {
                     container.innerHTML = '<div class="list-group-item text-muted">Apenas espaços em branco foram removidos.</div>';
                } else {
                    log.forEach(item => {
                        const div = document.createElement('div');
                        div.className = 'list-group-item';
                        
                        let content = `<strong>Linha ${item.id}</strong>: `;
                        if (item.status === 'success') {
                            content += `<span class="text-success">Corrigida</span>. `;
                            content += item.changes.length ? item.changes.join(', ') : 'Espaços removidos.';
                        } else {
                            content += `<span class="text-warning">Parcialmente ajustada</span>. `;
                            content += item.changes.length ? item.changes.join(', ') + '. ' : '';
                            content += `<br><small class="text-danger">Erros restantes: ${item.remaining.join(', ')}</small>`;
                        }
                        
                        div.innerHTML = content;
                        container.appendChild(div);
                    });
                }

                try {
                    const modalEl = document.getElementById('correctionModal');
                    if (window.bootstrap) {
                        new bootstrap.Modal(modalEl).show();
                    } else {
                        // Fallback
                        const modal = new bootstrap.Modal(modalEl);
                        modal.show();
                    }
                } catch (e) {
                    console.error(e);
                    alert('Correção concluída. ' + count + ' linhas corrigidas.');
                }
            }

            function setTab(tab, btn) {
                currentTab = tab;
                
                // Update Buttons
                const buttons = btn.parentElement.querySelectorAll('button');
                buttons.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                
                // Update View Controls Visibility
                const viewControls = document.getElementById('viewControls');
                if (tab === 'errors') {
                    viewControls.classList.remove('d-none');
                } else {
                    viewControls.classList.add('d-none');
                }

                // Update Layout for Original File
                const listPanel = document.getElementById('listPanel');
                const editorPanel = document.getElementById('editorPanel');
                
                if (tab === 'original') {
                    listPanel.classList.remove('col-md-6');
                    listPanel.classList.add('col-12');
                    editorPanel.classList.add('d-none');
                } else {
                    listPanel.classList.remove('col-12');
                    listPanel.classList.add('col-md-6');
                    editorPanel.classList.remove('d-none');
                }

                renderLines();
            }

            function setViewMode(mode) {
                viewMode = mode;
                document.getElementById('viewListBtn').classList.toggle('active', mode === 'list');
                document.getElementById('viewGroupBtn').classList.toggle('active', mode === 'group');
                renderLines();
            }

            function setEditMode(mode) {
                editMode = mode;
                document.getElementById('btnDetailed').classList.toggle('active', mode === 'detailed');
                document.getElementById('btnDetailed').classList.toggle('text-muted', mode !== 'detailed');
                document.getElementById('btnQuick').classList.toggle('active', mode === 'quick');
                document.getElementById('btnQuick').classList.toggle('text-muted', mode !== 'quick');
                renderEditor();
            }

            function toggleEditor() {
                const panel = document.getElementById('editorPanel');
                const listPanel = document.getElementById('listPanel');
                const btnText = document.getElementById('toggleEditorText');
                
                if (panel.style.display === 'none') {
                    panel.style.display = 'block';
                    listPanel.classList.remove('col-md-12');
                    listPanel.classList.add('col-md-6');
                    btnText.textContent = 'Ocultar Editor';
                } else {
                    panel.style.display = 'none';
                    listPanel.classList.remove('col-md-6');
                    listPanel.classList.add('col-md-12');
                    btnText.textContent = 'Mostrar Editor';
                }
            }

            function updateStats() {
                document.getElementById('statTotal').textContent = validationData.stats.total;
                document.getElementById('statErrors').textContent = validationData.stats.errors;
                document.getElementById('statCorrected').textContent = validationData.stats.corrected;
                document.getElementById('statValid').textContent = validationData.stats.valid;
                
                document.getElementById('tabErrorCount').textContent = validationData.stats.errors;
                document.getElementById('tabValidCount').textContent = validationData.stats.valid;
                document.getElementById('tabCorrectedCount').textContent = validationData.stats.corrected;
            }

            // --- Export ---

            function downloadCorrected() {
                const content = validationData.lines.map(l => l.content).join('\n');
                const blob = new Blob([content], { type: 'text/plain' });
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = 'corrected_' + validationData.filename;
                a.click();
            }

            function exportPDF() {
                const { jsPDF } = window.jspdf;
                const doc = new jsPDF();

                doc.setFontSize(18);
                doc.text("Relatório de Validação R2D2", 10, 10);
                
                doc.setFontSize(12);
                doc.text(`Arquivo: ${validationData.filename}`, 10, 20);
                doc.text(`Total de Linhas: ${validationData.stats.total}`, 10, 30);
                doc.text(`Erros Encontrados: ${validationData.stats.errors}`, 10, 40);
                
                let y = 50;
                doc.setFontSize(10);
                
                const errors = validationData.lines.filter(l => l.status === 'error');
                errors.forEach((line, i) => {
                    if (y > 280) {
                        doc.addPage();
                        y = 10;
                    }
                    doc.setTextColor(255, 0, 0);
                    doc.text(`Linha ${line.id} (${line.recordType}): ${line.errors[0]}`, 10, y);
                    y += 7;
                });

                doc.save("relatorio_validacao.pdf");
            }

            // Initial Render with Loader
            setTimeout(() => {
                renderLines();
                const loader = document.getElementById('loadingOverlay');
                if (loader) loader.style.display = 'none';
            }, 100);
        </script>
    <?php endif; ?>

</div>

<!-- Correction Summary Modal -->
<div class="modal fade" id="correctionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Resumo da Correção Automática</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>
                    <span id="correctionSummaryText"></span>
                </div>
                <div class="list-group list-group-flush small overflow-auto" style="max-height: 400px;" id="correctionLog">
                    <!-- Logs go here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Entendi</button>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
