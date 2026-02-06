<?php
session_start();
require_once 'includes/header.php';

$jsonFile = __DIR__ . '/data/release_notes.json';
$isAdmin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;

// --- Helper Functions ---
function loadData($file) {
    if (!file_exists($file)) return [];
    $data = json_decode(file_get_contents($file), true);
    return is_array($data) ? $data : [];
}

function saveData($file, $data) {
    // Sort versions by version number desc
    usort($data, function($a, $b) {
        return version_compare($b['version'], $a['version']);
    });
    if (file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) === false) {
        global $message, $messageType;
        $message = "Erro ao salvar arquivo de dados (permissão negada?).";
        $messageType = "danger";
        return false;
    }
    return true;
}

function generateId() {
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}

function parseHtmlDescription($node) {
    $text = '';
    foreach ($node->childNodes as $child) {
        if ($child->nodeType === XML_TEXT_NODE) {
            $text .= trim($child->textContent);
        } elseif ($child->nodeName === 'p') {
            $text .= trim($child->textContent) . "\n\n";
        } elseif ($child->nodeName === 'ul') {
            foreach ($child->childNodes as $li) {
                if ($li->nodeName === 'li') {
                    $text .= "- " . trim($li->textContent) . "\n";
                }
            }
            $text .= "\n";
        } elseif ($child->nodeName === 'br') {
            $text .= "\n";
        } else {
            $text .= trim($child->textContent);
        }
    }
    return trim($text);
}

// --- Handle Actions ---
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Login
    if ($action === 'login') {
        $user = trim($_POST['user'] ?? '');
        $pass = trim($_POST['pass'] ?? '');
        
        // Credentials from nfse-nacional.php
        if ($user === 'administrador' && $pass === 'S9T"jR<@d78t') {
            $_SESSION['is_admin'] = true;
            header("Location: release_notes.php");
            exit;
        } else {
            $message = "Credenciais inválidas.";
            $messageType = "danger";
        }
    }

    // Logout
    if ($action === 'logout') {
        unset($_SESSION['is_admin']);
        header("Location: release_notes.php");
        exit;
    }

    // Admin Actions
    if ($isAdmin) {
        $data = loadData($jsonFile);

                if ($action === 'import_html') {
            $htmlContent = $_POST['html_content'] ?? '';
            $url = $_POST['import_url'] ?? '';

            if (!empty($url)) {
                // Try to fetch content from URL
                $context = stream_context_create([
                    "http" => [
                        "header" => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36\r\n"
                    ]
                ]);
                
                $fetchedContent = @file_get_contents($url, false, $context);
                
                if ($fetchedContent !== false) {
                    $htmlContent = $fetchedContent;
                } else {
                    $message = "Erro ao buscar conteúdo da URL. Verifique se o endereço está correto e acessível.";
                    $messageType = "danger";
                }
            }
            
            if (!empty($htmlContent)) {
                // Debug Log
                file_put_contents('data/debug_import.txt', "--- New Import " . date('Y-m-d H:i:s') . " ---\n", FILE_APPEND);

                $dom = new DOMDocument();
                libxml_use_internal_errors(true);
                
                // Force UTF-8 using mb_convert_encoding which handles special chars better for DOMDocument
                // This converts chars to HTML entities (e.g. ã becomes &atilde;)
                $htmlEncoded = mb_convert_encoding($htmlContent, 'HTML-ENTITIES', 'UTF-8');
                
                $dom->loadHTML($htmlEncoded, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD); 
                libxml_clear_errors();

                file_put_contents('data/debug_import.txt', "HTML Loaded. Length: " . strlen($htmlContent) . "\n", FILE_APPEND);

                $xpath = new DOMXPath($dom);

                // Extract Version
                $version = 'Nova Versão';
                $h1 = $xpath->query('//h1');
                if ($h1->length > 0) {
                    $h1Text = $h1->item(0)->textContent;
                    file_put_contents('data/debug_import.txt', "Found H1: " . $h1Text . "\n", FILE_APPEND);
                    // Try to extract "6.12.22" from "Versão 6.12.22-interno"
                    if (preg_match('/Versão\s+([\d\.]+)/i', $h1Text, $matches)) {
                        $version = $matches[1];
                    } else {
                        $version = trim($h1Text);
                    }
                } else {
                    file_put_contents('data/debug_import.txt', "No H1 found.\n", FILE_APPEND);
                }

                // Extract Notes from Table
                $rows = $xpath->query('//table//tr');
                file_put_contents('data/debug_import.txt', "Found Table Rows: " . $rows->length . "\n", FILE_APPEND);
                $newNotes = [];
                
                foreach ($rows as $index => $row) {
                    if ($index === 0) continue; // Skip header

                    $cols = $xpath->query('.//td', $row);
                    if ($cols->length >= 4) { // Ensure enough columns
                        
                        // Mapping based on user provided HTML:
                        // 0: Tema, 1: Tipo, 2: Escopo, 3: Descrição, 4: Referência, 5: Observação
                        
                        $theme = trim($cols->item(0)->textContent);
                        $type = trim($cols->item(1)->textContent);
                        $scope = trim($cols->item(2)->textContent);
                        
                        // Description (Handle HTML)
                        $descNode = $cols->item(3);
                        $description = parseHtmlDescription($descNode);

                        // Ref
                        $ref = '';
                        if ($cols->length > 4) {
                            $ref = trim($cols->item(4)->textContent);
                        }

                        // Observation
                        $observation = '';
                        if ($cols->length > 5) {
                            $observation = trim($cols->item(5)->textContent);
                        }

                        $newNotes[] = [
                            'id' => generateId(),
                            'theme' => $theme,
                            'scope' => $scope,
                            'type' => $type,
                            'description' => $description,
                            'observation' => $observation,
                            'ref' => $ref
                        ];
                    }
                }

                if (!empty($newNotes)) {
                    $newVersionEntry = [
                        'id' => generateId(),
                        'version' => $version,
                        'date' => date('Y-m-d'), // Default to today
                        'title' => 'Importado via HTML',
                        'description' => 'Versão importada automaticamente.',
                        'tags' => [],
                        'download_desktop' => 'https://builds.unipluscdn.com/uniplusdesktop/',
                        'download_web' => 'https://builds.unipluscdn.com/uniplusweb/',
                        'notes' => $newNotes
                    ];
                    array_unshift($data, $newVersionEntry);
                    saveData($jsonFile, $data);
                    $message = "Importação realizada com sucesso! " . count($newNotes) . " notas encontradas.";
                    $messageType = "success";
                } else {
                    $message = "Nenhuma nota encontrada. Se estiver usando URL, verifique se a página é pública (sem login). Caso contrário, use a aba 'Colar HTML'.";
                    $messageType = "warning";
                }
            }
        } elseif ($action === 'add_version') {
            // Handle Tags
            $tags = [];
            if (!empty($_POST['tags'])) {
                $tags = array_map('trim', explode(',', $_POST['tags']));
            }

            $newVersion = [
                'id' => generateId(),
                'version' => $_POST['version'],
                'date' => $_POST['date'],
                'title' => $_POST['title'] ?? '',
                'description' => $_POST['description'] ?? '',
                'tags' => $tags,
                'download_desktop' => $_POST['download_desktop'] ?? '',
                'download_web' => $_POST['download_web'] ?? '',
                'notes' => []
            ];
            array_unshift($data, $newVersion);
            saveData($jsonFile, $data);
            $message = "Versão adicionada com sucesso!";
            $messageType = "success";

        } elseif ($action === 'edit_version') {
            $id = $_POST['version_id'];
            foreach ($data as &$ver) {
                if ($ver['id'] === $id) {
                    $ver['version'] = $_POST['version'];
                    $ver['date'] = $_POST['date'];
                    $ver['title'] = $_POST['title'] ?? '';
                    $ver['description'] = $_POST['description'] ?? '';
                    $ver['download_desktop'] = $_POST['download_desktop'] ?? '';
                    $ver['download_web'] = $_POST['download_web'] ?? '';
                    
                    if (!empty($_POST['tags'])) {
                        $ver['tags'] = array_map('trim', explode(',', $_POST['tags']));
                    } else {
                        $ver['tags'] = [];
                    }
                    break;
                }
            }
            saveData($jsonFile, $data);
            $message = "Versão atualizada!";
            $messageType = "success";

        } elseif ($action === 'delete_version') {
            $id = $_POST['version_id'];
            $data = array_filter($data, function($v) use ($id) { return $v['id'] !== $id; });
            saveData($jsonFile, array_values($data));
            $message = "Versão removida.";
            $messageType = "success";

        } elseif ($action === 'add_note') {
            $targetVersionId = $_POST['version_id'];
            $noteAdded = false;

            foreach ($data as &$ver) {
                if ($ver['id'] === $targetVersionId) {
                    $ver['notes'][] = [
                        'id' => generateId(),
                        'theme' => $_POST['theme'] ?? '',
                        'scope' => $_POST['scope'],
                        'type' => $_POST['type'],
                        'description' => $_POST['description'],
                        'observation' => $_POST['observation'] ?? '',
                        'ref' => $_POST['ref']
                    ];
                    $noteAdded = true;
                    break;
                }
            }
            
            if ($noteAdded) {
                if (saveData($jsonFile, $data)) {
                    $message = "Nota adicionada!";
                    $messageType = "success";
                }
            } else {
                $message = "Erro: Versão não encontrada.";
                $messageType = "danger";
            }

        } elseif ($action === 'edit_note') {
            $versionId = $_POST['version_id']; // Original version ID
            $newVersionId = $_POST['new_version_id']; // Target version ID (might be same)
            $noteId = $_POST['note_id'];
            
            $noteToMove = null;

            // 1. Find and remove note from old version
            foreach ($data as &$ver) {
                if ($ver['id'] === $versionId) {
                    foreach ($ver['notes'] as $k => $note) {
                        if ($note['id'] === $noteId) {
                            $noteToMove = $note;
                            // Update fields
                            $noteToMove['theme'] = $_POST['theme'] ?? '';
                            $noteToMove['scope'] = $_POST['scope'];
                            $noteToMove['type'] = $_POST['type'];
                            $noteToMove['description'] = $_POST['description'];
                            $noteToMove['observation'] = $_POST['observation'] ?? '';
                            $noteToMove['ref'] = $_POST['ref'];
                            
                            unset($ver['notes'][$k]);
                            $ver['notes'] = array_values($ver['notes']);
                            break 2;
                        }
                    }
                }
            }

            // 2. Add to new version (or back to old if same)
            if ($noteToMove) {
                foreach ($data as &$ver) {
                    if ($ver['id'] === $newVersionId) {
                        $ver['notes'][] = $noteToMove;
                        break;
                    }
                }
                saveData($jsonFile, $data);
                $message = "Nota atualizada!";
                $messageType = "success";
            } else {
                $message = "Erro: Nota não encontrada.";
                $messageType = "danger";
            }

        } elseif ($action === 'delete_note') {
            $versionId = $_POST['version_id'];
            $noteId = $_POST['note_id'];
            foreach ($data as &$ver) {
                if ($ver['id'] === $versionId) {
                    $ver['notes'] = array_filter($ver['notes'], function($n) use ($noteId) { return $n['id'] !== $noteId; });
                    $ver['notes'] = array_values($ver['notes']);
                    break;
                }
            }
            saveData($jsonFile, $data);
            $message = "Nota removida.";
            $messageType = "success";
        }
    }
}

