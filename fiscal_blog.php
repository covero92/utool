<?php
session_start();
require_once 'includes/header.php';
require_once 'includes/portal_auth.php'; 

// --- CONFIGURATION ---
$jsonFile = __DIR__ . '/data/fiscal_blog.json';
$uploadDir = __DIR__ . '/uploads/blog/';
$uploadUrl = 'uploads/blog/';

if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$isAdmin = isAdmin(); 
$currentUser = $_SESSION['user_full_name'] ?? 'Admin'; 

// --- HELPER FUNCTIONS ---
function loadPosts($file) {
    if (!file_exists($file)) return [];
    $data = json_decode(file_get_contents($file), true);
    return is_array($data) ? $data : [];
}

function savePosts($file, $data) {
    usort($data, function($a, $b) {
        return strtotime($b['date']) - strtotime($a['date']);
    });
    return file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
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

function handleUpload($fileInput, $targetDir) {
    if (isset($_FILES[$fileInput]) && $_FILES[$fileInput]['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES[$fileInput]['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $ext;
        $targetPath = $targetDir . $filename;
        if (move_uploaded_file($_FILES[$fileInput]['tmp_name'], $targetPath)) {
            return $filename; // Return just the filename
        }
    }
    return null;
}

// --- ACTION HANDLING ---
$message = '';
$messageType = '';

// Check Session Message (PRG Pattern)
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $messageType = $_SESSION['messageType'];
    unset($_SESSION['message']);
    unset($_SESSION['messageType']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isAdmin) {
    $action = $_POST['action'] ?? '';
    $data = loadPosts($jsonFile);

    if ($action === 'save_post') {
        $id = $_POST['id'] ?? '';
        $isEdit = !empty($id);

        $tags = [];
        if (!empty($_POST['tags'])) {
            $tags = array_map('trim', explode(',', $_POST['tags']));
        }

        // Handle Uploads
        $coverImage = handleUpload('cover_image', $uploadDir);
        $attachment = handleUpload('attachment', $uploadDir);

        // Preserve existing files if editing and no new upload
        $existingPost = null;
        if ($isEdit) {
            foreach ($data as $p) {
                if ($p['id'] === $id) {
                    $existingPost = $p;
                    break;
                }
            }
        }

        $finalCover = $coverImage ? $coverImage : ($existingPost['cover_image'] ?? null);
        $finalAttach = $attachment ? $attachment : ($existingPost['attachment'] ?? null);

        $post = [
            'id' => $isEdit ? $id : generateId(),
            'title' => $_POST['title'],
            'summary' => $_POST['summary'],
            'content' => $_POST['content'], 
            'category' => $_POST['category'],
            'author' => $_POST['author'] ?? $currentUser,
            'date' => $_POST['date'] ?? date('Y-m-d'),
            'tags' => $tags,
            'cover_image' => $finalCover,
            'attachment' => $finalAttach
        ];

        if ($isEdit) {
            foreach ($data as &$p) {
                if ($p['id'] === $id) {
                    $p = $post;
                    break;
                }
            }
        } else {
            array_unshift($data, $post);
        }

        if (savePosts($jsonFile, $data)) {
            $_SESSION['message'] = "Post salvo com sucesso!";
            $_SESSION['messageType'] = "success";
        } else {
            $_SESSION['message'] = "Erro ao salvar arquivo.";
            $_SESSION['messageType'] = "danger";
        }
        
        header("Location: fiscal_blog.php");
        exit;

    } elseif ($action === 'delete_post') {
        $id = $_POST['id'];
        $data = array_filter($data, function($p) use ($id) { return $p['id'] !== $id; });
        savePosts($jsonFile, array_values($data));
        
        $_SESSION['message'] = "Post removido.";
        $_SESSION['messageType'] = "success";
        
        header("Location: fiscal_blog.php");
        exit;
    }
}

// --- DATA PREPARATION ---
$allPosts = loadPosts($jsonFile);

// Filter Logic
$filterCategory = $_GET['category'] ?? '';
$filterTag = $_GET['tag'] ?? '';
$searchQuery = $_GET['q'] ?? '';

$filteredPosts = array_filter($allPosts, function($p) use ($filterCategory, $filterTag, $searchQuery) {
    if ($filterCategory && $p['category'] !== $filterCategory) return false;
    if ($filterTag && !in_array($filterTag, $p['tags'])) return false;
    if ($searchQuery) {
        $term = stripos($p['title'], $searchQuery) !== false 
             || stripos($p['summary'], $searchQuery) !== false;
        if (!$term) return false;
    }
    return true;
});

// Extract Categories and Tags
$categories = [];
$allTags = [];
foreach ($allPosts as $p) {
    if (!empty($p['category'])) $categories[$p['category']] = ($categories[$p['category']] ?? 0) + 1;
    foreach ($p['tags'] as $t) {
        $allTags[$t] = ($allTags[$t] ?? 0) + 1;
    }
}
arsort($categories);
arsort($allTags);
?>

<!-- TinyMCE (Free CDN) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.2/tinymce.min.js" referrerpolicy="origin"></script>
<script>
  tinymce.init({
    selector: '#edit-content',
    height: 400,
    plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
    toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline js-strikethrough | link image media table | align lineheight | numlist bullist indent outdent | emoticons charmap | removeformat',
  });
</script>

<style>
    /* UI Refinements */
    .blog-post-card {
        transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        border: 1px solid rgba(255, 255, 255, 0.6);
    }
    .blog-post-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 30px rgba(0,0,0,0.08);
        border-color: rgba(255, 255, 255, 0.9);
        z-index: 10;
        position: relative;
    }

    .sidebar-link {
        color: #475569;
        font-weight: 500;
        padding: 8px 12px;
        border-radius: 10px;
        margin-bottom: 4px;
        display: flex;
        justify-content: space-between; /* Align text and badge */
        align-items: center;
        transition: all 0.2s;
        border: 1px solid transparent;
    }
    .sidebar-link:hover {
        background: #fff;
        border-color: rgba(0,0,0,0.05);
        color: var(--color-accent);
    }
    .sidebar-link.active {
        background: #fff;
        color: var(--color-accent);
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        font-weight: 600;
    }
    
    .badge-tag {
        background: rgba(255, 255, 255, 0.5);
        color: #64748b;
        font-weight: 500;
        font-size: 0.75rem;
        border: 1px solid rgba(0,0,0,0.1) !important;
        transition: all 0.2s;
    }
    .badge-tag:hover {
        background: #fff;
        color: var(--color-accent);
        border-color: var(--color-accent) !important;
    }

    .topic-badge {
        font-size: 0.7rem;
        letter-spacing: 0.5px;
        text-transform: uppercase;
    }

    /* Article Content Styles */
    #view-content {
        font-size: 1.15rem !important; 
        color: #2d3748;
        line-height: 1.8;
    }
    #view-content p { margin-bottom: 1.5rem; }
    #view-content h2, #view-content h3 { 
        font-family: 'Inter', sans-serif;
        font-weight: 700; 
        margin-top: 2rem; 
        margin-bottom: 1rem;
        color: #1a202c;
    }
    #view-content ul, #view-content ol { margin-bottom: 1.5rem; padding-left: 1.5rem; }
    #view-content li { margin-bottom: 0.5rem; }
    
    /* Ensure images in content don't overflow */
    #view-content img {
        max-width: 100%;
        height: auto;
        border-radius: 8px;
        margin: 1.5rem 0;
        box-shadow: 0 4px 6px rgba(0,0,0,0.05);
    }
    #view-content blockquote {
        border-left: 4px solid var(--color-accent);
        padding-left: 1rem;
        font-style: italic;
        color: #718096;
        background: #f7fafc;
        padding: 1rem;
        border-radius: 0 8px 8px 0;
    }
