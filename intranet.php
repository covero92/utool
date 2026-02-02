<?php
// intranet.php - Customizada
include 'includes/header.php';
require_once 'includes/portal_helpers.php';
require_once 'includes/portal_auth.php';

if (!isLoggedIn()) {
    header("Location: index.php");
    exit;
}

$currentUser = getCurrentUser();
$portal = new SupportPortal();
$users = (new PortalAuth())->getOnlineUsers(); // Reusing for sidebar if needed

// --- LOAD DATA ---
$jsonData = file_get_contents('data/intranet.json');
$data = json_decode($jsonData, true);

$comunicados = $data['comunicados'] ?? [];
$kb = $data['kb'] ?? [];

// --- FILTER LOGIC ---
$filterTeam = $_GET['team'] ?? 'todos'; // todos, pdv, fiscal, retaguarda
$search = $_GET['q'] ?? '';

// Filter Knowledge Base
$filteredKb = array_filter($kb, function($item) use ($filterTeam, $search) {
    // Team Filter
    if ($filterTeam !== 'todos' && $item['equipe'] !== 'todos' && $item['equipe'] !== $filterTeam) {
        return false;
    }
    // Search Filter
    if ($search) {
        $term = strtolower($search);
        return strpos(strtolower($item['titulo']), $term) !== false || 
               strpos(strtolower($item['conteudo']), $term) !== false ||
               in_array($term, array_map('strtolower', $item['tags']));
    }
    return true;
});