$versions = loadData($jsonFile);
// Ensure display is sorted by version desc
usort($versions, function($a, $b) {
    return version_compare($b['version'], $a['version']);
});
?>

</div> <!-- Close header container -->
<div class="container-fluid px-4 pt-3 pb-0 bg-light min-vh-100">

    <div class="row g-4 align-items-center mb-4">
        <div class="col-md-6">
            <div class="d-inline-flex align-items-center gap-3 user-select-none">
                <div class="bg-gradient-primary-to-secondary text-white rounded-4 d-flex align-items-center justify-content-center shadow-lg"
                    style="width: 56px; height: 56px; background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);">
                    <i class="bi bi-file-earmark-text-fill fs-2"></i>
                </div>
                <div>
                    <h1 class="display-6 fw-bold mb-0 text-dark" style="font-family: 'Outfit', sans-serif;">
                        Release <span class="text-primary">Notes Uniplus</span>
                    </h1>
                    <p class="text-muted small mb-0 fw-medium opacity-75">Histórico de atualizações e melhorias.</p>
                </div>
            </div>
        </div>
        <div class="col-md-6 d-flex justify-content-end gap-2">
            <?php if ($isAdmin): ?>
                <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#importModal">
                    <i class="bi bi-filetype-html me-2"></i>Importar HTML
                </button>
                <button class="btn btn-primary btn-sm" onclick="openAddNoteModal()">
                    <i class="bi bi-plus-lg me-2"></i>Adicionar Nota
                </button>
                <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addVersionModal">
                    <i class="bi bi-folder-plus me-2"></i>Nova Versão
                </button>
                <form method="POST" class="d-inline">
                    <input type="hidden" name="action" value="logout">
                    <button type="submit" class="btn btn-outline-danger btn-sm">
                        <i class="bi bi-box-arrow-right me-2"></i>Sair
                    </button>
                </form>
            <?php else: ?>
                <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#loginModal">
                    <i class="bi bi-shield-lock me-2"></i>Admin
                </button>
            <?php endif; ?>
            <a href="index.php" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-2"></i>Voltar
            </a>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
            <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- PREMIUM DARK THEME SYSTEM -->
    <!-- HUB THEME SYSTEM -->
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@400;500;600;700&display=swap');

        :root {
            /* HUB ALIGNMENT VARS */
            --bg-body: #f3f5f9;
            --bg-body-gradient: linear-gradient(135deg, #f0f4f8 0%, #dbeafe 100%);
            
            --bg-surface: rgba(255, 255, 255, 0.85);
            --bg-sidebar: rgba(255, 255, 255, 0.85);
            
            /* Modern Palette */
            --primary: #3b82f6; /* Blue 500 */
            --primary-dark: #2563eb; /* Blue 600 */
            --primary-light: #eff6ff; /* Blue 50 */
            
            --secondary: #64748b; /* Slate 500 */
            --text-main: #0f172a; /* Slate 900 */
            --text-muted: #64748b; /* Slate 500 */
            
            --border-color: #e2e8f0; /* Slate 200 */
            --border-hover: #cbd5e1; /* Slate 300 */
            
            /* Glass & Shadows */
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
            --glass-bg: rgba(255, 255, 255, 0.9);
            --glass-border: rgba(255, 255, 255, 0.5);
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background: var(--bg-body-gradient) !important;
            background-attachment: fixed !important;
            color: var(--text-main) !important;
            line-height: 1.6;
        }

        h1, h2, h3, h4, h5, h6 {
            color: var(--text-main);
            letter-spacing: -0.025em;
        }

        /* --- MODERN DASHBOARD CARDS -- PREMIUM --- */
        .stat-card {
            background: #fff;
            border: none;
            border-radius: 20px;
            padding: 1.5rem;
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: 100%;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.025);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        /* Gradients */
        .card-gradient-blue { margin-bottom:0; background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%); border: 1px solid #bfdbfe; }
        .card-gradient-green { margin-bottom:0; background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border: 1px solid #bbf7d0; }
        .card-gradient-purple { margin-bottom:0; background: linear-gradient(135deg, #f5f3ff 0%, #ede9fe 100%); border: 1px solid #ddd6fe; }
        .card-gradient-red { margin-bottom:0; background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%); border: 1px solid #fecaca; }

        .stat-value {
            font-size: 2.75rem;
            font-weight: 800;
            letter-spacing: -0.03em;
            color: var(--text-main);
            line-height: 1;
            margin: 0.5rem 0;
            font-family: 'Outfit', sans-serif;
            position: relative;
            z-index: 2;
        }

        .stat-label {
            font-size: 0.85rem;
            color: var(--text-muted);
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            position: relative;
            z-index: 2;
        }

        .stat-icon {
            position: absolute;
            right: -10px;
            bottom: -10px;
            font-size: 5rem;
            opacity: 0.15;
            transform: rotate(-10deg);
            z-index: 1;
            transition: all 0.3s ease;
        }

        .stat-card:hover .stat-icon {
            transform: rotate(0deg) scale(1.1);
            opacity: 0.2;
        }

        .stat-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            background: rgba(255,255,255,0.6);
            backdrop-filter: blur(4px);
            margin-bottom: 0.75rem;
            width: fit-content;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
            z-index: 2;
            position: relative;
        }

        /* --- FILTERS TOOLBAR --- */
        .filter-toolbar {
            background: var(--bg-surface);
            border-radius: 12px;
            padding: 1rem;
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-sm);
            margin-bottom: 2rem;
            position: sticky;
            top: 1rem;
            z-index: 50;
        }
        
        .form-control, .form-select {
            border-radius: 8px;
            border: 1px solid var(--border-color);
            padding: 0.6rem 1rem;
            font-size: 0.95rem;
            transition: all 0.2s;
            background-color: #f8fafc;
        }
        
        .form-control:focus, .form-select:focus {
            background-color: #fff;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px var(--primary-light);
            outline: none;
        }

        /* --- VERSION CARDS --- */
        .version-section .card {
            border: 1px solid var(--border-color) !important;
            border-radius: 16px !important;
            box-shadow: var(--shadow-sm) !important;
            overflow: hidden;
            background: var(--bg-surface) !important;
            transition: box-shadow 0.3s;
            margin-bottom: 2rem !important;
        }
        
        .version-section .card:hover {
            box-shadow: var(--shadow-md) !important;
        }

        .version-card-header {
            background: #ffffff !important;
            border-bottom: 1px solid var(--border-color) !important;
            padding: 1.5rem !important;
        }

        .version-badge {
            background: var(--primary-light);
            color: var(--primary-dark);
            padding: 0.35em 0.8em;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.85rem;
            letter-spacing: 0.01em;
        }

        /* --- TABLE STYLES --- */
        .table-responsive {
            border-radius: 0 0 16px 16px;
        }

        .table {
            margin-bottom: 0;
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }
        
        .table th {
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            background: #f8fafc !important;
            border-bottom: 1px solid var(--border-color) !important;
            padding: 1rem 1.5rem;
        }
        
        .table td {
            vertical-align: middle;
            padding: 0.45rem 0.8rem;
            border-bottom: 1px solid var(--border-color);
            color: var(--text-main);
            background: #fff;
        }
        
        .table tr:last-child td {
            border-bottom: none;
        }
        
        .table-hover tbody tr:hover td {
            background-color: #f1f5f9;
        }

        /* Type Badges */
        .badge-type {
            padding: 0.4em 0.8em;
            border-radius: 6px;
            font-weight: 500;
            font-size: 0.8rem;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
        }
        
        .type-melhoria { background: #ecfdf5; color: #059669; border: 1px solid #d1fae5; }
        .type-correcao { background: #fef2f2; color: #dc2626; border: 1px solid #fee2e2; }
        .type-nova { background: #eff6ff; color: #2563eb; border: 1px solid #dbeafe; }
        .type-ajuste { background: #fffbeb; color: #d97706; border: 1px solid #fef3c7; }

        /* Links */
        .ref-link {
            font-family: monospace;
            background: #f1f5f9;
            padding: 2px 6px;
            border-radius: 4px;
            color: var(--text-muted);
            text-decoration: none;
            font-size: 0.85rem;
            transition: all 0.2s;
            display: inline-block;
        }
        .ref-link:hover {
            background: var(--primary-light);
            color: var(--primary);
        }

        /* Sidebar */
        #sidebarCol {
            position: sticky;
            top: 20px;
        }

        #versionSidebar .list-group-item {
            border: none;
            border-left: 3px solid transparent;
            margin-bottom: 4px;
            border-radius: 0 8px 8px 0;
            color: var(--text-muted);
            font-weight: 500;
            transition: all 0.2s;
            padding: 0.8rem 1rem;
        }
        
        #versionSidebar .list-group-item:hover {
            background: #f8fafc;
            color: var(--text-main);
        }
        
        #versionSidebar .list-group-item.active {
            background: var(--primary-light);
            color: var(--primary);
            border-left-color: var(--primary);
            font-weight: 600;
        }

        .note-desc { 
            font-size: 1rem; 
            color: #334155; 
            line-height: 1.7;
        }
        
        /* Loading Overlay */
        #loadingOverlay.show { display: flex !important; }

        /* Buttons */
        .btn-primary {
            background-color: var(--primary);
            border-color: var(--primary);
        }
        .btn-primary:hover {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
        }
        .btn-outline-secondary {
            color: var(--secondary);
            border-color: #cbd5e1;
        }
        .btn-outline-secondary:hover {
            background-color: #f1f5f9;
            color: var(--text-main);
        }

    </style>
    
    <script>
        document.addEventListener('DOMContentLoaded', () => {
             // Aggressively remove conflicting classes
            const cleanClasses = ['bg-light', 'bg-white', 'text-dark', 'table-light'];
            cleanClasses.forEach(cls => {
                document.querySelectorAll('.' + cls).forEach(el => el.classList.remove(cls));
            });
            document.body.classList.remove('bg-light');
        });
    </script>
    
    <div class="container-fluid px-4 py-3 min-vh-100">
    <?php
    // Calculate Stats
    $stats = [
        'total_versions' => count($versions),
        'total_notes' => 0,
        'types' => [],
        'scopes' => ['Desktop' => 0, 'Web' => 0]
    ];
    foreach ($versions as $v) {
        if (!empty($v['notes'])) {
            $stats['total_notes'] += count($v['notes']);
            foreach ($v['notes'] as $n) {
                $type = $n['type'] ?? 'Outros';
                $scope = $n['scope'] ?? 'Geral';
                $stats['types'][$type] = ($stats['types'][$type] ?? 0) + 1;
                
                if (stripos($scope, 'Desktop') !== false) $stats['scopes']['Desktop']++;
                if (stripos($scope, 'Web') !== false) $stats['scopes']['Web']++;
            }
        }
    }
    ?>

    <!-- Stats Dashboard -->
    <div class="row g-4 mb-4">
        <div class="col-md-2">
            <div class="stat-card card-gradient-blue">
                <div class="stat-badge text-primary"><i class="bi bi-layers-fill me-1"></i> Histórico</div>
                <div class="stat-value"><?php echo $stats['total_versions']; ?></div>
                <div class="stat-label">Total de Versões</div>
                <i class="bi bi-layers stat-icon text-primary"></i>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="stat-card card-gradient-green">
                <div class="stat-badge text-success"><i class="bi bi-file-earmark-check-fill me-1"></i> Changelog</div>
                <div class="stat-value"><?php echo $stats['total_notes']; ?></div>
                <div class="stat-label">Total de Notas</div>
                <i class="bi bi-list-check stat-icon text-success"></i>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card card-gradient-purple">
                <div class="stat-badge" style="color: #7c3aed"><i class="bi bi-laptop me-1"></i> Plataformas</div>
                <div class="stat-value" style="font-size: 2.2rem;">
                    <?php echo $stats['scopes']['Desktop']; ?>
                    <span class="text-muted opacity-25 fw-light">/</span>
                    <?php echo $stats['scopes']['Web']; ?>
                </div>
                <div class="stat-label">Desktop / Web</div>
                <i class="bi bi-pc-display stat-icon" style="color: #7c3aed"></i>
            </div>
        </div>

        <div class="col-md-4">
            <div class="stat-card card-gradient-red">
                <div class="stat-badge text-danger"><i class="bi bi-tools me-1"></i> Manutenção</div>
                <div class="d-flex align-items-baseline gap-3">
                    <div class="stat-value text-danger"><?php echo $stats['types']['Correção'] ?? 0; ?></div>
                    <div class="d-flex flex-column align-items-start">
                        <span class="badge bg-white text-success shadow-sm border border-success border-opacity-25 px-2 py-1 rounded-pill">
                            <i class="bi bi-arrow-up-right"></i> <?php echo $stats['types']['Melhoria'] ?? 0; ?> Melhorias
                        </span>
                    </div>
                </div>
                <div class="stat-label">Correções Realizadas</div>
                <i class="bi bi-bug stat-icon text-danger"></i>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Sidebar: Versions List -->
        <div class="col-md-2 mb-4" id="sidebarCol">
            <div class="card shadow-sm sticky-top" style="top: 20px; max-height: 80vh; overflow-y: auto;">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0 fw-bold">Versões</h6>
                        <button class="btn btn-sm btn-link text-muted p-0" onclick="toggleSidebar()"><i class="bi bi-chevron-left"></i></button>
                    </div>
                    <input type="text" id="sidebarSearch" class="form-control form-control-sm" placeholder="Filtrar versões...">
                </div>
                <div class="list-group list-group-flush" id="versionSidebar">
                    <?php foreach ($versions as $ver): ?>
                        <a href="#v-<?php echo $ver['id']; ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center sidebar-link" data-target="v-<?php echo $ver['id']; ?>">
                            <div>
                                <div class="fw-bold"><?php echo htmlspecialchars($ver['version']); ?></div>
                                <div class="small text-muted"><?php echo date('d/m/Y', strtotime($ver['date'])); ?></div>
                            </div>
                            <span class="badge bg-secondary bg-opacity-10 text-secondary border"><?php echo count($ver['notes'] ?? []); ?></span>
                        </a>
                    <?php endforeach; ?>
                    <?php if (empty($versions)): ?>
                        <div class="list-group-item text-muted small text-center py-4">Nenhuma versão cadastrada.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-md-10" id="mainCol">
            
            <div class="d-flex mb-3">
                <button class="btn btn-outline-secondary btn-sm me-2 d-none" id="btnShowSidebar" onclick="toggleSidebar()">
                    <i class="bi bi-layout-sidebar"></i> Mostrar Menu
                </button>
            </div>
            
            <!-- Filters Toolbar -->
            <div class="filter-toolbar d-flex flex-wrap gap-3 align-items-center mb-4 sticky-top" style="top: 20px;">
                <div class="flex-grow-1" style="min-width: 250px;">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control border-start-0 ps-0" id="searchInput" placeholder="Pesquisar versões, notas ou temas...">
                    </div>
                </div>
                
                <div style="min-width: 180px;">
                    <select class="form-select" id="filterTheme">
                        <option value="">Todos os Temas</option>
                        <?php 
                            $themes = [];
                            foreach ($versions as $v) foreach ($v['notes'] as $n) if(!empty($n['theme'])) $themes[] = $n['theme'];
                            $themes = array_unique($themes);
                            sort($themes);
                            foreach ($themes as $t) echo "<option value='" . htmlspecialchars($t) . "'>" . htmlspecialchars($t) . "</option>";
                        ?>
                    </select>
                </div>

                <div style="min-width: 180px;">
                    <select class="form-select" id="filterScope">
                        <option value="">Todos os Escopos</option>
                        <?php 
                            $scopes = [];
                            foreach ($versions as $v) {
                                foreach ($v['notes'] as $n) {
                                    if (!empty($n['scope'])) {
                                        if (strlen($n['scope']) < 40 && substr_count($n['scope'], ' ') < 4) {
                                            $scopes[] = $n['scope'];
                                        }
                                    }
                                }
                            }
                            $scopes = array_unique($scopes);
                            sort($scopes);
                            foreach ($scopes as $s) echo "<option value='" . htmlspecialchars($s) . "'>" . htmlspecialchars($s) . "</option>";
                        ?>
                    </select>
                </div>

                <div style="min-width: 180px;">
                     <select class="form-select" id="filterType">
                        <option value="">Todos os Tipos</option>
                        <?php 
                            $types = [];
                            foreach ($versions as $v) foreach ($v['notes'] as $n) if(!empty($n['type'])) $types[] = $n['type'];
                            $types = array_unique($types);
                            sort($types);
                            foreach ($types as $t) echo "<option value='" . htmlspecialchars($t) . "'>" . htmlspecialchars($t) . "</option>";
                        ?>
                    </select>
                </div>

                <div>
                     <button class="btn btn-outline-secondary" onclick="clearFilters()" title="Limpar Filtros">
                        <i class="bi bi-x-lg"></i>
                     </button>
                </div>
            </div>

            <!-- Loader -->
            <div id="loader" class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="text-muted mt-2">Carregando notas...</p>
            </div>

            <!-- Container for JS Render -->
            <div id="versionsContainer"></div>
            
            <!-- End of List Sentine for Observer -->
            <div id="sentinel" class="py-4 text-center text-muted small opacity-50">
                <i class="bi bi-three-dots"></i>
            </div>
            
            <!-- Loading Overlay -->
            <div id="loadingOverlay">
                <div class="loading-content">
                    <div class="loading-spinner"></div>
                    <h5 class="mb-2">Carregando versões...</h5>
                    <p class="text-muted small mb-0">Aguarde enquanto preparamos o conteúdo</p>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    // --- Data Injection ---
    const RELEASE_DATA = <?php echo json_encode($versions); ?>;
    const IS_ADMIN = <?php echo $isAdmin ? 'true' : 'false'; ?>;
    
    // --- State ---
    let currentData = RELEASE_DATA; // Data currently being displayed (filtered or full)
    let renderedCount = 0;
    const BATCH_SIZE = 5;
    
    const container = document.getElementById('versionsContainer');
    const sentinel = document.getElementById('sentinel');
    const loader = document.getElementById('loader');

    // --- Template Functions ---
    // Use simple string concat for speed (or Template Literals)
    
    function escapeHtml(text) {
        if (!text) return '';
        return text
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    function nl2br(text) {
        if (!text) return '';
        return text.replace(/\n/g, "<br>");
    }

    function parseLinks(text) {
        if (!text) return '';
        
        // Convert URLs to clickable links
        text = text.replace(/(https?:\/\/[^\s<]+)/g, '<a href="$1" target="_blank" class="text-primary text-decoration-underline"><i class="bi bi-link-45deg"></i>$1</a>');
        
        // Convert reference numbers (6 digits) to clickable links (Beemore format)
        text = text.replace(/\b(\d{6})\b/g, '<a href="https://app.beemore.com/go/item/$1" target="_blank" class="ref-link"><i class="bi bi-hash"></i>$1</a>');
        
        // Convert markdown-style links [text](url)
        text = text.replace(/\[([^\]]+)\]\(([^\)]+)\)/g, '<a href="$2" target="_blank" class="text-primary text-decoration-underline">$1</a>');
        
        return text;
    }

    // --- Clipboard Helper ---
    function copyTicket(ref, btn) {
        // Extract number only (remove # if present)
        const ticket = ref.replace(/[^0-9]/g, '');
        
        navigator.clipboard.writeText(ticket).then(() => {
            // Visual Feedback
            const icon = btn.querySelector('i');
            const originalClass = icon.className;
            
            icon.className = 'bi bi-check-lg text-success';
            btn.classList.add('text-success');
            btn.classList.remove('text-muted');
            
            setTimeout(() => {
                icon.className = originalClass;
                btn.classList.remove('text-success');
                btn.classList.add('text-muted');
            }, 1000);
        }).catch(err => {
            console.error('Falha ao copiar:', err);
        });
    }

    function getTypeIcon(type) {
        switch(type) {
            case 'Correção':
                return '<i class="bi bi-bug-fill text-danger me-1"></i>';
            case 'Melhoria':
                return '<i class="bi bi-stars text-primary me-1"></i>';
            case 'Ajuste':
                return '<i class="bi bi-wrench text-warning me-1"></i>';
            case 'Nova Feature':
                return '<i class="bi bi-plus-circle-fill text-success me-1"></i>';
            default:
                return '<i class="bi bi-circle-fill text-secondary me-1"></i>';
        }
    }

    function formatDate(dateStr) {
        if (!dateStr) return '';
        const parts = dateStr.split('-');
        return `${parts[2]}/${parts[1]}/${parts[0]}`;
    }

    function getBadgeClass(type) {
        switch (type) {
            case 'Melhoria': return 'badge-type type-melhoria';
            case 'Correção': return 'badge-type type-correcao';
            case 'Nova Feature': return 'badge-type type-nova';
            case 'Ajuste': return 'badge-type type-ajuste';
            default: return 'badge-type bg-secondary bg-opacity-10 text-secondary';
        }
    }

    function renderVersionCard(ver) {
        const dateFormatted = formatDate(ver.date);
        const isImportTitle = (ver.title || '').toLowerCase().includes('importado via html');
        const showTitle = ver.title && (IS_ADMIN || !isImportTitle);
        
        const isImportDesc = (ver.description || '').toLowerCase().includes('versão importada automaticamente');
        const showDesc = ver.description && (IS_ADMIN || !isImportDesc);

        let html = `
        <div class="card mb-5 version-section" id="v-${ver.id}">
            <div class="card-header version-card-header">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <h4 class="mb-0 fw-bolder text-dark" style="letter-spacing: -0.01em;">${escapeHtml(ver.version)}</h4>
                            <span class="version-badge"><i class="bi bi-calendar4-week me-1"></i>${dateFormatted}</span>
                        </div>
                        
                        ${showTitle ? `<h6 class="fw-semibold text-secondary mb-1">${escapeHtml(ver.title)}</h6>` : ''}
                        ${showDesc ? `<p class="text-muted small mb-3" style="line-height: 1.5;">${nl2br(escapeHtml(ver.description))}</p>` : ''}

                        <!-- Downloads & Actions -->
                        <div class="d-flex gap-2 mt-3">
                            ${ver.download_desktop ? `
                                <a href="${escapeHtml(ver.download_desktop)}" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                    <i class="bi bi-windows me-1"></i> Desktop
                                </a>` : ''}
                            ${ver.download_web ? `
                                <a href="${escapeHtml(ver.download_web)}" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                    <i class="bi bi-globe me-1"></i> Web
                                </a>` : ''}
                            <div class="btn-group">
                                <button class="btn btn-sm btn-outline-secondary rounded-pill px-3 dropdown-toggle" data-bs-toggle="dropdown">
                                    <i class="bi bi-download me-1"></i> Exportar
                                </button>
                                <ul class="dropdown-menu shadow-lg border-0">
                                    <li><a class="dropdown-item py-2" href="#" onclick="exportToPDF('v-${ver.id}', '${escapeHtml(ver.version)}', false)"><i class="bi bi-file-pdf me-2 text-danger"></i>PDF Completo</a></li>
                                    <li><a class="dropdown-item py-2" href="#" onclick="exportToPDF('v-${ver.id}', '${escapeHtml(ver.version)}', true)"><i class="bi bi-file-text me-2 text-primary"></i>PDF Cliente (Sem Ref/Obs)</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    ${IS_ADMIN ? `
                        <div class="dropdown">
                            <button class="btn btn-link text-muted p-0" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-three-dots-vertical fs-5"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0">
                                <li>
                                    <button class="dropdown-item py-2" onclick='editVersion(${JSON.stringify(ver).replace(/'/g, "&#39;")})'>
                                        <i class="bi bi-pencil me-2 text-primary"></i>Editar Detalhes
                                    </button>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <button class="dropdown-item py-2 text-danger" onclick="confirmDeleteVersion('${ver.id}')">
                                        <i class="bi bi-trash me-2"></i>Excluir Versão
                                    </button>
                                </li>
                            </ul>
                        </div>
                    ` : ''}
                </div>
            </div>
            
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th style="width: 1%; white-space: nowrap;">Tema</th>
                                <th style="width: 1%; white-space: nowrap;">Tipo</th>
                                <th style="width: 1%; white-space: nowrap;">Escopo</th>
                                <th>Descrição</th>
                                <th style="width: 1%; white-space: nowrap;" class="col-ref">Ref.</th>
                                <th style="width: 20%;" class="col-obs">Observação</th>
                                ${IS_ADMIN ? '<th style="width: 1%; white-space: nowrap;" class="text-end col-actions">Ações</th>' : ''}
                            </tr>
                        </thead>
                        <tbody>
                            ${(ver.notes && ver.notes.length > 0) ? ver.notes.map(note => `
                                <tr class="note-row">
                                    <td class="fw-semibold text-dark small text-nowrap">${escapeHtml(note.theme)}</td>
                                    <td class="text-nowrap">
                                        <span class="${getBadgeClass(note.type)}">${getTypeIcon(note.type)}${escapeHtml(note.type)}</span>
                                    </td>
                                    <td class="text-nowrap"><span class="badge bg-light text-secondary border fw-normal">${escapeHtml(note.scope)}</span></td>
                                    <td class="text-wrap note-desc">${parseLinks(nl2br(escapeHtml(note.description)))}</td>
                                    <td class="small text-nowrap col-ref">
                                        ${note.ref ? `
                                            <div class="d-flex align-items-center gap-1">
                                                <a href="#" class="ref-link text-decoration-none">${escapeHtml(note.ref)}</a>
                                                <button class="btn btn-link btn-sm p-0 text-muted opacity-50 hover-opacity-100" 
                                                        title="Copiar Ticket" 
                                                        onclick="copyTicket('${escapeHtml(note.ref)}', this)">
                                                    <i class="bi bi-clipboard"></i>
                                                </button>
                                            </div>
                                        ` : ''}
                                    </td>
                                    <td class="small text-muted note-obs text-wrap col-obs">${parseLinks(nl2br(escapeHtml(note.observation)))}</td>
                                    ${IS_ADMIN ? `
                                        <td class="text-end text-nowrap col-actions">
                                            <div class="d-flex justify-content-end gap-1">
                                                <button class="btn btn-light btn-sm text-primary" title="Editar"
                                                    onclick='editNote(${JSON.stringify(note).replace(/'/g, "&#39;")}, "${ver.id}")'>
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <form method="POST" class="d-inline" onsubmit="return confirm('Remover esta nota?');">
                                                    <input type="hidden" name="action" value="delete_note">
                                                    <input type="hidden" name="version_id" value="${ver.id}">
                                                    <input type="hidden" name="note_id" value="${note.id}">
                                                    <button type="submit" class="btn btn-light btn-sm text-danger" title="Excluir">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    ` : ''}
                                </tr>
                            `).join('') : `
                                <tr>
                                    <td colspan="${IS_ADMIN ? 7 : 6}" class="p-5 text-center text-muted">
                                        <div class="mb-2"><i class="bi bi-clipboard-x display-6 opacity-25"></i></div>
                                        <div class="small">Nenhuma nota lançada nesta versão.</div>
                                    </td>
                                </tr>
                            `}
                        </tbody>
                    </table>
                </div>
            </div>
            ${IS_ADMIN ? `
                <div class="card-footer bg-white border-top-0 text-center py-3">
                    <button class="btn btn-outline-primary btn-sm rounded-pill px-4" 
                        onclick="openAddNoteModal('${ver.id}')">
                        <i class="bi bi-plus-lg me-1"></i>Adicionar Nota
                    </button>
                </div>
            ` : ''}
        </div>`;
        return html;
    }

    // --- Controller ---

    function renderNextBatch() {
        if (renderedCount >= currentData.length) {
            sentinel.textContent = "Fim dos resultados";
            return;
        }

        const nextBatch = currentData.slice(renderedCount, renderedCount + BATCH_SIZE);
        let htmlBuffer = '';
        nextBatch.forEach(ver => {
            htmlBuffer += renderVersionCard(ver);
        });
        
        // Use insertAdjacentHTML for better performance than innerHTML +=
        container.insertAdjacentHTML('beforeend', htmlBuffer);
        renderedCount += nextBatch.length;

        // Sync Observer
        if (renderedCount >= currentData.length) {
            sentinel.textContent = "Fim dos resultados";
        } else {
            sentinel.innerHTML = '<div class="spinner-border spinner-border-sm text-muted"></div> Carregando mais...';
        }
    }

    function resetRender(newData) {
        currentData = newData;
        container.innerHTML = '';
        renderedCount = 0;
        sentinel.innerHTML = '<div class="spinner-border spinner-border-sm text-muted"></div>';
        window.scrollTo({ top: 0, behavior: 'smooth' }); // Optional: scroll to top when filter changes
        renderNextBatch();
    }

    // --- Filter Logic ---
    function filterNotes() {
        const searchText = document.getElementById('searchInput').value.toLowerCase();
        const filterTheme = document.getElementById('filterTheme').value;
        const filterScope = document.getElementById('filterScope').value;
        const filterType = document.getElementById('filterType').value;

        // Filter the DATA, not the HTML
        const filtered = RELEASE_DATA.map(ver => {
            // Check if version itself matches (e.g. version number)
            const verMatches = ver.version.toLowerCase().includes(searchText);

            // Filter Notes within Version
            const validNotes = (ver.notes || []).filter(note => {
                const text = (note.description + ' ' + note.theme + ' ' + note.type + ' ' + note.scope).toLowerCase();
                const theme = note.theme || '';
                const scope = note.scope || '';
                const type = note.type || '';

                const matchText = text.includes(searchText); // Basic check
                const matchTheme = filterTheme === '' || theme === filterTheme;
                const matchScope = filterScope === '' || scope === filterScope;
                const matchType = filterType === '' || type === filterType;
                
                return matchText && matchTheme && matchScope && matchType;
            });

            // Return version if it has matches OR if the version header matched (and we keep all notes? No, filter notes)
            // Implementation choice: Only show matching notes.
            // If version matches but notes don't, show version with empty notes? Or show all notes?
            // Let's stick to: If Text Search matches version number, show ALL notes (subject to other filters).
            // IF Text Search matches Note, show Note.
            
            // Actually, simpler: just filter notes. If NO notes match, do not show version (unless version number matches search text specifically? let's stick to content first).
            
            if (validNotes.length > 0) {
                 // Return a shallow copy with filtered notes
                 return { ...ver, notes: validNotes };
            } else if (verMatches && filterTheme === '' && filterScope === '' && filterType === '') {
                // If searching for "6.12", show the version even if notes don't have "6.12" in text.
                return ver; 
            }
            return null;
        }).filter(v => v !== null);

        resetRender(filtered);
    }
    
    function clearFilters() {
        document.getElementById('searchInput').value = '';
        document.getElementById('filterTheme').value = '';
        document.getElementById('filterScope').value = '';
        document.getElementById('filterType').value = '';
        resetRender(RELEASE_DATA);
    }


    // --- Init ---
    // Intersection Observer for Infinite Scroll
    const scrollObserver = new IntersectionObserver((entries) => {
        if (entries[0].isIntersecting) {
            renderNextBatch();
        }
    }, { rootMargin: '200px' });

    document.addEventListener('DOMContentLoaded', () => {
        // Initial Render
        loader.style.display = 'none';
        renderNextBatch();
        
        // Start watching sentinel
        scrollObserver.observe(sentinel);
        
        // Setup Filter Listeners
        document.getElementById('searchInput').addEventListener('keyup', filterNotes);
        document.getElementById('filterTheme').addEventListener('change', filterNotes);
        document.getElementById('filterScope').addEventListener('change', filterNotes);
        document.getElementById('filterType').addEventListener('change', filterNotes);
        
        // Smart Sidebar Navigation
        document.querySelectorAll('.sidebar-link').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const targetId = this.getAttribute('data-target');
                const targetElement = document.getElementById(targetId);
                
                // If element exists, scroll to it
                if (targetElement) {
                    targetElement.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    
                    // Update active state
                    document.querySelectorAll('.sidebar-link').forEach(l => l.classList.remove('active'));
                    this.classList.add('active');
                } else {
                    // Element not rendered yet - load all versions until we find it
                    const targetIndex = currentData.findIndex(v => 'v-' + v.id === targetId);
                    
                    if (targetIndex !== -1) {
                        // Show loading overlay
                        const overlay = document.getElementById('loadingOverlay');
                        overlay.classList.add('show');
                        
                        // Async batch loading
                        const loadBatches = () => {
                            if (renderedCount <= targetIndex) {
                                renderNextBatch();
                                requestAnimationFrame(loadBatches);
                            } else {
                                // All batches loaded, hide overlay and scroll
                                overlay.classList.remove('show');
                                
                                setTimeout(() => {
                                    const element = document.getElementById(targetId);
                                    if (element) {
                                        element.scrollIntoView({ behavior: 'smooth', block: 'start' });
                                        
                                        // Update active state
                                        document.querySelectorAll('.sidebar-link').forEach(l => l.classList.remove('active'));
                                        this.classList.add('active');
                                    }
                                }, 100);
                            }
                        };
                        
                        // Start async loading
                        requestAnimationFrame(loadBatches);
                    }
                }
            }.bind(link)); // Bind to preserve 'this' context
        });
        
        // Scroll Spy - Highlight visible version in sidebar
        const versionObserver = new IntersectionObserver((entries) => {
            // Find the version that's closest to the top of the viewport
            let topMostVersion = null;
            let topMostDistance = Infinity;
            
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const rect = entry.target.getBoundingClientRect();
                    const distanceFromTop = Math.abs(rect.top);
                    
                    // Prefer versions that are at or near the top of viewport
                    if (distanceFromTop < topMostDistance && rect.top <= 100) {
                        topMostDistance = distanceFromTop;
                        topMostVersion = entry.target;
                    }
                }
            });
            
            if (topMostVersion) {
                const versionId = topMostVersion.id;
                const correspondingLink = document.querySelector(`.sidebar-link[data-target="${versionId}"]`);
                
                if (correspondingLink && !correspondingLink.classList.contains('active')) {
                    // Remove active from all links
                    document.querySelectorAll('.sidebar-link').forEach(l => l.classList.remove('active'));
                    // Add active to current
                    correspondingLink.classList.add('active');
                    
                    // Scroll sidebar to show active item (only if not already visible)
                    const sidebarContainer = document.querySelector('#versionSidebar').parentElement;
                    const linkRect = correspondingLink.getBoundingClientRect();
                    const containerRect = sidebarContainer.getBoundingClientRect();
                    
                    // Check if link is outside visible area of sidebar
                    if (linkRect.top < containerRect.top || linkRect.bottom > containerRect.bottom) {
                        correspondingLink.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                }
            }
        }, {
            threshold: [0, 0.1, 0.2, 0.3, 0.4, 0.5],
            rootMargin: '-10px 0px -80% 0px' // Watch top 20% of viewport
        });
        
        // Observe all version cards as they're rendered
        const observeVersionCards = () => {
            document.querySelectorAll('.version-section').forEach(card => {
                versionObserver.observe(card);
            });
        };
        
        // Initial observation
        setTimeout(observeVersionCards, 500);
        
        // Re-observe when new versions are loaded
        const originalRenderNextBatch = renderNextBatch;
        renderNextBatch = function() {
            originalRenderNextBatch();
            setTimeout(observeVersionCards, 100);
        };
    });