</style>

<div class="container-fluid py-4 px-4">
    <!-- Header -->
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-3">
         <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none text-muted small">Hub</a></li>
                <li class="breadcrumb-item active small" aria-current="page">Fiscal</li>
            </ol>
        </nav>
        <div class="d-flex gap-2">
            <?php if ($isAdmin): ?>
                <button class="btn btn-primary rounded-pill shadow-sm px-4 fw-bold" onclick="openEditor()">
                    <i class="bi bi-pencil-square me-2"></i>Novo Post
                </button>
            <?php endif; ?>
            <a href="index.php" class="btn btn-white border shadow-sm rounded-pill px-3">Voltar</a>
        </div>
    </div>

    <!-- Personalized Title Section -->
    <div class="text-center mb-5 position-relative">
        <h1 class="display-4 fw-bold ls-tight mb-2" style="
            background: linear-gradient(135deg, #1a202c 0%, #2d3748 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-family: 'Outfit', sans-serif;
            letter-spacing: -0.02em;
        ">Fiscal News Suporte</h1>
        <p class="text-secondary lead fs-6 mb-0 position-relative d-inline-block">
            Central de atualizações tributárias e fiscais
            <span class="position-absolute start-50 translate-middle-x top-100 mt-3" 
                  style="width: 60px; height: 4px; background: #3b82f6; border-radius: 2px;"></span>
        </p>
    </div>

    <!-- Alerts -->
    <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show border-0 shadow-sm rounded-4 mb-4" role="alert">
            <i class="bi bi-info-circle-fill me-2"></i> <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- Sidebar -->
        <div class="col-lg-3">
            <div class="glass-card p-4 h-100 sticky-top" style="top: 20px; z-index: 1;">
                <!-- Search -->
                <form action="" method="GET" class="mb-3" id="searchForm">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-0 rounded-start-pill ps-3"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="q" id="searchInput" class="form-control border-0 shadow-sm bg-white py-2" placeholder="Buscar artigo..." value="<?php echo htmlspecialchars($searchQuery); ?>">
                        <button class="btn bg-white border-0 rounded-end-pill shadow-sm text-muted" type="button" onclick="clearSearch()" title="Limpar pesquisa">
                            <i class="bi bi-x-circle"></i>
                        </button>
                    </div>
                </form>
                <div class="mb-4 text-center">
                     <a href="fiscal_blog.php" class="btn btn-sm btn-outline-primary rounded-pill px-4 w-100 fw-bold">Ver todos os posts</a>
                </div>

                <!-- Categories -->
                <h6 class="text-uppercase text-muted fw-bold small mb-3 ls-1">Tópicos</h6>
                <div class="mb-5">
                    <a href="fiscal_blog.php" class="sidebar-link <?php echo empty($filterCategory) ? 'active' : ''; ?>">
                        <span><i class="bi bi-grid-fill me-2 opacity-75"></i>Todos</span>
                    </a>
                    <?php foreach ($categories as $cat => $count): ?>
                        <a href="?category=<?php echo urlencode($cat); ?>" class="sidebar-link <?php echo $filterCategory === $cat ? 'active' : ''; ?>">
                            <span><i class="bi bi-hash me-2 opacity-75"></i><?php echo htmlspecialchars($cat); ?></span>
                            <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill px-2"><?php echo $count; ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>

                <!-- Tags -->
                <h6 class="text-uppercase text-muted fw-bold small mb-3 ls-1">Tags Populares</h6>
                <div class="d-flex flex-wrap gap-2">
                    <?php 
                    $topTags = array_slice($allTags, 0, 15); // Limit to top 15 tags
                    foreach ($topTags as $tag => $count): ?>
                        <a href="?tag=<?php echo urlencode($tag); ?>" class="badge rounded-pill text-decoration-none badge-tag pb-2 pt-2 px-3">
                            #<?php echo htmlspecialchars($tag); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Feed -->
        <div class="col-lg-9">
            <?php if (empty($filteredPosts)): ?>
                <div class="glass-card p-5 text-center text-muted py-5">
                    <div class="mb-3 p-4 rounded-circle bg-light d-inline-block">
                        <i class="bi bi-search fs-1 opacity-25"></i>
                    </div>
                    <h4>Nenhum post encontrado.</h4>
                    <p>Tente mudar o filtro ou pesquisar por outra coisa.</p>
                    <a href="fiscal_blog.php" class="btn btn-outline-primary rounded-pill mt-2 px-4">Limpar Filtros</a>
                </div>
            <?php else: ?>
                <div class="d-flex flex-column gap-4">
                    <?php foreach ($filteredPosts as $post): ?>
                        <div class="card blog-post-card glass-card p-0">
                            <div class="row g-0">
                                <?php if(!empty($post['cover_image'])): ?>
                                    <div class="col-md-4 position-relative overflow-hidden" style="min-height: 220px;">
                                        <img src="<?php echo $uploadUrl . $post['cover_image']; ?>" class="w-100 h-100 object-fit-cover" alt="Cover">
                                        <!-- Overlay gradient for text readability if we wanted text over image, but here just clean -->
                                    </div>
                                <?php endif; ?>
                                
                                <div class="<?php echo !empty($post['cover_image']) ? 'col-md-8' : 'col-md-12'; ?> d-flex flex-column">
                                    <div class="card-body p-4 d-flex flex-column h-100">
                                        
                                        <!-- Header -->
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3 py-1 fw-bold topic-badge">
                                                <?php echo htmlspecialchars($post['category']); ?>
                                            </span>
                                            <small class="text-muted fw-bold" style="font-size: 0.75rem;">
                                                <?php echo date('d \d\e M, Y', strtotime($post['date'])); ?>
                                            </small>
                                        </div>
                                        
                                        <!-- Title -->
                                        <h3 class="card-title fw-bold text-dark mb-2 lh-sm">
                                            <a href="#" class="text-decoration-none text-dark hover-underline" onclick="viewPost(<?php echo htmlspecialchars(json_encode($post)); ?>); return false;">
                                                <?php echo htmlspecialchars($post['title']); ?>
                                            </a>
                                        </h3>
                                        
                                        <!-- Summary -->
                                        <p class="card-text text-secondary mb-4 small flex-grow-1" style="line-height: 1.6;">
                                            <?php echo htmlspecialchars($post['summary']); ?>
                                        </p>
                                        
                                        <!-- Footer -->
                                        <div class="d-flex justify-content-between align-items-end mt-2">
                                            <!-- Tags (Limited to 3) -->
                                            <div class="d-flex gap-1 flex-wrap">
                                                <?php 
                                                $maxTags = 3;
                                                $countTags = count($post['tags']);
                                                $displayTags = array_slice($post['tags'], 0, $maxTags);
                                                
                                                foreach ($displayTags as $t): ?>
                                                    <span class="badge bg-light text-muted border fw-normal">#<?php echo htmlspecialchars($t); ?></span>
                                                <?php endforeach; 
                                                
                                                if ($countTags > $maxTags): ?>
                                                    <span class="badge bg-white text-muted border fw-normal">+<?php echo ($countTags - $maxTags); ?></span>
                                                <?php endif; ?>
                                            </div>

                                            <div class="d-flex gap-2 align-items-center">
                                                 <?php if(!empty($post['attachment'])): ?>
                                                    <i class="bi bi-paperclip text-muted" title="Possui anexo"></i>
                                                <?php endif; ?>

                                                <button class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-bold" onclick="viewPost(<?php echo htmlspecialchars(json_encode($post)); ?>)">Ler Artigo</button>
                                                
                                                <?php if ($isAdmin): ?>
                                                    <div class="dropdown">
                                                        <button class="btn btn-sm btn-light text-muted rounded-circle" data-bs-toggle="dropdown" data-bs-display="static">
                                                            <i class="bi bi-three-dots"></i>
                                                        </button>
                                                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 p-1 rounded-3">
                                                            <li><a class="dropdown-item rounded-2 small" href="#" onclick="editPost(<?php echo htmlspecialchars(json_encode($post)); ?>)">✏️ Editar</a></li>
                                                            <li>
                                                                <form method="POST" onsubmit="return confirm('Tem certeza?');">
                                                                    <input type="hidden" name="action" value="delete_post">
                                                                    <input type="hidden" name="id" value="<?php echo $post['id']; ?>">
                                                                    <button type="submit" class="dropdown-item rounded-2 text-danger small">🗑️ Excluir</button>
                                                                </form>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Editor Modal (Unchanged Logically, just CSS inherited) -->
<div class="modal fade" id="editorModal" tabindex="-1" data-bs-backdrop="static">
    <!-- ... (Keep existing Editor Modal content) ... -->
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form method="POST" class="modal-content border-0 shadow rounded-4" id="postForm" enctype="multipart/form-data">
            <input type="hidden" name="action" value="save_post">
            <input type="hidden" name="id" id="edit-id">
            
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="editorTitle">Novo Post</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label small fw-bold text-muted">Título</label>
                        <input type="text" name="title" id="edit-title" class="form-control bg-light border-0" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted">Categoria</label>
                        <input type="text" name="category" id="edit-category" class="form-control bg-light border-0" list="catList" required>
                        <datalist id="catList">
                            <option value="NFS-e">
                            <option value="NF-e">
                            <option value="Legislação">
                            <option value="Comunicado">
                            <option value="Reforma Tributária">
                        </datalist>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted">Data</label>
                        <input type="date" name="date" id="edit-date" class="form-control bg-light border-0" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted">Autor</label>
                        <input type="text" name="author" id="edit-author" class="form-control bg-light border-0" value="<?php echo htmlspecialchars($currentUser); ?>" required>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted">Capa (Opcional)</label>
                        <input type="file" name="cover_image" class="form-control bg-light border-0" accept="image/*">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted">Anexo (Opcional)</label>
                        <input type="file" name="attachment" class="form-control bg-light border-0">
                    </div>

                    <div class="col-md-12">
                        <label class="form-label small fw-bold text-muted">Resumo</label>
                        <textarea name="summary" id="edit-summary" class="form-control bg-light border-0" rows="2" required></textarea>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label small fw-bold text-muted">Conteúdo</label>
                        <textarea name="content" id="edit-content" class="form-control" rows="8"></textarea>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label small fw-bold text-muted">Tags</label>
                        <input type="text" name="tags" id="edit-tags" class="form-control bg-light border-0" placeholder="ex: urgente, sc, layout">
                        <div class="form-text small">Separe por vírgula</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary rounded-pill px-4">Salvar Post</button>
            </div>
        </form>
    </div>
</div>

<!-- View Modal (Refined) -->
<div class="modal fade" id="viewModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow rounded-4 overflow-hidden">
            <!-- Full Width Cover -->
            <div id="view-cover-container" class="position-relative" style="display:none; height: 300px;">
                <img id="view-cover" src="" class="w-100 h-100 object-fit-cover">
                <div class="position-absolute bottom-0 start-0 w-100 p-4" style="background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);">
                     <!-- Optional: Title over image? Kept separate for cleaner read -->
                </div>
            </div>

            <div class="modal-header border-0 pb-0 px-4 pt-4">
                <div class="w-100">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3 py-1 fw-bold topic-badge" id="view-category"></span>
                        <div class="text-muted small">
                             <i class="bi bi-calendar3 me-1"></i> <span id="view-date"></span>
                        </div>
                    </div>
                    <h2 class="modal-title fw-bold text-dark lh-sm mb-2" id="view-title" style="font-size: 1.75rem;"></h2>
                    <p class="text-secondary small mb-0">Publicado por <strong class="text-dark" id="view-author"></strong></p>
                </div>
                <button type="button" class="btn-close ms-2 align-self-start" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body px-4 py-4">
                <div id="view-content" class="lh-lg text-dark fs-6" style="font-size: 1.1rem !important; color: #333;">
                    <!-- HTML Content -->
                </div>
                
                <div id="view-attachment-container" class="mt-5 p-3 bg-light rounded-3 border align-items-center" style="display:none;">
                    <div class="bg-white p-2 rounded-circle shadow-sm me-3 text-primary">
                        <i class="bi bi-file-earmark-arrow-down fs-4"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-0 fw-bold text-dark">Material Complementar</h6>
                        <small class="text-muted">Clique para baixar o arquivo anexo.</small>
                    </div>
                    <a href="#" id="view-attachment-link" class="btn btn-primary rounded-pill px-4 fw-bold" target="_blank">
                        Baixar
                    </a>
                </div>

                <hr class="my-4 opacity-10">
                <div class="d-flex align-items-center gap-2">
                    <span class="small text-muted fw-bold me-2">TAGS:</span>
                    <div id="view-tags" class="d-flex flex-wrap gap-2"></div>
                </div>
            </div>
            <!-- No footer, clean look -->
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const editorModal = new bootstrap.Modal(document.getElementById('editorModal'));
    const viewModal = new bootstrap.Modal(document.getElementById('viewModal'));
    const uploadUrl = '<?php echo $uploadUrl; ?>'; // Pass JS variable

    // Fix TinyMCE inside Bootstrap Modal focus issue
    document.addEventListener('focusin', (e) => {
        if (e.target.closest(".tox-tinymce, .tox-tinymce-aux, .moxman-window, .tam-assetmanager-root") !== null) {
            e.stopImmediatePropagation();
        }
    });

    function openEditor() {
        document.getElementById('postForm').reset();
        document.getElementById('edit-id').value = '';
        document.getElementById('editorTitle').innerText = 'Novo Post';
        document.getElementById('edit-date').value = new Date().toISOString().split('T')[0];
        document.getElementById('edit-author').value = '<?php echo $currentUser; ?>'; // Reset to current user
        if(tinymce.get('edit-content')) {
            tinymce.get('edit-content').setContent('');
        }
        editorModal.show();
    }

    function editPost(post) {
        document.getElementById('edit-id').value = post.id;
        document.getElementById('edit-title').value = post.title;
        document.getElementById('edit-category').value = post.category;
        document.getElementById('edit-date').value = post.date;
        document.getElementById('edit-author').value = post.author || '<?php echo $currentUser; ?>';
        document.getElementById('edit-summary').value = post.summary;
        
        // Set TinyMCE Content
        if(tinymce.get('edit-content')) {
            tinymce.get('edit-content').setContent(post.content);
        } else {
             document.getElementById('edit-content').value = post.content;
        }

        document.getElementById('edit-tags').value = post.tags.join(', ');
        
        document.getElementById('editorTitle').innerText = 'Editar Post';
        editorModal.show();
    }

    function viewPost(post) {
        document.getElementById('view-title').innerText = post.title;
        document.getElementById('view-category').innerText = post.category;
        document.getElementById('view-date').innerText = new Date(post.date).toLocaleDateString(); 
        document.getElementById('view-author').innerText = post.author;
        document.getElementById('view-content').innerHTML = post.content;
        
        // Handle Cover Image
        const coverContainer = document.getElementById('view-cover-container');
        const coverImg = document.getElementById('view-cover');
        if (post.cover_image) {
            coverImg.src = uploadUrl + post.cover_image;
            coverContainer.style.display = 'block';
        } else {
            coverContainer.style.display = 'none';
        }

        // Handle Attachment
        const attachContainer = document.getElementById('view-attachment-container');
        const attachLink = document.getElementById('view-attachment-link');
        
        // Strict check: must be a non-empty string and not "null" or "undefined"
        if (post.attachment && 
            typeof post.attachment === 'string' && 
            post.attachment.trim() !== "" && 
            post.attachment !== "null" && 
            post.attachment !== "undefined") {
            
            attachLink.href = uploadUrl + post.attachment;
            attachLink.setAttribute('download', post.attachment);
            attachContainer.style.display = 'flex';
        } else {
            attachLink.href = '#';
            attachLink.removeAttribute('download');
            attachContainer.style.display = 'none';
        }

        const tagsContainer = document.getElementById('view-tags');
        tagsContainer.innerHTML = '';
        post.tags.forEach(tag => {
            const span = document.createElement('span');
            span.className = 'badge bg-light text-secondary border';
            span.innerText = '#' + tag;
            tagsContainer.appendChild(span);
        });

        viewModal.show();
    }

    function clearSearch() {
        document.getElementById('searchInput').value = '';
        window.location.href = 'fiscal_blog.php';
    }
</script>
</body>
</html>
