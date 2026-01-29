<?php
// --- CONTROLE DE VERSÃO ---
$appVersion = '1.0';

// --- LÓGICA DE VALIDAÇÃO ---
$validationResult = null;
$xmlContent = '';
$xsdContent = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Obter conteúdo do XML
    if (isset($_FILES['xml_file']) && $_FILES['xml_file']['error'] === UPLOAD_ERR_OK) {
        $xmlContent = file_get_contents($_FILES['xml_file']['tmp_name']);
    } elseif (!empty($_POST['xml_text'])) {
        $xmlContent = $_POST['xml_text'];
    }

    // 2. Obter conteúdo do XSD
    if (isset($_FILES['xsd_file']) && $_FILES['xsd_file']['error'] === UPLOAD_ERR_OK) {
        $xsdContent = file_get_contents($_FILES['xsd_file']['tmp_name']);
    } elseif (!empty($_POST['xsd_text'])) {
        $xsdContent = $_POST['xsd_text'];
    }

    // 3. Validar
    if ($xmlContent && $xsdContent) {
        // HACK: Ajustar schemaLocation do xmldsig para o arquivo local se estiver relativo
        // Isso permite que o XSD da NFS-e encontre a definição de assinatura sem precisar estar na mesma pasta física durante o upload
        $baseDir = str_replace('\\', '/', __DIR__);
        $xsdContent = str_replace(
            'schemaLocation="xmldsig-core-schema20020212.xsd"', 
            'schemaLocation="file:///' . $baseDir . '/validador_nfse/xmldsig-core-schema20020212.xsd"', 
            $xsdContent
        );

        libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        $dom->preserveWhiteSpace = false;
        
        // Tenta carregar o XML
        if (!$dom->loadXML($xmlContent)) {
            $validationResult = [
                'success' => false,
                'errors' => libxml_get_errors(),
                'message' => 'O XML fornecido é inválido (erro de sintaxe).'
            ];
            libxml_clear_errors();
        } else {
            // Tenta validar contra o XSD
            if ($dom->schemaValidateSource($xsdContent)) {
                $validationResult = [
                    'success' => true,
                    'message' => 'O XML é VÁLIDO de acordo com o Schema XSD fornecido.'
                ];
            } else {
                $validationResult = [
                    'success' => false,
                    'errors' => libxml_get_errors(),
                    'message' => 'O XML NÃO é válido de acordo com o Schema XSD.'
                ];
                libxml_clear_errors();
            }
        }
    } else {
        $validationResult = [
            'success' => false,
            'message' => 'Por favor, forneça tanto o XML quanto o XSD.'
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validador XML NFS-e - uTool</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;700&display=swap" rel="stylesheet">
    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        :root {
            /* Theme Variables matching Index */
            --glass-border: rgba(255, 255, 255, 0.5);
            --glass-highlight: rgba(255, 255, 255, 0.8);
            --glass-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.15);
            --color-body-bg-gradient: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            --color-text-main: #1e293b;
            --color-text-secondary: #64748b;
            --color-accent-purple: #8b5cf6;
            --color-accent-purple-dark: #7c3aed;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--color-body-bg-gradient);
            background-attachment: fixed;
            color: var(--color-text-main);
            height: 100vh;
            overflow: hidden; /* Prevent body scroll, use inner scroll */
        }

        /* Glass Cards */
        .glass-card {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.6);
            box-shadow: 0 8px 20px rgba(0,0,0,0.05);
            border-radius: 16px;
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .glass-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 28px rgba(0,0,0,0.1);
        }

        .glass-header {
            background: rgba(255, 255, 255, 0.5);
            border-bottom: 1px solid rgba(255, 255, 255, 0.3);
            padding: 1rem 1.5rem;
        }

        /* Editor Areas */
        .editor-container {
            position: relative;
            background: rgba(255, 255, 255, 0.5);
            border-radius: 12px;
            border: 1px solid rgba(0,0,0,0.08);
            transition: all 0.2s;
        }
        
        .editor-container:focus-within {
            background: #fff;
            box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.15);
            border-color: var(--color-accent-purple);
        }

        .editor-area {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.95rem;
            height: 100%;
            width: 100%;
            background: transparent;
            border: none;
            resize: none; /* Disable manual resize as it fills area */
            color: #334155;
            line-height: 1.6;
        }
        
        .editor-area:focus {
            outline: none;
            box-shadow: none;
            background: transparent;
        }

        /* Floating Badge */
        .floating-badge {
            position: absolute;
            top: 1rem;
            right: 1.5rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            opacity: 0.7;
            pointer-events: none;
        }

        /* Custom File Input */
        .file-upload-wrapper {
            position: relative;
            overflow: hidden;
            display: inline-block;
        }

        /* Alerts */
        .alert-modern {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }
        .alert-modern.alert-success {
            background: linear-gradient(to right, #d1fae5, #ecfdf5);
            color: #065f46;
        }
        .alert-modern.alert-danger {
            background: linear-gradient(to right, #fee2e2, #fef2f2);
            color: #991b1b;
        }

        /* Tables */
        .table-custom th {
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
            color: var(--color-text-secondary);
            border-bottom: 2px solid rgba(0,0,0,0.05);
        }
        .table-custom td {
            vertical-align: middle;
            font-size: 0.9rem;
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }

        /* Buttons */
        .btn-gradient-primary {
            background: linear-gradient(135deg, var(--color-accent-purple) 0%, var(--color-accent-purple-dark) 100%);
            color: white;
            border: none;
            box-shadow: 0 4px 12px rgba(124, 58, 237, 0.3);
            transition: all 0.3s;
        }
        .btn-gradient-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(124, 58, 237, 0.4);
            color: white;
        }

        .btn-glass {
            background: rgba(255, 255, 255, 0.8);
            border: 1px solid rgba(0,0,0,0.1);
            color: var(--color-text-main);
            transition: all 0.2s;
        }
        .btn-glass:hover {
            background: #fff;
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        }

        /* Scrollbar */
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: rgba(0,0,0,0.05); border-radius: 4px; }
        ::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.2); border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: rgba(0,0,0,0.3); }

    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg border-bottom border-white border-opacity-50" style="background: rgba(255,255,255,0.7); backdrop-filter: blur(10px);">
    <div class="container-fluid px-4">
        <a href="index.php" class="btn btn-outline-secondary btn-sm rounded-circle me-3 border-0 bg-light shadow-sm" style="width: 32px; height: 32px; padding: 0; display: flex; align-items: center; justify-content: center;" title="Voltar para Hub">
            <i class="bi bi-arrow-left"></i>
        </a>
        <a class="navbar-brand d-flex align-items-center gap-2 m-0" href="nfse_validator.php">
            <div class="bg-primary bg-gradient rounded-3 text-white d-flex align-items-center justify-content-center shadow-sm" style="width: 36px; height: 36px;">
                <i class="bi bi-shield-check"></i>
            </div>
            <div>
                <span class="fw-bold text-dark" style="letter-spacing: -0.5px;">Validador NFS-e</span>
                <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill fw-normal ms-2" style="font-size: 0.7rem;">v<?php echo $appVersion; ?></span>
            </div>
        </a>
    </div>