</script>

        </div>
    </div>
</div>

<!-- Back to Top Button -->
<button type="button" class="btn btn-primary rounded-circle shadow-lg" id="btnBackToTop" style="position: fixed; bottom: 30px; right: 30px; display: none; z-index: 1000;">
    <i class="bi bi-arrow-up"></i>
</button>

<!-- Login Modal -->
<div class="modal fade" id="loginModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <form method="POST" class="modal-content">
            <input type="hidden" name="action" value="login">
            <div class="modal-header">
                <h5 class="modal-title">Acesso Admin</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Usuário</label>
                    <input type="text" class="form-control" name="user" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Senha</label>
                    <input type="password" class="form-control" name="pass" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary w-100">Entrar</button>
            </div>
        </form>
    </div>
</div>

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form method="POST" class="modal-content">
            <input type="hidden" name="action" value="import_html">
            <div class="modal-header">
                <h5 class="modal-title">Importar Release Notes</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <ul class="nav nav-tabs mb-3" id="importTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="url-tab" data-bs-toggle="tab" data-bs-target="#url-content" type="button" role="tab">Via URL</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="html-tab" data-bs-toggle="tab" data-bs-target="#html-content" type="button" role="tab">Colar HTML</button>
                    </li>
                </ul>
                
                <div class="tab-content" id="importTabContent">
                    <div class="tab-pane fade show active" id="url-content" role="tabpanel">
                        <div class="mb-3">
                            <label class="form-label">URL da Página de Release Notes</label>
                            <input type="url" class="form-control" name="import_url" placeholder="https://ajuda.intelidata.com.br/...">
                            <div class="form-text text-warning"><i class="bi bi-exclamation-triangle me-1"></i>Funciona apenas para páginas públicas. Se a página exigir login, use a aba "Colar HTML" ou o Bookmarklet.</div>
                        </div>

                        <div class="alert alert-info bg-light border-0">
                            <strong><i class="bi bi-bookmark-star me-2"></i>Dica: Use o Bookmarklet!</strong>
                            <p class="small mb-2 text-muted">Para importar de páginas restritas, arraste o botão abaixo para sua barra de favoritos. Ao clicar nele na página de release, o HTML será copiado automaticamente.</p>
                            <a href="javascript:(function(){const html=document.documentElement.outerHTML;const el=document.createElement('textarea');el.value=html;document.body.appendChild(el);el.select();document.execCommand('copy');document.body.removeChild(el);alert('HTML Copiado! Volte para o sistema e cole na aba \'Colar HTML\'.');})();" 
                               class="btn btn-primary btn-sm rounded-pill" 
                               title="Arraste para os favoritos"
                               onclick="event.preventDefault(); alert('Arraste este botão para sua barra de favoritos do navegador.');">
                                <i class="bi bi-file-earmark-code me-1"></i>Copiar HTML Release
                            </a>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="html-content" role="tabpanel">
                        <div class="mb-3">
                            <label class="form-label">Código HTML</label>
                            <textarea class="form-control" name="html_content" rows="10" placeholder="<!doctype html>..."></textarea>
                            <div class="form-text">Cole o código fonte da página se a importação via URL falhar.</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-success">Importar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Add/Edit Version -->
