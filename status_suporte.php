<?php
// status_suporte.php
include 'includes/header.php';
require_once 'includes/portal_auth.php';
require_once 'includes/permission_manager.php';
require_once 'includes/db_connection.php';

// Auth & Permission Check
if (!isLoggedIn()) {
    header("Location: index.php");
    exit;
}

$pm = new PermissionManager();
$currentUserRole = $_SESSION['user_role_id'] ?? 0;

if (!$pm->canView('status_suporte', $currentUserRole)) {
    // Redirect or Show Access Denied
    echo "<div class='container py-5'><div class='alert alert-danger'>Acesso negado. Você não tem permissão para visualizar este dashboard.</div><a href='index.php' class='btn btn-light'>Voltar</a></div>";
    exit;
}

// Fetch Data
$pdo = getDBConnection();
$data = [];
if ($pdo) {
    // Get latest record
    $stmt = $pdo->query("SELECT * FROM dashboard ORDER BY id DESC LIMIT 1");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        $data['queues'] = json_decode($row['queues'], true);
        $data['kpi'] = json_decode($row['kpi'], true);
        $data['insights'] = json_decode($row['insights'], true);
        $data['last_update'] = $row['updated_at'];
    }
}

// Defaults
$queues = $data['queues'] ?? ['pdv' => '-', 'fiscal' => '-', 'retaguarda' => '-', 'triagem' => '-'];
$kpi = $data['kpi'] ?? ['chatsAtendidos' => '-', 'totalTickets' => '-', 'tme' => '--', 'tma' => '--'];
$insights = $data['insights'] ?? ['avgHourly' => '-', 'systems' => []];

?>