</nav>

    <!-- Layout Wrapper: Flex Column to fill 100vh -->
    <div class="d-flex flex-column vh-100 pt-3 pb-5">
        
        <!-- Results Section (Collapsible/Overlay logic could be better, but we'll keep it inline for now, just compacter) -->
        <?php if ($validationResult): ?>
            <div class="container-fluid px-4 mb-3 flex-shrink-0">
                 <div class="alert alert-modern <?php echo $validationResult['success'] ? 'alert-success' : 'alert-danger'; ?> py-2 px-3 d-flex align-items-center justify-content-between shadow-sm m-0" role="alert">
                    <div class="d-flex align-items-center">
                        <i class="bi <?php echo $validationResult['success'] ? 'bi-check-circle-fill' : 'bi-x-circle-fill'; ?> fs-5 me-2"></i>
                        <span class="fw-bold me-2"><?php echo $validationResult['success'] ? 'Válido' : 'Inválido'; ?>:</span>
                        <span class="small opacity-75 text-truncate" style="max-width: 600px;"><?php echo htmlspecialchars($validationResult['message']); ?></span>
                    </div>
                    <?php if (!$validationResult['success'] && !empty($validationResult['errors'])): ?>
                        <button class="btn btn-sm btn-light btn-glass border-0 py-0 px-2" type="button" data-bs-toggle="collapse" data-bs-target="#errorDetails" aria-expanded="false" title="Ver Detalhes">
                            <i class="bi bi-chevron-down"></i>
                        </button>
                    <?php endif; ?>
                </div>
                <!-- Error Details Collapse -->
                 <?php if (!$validationResult['success'] && !empty($validationResult['errors'])): ?>
                    <div class="collapse mt-2 position-absolute w-100 start-0 px-4" id="errorDetails" style="z-index: 1000; max-width: 100%;">
                        <div class="glass-card bg-white border-0 shadow-lg" style="max-height: 300px; overflow-y: auto;">
                            <div class="p-0 table-responsive">
                                <table class="table table-custom table-hover mb-0 sticky-top">
                                    <thead class="bg-light sticky-top">
                                        <tr><th class="ps-4 py-2" style="width: 80px;">Linha</th><th class="py-2">Erro</th></tr>
                                    </thead>
                                    <tbody class="bg-white">
                                        <?php foreach ($validationResult['errors'] as $error): ?>
                                            <tr><td class="ps-4"><span class="badge bg-danger bg-opacity-10 text-danger"><?php echo $error->line; ?></span></td><td class="font-monospace small text-danger py-2"><?php echo htmlspecialchars(trim($error->message)); ?></td></tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Main Split Form -->
        <form action="nfse_validator.php" method="post" enctype="multipart/form-data" class="container-fluid px-3 flex-grow-1 overflow-hidden d-flex flex-column">
            
            <div class="row h-100 g-3">
                
                <!-- XSD Column -->
                <div class="col-md-6 h-100">
                    <div class="glass-card h-100 d-flex flex-column border-primary border-opacity-25" style="border-width: 0 0 0 4px;">
                        <div class="glass-header py-2 px-3 d-flex justify-content-between align-items-center bg-white bg-opacity-25">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-primary bg-opacity-10 text-primary rounded-1"><i class="bi bi-filetype-xsd"></i> SCHEMA</span>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-xs btn-outline-primary rounded-pill px-2 py-0 fs-7" title="Formatar Código" onclick="formatEditor('xsd')">
                                    <i class="bi bi-code-slash"></i>
                                </button>
                                <div class="file-upload-wrapper">
                                    <label for="xsd_file" class="btn btn-xs btn-outline-secondary rounded-pill px-2 py-0 fs-7" title="Upload">
                                        <i class="bi bi-upload"></i>
                                    </label>
                                    <input type="file" name="xsd_file" id="xsd_file" accept=".xsd" class="d-none" onchange="loadFileContent(this, 'xsd_text')">
                                </div>
                            </div>
                        </div>
                        <div class="p-3 flex-grow-1 d-flex flex-column">
                            <div class="editor-container flex-grow-1 overflow-hidden">
                                 <!-- Monaco Editor Container -->
                                <div id="xsd_editor_container" class="w-100 h-100 rounded-3"></div>
                                <!-- Hidden input to store value for POST -->
                                <textarea name="xsd_text" id="xsd_text" class="d-none"><?php echo htmlspecialchars($xsdContent); ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- XML Column -->
                <div class="col-md-6 h-100">
                     <div class="glass-card h-100 d-flex flex-column border-success border-opacity-25" style="border-width: 0 0 0 4px;">
                        <div class="glass-header py-2 px-3 d-flex justify-content-between align-items-center bg-white bg-opacity-25">
                             <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-success bg-opacity-10 text-success rounded-1"><i class="bi bi-filetype-xml"></i> XML</span>
                            </div>
                             <div class="d-flex gap-2">
                                <button type="button" class="btn btn-xs btn-outline-success rounded-pill px-2 py-0 fs-7" title="Formatar Código" onclick="formatEditor('xml')">
                                    <i class="bi bi-code-slash"></i>
                                </button>
                                 <div class="file-upload-wrapper">
                                    <label for="xml_file" class="btn btn-xs btn-outline-secondary rounded-pill px-2 py-0 fs-7" title="Upload">
                                        <i class="bi bi-upload"></i>
                                    </label>
                                    <input type="file" name="xml_file" id="xml_file" accept=".xml" class="d-none" onchange="loadFileContent(this, 'xml_text')">
                                </div>
                            </div>
                        </div>
                        <div class="p-3 flex-grow-1 d-flex flex-column">
                            <div class="editor-container flex-grow-1 overflow-hidden">
                                <!-- Monaco Editor Container -->
                                <div id="xml_editor_container" class="w-100 h-100 rounded-3"></div>
                                <!-- Hidden input to store value for POST -->
                                <textarea name="xml_text" id="xml_text" class="d-none"><?php echo htmlspecialchars($xmlContent); ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        <!-- Float Action Button -->
        <div class="position-absolute bottom-0 start-50 translate-middle-x mb-4 z-3">
             <div class="glass-card p-1 d-flex gap-1 shadow-lg rounded-pill border border-white border-opacity-50">
                <button type="button" class="btn btn-glass border-0 rounded-pill px-3 py-2 text-danger" onclick="clearForms()" title="Limpar">
                    <i class="bi bi-trash3"></i>
                </button>
                <div class="vr my-2 opacity-25"></div>
                <button type="submit" class="btn btn-gradient-primary rounded-pill px-4 py-2 fw-bold d-flex align-items-center gap-2">
                    <span>VALIDAR</span> <i class="bi bi-play-circle-fill"></i>
                </button>
            </div>
        </div>

    </form>
</div>

<!-- Monaco Editor Loader -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.44.0/min/vs/loader.min.js"></script>


<!-- Scripts de Lógica Básica (Carregados Primeiro e Separados) -->
<script>
    // Inicializar variáveis globais
    var xsdEditor = null;
    var xmlEditor = null;

    function loadFileContent(input, textareaId) {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            const reader = new FileReader();

            reader.onload = function(e) {
                const content = e.target.result;
                
                // 1. Tenta atualizar o Monaco Editor se existir
                let editorUpdated = false;
                if (textareaId === 'xsd_text' && xsdEditor) {
                    try { xsdEditor.setValue(content); editorUpdated = true; } catch(e) { console.error(e); }
                } else if (textareaId === 'xml_text' && xmlEditor) {
                    try { xmlEditor.setValue(content); editorUpdated = true; } catch(e) { console.error(e); }
                }

                // 2. Tenta formatar se o editor foi atualizado
                if (editorUpdated) {
                    setTimeout(() => formatEditor(textareaId === 'xsd_text' ? 'xsd' : 'xml'), 500);
                }

                // 3. SEMPRE atualiza o textarea oculto/fallback
                const hiddenArea = document.getElementById(textareaId);
                if (hiddenArea) {
                    hiddenArea.value = content;
                    hiddenArea.classList.remove('d-none'); // Mostra se o Monaco falhar
                    if (editorUpdated) hiddenArea.classList.add('d-none'); // Esconde de volta se funcionou
                }
            }
            reader.readAsText(input.files[0]);
        }
    }

    function formatEditor(type) {
        try {
            const editor = (type === 'xsd') ? xsdEditor : xmlEditor;
            if (editor) {
                // Tenta formatar nativamente do Monaco
                const action = editor.getAction('editor.action.formatDocument');
                
                // Se for XML, força nosso formatador manual primeiro porque o nativo do Monaco CDN pode falhar
                if (type === 'xml') {
                     const val = editor.getValue();
                     // Só aplica se não estiver formatado (poucas linhas) ou se o usuário pediu
                     editor.setValue(formatXml(val));
                } 
                // Para XSD ou fallback
                else {
                    if (action) {
                        action.run().then(() => {
                            // Sucesso, mas verifica se mudou algo
                            if (editor.getValue().split('\n').length < 5) {
                                editor.setValue(formatXml(editor.getValue()));
                            }
                        }).catch(() => {
                            editor.setValue(formatXml(editor.getValue()));
                        });
                    } else {
                        editor.setValue(formatXml(editor.getValue()));
                    }
                }
            }
        } catch(e) {
            console.error('Erro ao formatar:', e);
            try {
                 const editor = (type === 'xsd') ? xsdEditor : xmlEditor;
                 editor.setValue(formatXml(editor.getValue()));
            } catch(z) {
                alert('Não foi possível formatar automaticamente.');
            }
        }
    }

    // Função auxiliar para formatar XML manualmente (Pretty Print)
    function formatXml(xml) {
        if (!xml) return '';
        
        var formatted = '';
        // Regex mais permissiva para encontrar junções de tags
        var reg = /(>)\s*(<)(\/*)/g;
        xml = xml.replace(reg, '$1\r\n$2$3');
        var pad = 0;
        
        var lines = xml.split(/\r?\n/);
        
        lines.forEach(function(node) {
            node = node.trim();
            if (!node) return;

            var indent = 0;
            // 1. Tag de fechamento e abertura na mesma linha: <tag>content</tag> -> Mantém nível
            if (node.match( /.+<\/\w[^>]*>$/ )) {
                indent = 0;
            } 
            // 2. Tag de fechamento: </tag> -> Recua antes de imprimir
            else if (node.match( /^<\/\w/ )) {
                if (pad != 0) pad -= 1;
            } 
            // 3. Tag de abertura: <tag> -> Avança para a próxima (exceto <?xml e <!DOCTYPE)
            else if (node.match( /^<\w[^>]*[^\/]>.*$/ ) && !node.startsWith('<?') && !node.startsWith('<!')) {
                indent = 1;
            } 
            // 4. Se for tag de auto-fechamento <tag /> ou conteúdo solto -> Mantém nível
            else {
                indent = 0;
            }

            var padding = '';
            for (var i = 0; i < pad; i++) {
                padding += '  ';
            }

            formatted += padding + node + '\r\n';
            
            // Aplica o indent para a próxima linha
            pad += indent;
        });

        return formatted.trim();
    }

    function clearForms() {
        if(confirm('Tem certeza que deseja limpar todos os campos?')) {
            try {
                if(xsdEditor) xsdEditor.setValue('');
                if(xmlEditor) xmlEditor.setValue('');
                if(xmlEditor) monaco.editor.setModelMarkers(xmlEditor.getModel(), "owner", []);
            } catch(e) { console.error(e); }
            
            document.getElementById('xsd_text').value = '';
            document.getElementById('xml_text').value = '';
        }
    }
</script>

<!-- Monaco Editor Loader -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.44.0/min/vs/loader.min.js"></script>

<script>
    if (typeof require !== 'undefined') {
        require.config({ paths: { 'vs': 'https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.44.0/min/vs' }});

        // Pega os erros do PHP para usar no JS
        const validationErrors = <?php echo isset($validationResult['errors']) ? json_encode($validationResult['errors']) : '[]'; ?>;

        require(['vs/editor/editor.main'], function() {
            try {
                // Inicializa XSD Editor
                xsdEditor = monaco.editor.create(document.getElementById('xsd_editor_container'), {
                    value: document.getElementById('xsd_text').value,
                    language: 'xml',
                    theme: 'vs-light',
                    automaticLayout: true,
                    minimap: { enabled: false },
                    fontSize: 13,
                    scrollBeyondLastLine: false,
                    roundedSelection: true,
                    wordWrap: 'on',
                    padding: { top: 10, bottom: 10 }
                });

                // Inicializa XML Editor
                xmlEditor = monaco.editor.create(document.getElementById('xml_editor_container'), {
                    value: document.getElementById('xml_text').value,
                    language: 'xml',
                    theme: 'vs-light',
                    automaticLayout: true,
                    minimap: { enabled: false },
                    fontSize: 13,
                    scrollBeyondLastLine: false,
                    roundedSelection: true,
                    wordWrap: 'on',
                    padding: { top: 10, bottom: 10 }
                });
                
                // Se carregou com sucesso, esconde os textareas de fallback
                document.getElementById('xsd_text').classList.add('d-none');
                document.getElementById('xml_text').classList.add('d-none');

                // Formatação Automática robusta
                const formatEditors = () => {
                    try {
                        if (xsdEditor && xsdEditor.getValue().trim()) {
                            const action = xsdEditor.getAction('editor.action.formatDocument');
                            if (action) action.run();
                        }
                        if (xmlEditor && xmlEditor.getValue().trim()) {
                            const action = xmlEditor.getAction('editor.action.formatDocument');
                            if (action) action.run();
                        }
                    } catch(e) {}
                };

                setTimeout(formatEditors, 1000);

                // Aplica decorações de erro se houver
                if (validationErrors.length > 0) {
                    const markers = validationErrors.map(err => {
                        return {
                            startLineNumber: parseInt(err.line),
                            startColumn: 1,
                            endLineNumber: parseInt(err.line),
                            endColumn: 1000,
                            message: err.message,
                            severity: monaco.MarkerSeverity.Error
                        };
                    });
                    if (xmlEditor) {
                        try {
                            monaco.editor.setModelMarkers(xmlEditor.getModel(), "owner", markers);
                            if(markers.length > 0) xmlEditor.revealLineInCenter(markers[0].startLineNumber);
                        } catch(e) {}
                    }
                }

                // Sync editors with hidden textareas before submit
                document.querySelector('form').addEventListener('submit', function() {
                    try {
                        if (xsdEditor) document.getElementById('xsd_text').value = xsdEditor.getValue();
                        if (xmlEditor) document.getElementById('xml_text').value = xmlEditor.getValue();
                    } catch(e) {}
                });
            } catch (err) {
                console.error("Erro ao inicializar Monaco:", err);
                alert("Atenção: O editor avançado não pôde ser carregado. Usando modo básico.");
                document.getElementById('xsd_text').classList.remove('d-none');
                document.getElementById('xml_text').classList.remove('d-none');
            }
        });
    } else {
        console.warn("Monaco Loader (require.js) não foi carregado.");
        // Fallback: mostra os textareas originais se o script do monaco falhar
        document.getElementById('xsd_text').classList.remove('d-none');
        document.getElementById('xml_text').classList.remove('d-none');
        document.getElementById('xsd_text').style.height = '100%';
        document.getElementById('xml_text').style.height = '100%';
    }
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