<div class="modal fade" id="addVersionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form method="POST" class="modal-content">
            <input type="hidden" name="action" value="add_version" id="versionAction">
            <input type="hidden" name="version_id" id="versionId">
            <div class="modal-header">
                <h5 class="modal-title" id="versionModalTitle">Nova Versão</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Número da Versão</label>
                        <input type="text" class="form-control" name="version" id="inputVersion" placeholder="Ex: 6.11.87" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Data de Lançamento</label>
                        <input type="date" class="form-control" name="date" id="inputDate" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Título da Observação</label>
                        <input type="text" class="form-control" name="title" id="inputTitle" placeholder="Ex: Atualização Crítica de Segurança">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Descrição da Observação</label>
                        <textarea class="form-control" name="description" id="inputDesc" rows="2" placeholder="Resumo das principais mudanças..."></textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Tags (separadas por vírgula)</label>
                        <input type="text" class="form-control" name="tags" id="inputTags" placeholder="Ex: Estável, Hotfix, Major">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Link Download Desktop</label>
                        <input type="text" class="form-control" name="download_desktop" id="inputDlDesktop" value="https://builds.unipluscdn.com/uniplusdesktop/">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Link Download Web</label>
                        <input type="text" class="form-control" name="download_web" id="inputDlWeb" value="https://builds.unipluscdn.com/uniplusweb/">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Salvar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Add/Edit Note -->