<div class="container-fluid py-4 px-5">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h1 class="display-6 fw-bold text-dark mb-0">Status <span class="text-primary">Suporte</span></h1>
            <p class="text-muted small mb-0">Monitoramento em tempo real da operação.</p>
        </div>
        <div class="d-flex align-items-center gap-3">
             <span class="badge bg-white text-muted shadow-sm border fw-normal p-2">
                <i class="bi bi-clock me-1"></i> Atualizado: <?php echo $data['last_update'] ?? 'Nunca'; ?>
             </span>
             <a href="index.php" class="btn btn-primary rounded-pill px-4 shadow-sm fw-bold"><i class="bi bi-arrow-left me-2"></i>Voltar</a>
        </div>
    </div>

    <!-- Queues Row -->
    <div class="row g-4 mb-5">
        <div class="col-12">
            <h6 class="text-uppercase text-secondary fw-bold small opacity-75 mb-3" style="letter-spacing: 1px;">Filas de Atendimento</h6>
        </div>
        <?php
        $queueConfig = [
            'pdv' => ['label' => 'Fila PDV', 'icon' => 'bi-shop', 'color' => 'primary'],
            'fiscal' => ['label' => 'Fila Fiscal', 'icon' => 'bi-receipt', 'color' => 'indigo'],
            'retaguarda' => ['label' => 'Fila Retaguarda', 'icon' => 'bi-database', 'color' => 'purple'],
            'triagem' => ['label' => 'Triagem', 'icon' => 'bi-funnel', 'color' => 'info'],
        ];

        foreach ($queueConfig as $key => $conf): 
            $val = $queues[$key] ?? 0;
            $isHigh = is_numeric($val) && $val > 5;
            $cardBg = $isHigh ? 'bg-danger text-white' : 'bg-white';
            $textClass = $isHigh ? 'text-white' : 'text-dark';
            $mutedClass = $isHigh ? 'text-white opacity-75' : 'text-muted';
            $iconBg = $isHigh ? 'bg-white text-danger' : "bg-{$conf['color']} text-white";
        ?>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden <?php echo $cardBg; ?>">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="d-flex flex-column">
                            <span class="<?php echo $mutedClass; ?> small fw-bold text-uppercase"><?php echo $conf['label']; ?></span>
                        </div>
                        <div class="rounded-circle shadow-sm d-flex align-items-center justify-content-center <?php echo $iconBg; ?>" 
                             style="width: 40px; height: 40px;">
                            <i class="bi <?php echo $conf['icon']; ?>"></i>
                        </div>
                    </div>
                    <h1 class="display-4 fw-bold mb-0 <?php echo $textClass; ?>"><?php echo $val; ?></h1>
                    <?php if ($isHigh): ?>
                        <div class="mt-2"><span class="badge bg-white text-danger rounded-pill"><i class="bi bi-exclamation-circle me-1"></i>Crítico</span></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- KPIs Row -->
    <div class="row g-4">
        <div class="col-lg-8">
            <h6 class="text-uppercase text-secondary fw-bold small opacity-75 mb-3" style="letter-spacing: 1px;">Performance do Dia</h6>
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
                        <div class="card-body p-4 d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="text-muted small fw-bold text-uppercase mb-1">Chats Atendidos</h6>
                                <h2 class="fw-bold text-dark mb-0"><?php echo $kpi['chatsAtendidos']; ?></h2>
                            </div>
                            <div class="icon-box bg-success-gradient text-white rounded-4 shadow-sm">
                                <i class="bi bi-chat-dots-fill"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
                        <div class="card-body p-4 d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="text-muted small fw-bold text-uppercase mb-1">Total Tickets</h6>
                                <h2 class="fw-bold text-dark mb-0"><?php echo $kpi['totalTickets']; ?></h2>
                            </div>
                            <div class="icon-box bg-primary-gradient text-white rounded-4 shadow-sm">
                                <i class="bi bi-ticket-perforated-fill"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
                        <div class="card-body p-4">
                            <h6 class="text-muted small fw-bold text-uppercase mb-3">Tempo Médio (TME / TMA)</h6>
                            <div class="d-flex justify-content-between">
                                <div>
                                    <span class="d-block text-secondary small">Espera (TME)</span>
                                    <h3 class="fw-bold text-dark"><?php echo $kpi['tme']; ?></h3>
                                </div>
                                <div class="vr mx-3 opacity-25"></div>
                                <div>
                                    <span class="d-block text-secondary small">Atendimento (TMA)</span>
                                    <h3 class="fw-bold text-dark"><?php echo $kpi['tma']; ?></h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
                        <div class="card-body p-4">
                             <h6 class="text-muted small fw-bold text-uppercase mb-3">Ritmo / Hora</h6>
                             <div class="d-flex align-items-center">
                                 <h2 class="fw-bold text-dark me-3"><?php echo $insights['avgHourly']; ?></h2>
                                 <span class="badge bg-success bg-opacity-10 text-success rounded-pill">tickets/h</span>
                             </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <h6 class="text-uppercase text-secondary fw-bold small opacity-75 mb-3" style="letter-spacing: 1px;">Sistemas (Top Caso)</h6>
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
                <div class="card-body p-4">
                    <?php if (empty($insights['systems'])): ?>
                        <div class="text-center text-muted py-5">
                            <i class="bi bi-bar-chart fs-1 opacity-25"></i>
                            <p class="mt-2 small">Sem dados suficientes.</p>
                        </div>
                    <?php else: ?>
                        <div class="d-flex flex-column gap-3">
                            <?php foreach ($insights['systems'] as $sys): 
                                $pct = min(100, max(5, ($sys['value'] / 50) * 100)); // Dummy scaling
                            ?>
                            <div>
                                <div class="d-flex justify-content-between small fw-bold mb-1">
                                    <span><?php echo $sys['name']; ?></span>
                                    <span><?php echo $sys['value']; ?></span>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-primary rounded-pill" style="width: <?php echo $pct; ?>%"></div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Specific styles for dashboard cards */
    .bg-indigo { background-color: #6610f2; }
    .bg-purple { background-color: #6f42c1; }
    .text-indigo { color: #6610f2; }
    .text-purple { color: #6f42c1; }
</style>

<?php 
// Close 
?>
