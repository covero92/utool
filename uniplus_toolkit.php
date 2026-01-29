<?php
include 'includes/header.php';
?>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h1 class="display-5 fw-bold text-dark mb-0">Uniplus <span class="text-indigo">Toolkit</span></h1>
            <p class="lead text-muted mb-0">Caixa de ferramentas avançadas para manutenção.</p>
        </div>
        <a href="index.php" class="btn btn-outline-secondary rounded-pill"><i class="bi bi-arrow-left me-2"></i>Voltar ao Hub</a>
    </div>

    <div class="row g-4">
        <!-- Card 1: Config Editor -->
        <div class="col-md-6 col-lg-4">
            <a href="uniplus_config.php" class="text-decoration-none">
                <div class="card h-100 border-0 shadow-sm hover-card rounded-4 p-4 text-center">
                    <div class="mb-3">
                        <div class="icon-box bg-indigo-gradient text-white rounded-circle mx-auto shadow-sm" style="width: 80px; height: 80px; font-size: 2rem;">
                            <i class="bi bi-sliders"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold text-dark">Editor de Config</h3>
                    <p class="text-muted small mb-0">Gerencie parâmetros do `uniplus.properties` com segurança, busca e backups automáticos.</p>
                    <div class="mt-4">
                        <span class="btn btn-sm btn-light rounded-pill px-4 fw-bold text-indigo">Acessar</span>
                    </div>
                </div>
            </a>
        </div>

        <!-- Card 2: Health Check -->
        <div class="col-md-6 col-lg-4">
            <a href="uniplus_health.php" class="text-decoration-none">
                <div class="card h-100 border-0 shadow-sm hover-card rounded-4 p-4 text-center">
                    <div class="mb-3">
                        <div class="icon-box bg-success-gradient text-white rounded-circle mx-auto shadow-sm" style="width: 80px; height: 80px; font-size: 2rem;">
                            <i class="bi bi-heart-pulse"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold text-dark">Health Check</h3>
                    <p class="text-muted small mb-0">Diagnóstico completo: status do banco, integridade de arquivos, processos e portas.</p>
                    <div class="mt-4">
                        <span class="btn btn-sm btn-light rounded-pill px-4 fw-bold text-success">Diagnosticar</span>
                    </div>
                </div>
            </a>
        </div>

        <!-- Card 3: Log Viewer -->
        <div class="col-md-6 col-lg-4">
            <a href="uniplus_logs.php" class="text-decoration-none">
                <div class="card h-100 border-0 shadow-sm hover-card rounded-4 p-4 text-center">
                    <div class="mb-3">
                        <div class="icon-box bg-danger-gradient text-white rounded-circle mx-auto shadow-sm" style="width: 80px; height: 80px; font-size: 2rem;">
                            <i class="bi bi-terminal"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold text-dark">Logs em Tempo Real</h3>
                    <p class="text-muted small mb-0">Monitore logs do sistema (uniplus.log, yoda.log) com destaque de erros.</p>
                    <div class="mt-4">
                        <span class="btn btn-sm btn-light rounded-pill px-4 fw-bold text-danger">Monitorar</span>
                    </div>
                </div>
            </a>
        </div>

        <!-- Card 4: SQL Editor -->
        <div class="col-md-6 col-lg-4">
            <a href="sql_editor.php" class="text-decoration-none">
                <div class="card h-100 border-0 shadow-sm hover-card rounded-4 p-4 text-center">
                    <div class="mb-3">
                        <div class="icon-box bg-primary-gradient text-white rounded-circle mx-auto shadow-sm" style="width: 80px; height: 80px; font-size: 2rem;">
                            <i class="bi bi-database"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold text-dark">Editor SQL</h3>
                    <p class="text-muted small mb-0">Navegue por tabelas, views e execute queries diretamente no banco.</p>
                    <div class="mt-4">
                        <span class="btn btn-sm btn-light rounded-pill px-4 fw-bold text-primary">Acessar</span>
                    </div>
                </div>
            </a>
        </div>
    </div>
</div>

<style>
    .bg-indigo { background-color: #6610f2; }
    .text-indigo { color: #6610f2; }
    .bg-indigo-gradient { background: linear-gradient(135deg, #6610f2 0%, #520dc2 100%); }
    
    .hover-card {
        transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.3s;
    }
    .hover-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 1rem 3rem rgba(0,0,0,.15) !important;
    }
</style>

<?php include 'includes/footer.php'; ?>