<div class="modal fade" id="addNoteModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form method="POST" class="modal-content">
            <input type="hidden" name="action" value="add_note" id="noteAction">
            <input type="hidden" name="version_id" id="noteVersionId"> <!-- Original Version ID -->
            <input type="hidden" name="note_id" id="noteId">
            
            <div class="modal-header">
                <h5 class="modal-title" id="noteModalTitle">Adicionar Nota</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">Versão</label>
                        <select class="form-select" name="new_version_id" id="noteTargetVersionId" required>
                            <?php foreach ($versions as $v): ?>
                                <option value="<?php echo $v['id']; ?>"><?php echo htmlspecialchars($v['version']); ?> (<?php echo date('d/m/Y', strtotime($v['date'])); ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Tema</label>
                        <input type="text" class="form-control" name="theme" id="noteTheme" placeholder="Ex: API, Cadastros, Backups" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Escopo</label>
                        <select class="form-select" name="scope" id="noteScope">
                            <option value="Desktop">Desktop</option>
                            <option value="Web">Web</option>
                            <option value="Mobile">Mobile</option>
                            <option value="Ambos">Ambos</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tipo</label>
                        <select class="form-select" name="type" id="noteType">
                            <option value="Melhoria">Melhoria</option>
                            <option value="Correção">Correção</option>
                            <option value="Nova Feature">Nova Feature</option>
                            <option value="Ajuste">Ajuste</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Descrição</label>
                        <textarea class="form-control" name="description" id="noteDescription" rows="3" required></textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Observação (Opcional)</label>
                        <textarea class="form-control" name="observation" id="noteObservation" rows="2" placeholder="Detalhes técnicos ou notas adicionais..."></textarea>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Referência (Ticket/Task)</label>
                        <input type="text" class="form-control" name="ref" id="noteRef" placeholder="Ex: #1234">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Salvar Nota</button>
            </div>
        </form>
    </div>
</div>

<!-- Hidden Form for Delete Version -->
<form id="deleteVersionForm" method="POST" style="display:none;">
    <input type="hidden" name="action" value="delete_version">
    <input type="hidden" name="version_id" id="deleteVersionId">
</form>

<!-- HTML2PDF Library -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

<script>
function openAddNoteModal(preselectedVersionId = null) {
    document.getElementById('noteAction').value = 'add_note';
    document.getElementById('noteModalTitle').textContent = 'Adicionar Nota';
    document.getElementById('noteId').value = '';
    
    // Reset fields
    document.getElementById('noteTheme').value = '';
    document.getElementById('noteScope').value = 'Desktop';
    document.getElementById('noteType').value = 'Melhoria';
    document.getElementById('noteDescription').value = '';
    document.getElementById('noteObservation').value = '';
    document.getElementById('noteRef').value = '';
    
    // Set Version Select
    const select = document.getElementById('noteTargetVersionId');
    if (preselectedVersionId) {
        select.value = preselectedVersionId;
        document.getElementById('noteVersionId').value = preselectedVersionId;
    } else if (select.options.length > 0) {
        select.selectedIndex = 0;
        document.getElementById('noteVersionId').value = select.value;
    }

    new bootstrap.Modal(document.getElementById('addNoteModal')).show();
}

function editNote(note, versionId) {
    document.getElementById('noteAction').value = 'edit_note';
    document.getElementById('noteModalTitle').textContent = 'Editar Nota';
    document.getElementById('noteVersionId').value = versionId; // Original Version
    document.getElementById('noteId').value = note.id;
    
    // Populate fields
    document.getElementById('noteTargetVersionId').value = versionId;
    document.getElementById('noteTheme').value = note.theme || '';
    document.getElementById('noteScope').value = note.scope;
    document.getElementById('noteType').value = note.type;
    document.getElementById('noteDescription').value = note.description;
    document.getElementById('noteObservation').value = note.observation || '';
    document.getElementById('noteRef').value = note.ref;

    new bootstrap.Modal(document.getElementById('addNoteModal')).show();
}

function confirmDeleteVersion(id) {
    if(confirm('Tem certeza que deseja excluir esta versão e todas as suas notas?')) {
        document.getElementById('deleteVersionId').value = id;
        document.getElementById('deleteVersionForm').submit();
    }
}

function editVersion(ver) {
    document.getElementById('versionAction').value = 'edit_version';
    document.getElementById('versionId').value = ver.id;
    document.getElementById('versionModalTitle').textContent = 'Editar Versão';
    
    document.getElementById('inputVersion').value = ver.version;
    document.getElementById('inputDate').value = ver.date;
    document.getElementById('inputTitle').value = ver.title || '';
    document.getElementById('inputDesc').value = ver.description || '';
    document.getElementById('inputTags').value = ver.tags ? ver.tags.join(', ') : '';
    document.getElementById('inputDlDesktop').value = ver.download_desktop || '';
    document.getElementById('inputDlWeb').value = ver.download_web || '';

    new bootstrap.Modal(document.getElementById('addVersionModal')).show();
}

// Reset modal on close
document.getElementById('addVersionModal').addEventListener('hidden.bs.modal', function () {
    document.getElementById('versionAction').value = 'add_version';
    document.getElementById('versionModalTitle').textContent = 'Nova Versão';
    this.querySelector('form').reset();
    document.getElementById('inputDlDesktop').value = "https://builds.unipluscdn.com/uniplusdesktop/";
    document.getElementById('inputDlWeb').value = "https://builds.unipluscdn.com/uniplusweb/";
});

function exportToPDF(elementId, version, clientMode = false) {
    const element = document.getElementById(elementId);
    const opt = {
        margin:       0.5,
        filename:     `Release_Notes_${version}${clientMode ? '_Cliente' : ''}.pdf`,
        image:        { type: 'jpeg', quality: 0.98 },
        html2canvas:  { scale: 2 },
        jsPDF:        { unit: 'in', format: 'letter', orientation: 'landscape' }
    };
    
    // Clone element to remove buttons for PDF
    const clone = element.cloneNode(true);
    const buttons = clone.querySelectorAll('button, .dropdown, .btn, .btn-group');
    buttons.forEach(btn => btn.remove());

    if (clientMode) {
        // Remove Ref and Observation columns
        const refs = clone.querySelectorAll('.col-ref');
        const obs = clone.querySelectorAll('.col-obs');
        const actions = clone.querySelectorAll('.col-actions');
        
        refs.forEach(el => el.remove());
        obs.forEach(el => el.remove());
        actions.forEach(el => el.remove());
    }
    
    html2pdf().set(opt).from(clone).save();
}

// Scroll Spy & Back to Top (Modified for Dynamic Content)
// Note: ScrollSpy needs to re-run when content loads. 
// However, since we are doing infinite scroll, full scroll spy across all element is tricky.
// Let's keep Sidebar Links -> Scroll To Element logic.

const sidebarLinks = document.querySelectorAll('.sidebar-link');
const btnBackToTop = document.getElementById('btnBackToTop');

// Back to Top Logic
window.onscroll = function() {
    if (document.body.scrollTop > 300 || document.documentElement.scrollTop > 300) {
        btnBackToTop.style.display = "block";
    } else {
        btnBackToTop.style.display = "none";
    }
};

btnBackToTop.addEventListener('click', function() {
    window.scrollTo({ top: 0, behavior: 'smooth' });
});

// Sidebar Link Click Handler
// Since elements might not be rendered, we need to handle this.
// Current strategy: Render requested element if not present? 
// Or just let the user scroll.
// For now, infinite scroll is the main nav method. Sidebar is just a list.
// Upgrading sidebar to filter the list effectively jumps to that version if we filter by version name.


    // Sidebar Link Click Handler (Smart Load)
    sidebarLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            const targetId = link.getAttribute('data-target'); // v-xxxx
            const versionId = targetId.replace('v-', '');
            
            // 1. Try to find existing element
            const el = document.getElementById(targetId);
            if (el) {
                el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                // Visual feedback
                el.classList.add('shadow-lg');
                setTimeout(() => el.classList.remove('shadow-lg'), 1000);
            } else {
                // 2. Element not rendered - Load it!
                const index = currentData.findIndex(v => v.id === versionId);
                
                if (index === -1) {
                    alert('Versão não encontrada na lista atual (verifique os filtros).');
                    return;
                }
                
                // Show loading overlay
                const overlay = document.getElementById('loadingOverlay');
                if(overlay) {
                    overlay.classList.add('show');
                    const text = overlay.querySelector('h5');
                    if(text) text.textContent = "Localizando versão...";
                }

                // Render in next tick to allow UI to update
                setTimeout(() => {
                    // We need to render from current 'renderedCount' up to 'index + 1'
                    // Optimisation: Render in one go
                    const missingData = currentData.slice(renderedCount, index + 1);
                    
                    if (missingData.length > 0) {
                        let htmlBuffer = '';
                        missingData.forEach(ver => {
                            htmlBuffer += renderVersionCard(ver);
                        });
                        container.insertAdjacentHTML('beforeend', htmlBuffer);
                        renderedCount += missingData.length;
                        
                        // Sync Sentinel
                        if (renderedCount >= currentData.length) {
                            sentinel.textContent = "Fim dos resultados";
                        }
                    }
                    
                    // Scroll to newly created element
                    const newEl = document.getElementById(targetId);
                    if (newEl) {
                        newEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
                        newEl.classList.add('shadow-lg');
                        setTimeout(() => newEl.classList.remove('shadow-lg'), 1000);
                    }
                    
                    // Hide overlay
                    if(overlay) {
                        overlay.classList.remove('show');
                        setTimeout(() => {
                             const text = overlay.querySelector('h5');
                             if(text) text.textContent = "Carregando versões...";
                        }, 200);
                    }
                }, 50);
            }
        });
    });

