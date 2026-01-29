<?php 
include 'includes/header.php'; 
require_once 'includes/portal_helpers.php';

require_once 'includes/portal_auth.php';

$portal = new SupportPortal();
$password = $portal->getTechnicalPassword();
$build = $portal->getLatestBuild();
$notices = $portal->getNotices();
$weatherConfig = $portal->getConfig('weather');

// Auth State
$loggedIn = isLoggedIn();
$currentUser = getCurrentUser();
$isAdmin = isAdmin();
$isSupport = isSupport();

// Track Online Status
if ($loggedIn) {
    (new PortalAuth())->updateLastSeen($_SESSION['user_id']);
}
$onlineUsers = (new PortalAuth())->getOnlineUsers();
?>

<!-- FORCE GLASS THEME OVERRIDES -->
<!-- GLASS THEME SYSTEM -->
<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@400;500;600;700&display=swap');

    :root {
        /* LIGHT GLASS THEME VARS */
        --glass-border: rgba(255, 255, 255, 0.4);
        --glass-highlight: rgba(255, 255, 255, 0.7);
        --glass-shadow: 0 12px 40px -8px rgba(0, 0, 0, 0.08);

        /* Background */
        --color-body-bg: #f3f5f9;
        --color-body-bg-gradient: linear-gradient(135deg, #f0f4f8 0%, #dbeafe 100%);

        /* Cards & Glass */
        --color-card-bg: rgba(255, 255, 255, 0.75);
        --color-card-bg-hover: rgba(255, 255, 255, 0.95);
        
        /* Text */
        --font-primary: 'Outfit', sans-serif;
        --font-body: 'Inter', sans-serif;
        
        --color-text-main: #1e293b;
        --color-accent: #3b82f6; 
    }

    /* GLOBAL STYLES */
    body {
        font-family: var(--font-body);
        background: var(--color-body-bg-gradient) !important;
        background-attachment: fixed !important;
        color: var(--color-text-main);
        min-height: 100vh;
    }

    h1, h2, h3, h4, h5, h6 {
        font-family: var(--font-primary);
        font-weight: 700;
        letter-spacing: -0.02em;
        color: #0f172a;
    }

    /* GLASS UTILITIES */
    .glass-panel, .card {
        background: var(--color-card-bg) !important;
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border: 1px solid var(--glass-border) !important;
        box-shadow: var(--glass-shadow);
        border-radius: 20px !important;
        transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
    }
    
    .card-glass {
        background: rgba(255, 255, 255, 0.95) !important;
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border: 1px solid var(--glass-border) !important;
        box-shadow: var(--glass-shadow);
    }
    
    .hover-card:hover {
        background: var(--color-card-bg-hover) !important;
        transform: translateY(-4px);
        box-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.12) !important;
        border-color: #fff !important;
    }

    /* WIDGETS */
    .widget-value {
        font-family: var(--font-primary);
        font-weight: 700;
        letter-spacing: -1px;
    }

    /* SEARCH BAR */
    #tool-search {
        background: rgba(255, 255, 255, 0.85) !important;
        border: 1px solid rgba(255,255,255,0.8) !important;
        backdrop-filter: blur(10px);
        font-size: 1.1rem;
        transition: all 0.3s ease;
    }
    #tool-search:focus {
        background: #fff !important;
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15) !important;
        border-color: var(--color-accent) !important;
    }

    /* ICON BOXES */
    .icon-box {
        border-radius: 16px;
        box-shadow: 0 8px 16px -4px rgba(0,0,0,0.1); 
        width: 60px;
        height: 60px;
        font-size: 1.6rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* GRADIENTS (Updated) */
    .bg-primary-gradient { background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); }
    .bg-success-gradient { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }
    .bg-info-gradient { background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%); }
    .bg-warning-gradient { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); }
    .bg-danger-gradient { background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); }
    .bg-purple-gradient { background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); }
    .bg-orange-gradient { background: linear-gradient(135deg, #f97316 0%, #ea580c 100%); }
    .bg-indigo-gradient { background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%); }
    .bg-teal-gradient { background: linear-gradient(135deg, #14b8a6 0%, #0d9488 100%); }
</style>
<script>
    // Body cleanup
    document.addEventListener('DOMContentLoaded', () => {
        document.body.classList.remove('bg-light');
    });
</script>

</div> <!-- Close Header Container -->

<!-- Top Right User Profile Removed (Moved to Sidebar) -->

<div class="container-fluid">
    <div class="row">
        
        <!-- Left Sidebar: Utils & Info (Fixed/Sticky) -->
        <div class="col-lg-3 col-xl-2 min-vh-100 py-4 d-flex flex-column gap-4 position-sticky top-0" style="height: 100vh; overflow-y: auto;">
            
            <!-- Welcome Message -->
            <?php 
             if($loggedIn) {
                date_default_timezone_set('America/Sao_Paulo');
                $hour = date('H');
                $greeting = ($hour >= 5 && $hour < 12) ? 'Bom dia' : (($hour >= 12 && $hour < 18) ? 'Boa tarde' : 'Boa noite');
                
                // Greeting Logic: Preferred Name > First Name of Full Name
                $displayName = $_SESSION['user_preferred_name'] ?? '';
                if (empty($displayName)) {
                    $parts = explode(' ', $currentUser); // $currentUser is FullName from auth
                    $displayName = $parts[0];
                }
                
                echo "<div class='px-2 mb-2'><h4 class='fw-bold text-dark mb-0'>$greeting,</h4><h4 class='fw-light text-secondary'>$displayName.</h4></div>";
             }
            ?>

            <!-- Online Users Sidebar Widget -->
            <div class="mt-4 px-3">
                <h6 class="text-secondary fw-bold text-uppercase small mb-3 opacity-75" style="letter-spacing: 1px;">Online Agora</h6>
                <?php if(empty($onlineUsers)): ?>
                    <p class="text-muted small">Ninguém online.</p>
                <?php else: ?>
                    <ul class="list-unstyled mb-0 d-flex flex-column gap-2">
                        <?php foreach($onlineUsers as $u): 
                             $isMe = ($loggedIn && $currentUser === $u['full_name']);
                             $statusColor = 'bg-success'; 
                        ?>
                        <li class="d-flex align-items-center bg-white bg-opacity-50 p-2 rounded-3 border border-white shadow-sm">
                            <div class="position-relative">
                                <div class="rounded-circle bg-gradient-primary-to-secondary d-flex align-items-center justify-content-center text-white fw-bold shadow-sm" 
                                     style="width: 36px; height: 36px; font-size: 0.85rem; background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);">
                                    <?php echo strtoupper(substr($u['full_name'], 0, 2)); ?>
                                </div>
                                <span class="position-absolute bottom-0 end-0 p-1 <?php echo $statusColor; ?> border border-white rounded-circle"></span>
                            </div>
                            <div class="ms-3 lh-1">
                                <span class="d-block fw-bold text-dark small text-truncate" style="max-width: 140px;"><?php echo htmlspecialchars($u['full_name']); ?></span>
                                <span class="d-block text-muted mt-1" style="font-size: 0.7rem;"><?php echo ucfirst($u['role']); ?> <?php if($isMe) echo '(Você)'; ?></span>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>

            <!-- Fiscal Blog Widget -->
            <div class="mt-4 px-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="text-secondary fw-bold text-uppercase small mb-0 opacity-75" style="letter-spacing: 1px;">Updates Fiscais</h6>
                    <a href="fiscal_blog.php" class="text-primary small text-decoration-none fw-bold">Ver todos</a>
                </div>
                
                <?php
                    $blogFile = __DIR__ . '/data/fiscal_blog.json';
                    $latestPosts = [];
                    if (file_exists($blogFile)) {
                        $posts = json_decode(file_get_contents($blogFile), true);
                        if ($posts) {
                            usort($posts, function($a, $b) { return strtotime($b['date']) - strtotime($a['date']); });
                            $latestPosts = array_slice($posts, 0, 3);
                        }
                    }
                ?>
                
                <?php if(empty($latestPosts)): ?>
                    <div class="text-center p-4 bg-white bg-opacity-50 rounded-4 border border-white">
                        <i class="bi bi-newspaper fs-4 text-muted opacity-50 mb-2"></i>
                        <p class="text-muted small mb-0">Nenhuma notícia.</p>
                    </div>
                <?php else: ?>
                    <div class="d-flex flex-column gap-3">
                        <?php foreach($latestPosts as $post): 
                            $isNew = (strtotime($post['date']) > strtotime('-3 days'));
                        ?>
                            <a href="fiscal_blog.php?q=<?php echo urlencode($post['title']); ?>" class="text-decoration-none text-dark card border-0 shadow-sm p-3 hover-card" style="background: rgba(255,255,255,0.7);">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-2 py-1" style="font-size: 0.65rem;"><?php echo htmlspecialchars($post['category']); ?></span>
                                    <small class="text-muted" style="font-size: 0.65rem;"><?php echo date('d \d\e M', strtotime($post['date'])); ?></small>
                                </div>
                                <div class="fw-bold small lh-sm text-truncate-2" style="font-size: 0.85rem;"><?php echo htmlspecialchars($post['title']); ?></div>
                                <?php if($isNew): ?>
                                    <div class="mt-2">
                                        <span class="badge bg-danger rounded-pill shadow-sm" style="font-size: 0.6rem;">NOVO</span>
                                    </div>
                                <?php endif; ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

            <!-- Version Display in Sidebar -->


        </div>
    </div>

        <!-- Main Content: Tools Grid (Scrollable) -->
        <div class="col-lg-9 col-xl-10 py-5 px-5">
            
            <!-- Search & Widgets Header -->
            <div class="mb-5">
                <div class="row align-items-center mb-5">
                    <div class="col-md-5">
                         <div class="d-inline-flex align-items-center gap-3 user-select-none">
                            <div class="bg-gradient-primary-to-secondary text-white rounded-4 d-flex align-items-center justify-content-center shadow-lg" 
                                 style="width: 56px; height: 56px; background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);">
                                <i class="bi bi-grid-fill fs-3"></i>
                            </div>
                            <div>
                                <h1 class="display-6 fw-bold mb-0 text-dark" style="font-family: 'Outfit', sans-serif;">
                                    Suporte <span class="text-primary">Hub</span>
                                </h1>
                                <p class="text-muted small mb-0 fw-medium opacity-75">Portal de ferramentas e utilitários.</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Search Bar -->
                    <div class="col-md-7">
                        <div class="position-relative">
                            <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-4 text-primary opacity-50 fs-5"></i>
                            <input type="text" id="tool-search" class="form-control form-control-lg rounded-pill border-0 ps-5 py-3 text-dark shadow-sm" placeholder="O que você procura hoje?" onkeyup="filterTools()">
                        </div>
                    </div>

                    <!-- Profile Widget (Moved Here) -->
                    <div class="d-flex justify-content-end" style="position: relative; z-index: 1060;">
                        <?php if($loggedIn): ?>
                            <div class="dropdown">
                                <button class="btn btn-white shadow-sm rounded-pill px-3 py-2 dropdown-toggle text-primary fw-bold border-0" type="button" data-bs-toggle="dropdown">
                                    <i class="bi bi-person-circle me-2"></i>Menu
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2 rounded-4 p-2">
                                    <li><h6 class="dropdown-header">Minha Conta: <?php echo htmlspecialchars($currentUser); ?></h6></li>
                                    <li><a class="dropdown-item rounded-3" href="profile.php"><i class="bi bi-person me-2"></i>Meu Perfil</a></li>
                                    <?php if($isAdmin): ?>
                                        <li><a class="dropdown-item rounded-3" href="admin_users.php"><i class="bi bi-people me-2"></i>Gerenciar Usuários</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                    <?php endif; ?>
                                    <li><a class="dropdown-item rounded-3" href="#" data-bs-toggle="modal" data-bs-target="#changePassModal"><i class="bi bi-key me-2"></i>Alterar Senha</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form method="POST" action="includes/portal_actions.php">
                                            <input type="hidden" name="portal_action" value="logout">
                                            <button type="submit" class="dropdown-item rounded-3 text-danger"><i class="bi bi-box-arrow-right me-2"></i>Sair</button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        <?php else: ?>
                            <button class="btn btn-sm btn-primary shadow-sm rounded-pill px-4 fw-bold" data-bs-toggle="modal" data-bs-target="#portalLoginModal">
                                <i class="bi bi-box-arrow-in-right me-2"></i>Entrar
                            </button>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Widgets Row -->
                <div class="row g-4 mb-4">
                    <!-- Technical Password Widget -->
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm rounded-4 h-100 position-relative overflow-hidden group-action hover-card">
                             <div class="card-body p-4 position-relative z-1 d-flex flex-column justify-content-between">
                                <div class="d-flex justify-content-between align-items-start">
                                    <h6 class="text-uppercase text-secondary fw-bold small opacity-75 mb-0" style="letter-spacing: 1px;">Senha Técnica</h6>
                                    <div class="bg-primary bg-opacity-10 p-2 rounded-3 text-primary">
                                        <i class="bi bi-key-fill fs-5"></i>
                                    </div>
                                </div>
                                <div class="d-flex align-items-end justify-content-between mt-3">
                                    <h2 class="display-5 widget-value mb-0 text-primary"><?php echo $password; ?></h2>
                                    <div class="text-end lh-1 text-muted">
                                        <div class="small fw-bold"><?php echo date('d/m/Y'); ?></div>
                                        <div id="brasilia-clock" class="small opacity-75">--:--:--</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Weather Widget -->
                    <div class="col-md-4">
                         <div class="card border-0 shadow-sm rounded-4 h-100 position-relative overflow-hidden group-action hover-card">
                            <?php if($isSupport): ?>
                                <button class="btn btn-sm btn-light btn-action position-absolute top-0 end-0 m-2 rounded-circle shadow-sm" style="width: 28px; height: 28px; padding: 0;" onclick="openWeatherModal()"><i class="bi bi-pencil-fill text-primary" style="font-size: 0.7rem;"></i></button>
                            <?php endif; ?>
                            <div class="card-body p-4 d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-uppercase text-secondary fw-bold small opacity-75 mb-2" style="letter-spacing: 1px;">Clima & Tempo</h6>
                                    <div class="d-flex align-items-baseline">
                                        <h3 class="widget-value mb-0 me-2 text-dark" style="font-size: 2.5rem;" id="weather-temp">--</h3>
                                        <span class="small text-muted fw-bold" id="weather-desc">...</span>
                                    </div>
                                    <div class="small text-muted mt-1 fw-medium"><i class="bi bi-geo-alt-fill me-1 text-danger opacity-75"></i> <span id="weather-city-display"><?php echo htmlspecialchars($weatherConfig['city'] ?? 'Brusque'); ?></span></div>
                                </div>
                                <i class="bi bi-cloud-sun fs-1 text-info opacity-75"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Version Widget -->
                    <div class="col-md-4">
                         <div class="card border-0 shadow-sm rounded-4 h-100 position-relative overflow-hidden group-action hover-card">
                             <?php if($isAdmin): ?>
                                <button class="btn btn-sm btn-light btn-action position-absolute top-0 end-0 m-2 rounded-circle shadow-sm" style="width: 28px; height: 28px; padding: 0;" onclick="openVersionModal()"><i class="bi bi-pencil-fill text-primary" style="font-size: 0.7rem;"></i></button>
                            <?php endif; ?>
                            <div class="card-body p-4 position-relative z-1 d-flex flex-column justify-content-between">
                                <div class="d-flex justify-content-between align-items-start">
                                    <h6 class="text-uppercase text-secondary fw-bold small opacity-75 mb-0" style="letter-spacing: 1px;">Versão Uniplus</h6>
                                    <div class="bg-success bg-opacity-10 p-2 rounded-3 text-success">
                                        <i class="bi bi-box-seam fs-5"></i>
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <h2 class="display-6 widget-value mb-0 text-dark">v<?php echo $build['version']; ?></h2>
                                    <div class="d-flex justify-content-between align-items-end mt-1">
                                        <p class="text-muted small mb-0 lh-1 opacity-75">Data release:<br><?php echo $build['date']; ?></p>
                                        <a href="release_notes.php" class="btn btn-sm btn-primary rounded-pill px-3 py-1 shadow-sm fs-7">Ver detalhes</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Horizontal Category Navigation -->
            <div class="mb-5">
                <div class="nav nav-pills gap-3 d-flex flex-nowrap overflow-auto py-2" id="hub-nav" style="scrollbar-width: none;">
                    <button class="nav-link active rounded-pill px-4 fw-bold shadow-sm d-flex align-items-center gap-2 flex-shrink-0" onclick="filterCategory('all', this)">
                        <i class="bi bi-grid-fill"></i> Todos
                    </button>
                    <button class="nav-link bg-white text-secondary rounded-pill px-4 fw-bold shadow-sm d-flex align-items-center gap-2 flex-shrink-0" onclick="filterCategory('suporte', this)">
                        <i class="bi bi-people-fill"></i> Suporte
                    </button>
                    <button class="nav-link bg-white text-secondary rounded-pill px-4 fw-bold shadow-sm d-flex align-items-center gap-2 flex-shrink-0" onclick="filterCategory('fiscal', this)">
                        <i class="bi bi-receipt"></i> Fiscal
                    </button>
                    <button class="nav-link bg-white text-secondary rounded-pill px-4 fw-bold shadow-sm d-flex align-items-center gap-2 flex-shrink-0" onclick="filterCategory('utilitarios', this)">
                        <i class="bi bi-tools"></i> Utilitários
                    </button>
                    <button class="nav-link bg-white text-secondary rounded-pill px-4 fw-bold shadow-sm d-flex align-items-center gap-2 flex-shrink-0" onclick="filterCategory('sistema', this)">
                        <i class="bi bi-hdd-network"></i> Sistema
                    </button>
                </div>
            </div>

            <div id="tools-main-container">
                <?php
                if (!function_exists('renderCard')) {
                    function renderCard($id, $title, $desc, $link, $iconClass, $iconName, $portal, $isAdmin, $isDev = false, $openNewTab = false) {
                        $isHidden = $portal->isBlocked($id); 
                        global $isSupport; 
                        $canSeeHidden = $isAdmin || $isSupport;

                        if ($isHidden && !$canSeeHidden) return;
    
                        $opacityClass = $isHidden ? 'opacity-50 grayscale' : '';
                        $pointerClass = ($isHidden && !$canSeeHidden) ? 'pe-none' : ''; 
                        
                        $eyeIcon = $isHidden ? 'bi-eye-slash-fill' : 'bi-eye';
                        $eyeColor = $isHidden ? 'text-danger' : 'text-muted';
                        
                        $badge = '';
                        if ($isDev) {
                            $badge = '<span class="badge bg-warning text-dark ms-2 small">Não Finalizado</span>';
                        }
                        
                        $targetAttr = $openNewTab ? 'target="_blank"' : '';
    
                        echo '
                        <div class="col-xl-3 col-lg-4 col-md-6 tool-col" data-title="' . strtolower($title) . '" data-desc="' . strtolower($desc) . '">
                            <div class="card h-100 border-0 shadow-sm hover-card rounded-4 position-relative group-action tool-card ' . $opacityClass . '">
                                ';
                                
                                
                                if (hasCapability('edit_tools')) {
                                    echo '
                                    <button class="btn btn-sm btn-white position-absolute top-0 end-0 m-2 rounded-circle shadow-sm btn-admin-toggle"
                                            style="width: 32px; height: 32px; padding: 0; z-index: 10;"
                                            onclick="toggleCard(\''.$id.'\', this); event.preventDefault();"
                                            title="Ocultar/Exibir">
                                        <i class="bi '.$eyeIcon.' '.$eyeColor.' small"></i>
                                    </button>';
                                }
    
                                echo '
                                <a href="'.$link.'" '.$targetAttr.' class="text-decoration-none '.$pointerClass.'">
                                    <div class="card-body p-4 card-body-content">
                                        <div class="d-flex align-items-center mb-3 card-header-flex">
                                            <div class="icon-box '.$iconClass.' text-white rounded-3 me-3 icon-responsive">
                                                <i class="bi '.$iconName.'"></i> 
                                            </div>
                                            <div class="d-flex flex-column text-container">
                                                <h5 class="card-title fw-bold text-dark mb-1 text-title lh-sm">'.$title.'</h5>
                                                <div class="badge-container">'.$badge.'</div>
                                            </div>
                                        </div>
                                        <p class="card-text text-muted small text-desc">'.$desc.'</p>
                                    </div>
                                </a>
                            </div>
                        </div>';
                    }
                }
                ?>

            <!-- Support Team -->
            <div class="mb-5 tool-section" id="sec-suporte">
                <h3 class="section-title text-secondary mb-4"><i class="bi bi-people-fill me-2"></i>Equipe Suporte</h3>
                <div class="row g-4 helper-row-container">
                    <?php
                    renderCard('card-team-intranet', 'Intranet Suporte', 'Ferramentas internas e gestão de conhecimento.', 'intranet.php', 'bg-indigo-gradient', 'bi-globe-americas', $portal, $isAdmin);
                    renderCard('card-team-ppr', 'Gestão PPR', 'Acompanhamento de metas e resultados PPR.', 'ppr_manager.php', 'bg-warning-gradient', 'bi-trophy-fill', $portal, $isAdmin);
                    renderCard('card-team-meetings', 'Reuniões & Pautas', 'Agenda de reuniões, atas e pautas do setor de suporte.', 'meetings.php', 'bg-teal-gradient', 'bi-calendar-event', $portal, $isAdmin);
                    ?>
                </div>
            </div>

            <!-- Fiscal Tools -->
            <div class="mb-5 tool-section" id="sec-fiscal">
                <h3 class="section-title text-secondary mb-4"><i class="bi bi-receipt me-2"></i>Fiscal</h3>
                <div class="row g-4 helper-row-container">
                    <?php
                    // Helper for card rendering

        
                    // Apply Cards (Internal)
                    renderCard('card-fiscal-blog', 'Blog Fiscal', 'Notícias e atualizações tributárias.', 'fiscal_blog.php', 'bg-danger-gradient', 'bi-newspaper', $portal, $isAdmin);
                    renderCard('card-xml-gen', 'Gerador XML NF-e', 'Gere XMLs de Importação a partir de DI.', 'xml_generator.php', 'bg-info-gradient', 'bi-file-earmark-code', $portal, $isAdmin, true);
                    renderCard('card-manifestador', 'Manifestador', 'Em desenvolvimento', '#', 'bg-secondary', 'bi-cloud-download', $portal, $isAdmin, true);
                    renderCard('card-nfse', 'NFS-e Nacional', 'Validador e Consulta NFS-e.', 'nfse-nacional.php', 'bg-orange-gradient', 'bi-building', $portal, $isAdmin, true);
                    renderCard('card-reforma', 'Reforma Tributária', 'Guia e tabelas de crédito.', 'reforma_tributaria.php', 'bg-success-gradient', 'bi-percent', $portal, $isAdmin);
                    renderCard('card-xml-analyzer', 'NFC-e / NF-e', 'Layout e regras de validação.', 'xml-analyzer.php', 'bg-warning-gradient', 'bi-bug', $portal, $isAdmin);
                    renderCard('card-extractor', 'Extrator Postgres', 'Extraia XMLs do banco.', 'postgres_xml_extractor.php', 'bg-primary-gradient', 'bi-database-down', $portal, $isAdmin, true);
                    renderCard('card-editor', 'Editor de Notas', 'Correção manual de valores.', 'invoice_tax_editor.php', 'bg-success-gradient', 'bi-pencil-square', $portal, $isAdmin, true);
                    
                    // NEW: Cidades NFS-e Project Integration
                    renderCard('card-cidades-nfse', 'Gestão Municípios NFS-e', 'Mapa de adesão e gestão de provedores (Receita Federal).', 'https://app.powerbi.com/view?r=eyJrIjoiNGQ4YTcxNmMtMzdhNC00Mzc5LTllM2EtMjY1MTM3NWQyZDgyIiwidCI6IjZmNDlhYTQzLTgyMmEtNGMyMC05NjcwLWRiNzcwMGJmMWViMCJ9&pageName=608609c2e0a53d7a3c6ev', 'bg-indigo-gradient', 'bi-bar-chart-fill', $portal, $isAdmin, false, true);

                    // NEW: Validador XML NFS-e (Genérico)
                    renderCard('card-nfse-validator', 'Validador XML NFS-e', 'Valide XML contra XSD.', 'nfse_validator.php', 'bg-purple-gradient', 'bi-shield-check', $portal, $isAdmin);
                    ?>
                </div>
            </div>



            <!-- Utilities -->
            <div class="mb-5 tool-section" id="sec-utilitarios">
                <h3 class="section-title text-secondary mb-4"><i class="bi bi-tools me-2"></i>Utilitários</h3>
                <div class="row g-4 helper-row-container">
                    <?php
                    // Core Utilities
                    renderCard('card-validator', 'Validador R2D2', 'Valide arquivos de importação.', 'validator.php', 'bg-success-gradient', 'bi-check-circle', $portal, $isAdmin);
                    renderCard('card-converters', 'Conversores', 'Conversão de unidades.', 'converters.php', 'bg-purple-gradient', 'bi-arrow-left-right', $portal, $isAdmin, true);
                    renderCard('card-calculators', 'Calculadoras Porcentagem', 'Cálculos de porcentagem.', 'calculators.php', 'bg-success-gradient', 'bi-calculator', $portal, $isAdmin);


                    // Migrated Hub Tools (External - TARGET BLANK)
                    renderCard('card-hub-restore', 'Restauração de Bases', 'Painel para baixar backups e monitorar restaurações no DBSERVER.', 'http://dbserver/restaurador', 'bg-info', 'bi-hdd-stack', $portal, $isAdmin, false, true);
                    renderCard('card-hub-fcs', 'FCS', 'Sistema de FCS (Fato, Causa e Solução).', 'http://fcs.intelidata.local/', 'bg-danger-gradient', 'bi-lightbulb', $portal, $isAdmin, false, true);

                    renderCard('card-hub-comercial', 'Portal Comercial', 'Painel de administração de licenças e UniplusWeb.', 'https://comercial.intelidata.inf.br', 'bg-primary', 'bi-briefcase', $portal, $isAdmin, false, true);

                    renderCard('card-hub-builds', 'Builds', 'Repositório de builds.', 'https://builds.unipluscdn.com', 'bg-warning-gradient', 'bi-box-seam', $portal, $isAdmin, false, true);
                    renderCard('card-hub-installers', 'Instaladores', 'Repositório de instaladores.', 'https://instaladores.unipluscdn.com', 'bg-info-gradient', 'bi-disc', $portal, $isAdmin, false, true);

                    renderCard('card-hub-zabbix', 'Zabbix Getcard', 'Ferramenta de monitoramento dos servidores Getcard.', 'https://zabbix.uniplusweb.com', 'bg-danger', 'bi-activity', $portal, $isAdmin, false, true);
                    renderCard('card-hub-cdu', 'Documentação do Sistema', 'Base de conhecimento do Uniplus', 'https://kb.beemore.com/dc/pt-br/domains/suporte/resources/documentacao-do-sistema/categories/produtos', 'bg-success-gradient', 'bi-journal-richtext', $portal, $isAdmin, false, true);
                    renderCard('card-hub-ead', 'EAD Intelidata', 'EAD Intelidata.', 'https://ead.intelidata.inf.br/', 'bg-purple-gradient', 'bi-mortarboard', $portal, $isAdmin, false, true);
                    renderCard('card-hub-youtube', 'Canal YouTube', 'Canal da Intelidata no YouTube.', 'https://www.youtube.com/@intelidatainformatica', 'bg-danger', 'bi-youtube', $portal, $isAdmin, false, true);
                    renderCard('card-hub-delete-report', 'Relatório de Exclusão DBSERVER', 'Relatório de bases excluídas.', 'http://webhook/relatorio.php', 'bg-secondary', 'bi-trash', $portal, $isAdmin, false, true);
                    renderCard('card-hub-support-util', 'Suporte Util', 'Ferramenta de utilitários do Suporte.', 'http://suporteutil.intelidata.local', 'bg-info', 'bi-archive', $portal, $isAdmin, false, true);
                    ?>
                </div>
            </div>
        
            <!-- System -->
            <div class="mb-5 tool-section" id="sec-sistema">
                <h3 class="section-title text-secondary mb-4"><i class="bi bi-hdd-network me-2"></i>Sistema</h3>
                <div class="row g-4 helper-row-container">
                    <?php
                    renderCard('card-dict', 'Dicionário de Dados', 'Consulte tabelas do sistema.', 'dictionary.php', 'bg-primary-gradient', 'bi-table', $portal, $isAdmin);
                    renderCard('card-sql-editor', 'Editor SQL', 'Editor SQL Postgres completo.', 'sql_editor.php', 'bg-indigo-gradient', 'bi-terminal-fill', $portal, $isAdmin, true);
                    renderCard('card-log', 'Analisador de Logs', 'Análise de logs Uniplus.', 'log-analyzer.php', 'bg-danger-gradient', 'bi-file-medical', $portal, $isAdmin, true);
                    renderCard('card-release', 'Release Notes Uniplus', 'Histórico de atualizações.', 'release_notes.php', 'bg-info-gradient', 'bi-megaphone', $portal, $isAdmin);
                    renderCard('card-audit', 'Auditor de Logs', 'Auditoria do sistema Uniplus.', 'audit_analyzer.php', 'bg-teal-gradient', 'bi-shield-check', $portal, $isAdmin, true);
                    renderCard('card-toolkit', 'Uniplus Toolkit', 'Ferramentas avançadas do sistema.', 'uniplus_toolkit.php', 'bg-indigo-gradient', 'bi-collection-fill', $portal, $isAdmin, true);
                    ?>
                </div>
            </div>

            </div> <!-- Close Tools Container -->
        </div> <!-- Close Main Column -->


        <!-- Right Sidebar removed -->

        
    </div> <!-- Close Row -->
</div> <!-- Close Container -->

<!-- Auth Modal (Login/Register) -->
<div class="modal fade" id="portalLoginModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content card-glass border-0 shadow-lg rounded-4 overflow-hidden" style="background: rgba(255, 255, 255, 0.95) !important;">
            <div class="modal-header border-0 pb-0 justify-content-center pt-4">
                <div class="text-center">
                    <div class="bg-gradient-primary-to-secondary text-white rounded-circle d-inline-flex align-items-center justify-content-center shadow-sm mb-3" 
                         style="width: 48px; height: 48px; background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);">
                        <i class="bi bi-person-fill fs-4"></i>
                    </div>
                    <h5 class="modal-title fw-bold font-primary mx-auto">Acesso ao Hub</h5>
                </div>
                <button type="button" class="btn-close position-absolute top-0 end-0 m-3" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 pt-2">
                
                <ul class="nav nav-pills nav-fill mb-4 bg-light bg-opacity-50 p-1 rounded-pill border" id="pills-tab" role="tablist">
                  <li class="nav-item" role="presentation">
                    <button class="nav-link active rounded-pill fw-bold small" id="pills-login-tab" data-bs-toggle="pill" data-bs-target="#pills-login" type="button" role="tab">Entrar</button>
                  </li>
                  <li class="nav-item" role="presentation">
                    <button class="nav-link rounded-pill fw-bold small" id="pills-register-tab" data-bs-toggle="pill" data-bs-target="#pills-register" type="button" role="tab">Criar Conta</button>
                  </li>
                </ul>
                
                <div class="tab-content" id="pills-tabContent">
                  <!-- LOGIN FORM -->
                  <div class="tab-pane fade show active" id="pills-login" role="tabpanel">
                      <form method="POST" action="includes/portal_actions.php">
                            <input type="hidden" name="portal_action" value="login">
                            
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control bg-light" id="loginUser" name="user" placeholder="Usuário" required>
                                <label for="loginUser">Usuário</label>
                            </div>
                            
                            <div class="form-floating mb-3">
                                <input type="password" class="form-control bg-light" id="loginPass" name="pass" placeholder="Senha" required>
                                <label for="loginPass">Senha</label>
                            </div>
                            
                            <?php if(isset($_SESSION['login_error'])): ?>
                                <div class="alert alert-danger py-2 small mb-3 border-0 bg-danger bg-opacity-10 text-danger rounded-3 fw-medium">
                                    <i class="bi bi-exclamation-circle me-1"></i> <?php echo $_SESSION['login_error']; unset($_SESSION['login_error']); ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if(isset($_SESSION['login_success_msg'])): ?>
                                <div class="alert alert-success py-2 small mb-3 border-0 bg-success bg-opacity-10 text-success rounded-3 fw-medium">
                                    <i class="bi bi-check-circle me-1"></i> <?php echo $_SESSION['login_success_msg']; unset($_SESSION['login_success_msg']); ?>
                                </div>
                            <?php endif; ?>

                            <button type="submit" class="btn btn-primary w-100 rounded-pill fw-bold py-2 shadow-sm">
                                Entrar <i class="bi bi-arrow-right-short ms-1"></i>
                            </button>
                      </form>
                  </div>
                  
                  <!-- REGISTER FORM -->
                  <div class="tab-pane fade" id="pills-register" role="tabpanel">
                      <form method="POST" action="includes/portal_actions.php">
                            <input type="hidden" name="portal_action" value="register">
                            
                            <div class="form-floating mb-2">
                                <input type="text" class="form-control bg-light" id="regName" name="full_name" placeholder="Nome Completo" required>
                                <label for="regName">Nome Completo</label>
                            </div>
                            
                            <div class="form-floating mb-2">
                                <input type="text" class="form-control bg-light" id="regUser" name="user" placeholder="Usuário" required>
                                <label for="regUser">Usuário</label>
                            </div>

                            <div class="form-floating mb-3">
                                <input type="password" class="form-control bg-light" id="regPass" name="pass" placeholder="Senha" required>
                                <label for="regPass">Senha</label>
                            </div>

                            <?php if(isset($_SESSION['register_error'])): ?>
                                <div class="alert alert-danger py-2 small mb-3 border-0 bg-danger bg-opacity-10 text-danger rounded-3 fw-medium">
                                    <?php echo $_SESSION['register_error']; unset($_SESSION['register_error']); ?>
                                </div>
                            <?php endif; ?>

                            <button type="submit" class="btn btn-primary w-100 rounded-pill fw-bold py-2 shadow-sm">
                                Criar Conta
                            </button>
                      </form>
                  </div>
                </div>

            </div>
        </div>
    </div>
</div>
                                        <label class="form-label small text-muted text-uppercase fw-bold">Nome Completo</label>
                                        <input type="text" class="form-control" name="full_name" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small text-muted text-uppercase fw-bold">Usuário</label>
                                        <input type="text" class="form-control" name="user" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small text-muted text-uppercase fw-bold">Senha</label>
                                        <input type="password" class="form-control" name="pass" required>
                                    </div>
                                    
                                    <?php if(isset($_SESSION['register_error'])): ?>
                                        <div class="alert alert-danger py-2 small mb-3"><?php echo $_SESSION['register_error']; unset($_SESSION['register_error']); ?></div>
                                    <?php endif; ?>

                                    <button type="submit" class="btn btn-success w-100 rounded-pill fw-bold py-2 text-white">Criar Conta</button>
                              </form>
                          </div>
                        </div>
                   </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Modern Card Styles */
/* Modern Card Styles */
.hover-card {
    transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
    border: 1px solid var(--color-glass-border) !important;
    /* Background handled by .card !important in top block */
}

/* Modals Glass */
.modal-content {
    background: rgba(30, 41, 59, 0.85) !important;
    backdrop-filter: blur(16px);
    border: 1px solid var(--color-glass-border);
    color: var(--color-text-main);
}
.modal-header, .modal-footer {
    border-color: var(--color-border) !important;
}
.btn-close {
    filter: invert(1) grayscale(100%) brightness(200%);
}

.section-title {
    font-size: 1.25rem;
    font-weight: 600;
    border-bottom: 2px solid var(--color-border);
    padding-bottom: 0.5rem;
    color: var(--color-text-secondary);
}

/* Icon Boxes with Gradients */
.icon-box {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    box-shadow: 0 4px 6px rgba(0,0,0,0.2);
}

.bg-primary-gradient { background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%); }
.bg-success-gradient { background: linear-gradient(135deg, #198754 0%, #146c43 100%); }
.bg-info-gradient { background: linear-gradient(135deg, #0dcaf0 0%, #0aa2c0 100%); }
.bg-warning-gradient { background: linear-gradient(135deg, #ffc107 0%, #ffca2c 100%); }
.bg-danger-gradient { background: linear-gradient(135deg, #dc3545 0%, #b02a37 100%); }
.bg-purple-gradient { background: linear-gradient(135deg, #6f42c1 0%, #59359a 100%); }
.bg-orange-gradient { background: linear-gradient(135deg, #fd7e14 0%, #e35d0b 100%); }
.bg-indigo-gradient { background: linear-gradient(135deg, #6610f2 0%, #520dc2 100%); }
.bg-teal-gradient { background: linear-gradient(135deg, #20c997 0%, #1aa179 100%); }

/* List View Styles */
.list-view .tool-col {
    width: 100% !important;
}

.list-view .card {
    border: none !important;
    border-bottom: 1px solid #f0f0f0 !important;
    border-radius: 8px !important;
    box-shadow: none !important;
    margin-bottom: 0px !important;
}

.list-view .card:hover {
    transform: none !important;
    background-color: #f8f9fa !important;
    box-shadow: none !important;
    z-index: 10;
}

.list-view .helper-row-container {
    --bs-gutter-y: 0.5rem;
}

.list-view .card-body-content {
    display: flex;
    align-items: center;
    padding: 1rem 1.5rem !important;
}

.list-view .card-header-flex {
    margin-bottom: 0 !important;
    margin-right: 2rem;
    min-width: 250px;
}

.list-view .icon-responsive {
    width: 36px !important;
    height: 36px !important;
    font-size: 0.8rem;
    margin-right: 1.5rem !important;
}

.list-view .icon-responsive i {
    font-size: 1.1rem !important;
}

.list-view .text-desc {
    margin-bottom: 0 !important;
    flex-grow: 1;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.list-view .btn-admin-toggle {
    display: block; /* Keep toggle visible */
    position: static !important;
    margin: 0 !important;
    margin-left: 1rem;
}

</style>

<!-- Weather Edit Modal -->
<div class="modal fade" id="weatherModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form id="weatherForm" class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Editar Clima</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label small fw-bold">Cidade</label>
                    <input type="text" class="form-control" name="city" id="weatherInputCity" value="<?php echo htmlspecialchars($weatherConfig['city'] ?? 'Brusque'); ?>" required>
                </div>
                <div class="row">
                    <div class="col-6 mb-3">
                        <label class="form-label small fw-bold">Latitude</label>
                        <input type="number" step="any" class="form-control" name="lat" id="weatherInputLat" value="<?php echo htmlspecialchars($weatherConfig['lat'] ?? '-27.1177'); ?>" required>
                    </div>
                    <div class="col-6 mb-3">
                        <label class="form-label small fw-bold">Longitude</label>
                        <input type="number" step="any" class="form-control" name="lon" id="weatherInputLon" value="<?php echo htmlspecialchars($weatherConfig['lon'] ?? '-48.9103'); ?>" required>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-primary w-100 rounded-pill" onclick="saveWeather()">Salvar</button>
            </div>
        </form>
    </div>
</div>

<!-- Version Edit Modal -->
<div class="modal fade" id="versionModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <form id="versionForm" class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Editar Versão</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label small fw-bold">Versão</label>
                    <input type="text" class="form-control" id="versionInput" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Data (dd/mm/aaaa)</label>
                    <input type="text" class="form-control" id="dateInput" required>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-primary w-100 rounded-pill" onclick="saveVersion()">Salvar</button>
            </div>
        </form>
    </div>
</div>

<!-- Notice Add Modal -->
<div class="modal fade" id="noticeModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form id="noticeForm" class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Adicionar Aviso</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label small fw-bold">Tipo</label>
                    <select class="form-select" id="noticeType">
                        <option value="info">Informação (Azul)</option>
                        <option value="warning">Atenção (Amarelo)</option>
                        <option value="danger">Alerta (Vermelho)</option>
                        <option value="success">Sucesso (Verde)</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Descrição</label>
                    <textarea class="form-control" id="noticeDesc" rows="3" required></textarea>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-primary w-100 rounded-pill" onclick="saveNotice()">Adicionar</button>
            </div>
        </form>
    </div>
</div>

<script>
// Hub Navigation & Filtering
function filterCategory(category, btn) {
    // Nav Active State
    document.querySelectorAll('#hub-nav .nav-link').forEach(el => {
         el.classList.remove('active', 'bg-primary', 'text-white');
         el.classList.add('text-secondary');
    });
    btn.classList.add('active', 'bg-primary', 'text-white');
    btn.classList.remove('text-secondary');

    // Filter Sections
    const sections = document.querySelectorAll('.tool-section');
    sections.forEach(sec => {
        if (category === 'all' || sec.id === 'sec-' + category) {
            sec.classList.remove('d-none');
            // Animate fade in
            sec.style.opacity = '0';
            setTimeout(() => sec.style.opacity = '1', 50);
        } else {
            sec.classList.add('d-none');
        }
    });
}

function filterTools() {
    const term = document.getElementById('tool-search').value.toLowerCase();
    const cols = document.querySelectorAll('.tool-col');
    
    cols.forEach(col => {
        const title = col.getAttribute('data-title');
        const desc = col.getAttribute('data-desc');
        
        if (title.includes(term) || desc.includes(term)) {
            col.style.display = 'block';
        } else {
            col.style.display = 'none';
        }
    });

    // If searching, show all sections temporarily (optional, but good UX)
    if(term.length > 0) {
        document.querySelectorAll('.tool-section').forEach(sec => sec.classList.remove('d-none'));
    } else {
        // Re-apply current category filter if search cleared (simplified: just click active)
        document.querySelector('#hub-nav .nav-link.active').click();
    }
}

const weatherConfig = {
    lat: <?php echo $weatherConfig['lat'] ?? -27.1177; ?>,
    lon: <?php echo $weatherConfig['lon'] ?? -48.9103; ?>
};

// Weather Fetch
document.addEventListener('DOMContentLoaded', () => {
    fetch(`https://api.open-meteo.com/v1/forecast?latitude=${weatherConfig.lat}&longitude=${weatherConfig.lon}&current_weather=true`)
        .then(response => response.json())
        .then(data => {
            if(data.current_weather) {
                document.getElementById('weather-temp').innerText = Math.round(data.current_weather.temperature) + "°C";
                const code = data.current_weather.weathercode;
                let desc = "Céu Limpo";
                if(code > 3) desc = "Nublado";
                if(code > 45) desc = "Nevoeiro";
                if(code > 50) desc = "Chuvoso";
                if(code > 95) desc = "Tempestade";
                document.getElementById('weather-desc').innerText = desc;
            }
        })
        .catch(err => {
            document.getElementById('weather-desc').innerText = "Indisponível";
        });
        
    // Auto open modal
    <?php if(isset($_SESSION['login_error_flag']) || isset($_SESSION['register_error_flag'])): ?>
        var myModal = new bootstrap.Modal(document.getElementById('portalLoginModal'));
        myModal.show();
        // Switch to register tab if register error
        <?php if(isset($_SESSION['register_error_flag'])): ?>
            const regTab = document.querySelector('#pills-register-tab');
            const regPill = new bootstrap.Tab(regTab);
            regPill.show();
            <?php unset($_SESSION['register_error_flag']); ?>
        <?php endif; ?>
        <?php unset($_SESSION['login_error_flag']); ?>
    <?php endif; ?>
});

// Admin Functions
function openWeatherModal() {
    new bootstrap.Modal(document.getElementById('weatherModal')).show();
}

function saveWeather() {
    const formData = new FormData();
    formData.append('action', 'update_weather');
    formData.append('city', document.getElementById('weatherInputCity').value);
    formData.append('lat', document.getElementById('weatherInputLat').value);
    formData.append('lon', document.getElementById('weatherInputLon').value);

    fetch('includes/portal_actions.php', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
        if(data.success) location.reload();
        else alert('Erro ao salvar.');
    });
}

function openVersionModal() {
    new bootstrap.Modal(document.getElementById('versionModal')).show();
}

function saveVersion() {
    const formData = new FormData();
    formData.append('action', 'update_version');
    formData.append('version', document.getElementById('versionInput').value);
    formData.append('date', document.getElementById('dateInput').value);

    fetch('includes/portal_actions.php', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
        if(data.success) location.reload();
        else alert('Erro ao salvar.');
    });
}

function openNoticeModal() {
    new bootstrap.Modal(document.getElementById('noticeModal')).show();
}

function saveNotice() {
    const formData = new FormData();
    formData.append('action', 'add_notice');
    formData.append('type', document.getElementById('noticeType').value);
    formData.append('description', document.getElementById('noticeDesc').value);

    fetch('includes/portal_actions.php', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
        if(data.success) location.reload();
        else alert('Erro ao salvar.');
    });
}

function deleteNotice(id) {
    if(!confirm("Remover este aviso?")) return;
    const formData = new FormData();
    formData.append('action', 'delete_notice');
    formData.append('notice_id', id);

    fetch('includes/portal_actions.php', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
        if(data.success) location.reload();
    });
}

function toggleCard(cardId, btn) {
    const formData = new FormData();
    formData.append('action', 'toggle_card');
    formData.append('card_id', cardId);

    fetch('includes/portal_actions.php', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
        if(data.success) {
            // Reload to reflect hidden state properly (rendering logic is PHP based)
            location.reload(); 
        }
    });
}

// View Mode Toggle
function setView(mode) {
    const container = document.getElementById('tools-main-container');
    const btnGrid = document.getElementById('btn-view-grid');
    const btnList = document.getElementById('btn-view-list');

    if (mode === 'list') {
        container.classList.add('list-view');
        btnList.classList.add('active', 'btn-light');
        btnList.classList.remove('btn-white');
        btnGrid.classList.remove('active', 'btn-light');
        btnGrid.classList.add('btn-white');
    } else {
        container.classList.remove('list-view');
        btnGrid.classList.add('active', 'btn-light');
        btnGrid.classList.remove('btn-white');
        btnList.classList.remove('active', 'btn-light');
        btnList.classList.add('btn-white');
    }
    localStorage.setItem('toolsViewMode', mode);
}

// Initialize View
document.addEventListener('DOMContentLoaded', () => {
    const savedMode = localStorage.getItem('toolsViewMode') || 'grid';
    setView(savedMode);
    
    // REMOVE BG-LIGHT to fix Glassmorphism
    document.body.classList.remove('bg-light');
});
</script>
</body>
</html>


<script>
    function updateClock() {
        // Brasilia Time (UTC-3)
        const now = new Date();
        // Adjust to Brazil time if client is elsewhere? 
        // Simple approach: Use toLocaleString with timeZone
        const options = { timeZone: 'America/Sao_Paulo', hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false };
        const timeString = now.toLocaleTimeString('pt-BR', options);
        const clockEl = document.getElementById('brasilia-clock');
        if(clockEl) clockEl.innerText = timeString;
    }
    setInterval(updateClock, 1000);
    updateClock(); // Initial call
</script>

<!-- Change Password Modal -->
<div class="modal fade" id="changePassModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Alterar Senha</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form id="changePassForm">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Senha Atual</label>
                        <input type="password" class="form-control" id="cpCurrent" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Nova Senha</label>
                        <input type="password" class="form-control" id="cpNew" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Confirmar Nova Senha</label>
                        <input type="password" class="form-control" id="cpConfirm" required>
                    </div>
                    <button type="button" class="btn btn-primary w-100 rounded-pill" onclick="savePassword()">Salvar Nova Senha</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function savePassword() {
    const current = document.getElementById('cpCurrent').value;
    const newP = document.getElementById('cpNew').value;
    const confirmP = document.getElementById('cpConfirm').value;
    
    if(newP !== confirmP) {
        alert("A nova senha e a confirmação não coincidem.");
        return;
    }
    
    if(newP.length < 4) {
        alert("A senha deve ter pelo menos 4 caracteres.");
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'change_password');
    formData.append('current_password', current);
    formData.append('new_password', newP);
    
    fetch('includes/portal_actions.php', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
        if(data.success) {
            alert("Senha alterada com sucesso!");
            location.reload();
        } else {
            alert('Erro: ' + (data.message || 'Erro desconhecido'));
        }
    });
}

function toggleCard(cardId, btn) {
    const icon = btn.querySelector('i');
    const fd = new FormData();
    fd.append('action', 'toggle_card');
    fd.append('card_id', cardId);
    
    fetch('includes/portal_actions.php', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(data => {
        if(data.success) {
            const card = btn.closest('.tool-card'); 
            if(data.blocked) {
                card.classList.add('opacity-50', 'grayscale');
                icon.className = 'bi bi-eye-slash-fill text-danger small';
            } else {
                card.classList.remove('opacity-50', 'grayscale');
                icon.className = 'bi bi-eye text-muted small';
            }
        } else {
            alert('Erro: ' + (data.message || 'Falha ao alterar status'));
        }
    })
    .catch(err => console.error(err));
}
</script>

<?php include 'includes/footer.php'; ?>
</body>
</html>
