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

        // Allow removing attachment
        if (isset($_POST['delete_attachment']) && $_POST['delete_attachment'] === 'on') {
            $finalAttach = null;
        }
        
        // Sanitize - prevent string "null"
        if ($finalAttach === 'null') {
            $finalAttach = null;
        }

        $post = [
            'id' => $isEdit ? $id : generateId(),
            'title' => $_POST['title'],
            'summary' => $_POST['summary'],
            'content' => $_POST['content'], 
            'area' => $_POST['area'] ?? 'Fiscal', // NOVO: Área do post
            'category' => $_POST['category'],
            'author' => $_POST['author'] ?? $currentUser,
            'date' => $_POST['date'] ?? date('Y-m-d'),
            'tags' => $tags,
            'cover_image' => $finalCover,
            'attachment' => $finalAttach,
            'video_url' => $_POST['video_url'] ?? null, // NOVO: URL de vídeo
            'video_file' => null // NOVO: Arquivo de vídeo (futuro)
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

// Deep Link Handling
$initialPost = null;
if (isset($_GET['post_id'])) {
    foreach ($allPosts as $p) {
        if ($p['id'] === $_GET['post_id']) {
            $initialPost = $p;
            break;
        }
    }
}

// Filter Logic
$filterArea = $_GET['area'] ?? ''; // NOVO: Filtro por área
$filterCategory = $_GET['category'] ?? '';
$filterTag = $_GET['tag'] ?? '';
$searchQuery = $_GET['q'] ?? '';

$filteredPosts = array_filter($allPosts, function($p) use ($filterArea, $filterCategory, $filterTag, $searchQuery) {
    // Garantir retrocompatibilidade: posts sem 'area' são considerados 'Fiscal'
    $postArea = $p['area'] ?? 'Fiscal';
    
    if ($filterArea && $postArea !== $filterArea) return false;
    if ($filterCategory && $p['category'] !== $filterCategory) return false;
    if ($filterTag && !in_array($filterTag, $p['tags'])) return false;
    if ($searchQuery) {
        $term = stripos($p['title'], $searchQuery) !== false 
             || stripos($p['summary'], $searchQuery) !== false;
        if (!$term) return false;
    }
    return true;
});

// Pagination Logic
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$perPage = 7;
$totalPosts = count($filteredPosts);
$totalPages = ceil($totalPosts / $perPage);
$offset = ($page - 1) * $perPage;

$currentPosts = array_slice($filteredPosts, $offset, $perPage);

// Extract Areas, Categories and Tags
$areas = [];
$categories = [];
$allTags = [];
foreach ($allPosts as $p) {
    // Áreas
    $postArea = $p['area'] ?? 'Fiscal';
    $areas[$postArea] = ($areas[$postArea] ?? 0) + 1;
    
    // Categorias
    if (!empty($p['category'])) $categories[$p['category']] = ($categories[$p['category']] ?? 0) + 1;
    
    // Tags
    foreach ($p['tags'] as $t) {
        $allTags[$t] = ($allTags[$t] ?? 0) + 1;
    }
}
arsort($areas);
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
                <li class="breadcrumb-item active small" aria-current="page">Blog Suporte</li>
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
    <div class="text-center mb-5 mt-2">
        <div class="d-inline-flex align-items-center justify-content-center gap-3 mb-2 user-select-none">
            <div class="bg-primary bg-gradient text-white rounded-4 d-flex align-items-center justify-content-center shadow-lg" 
                 style="width: 56px; height: 56px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <i class="bi bi-book fs-3"></i>
            </div>
            <h1 class="display-5 fw-bold ls-tight mb-0 text-dark" style="font-family: 'Outfit', sans-serif;">
                Blog <span class="text-primary">Suporte</span>
            </h1>
        </div>
        <p class="text-secondary small text-uppercase fw-bold ls-3 opacity-50 mb-0" style="font-size: 0.7rem;">
            Central de Conhecimento & Atualizações
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

                <!-- Areas Filter -->
                <h6 class="text-uppercase text-muted fw-bold small mb-3 ls-1">Áreas</h6>
                <div class="mb-4">
                    <a href="fiscal_blog.php" class="sidebar-link <?php echo empty($filterArea) ? 'active' : ''; ?>">
                        <span><i class="bi bi-grid-fill me-2 opacity-75"></i>Todas</span>
                        <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill px-2"><?php echo count($allPosts); ?></span>
                    </a>
                    <?php 
                    $areaIcons = [
                        'Fiscal' => 'bi-receipt',
                        'Retaguarda' => 'bi-server',
                        'PDV' => 'bi-shop',
                        'Geral' => 'bi-info-circle'
                    ];
                    foreach ($areas as $area => $count): 
                        $icon = $areaIcons[$area] ?? 'bi-folder';
                    ?>
                        <a href="?area=<?php echo urlencode($area); ?>" class="sidebar-link <?php echo $filterArea === $area ? 'active' : ''; ?>">
                            <span><i class="bi <?php echo $icon; ?> me-2 opacity-75"></i><?php echo htmlspecialchars($area); ?></span>
                            <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill px-2"><?php echo $count; ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>

                <!-- Categories -->
                <h6 class="text-uppercase text-muted fw-bold small mb-3 ls-1">Categorias</h6>
                <div class="mb-5">
                    <?php foreach ($categories as $cat => $count): ?>
                        <a href="?category=<?php echo urlencode($cat); ?><?php echo $filterArea ? '&area=' . urlencode($filterArea) : ''; ?>" class="sidebar-link <?php echo $filterCategory === $cat ? 'active' : ''; ?>">
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
            <?php if (empty($currentPosts)): ?>
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
                    <?php foreach ($currentPosts as $post): ?>
                        <div class="card blog-post-card glass-card p-0">
                            <div class="row g-0">
                                <?php if(!empty($post['cover_image'])): ?>
                                    <div class="col-md-4 position-relative overflow-hidden" style="min-height: 220px;">
                                        <img src="<?php echo $uploadUrl . $post['cover_image']; ?>" class="w-100 h-100 object-fit-cover" alt="Cover" loading="lazy">
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

                <!-- Pagination Controls -->
                <?php if ($totalPages > 1): ?>
                    <nav class="mt-5" aria-label="Page navigation">
                        <ul class="pagination justify-content-center gap-2">
                            <!-- Previous -->
                            <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                                <a class="page-link rounded-circle border-0 shadow-sm d-flex align-items-center justify-content-center" 
                                   href="?page=<?php echo $page - 1; ?>&q=<?php echo urlencode($searchQuery); ?>&category=<?php echo urlencode($filterCategory); ?>&tag=<?php echo urlencode($filterTag); ?>" 
                                   style="width: 40px; height: 40px;">
                                    <i class="bi bi-chevron-left"></i>
                                </a>
                            </li>
                            
                            <!-- Numbers -->
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                                    <a class="page-link rounded-circle border-0 shadow-sm d-flex align-items-center justify-content-center fw-bold" 
                                       href="?page=<?php echo $i; ?>&q=<?php echo urlencode($searchQuery); ?>&category=<?php echo urlencode($filterCategory); ?>&tag=<?php echo urlencode($filterTag); ?>"
                                       style="width: 40px; height: 40px;">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>

                            <!-- Next -->
                            <li class="page-item <?php echo ($page >= $totalPages) ? 'disabled' : ''; ?>">
                                <a class="page-link rounded-circle border-0 shadow-sm d-flex align-items-center justify-content-center" 
                                   href="?page=<?php echo $page + 1; ?>&q=<?php echo urlencode($searchQuery); ?>&category=<?php echo urlencode($filterCategory); ?>&tag=<?php echo urlencode($filterTag); ?>"
                                   style="width: 40px; height: 40px;">
                                    <i class="bi bi-chevron-right"></i>
                                </a>
                            </li>
                        </ul>
                    </nav>
                <?php endif; ?>
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
                        <label class="form-label small fw-bold text-muted">Área</label>
                        <select name="area" id="edit-area" class="form-select bg-light border-0" required>
                            <option value="Fiscal">📄 Fiscal</option>
                            <option value="Retaguarda">🖥️ Retaguarda</option>
                            <option value="PDV">🛒 PDV</option>
                            <option value="Geral">ℹ️ Geral</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted">Categoria</label>
                        <input type="text" name="category" id="edit-category" class="form-control bg-light border-0" list="catList" required>
                        <datalist id="catList">
                            <!-- Fiscal -->
                            <option value="NFS-e">
                            <option value="NF-e">
                            <option value="Legislação">
                            <option value="Reforma Tributária">
                            <!-- Retaguarda -->
                            <option value="Banco de Dados">
                            <option value="Integrações">
                            <option value="Performance">
                            <option value="Backup">
                            <!-- PDV -->
                            <option value="Terminal">
                            <option value="Impressoras">
                            <option value="SAT/TEF">
                            <option value="Offline">
                            <!-- Geral -->
                            <option value="Comunicado">
                            <option value="Tutorial">
                            <option value="Dicas">
                            <option value="Atualização">
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
                        <div class="form-check mt-2" id="div-delete-attachment" style="display:none;">
                            <input class="form-check-input" type="checkbox" name="delete_attachment" id="check-delete-attachment">
                            <label class="form-check-label small text-muted" for="check-delete-attachment">Remover anexo existente</label>
                        </div>
                    </div>
                    
                    <div class="col-md-12">
                        <label class="form-label small fw-bold text-muted">URL de Vídeo (Opcional)</label>
                        <input type="url" name="video_url" id="edit-video-url" class="form-control bg-light border-0" placeholder="https://www.youtube.com/watch?v=...">
                        <div class="form-text small">Cole o link do YouTube ou Vimeo</div>
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

<!-- View Modal (Modernized with TOC and Share) -->
<div class="modal fade" id="viewModal" tabindex="-1">
    <div class="modal-dialog modal-fullscreen-lg-down" style="max-width: 90%; margin: 2rem auto;">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <!-- Full Width Cover -->
            <div id="view-cover-container" class="position-relative" style="display:none; height: 350px;">
                <img id="view-cover" src="" class="w-100 h-100 object-fit-cover">
                <div class="position-absolute bottom-0 start-0 w-100 p-4" style="background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);">
                     <!-- Optional: Title over image -->
                </div>
            </div>

            <div class="modal-header border-0 pb-2 px-5 pt-4">
                <div class="w-100">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill px-3 py-1 fw-bold topic-badge" id="view-area"></span>
                            <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3 py-1 fw-bold topic-badge" id="view-category"></span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <div class="text-muted small">
                                 <i class="bi bi-calendar3 me-1"></i> <span id="view-date"></span>
                            </div>
                            <!-- Share Dropdown -->
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-primary rounded-pill px-3 dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    <i class="bi bi-share me-1"></i>Compartilhar
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow border-0 p-2 rounded-3">
                                    <li><a class="dropdown-item rounded-2 small" href="#" onclick="copyLink(); return false;"><i class="bi bi-link-45deg text-dark me-2"></i>Copiar Link</a></li>
                                    <li><hr class="dropdown-divider my-1"></li>
                                    <li><a class="dropdown-item rounded-2 small" href="#" onclick="copyContent(); return false;"><i class="bi bi-clipboard text-muted me-2"></i>Copiar Conteúdo</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <h2 class="modal-title fw-bold text-dark lh-sm mb-2" id="view-title" style="font-size: 2rem;"></h2>
                    <p class="text-secondary small mb-0">Publicado por <strong class="text-dark" id="view-author"></strong></p>
                </div>
                <button type="button" class="btn-close ms-3 align-self-start" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body px-5 py-4">
                <div class="row">
                    <!-- Table of Contents (Sidebar) -->
                    <div class="col-lg-3 d-none d-lg-block">
                        <div class="sticky-top" style="top: 20px;">
                            <h6 class="text-uppercase text-muted fw-bold small mb-3">Índice</h6>
                            <div id="toc-container" class="list-group list-group-flush">
                                <!-- TOC will be generated here -->
                            </div>
                        </div>
                    </div>

                    <!-- Main Content -->
                    <div class="col-lg-9">
                        <div style="max-width: 900px; margin: 0 auto;">
                            <!-- Video Container -->
                            <div id="view-video-container" style="display:none;" class="mb-4">
                                <div class="ratio ratio-16x9 rounded-3 overflow-hidden shadow-sm">
                                    <iframe id="view-video-iframe" src="" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                                </div>
                            </div>

                            <div id="view-content" class="lh-lg text-dark" style="font-size: 1.2rem !important; line-height: 1.9; color: #2d3748;">
                                <!-- HTML Content -->
                            </div>
                            
                            <div id="view-attachment-container" class="mt-5 p-4 bg-light rounded-3 border align-items-center" style="display:none;">
                                <div class="bg-white p-3 rounded-circle shadow-sm me-3 text-primary">
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

                            <hr class="my-5 opacity-10">
                            <div class="d-flex align-items-center gap-2">
                                <span class="small text-muted fw-bold me-2">TAGS:</span>
                                <div id="view-tags" class="d-flex flex-wrap gap-2"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const editorModal = new bootstrap.Modal(document.getElementById('editorModal'));
    const viewModal = new bootstrap.Modal(document.getElementById('viewModal'));
    const uploadUrl = '<?php echo $uploadUrl; ?>'; // Pass JS variable
    const initialPost = <?php echo json_encode($initialPost); ?>; // Deep link post

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
        document.getElementById('edit-area').value = post.area || 'Fiscal'; // NOVO
        document.getElementById('edit-category').value = post.category;
        document.getElementById('edit-date').value = post.date;
        document.getElementById('edit-author').value = post.author || '<?php echo $currentUser; ?>';
        document.getElementById('edit-summary').value = post.summary;
        document.getElementById('edit-video-url').value = post.video_url || ''; // NOVO
        
        // Set TinyMCE Content
        if(tinymce.get('edit-content')) {
            tinymce.get('edit-content').setContent(post.content);
        } else {
             document.getElementById('edit-content').value = post.content;
        }

        document.getElementById('edit-tags').value = post.tags.join(', ');
        
        // Show/Hide Delete Attachment
        const delDiv = document.getElementById('div-delete-attachment');
        const delCheck = document.getElementById('check-delete-attachment');
        if (post.attachment && post.attachment !== 'null' && post.attachment.trim() !== '') {
            delDiv.style.display = 'block';
            delCheck.checked = false;
        } else {
            delDiv.style.display = 'none';
            delCheck.checked = false;
        }
        
        document.getElementById('editorTitle').innerText = 'Editar Post';
        editorModal.show();
    }

    let currentPost = null; // Store current post for sharing

    function viewPost(post) {
        currentPost = post; // Store for sharing functions
        
        document.getElementById('view-title').innerText = post.title;
        document.getElementById('view-area').innerText = post.area || 'Fiscal';
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

        // Handle Video
        const videoContainer = document.getElementById('view-video-container');
        const videoIframe = document.getElementById('view-video-iframe');
        if (post.video_url) {
            let embedUrl = post.video_url;
            
            // Convert YouTube URL to embed
            if (embedUrl.includes('youtube.com/watch')) {
                const videoId = new URL(embedUrl).searchParams.get('v');
                embedUrl = `https://www.youtube.com/embed/${videoId}`;
            } else if (embedUrl.includes('youtu.be/')) {
                const videoId = embedUrl.split('youtu.be/')[1].split('?')[0];
                embedUrl = `https://www.youtube.com/embed/${videoId}`;
            } else if (embedUrl.includes('vimeo.com/')) {
                const videoId = embedUrl.split('vimeo.com/')[1].split('?')[0];
                embedUrl = `https://player.vimeo.com/video/${videoId}`;
            }
            
            videoIframe.src = embedUrl;
            videoContainer.style.display = 'block';
        } else {
            videoIframe.src = '';
            videoContainer.style.display = 'none';
        }

        // Handle Attachment
        const attachContainer = document.getElementById('view-attachment-container');
        const attachLink = document.getElementById('view-attachment-link');
        
        let attVal = post.attachment;

        // Normalize
        if (typeof attVal === 'string') {
            attVal = attVal.trim();
        }
        
        const hasAttachment = (typeof attVal === 'string') && 
                              (attVal.length > 0) &&
                              (attVal.toLowerCase() !== 'null') &&
                              (attVal.toLowerCase() !== 'undefined');
        
        if (hasAttachment) {
            attachLink.href = uploadUrl + post.attachment;
            attachLink.setAttribute('download', post.attachment);
            
            attachContainer.classList.add('d-flex');
            attachContainer.style.display = ''; // Clear inline display:none
        } else {
            attachLink.href = '#';
            attachLink.removeAttribute('download');
            
            attachContainer.classList.remove('d-flex');
            attachContainer.style.display = 'none';
        }

        // Handle Tags
        const tagsContainer = document.getElementById('view-tags');
        tagsContainer.innerHTML = '';
        post.tags.forEach(tag => {
            const span = document.createElement('span');
            span.className = 'badge bg-light text-secondary border';
            span.innerText = '#' + tag;
            tagsContainer.appendChild(span);
        });

        // Generate Table of Contents
        generateTOC();

        viewModal.show();
    }

    // Auto-open deep linked post
    if (initialPost) {
        viewPost(initialPost);
    }

    // Generate Table of Contents from headings
    function generateTOC() {
        const content = document.getElementById('view-content');
        const tocContainer = document.getElementById('toc-container');
        tocContainer.innerHTML = '';

        const headings = content.querySelectorAll('h2, h3');
        
        if (headings.length === 0) {
            tocContainer.innerHTML = '<p class="text-muted small">Nenhum capítulo encontrado</p>';
            return;
        }

        headings.forEach((heading, index) => {
            // Add ID to heading for anchor
            const id = `heading-${index}`;
            heading.id = id;

            // Create TOC item
            const link = document.createElement('a');
            link.href = `#${id}`;
            link.className = 'list-group-item list-group-item-action border-0 py-2 px-3 small';
            link.style.paddingLeft = heading.tagName === 'H3' ? '1.5rem' : '0.75rem';
            link.innerText = heading.innerText;
            
            link.onclick = (e) => {
                e.preventDefault();
                heading.scrollIntoView({ behavior: 'smooth', block: 'start' });
                
                // Highlight active
                tocContainer.querySelectorAll('a').forEach(a => a.classList.remove('active'));
                link.classList.add('active');
            };

            tocContainer.appendChild(link);
        });
    }

    // Share Functions
    function shareWhatsApp() {
        if (!currentPost) return;
        const text = `${currentPost.title}\n\n${currentPost.summary}\n\nLeia mais: ${window.location.href}`;
        window.open(`https://wa.me/?text=${encodeURIComponent(text)}`, '_blank');
    }

    function shareEmail() {
        if (!currentPost) return;
        const subject = currentPost.title;
        const body = `${currentPost.summary}\n\nLeia o artigo completo: ${window.location.href}`;
        window.location.href = `mailto:?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`;
    }

    function copyLink() {
        if (!currentPost) return;
        
        // Construct deep link
        const urlP = new URL(window.location.href);
        urlP.searchParams.set('post_id', currentPost.id);
        
        navigator.clipboard.writeText(urlP.toString()).then(() => {
            alert('Link do artigo copiado!');
        });
    }

    function copyContent() {
        if (!currentPost) return;
        const content = document.getElementById('view-content').innerText;
        navigator.clipboard.writeText(content).then(() => {
            alert('Conteúdo copiado para a área de transferência!');
        });
    }

    function clearSearch() {
        document.getElementById('searchInput').value = '';
        window.location.href = 'fiscal_blog.php';
    }
</script>
</body>
</html>