// Sidebar Toggle
function toggleSidebar() {
    const sidebar = document.getElementById('sidebarCol');
    const main = document.getElementById('mainCol');
    const btnShow = document.getElementById('btnShowSidebar');
    
    if (sidebar.classList.contains('collapsed')) {
        sidebar.classList.remove('collapsed');
        sidebar.classList.remove('d-none');
        main.classList.remove('col-md-12');
        main.classList.add('col-md-10');
        btnShow.classList.add('d-none');
    } else {
        sidebar.classList.add('collapsed');
        setTimeout(() => sidebar.classList.add('d-none'), 300); // Wait for transition
        main.classList.remove('col-md-10');
        main.classList.add('col-md-12');
        btnShow.classList.remove('d-none');
    }
}

// Sidebar Filter
document.getElementById('sidebarSearch').addEventListener('input', function(e) {
    const term = e.target.value.toLowerCase();
    const items = document.querySelectorAll('#versionSidebar .sidebar-link');
    
    items.forEach(item => {
        const text = item.textContent.toLowerCase();
        if (text.includes(term)) {
            item.classList.remove('d-none');
        } else {
            item.classList.add('d-none');
        }
    });
});
</script>

</div> <!-- End container-fluid -->
<?php require_once 'includes/footer.php'; ?>
