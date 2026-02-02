<?php
// ppr_manager.php
include 'includes/header.php';
require_once 'includes/portal_helpers.php';
require_once 'includes/portal_auth.php';

$currentYear = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

// Security Check
if (!isLoggedIn()) {
    echo "<div class='container py-5 text-center'><h3>Faça login para acessar esta ferramenta.</h3></div>";
    include 'includes/footer.php';
    exit;
}
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="display-6 fw-bold mb-0">Gestão de <span class="text-primary">PPR</span></h1>
            <p class="text-muted">Acompanhamento de metas e resultados.</p>
            <a href="index.php" class="btn btn-sm btn-outline-secondary mt-2"><i class="bi bi-arrow-left me-1"></i>Voltar ao Início</a>
        </div>
        
        <div class="d-flex gap-2 align-items-center">
            <select class="form-select w-auto" id="yearSelect" onchange="changeYear(this.value)">
                <?php for($y = 2024; $y <= date('Y')+1; $y++): ?>
                    <option value="<?php echo $y; ?>" <?php echo $y == $currentYear ? 'selected' : ''; ?>><?php echo $y; ?></option>
                <?php endfor; ?>
            </select>
        </div>
    </div>

    <ul class="nav nav-tabs mb-4" id="pprTabs" role="tablist">
        <li class="nav-item">
            <button class="nav-link active" id="dashboard-tab" data-bs-toggle="tab" data-bs-target="#dashboard" type="button">Dashboard</button>
        </li>
        <li class="nav-item">
            <button class="nav-link" id="charts-tab" data-bs-toggle="tab" data-bs-target="#charts" type="button" onclick="loadCharts()">Histórico & Gráficos</button>
        </li>
        <li class="nav-item">
            <button class="nav-link" id="audit-tab" data-bs-toggle="tab" data-bs-target="#audit" type="button" onclick="loadAudit()">Auditoria</button>
        </li>
    </ul>

    <div class="tab-content" id="pprTabContent">
        <!-- DASHBOARD TAB -->
        <div class="tab-pane fade show active" id="dashboard" role="tabpanel">
            <!-- Summary Row -->
            <div class="row mb-4">
                <div class="col-md-6 mb-3 mb-md-0">
                    <div class="card border-0 shadow-sm rounded-4 h-100 bg-primary bg-gradient text-white">
                        <div class="card-body p-4 d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="text-uppercase opacity-75 mb-1">Pontuação Atual (<?php echo $currentYear; ?>)</h6>
                                <h1 class="display-3 fw-bold mb-0" id="totalScore">100</h1>
                                <p class="opacity-75 mb-0 small">Minimo para PPR: 70</p>
                            </div>
                            <div class="text-end">
                                <i class="bi bi-trophy-fill opacity-50 display-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                         <div class="card-body p-4"> <!-- Condensed Status -->
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="fw-bold mb-0">Status das Metas</h6>
                                <?php if(isSupport() || isAdmin()): ?>
                                    <button class="btn btn-sm btn-outline-primary" onclick="savePPR()"><i class="bi bi-save me-1"></i>Salvar</button>
                                <?php endif; ?>
                            </div>
                             <div class="d-flex justify-content-between mb-1">
                                <span>Perdas Acumuladas:</span>
                                <span class="fw-bold text-danger" id="lostPoints">0</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Projeção (Se mantiver):</span>
                                <span class="fw-bold" id="projectedScore">100</span>
                            </div>
                         </div>
                    </div>
                </div>
            </div>

            <!-- Tables Row -->
            <div class="row">
                 <div class="col-12">
                     <!-- OKR 1 -->
                     <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-header bg-success bg-opacity-10 py-3 border-0">
                            <h5 class="fw-bold text-success mb-0">OKR 1 - Interação e Conteúdo</h5>
                        </div>
                        <div class="card-body p-0 table-responsive">
                            <table class="table table-hover align-middle mb-0 text-center" id="table-okr1">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="text-start ps-4" style="width: 250px;">Meta</th>
                                        <?php 
                                        $months = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];
                                        foreach ($months as $m) echo "<th>$m</th>"; 
                                        ?>
                                        <th class="text-start pe-4">Desc</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>

                    <!-- OKR 2 -->
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-header bg-primary bg-opacity-10 py-3 border-0">
                             <h5 class="fw-bold text-primary mb-0">OKR 2 - Satisfação e Agilidade</h5>
                        </div>
                        <div class="card-body p-0 table-responsive">
                            <table class="table table-hover align-middle mb-0 text-center" id="table-okr2">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="text-start ps-4" style="width: 250px;">Meta</th>
                                         <?php foreach ($months as $m) echo "<th>$m</th>"; ?>
                                        <th class="text-start pe-4">Desc</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                 </div>
            </div>
        </div>

        <!-- CHARTS TAB -->
        <div class="tab-pane fade" id="charts" role="tabpanel">
            <div class="row">
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-body">
                            <h5 class="card-title fw-bold">Evolução da Pontuação (Comparativo Anual)</h5>
                            <canvas id="chartHistory" height="200"></canvas>
                        </div>
                    </div>
                </div>
                 <div class="col-md-6">
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-body">
                            <h5 class="card-title fw-bold">Previsão 2025</h5>
                            <canvas id="chartProjection" height="200"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- AUDIT TAB -->
        <div class="tab-pane fade" id="audit" role="tabpanel">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-0">
                    <table class="table table-striped mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">Data/Hora</th>
                                <th>Usuário</th>
                                <th>Ação</th>
                                <th>Detalhes</th>
                                <th>Anterior</th>
                                <th>Novo</th>
                            </tr>
                        </thead>
                        <tbody id="auditTableBody">
                            <tr><td colspan="6" class="text-center py-4">Carregando...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Expose PHP variables to global window scope for ppr_manager.js
    window.currentYear = <?php echo $currentYear; ?>;
    window.canEdit = <?php echo (isSupport() || isAdmin()) ? 'true' : 'false'; ?>;
    window.isAdmin = <?php echo isAdmin() ? 'true' : 'false'; ?>;
</script>
<script src="assets/js/ppr_manager.js"></script>


<?php include 'includes/footer.php'; ?>