?>
<!-- CUSTOM STYLES FOR INTRANET -->
<!-- CUSTOM STYLES FOR INTRANET (LIGHT THEME) -->
<style>
    body {
        background: #f0f2f5 !important; /* Soft Gray */
        color: #2c3e50 !important;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    
    /* Override Bootstrap defaults for Light Mode */
    .bg-light { background: #fff !important; }
    .text-white { color: #2c3e50 !important; } 
    .text-white-50 { color: #6c757d !important; }
    .text-muted { color: #6c757d !important; }
    .border-white { border-color: #dee2e6 !important; }

    /* Glassmorphism Light */
    .glass-panel {
        background: rgba(255, 255, 255, 0.7) !important;
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.8);
        box-shadow: 0 4px 24px 0 rgba(0, 0, 0, 0.05); /* Soft Shadow */
        border-radius: 16px;
    }
    
    /* Navigation */
    .nav-pills .nav-link {
        color: #6c757d;
        transition: all 0.2s;
        font-weight: 500;
    }
    .nav-pills .nav-link:hover {
        background: rgba(0, 0, 0, 0.05);
        color: #2c3e50;
    }
    .nav-pills .nav-link.active {
        background: linear-gradient(135deg, #6610f2 0%, #520dc2 100%);
        color: #fff !important;
        box-shadow: 0 4px 15px rgba(102, 16, 242, 0.3);
    }
    
    /* Badges */
    .team-badge-pdv { background: #e91e63; color: white; }
    .team-badge-fiscal { background: #ff9800; color: white; }
    .team-badge-retaguarda { background: #00bcd4; color: white; }
    .team-badge-todos { background: #607d8b; color: white; }
    
    /* Cards */
    .kb-card {
        background: #fff;
        border: 1px solid #f0f0f0;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .kb-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.05);
        background: #fff;
    }
    
    /* Code Snippets */
    code {
        color: #d63384; 
        background: #f8f9fa;
        padding: 2px 6px;
        border: 1px solid #e9ecef;
        border-radius: 4px;
        font-family: 'Consolas', monospace;
    }

    /* Input Search */
    .form-control {
        background: #fff !important;
        border: 1px solid #dee2e6 !important;
        color: #2c3e50 !important;
    }
    .input-group-text {
        background: #fff !important;
        border: 1px solid #dee2e6 !important;
        color: #6c757d !important;
        border-right: none !important;
    }
    .form-control:focus {
        box-shadow: none;
        border-color: #6610f2 !important;
    }
</style>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Ensure no dark classes remain
        document.body.classList.add('bg-light');
        document.body.classList.remove('bg-dark');
    });
</script>

<div class="container-fluid py-4">
    <div class="row">
        
        <!-- SIDEBAR (Left) -->
        <div class="col-lg-3 col-xl-2">
            <div class="glass-panel p-3 mb-4 text-center">
                 <div class="mb-3">
                     <div class="d-inline-flex align-items-center justify-content-center bg-primary text-white rounded-circle" style="width: 64px; height: 64px; font-size: 1.5rem;">
                         <?php echo strtoupper(substr($currentUser, 0, 2)); ?>
                     </div>
                 </div>
                 <h5 class="text-white mb-0"><?php echo htmlspecialchars($currentUser); ?></h5>
                 <small class="text-muted">Suporte Técnico</small>
                 <hr class="border-secondary opacity-50 my-3">
                 <a href="index.php" class="btn btn-outline-dark btn-sm w-100 rounded-pill"><i class="bi bi-arrow-left me-2"></i>Voltar ao Hub</a>
            </div>

            <div class="glass-panel p-3">
                <h6 class="text-secondary text-uppercase small fw-bold mb-3">Equipes</h6>
                <div class="nav flex-column nav-pills gap-2">
                    <a href="?team=todos" class="nav-link rounded-3 <?php echo $filterTeam == 'todos' ? 'active' : ''; ?>">
                        <i class="bi bi-layers-fill me-2"></i> Geral
                    </a>
                    <a href="?team=pdv" class="nav-link rounded-3 <?php echo $filterTeam == 'pdv' ? 'active' : ''; ?>">
                        <i class="bi bi-shop me-2"></i> PDV
                    </a>
                    <a href="?team=fiscal" class="nav-link rounded-3 <?php echo $filterTeam == 'fiscal' ? 'active' : ''; ?>">
                        <i class="bi bi-receipt me-2"></i> Fiscal
                    </a>
                    <a href="?team=retaguarda" class="nav-link rounded-3 <?php echo $filterTeam == 'retaguarda' ? 'active' : ''; ?>">
                        <i class="bi bi-server me-2"></i> Retaguarda
                    </a>
                </div>
            </div>
        </div>

        <!-- MAIN CONTENT -->
        <div class="col-lg-9 col-xl-10">
            
            <!-- COMUNICADOS (Topo) -->
            <div class="mb-4">
                <h4 class="text-white fw-bold mb-3"><i class="bi bi-megaphone-fill me-2 text-warning"></i>Mural dos Líderes</h4>
                <div class="row g-3">
                    <?php foreach($comunicados as $com): 
                        if ($filterTeam !== 'todos' && $com['equipe'] !== 'todos' && $com['equipe'] !== $filterTeam) continue;
                    ?>
                    <div class="col-md-6 col-xl-4">
                        <div class="glass-panel p-3 h-100 position-relative overflow-hidden">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="badge rounded-pill bg-opacity-25 text-white border border-white border-opacity-25 team-badge-<?php echo $com['equipe']; ?>">
                                    <?php echo strtoupper($com['equipe']); ?>
                                </span>
                                <small class="text-muted"><?php echo $com['data']; ?></small>
                            </div>
                            <h5 class="text-white fw-bold"><?php echo $com['titulo']; ?></h5>
                            <p class="text-white-50 small mb-2" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                <?php echo $com['conteudo']; ?>
                            </p>
                            <div class="mt-2 d-flex align-items-center">
                                <i class="bi bi-person-circle me-1 text-muted"></i>
                                <small class="text-muted"><?php echo $com['autor']; ?></small>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- SEARCH BAR -->
            <div class="mb-4">
                <form action="" method="GET">
                    <input type="hidden" name="team" value="<?php echo htmlspecialchars($filterTeam); ?>">
                    <div class="input-group input-group-lg">
                        <span class="input-group-text bg-dark border-0 text-secondary ps-4 rounded-start-pill"><i class="bi bi-search"></i></span>
                        <input type="text" name="q" class="form-control bg-dark border-0 text-white rounded-end-pill" placeholder="Pesquisar SQL, Erro, Dica..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                </form>
            </div>

            <!-- KNOWLEDGE BASE FEED -->
             <div>
                <h4 class="text-white fw-bold mb-3"><i class="bi bi-lightbulb-fill me-2 text-info"></i>Base de Conhecimento</h4>
                
                <?php if(empty($filteredKb)): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-inbox fs-1 text-muted opacity-50"></i>
                        <p class="text-muted mt-2">Nenhum item encontrado.</p>
                    </div>
                <?php else: ?>
                    <div class="row g-3">
                        <?php foreach($filteredKb as $item): ?>
                        <div class="col-12">
                            <div class="glass-panel p-4 kb-card">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-white bg-opacity-10 border border-white border-opacity-10 text-white">
                                            <?php echo $item['categoria']; ?>
                                        </span>
                                        <h5 class="text-white fw-bold mb-0"><?php echo $item['titulo']; ?></h5>
                                    </div>
                                    <span class="badge rounded-pill team-badge-<?php echo $item['equipe']; ?> bg-opacity-75 text-white" style="font-size: 0.65rem;">
                                        <?php echo strtoupper($item['equipe']); ?>
                                    </span>
                                </div>
                                <div class="mt-3 bg-black bg-opacity-25 p-3 rounded-3 font-monospace text-white-50 small">
                                    <?php echo nl2br(htmlspecialchars($item['conteudo'])); ?>
                                </div>
                                <div class="mt-3 d-flex gap-2">
                                    <?php foreach($item['tags'] as $tag): ?>
                                        <small class="text-secondary">#<?php echo $tag; ?></small>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
             </div>

        </div>
    </div>
</div>

<?php 
// No footer needed for intranet layout, keeps it clean like a dashboard
?>
</body>
</html>
