<?php
require_once 'includes/auth_guard.php';
session_start();
include 'includes/header.php';
require_once 'includes/portal_helpers.php';
require_once 'includes/portal_auth.php';

// Auth Check - Allow guests
// if (!isLoggedIn()) {
//     header("Location: index.php");
//     exit;
// }

$currentUser = getCurrentUser();
$portal = new SupportPortal();
?>

<!-- Custom Styles -->
<style>
    body {
        background: #f8f9fa !important;
    }
    .script-card {
        transition: transform 0.2s, box-shadow 0.2s;
        border: none;
        border-radius: 12px;
        background: #fff;
        box-shadow: 0 4px 6px rgba(0,0,0,0.05);
    }
    .script-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 15px rgba(0,0,0,0.1);
    }
    .script-icon {
        width: 60px;
        height: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
        border-radius: 12px;
    }
    .install-btn {
        background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
        border: none;
        color: white;
        font-weight: 600;
        transition: all 0.2s;
    }
    .install-btn:hover {
        background: linear-gradient(135deg, #0b5ed7 0%, #084298 100%);
        transform: scale(1.02);
        color: white;
    }
    .version-badge {
        background: #e9ecef;
        color: #495057;
        font-weight: 600;
        font-size: 0.8rem;
        padding: 4px 10px;
        border-radius: 20px;
    }
</style>

<div class="container py-5">
    <div class="row mb-5 align-items-center">
        <div class="col-md-8">
            <h1 class="display-5 fw-bold text-dark mb-2">Central de Scripts</h1>
            <p class="text-muted lead">Automatize suas tarefas e melhore a produtividade com scripts oficiais.</p>
        </div>
        <div class="col-md-4 text-md-end">
            <a href="index.php" class="btn btn-outline-secondary rounded-pill px-4">
                <i class="bi bi-arrow-left me-2"></i>Voltar ao Portal
            </a>
        </div>
    </div>

    <!-- Instructions Banner -->
    <div class="alert alert-info border-0 shadow-sm rounded-4 mb-5 d-flex align-items-center p-4">
        <i class="bi bi-info-circle-fill fs-3 me-3 text-info"></i>
        <div>
            <h5 class="alert-heading fw-bold mb-1">Como instalar?</h5>
            <p class="mb-0 small opacity-75">Você precisa ter a extensão <strong>Tampermonkey</strong> instalada no seu navegador. Clique em "Instalar Script" para adicionar ou atualizar.</p>
        </div>
    </div>

    <div class="row g-4">
        
        <!-- Beemore Ticket Popup Script -->
        <div class="col-lg-6">
            <div class="script-card h-100 p-4">
                <div class="d-flex align-items-start mb-4">
                    <div class="script-icon bg-indigo-gradient text-white me-3 bg-primary" style="background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);">
                        <i class="bi bi-robot"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h4 class="fw-bold mb-0 text-dark">Beemore Ticket Popup</h4>
                            <span class="version-badge">v5.5.3</span>
                        </div>
                        <p class="text-muted mb-0">Adiciona um popup flutuante no chat do Beemore para criação rápida de tickets, com suporte a IA (resumos), Macros e correção de bugs do editor.</p>
                    </div>
                </div>

                <div class="bg-light rounded-3 p-3 mb-4">
                    <h6 class="fw-bold text-uppercase small text-secondary mb-2">Novidades</h6>
                    <ul class="mb-0 ps-3 small text-muted">
                        <li>Integração total com tema escuro (Dark Mode).</li>
                        <li>Correção de bug ao colar imagens (Paste).</li>
                        <li>Segurança aprimorada (API Key mascarada).</li>
                        <li>Botão 🤖 para abrir popup rapidamente.</li>
                    </ul>
                </div>

                <div class="d-flex gap-2">
                    <a href="assets/user_scripts/beemore_ticket_popup.user.js" class="btn install-btn w-100 py-2 rounded-3 shadow-sm">
                        <i class="bi bi-download me-2"></i>Instalar / Atualizar
                    </a>
                </div>
                <div class="mt-2 text-center">
                    <small class="text-muted" style="font-size: 0.75rem;">Última atualização: <?php echo date("d/m/Y", filemtime(__DIR__ . '/assets/user_scripts/beemore_ticket_popup.user.js')); ?></small>
                </div>
            </div>
        </div>

        <!-- Add more scripts here in future -->

    </div>
</div>

<?php include 'includes/footer.php'; ?>
