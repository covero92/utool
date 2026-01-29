<?php
// profile.php
require_once 'includes/portal_auth.php';
require_once 'includes/header.php';

if (!isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$auth = new PortalAuth();
$pdo = $auth->getPDO();
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
$stmt->execute([':id' => $_SESSION['user_id']]);
$u = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$u) {
    echo "Erro ao carregar perfil.";
    exit;
}
?>

<!-- Reuse Glass Theme Styles -->
<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@400;500;600;700&display=swap');

:root {
    /* LIGHTGLASS THEME VARS */
    --glass-border: rgba(255, 255, 255, 0.4);
    --glass-highlight: rgba(255, 255, 255, 0.7);
    --glass-shadow: 0 12px 40px -8px rgba(0, 0, 0, 0.08);

    /* Background */
    --color-body-bg: #f3f5f9;
    --color-body-bg-gradient: linear-gradient(135deg, #f0f4f8 0%, #dbeafe 100%);
    
    /* Text */
    --font-primary: 'Outfit', sans-serif;
    --font-body: 'Inter', sans-serif;
}

body {
    background: var(--color-body-bg);
    background-image: var(--color-body-bg-gradient);
    background-attachment: fixed;
    font-family: var(--font-body);
    color: #1e293b;
    min-height: 100vh;
}

/* Glass Cards */
.card-glass {
    background: rgba(255, 255, 255, 0.65);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border: 1px solid var(--glass-border);
    box-shadow: var(--glass-shadow);
}

.avatar-large {
    width: 100px;
    height: 100px;
    font-size: 2.5rem;
    font-family: var(--font-primary);
}

.form-floating > .form-control {
    border-radius: 1rem;
    border: 1px solid rgba(0,0,0,0.05);
    background: rgba(255,255,255,0.8);
}
.form-floating > .form-control:focus {
    background: #fff;
    box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.1);
    border-color: #86b7fe;
}

.section-title { font-family: var(--font-primary); letter-spacing: -0.5px; }

</style>

<div class="container py-5">
    
    <!-- Header -->
    <div class="row align-items-center mb-5 fade-in-up">
        <div class="col">
            <div class="d-inline-flex align-items-center gap-3">
                 <div class="bg-white text-primary rounded-4 d-flex align-items-center justify-content-center shadow-sm border border-white" 
                      style="width: 56px; height: 56px;">
                     <i class="bi bi-person-circle fs-2"></i>
                 </div>
                 <div>
                     <h1 class="display-6 fw-bold mb-0 text-dark section-title">
                         Meu <span class="text-primary">Perfil</span>
                     </h1>
                     <p class="text-muted small mb-0 fw-medium opacity-75">Gerencie suas informações pessoais.</p>
                 </div>
             </div>
        </div>
        <div class="col-auto">
            <a href="index.php" class="btn btn-white bg-white card-glass shadow-sm rounded-pill px-4 fw-bold text-dark transition-hover">
                <i class="bi bi-arrow-left me-2"></i>Voltar ao Hub
            </a>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card card-glass rounded-4 border-0 p-4 p-md-5">
                
                <form id="profileForm">
                    <input type="hidden" name="action" value="update_profile">
                    
                    <div class="text-center mb-5">
                        <div class="avatar-large rounded-circle bg-gradient-primary text-white d-inline-flex align-items-center justify-content-center shadow-lg mb-3" style="background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);">
                            <?php echo strtoupper(substr($u['username'], 0, 1)); ?>
                        </div>
                        <h4 class="fw-bold mb-1 font-primary"><?php echo htmlspecialchars($u['username']); ?></h4>
                        <span class="badge bg-light text-secondary border rounded-pill px-3"><?php echo ucfirst($u['role'] ?? 'Usuário'); ?></span>
                    </div>

                    <div class="row g-3">
                        <div class="col-12">
                            <h6 class="text-uppercase text-muted fw-bold small mb-3 ps-1">Dados Pessoais</h6>
                        </div>

                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="fullName" name="full_name" value="<?php echo htmlspecialchars($u['full_name']); ?>" placeholder="Nome Completo">
                                <label for="fullName">Nome Completo</label>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control border-primary border-opacity-25 bg-white" id="preferredName" name="preferred_name" value="<?php echo htmlspecialchars($u['preferred_name'] ?? ''); ?>" placeholder="Como quer ser chamado">
                                <label for="preferredName" class="text-primary fw-bold"><i class="bi bi-stars me-1"></i>Como quer ser chamado?</label>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="jobTitle" name="job_title" value="<?php echo htmlspecialchars($u['job_title'] ?? ''); ?>" placeholder="Cargo / Função">
                                <label for="jobTitle">Cargo / Função</label>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input type="date" class="form-control" id="birthDate" name="birth_date" value="<?php echo htmlspecialchars($u['birth_date'] ?? ''); ?>" placeholder="Data de Nascimento">
                                <label for="birthDate">Data de Nascimento</label>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="form-floating mb-4">
                                <textarea class="form-control" placeholder="Fale um pouco sobre você" id="bio" name="bio" style="height: 100px"><?php echo htmlspecialchars($u['bio'] ?? ''); ?></textarea>
                                <label for="bio">Bio / Sobre você</label>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 pt-3 border-top border-light">
                        <a href="index.php" class="btn btn-light rounded-pill px-4 fw-bold text-muted">Cancelar</a>
                        <button type="button" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm" onclick="saveProfile()">
                            <i class="bi bi-check-lg me-2"></i>Salvar Alterações
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>
</div>

<script>
function saveProfile() {
    const form = document.getElementById('profileForm');
    const formData = new FormData(form);
    
    fetch('includes/portal_actions.php', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
        if(data.success) {
            // Show toast or alert
            const btn = document.querySelector('button[onclick="saveProfile()"]');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="bi bi-check-circle-fill me-2"></i>Salvo!';
            btn.classList.replace('btn-primary', 'btn-success');
            
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            alert('Erro: ' + (data.message || 'Falha ao salvar.'));
        }
    })
    .catch(err => alert('Erro de conexão.'));
}
</script>

<?php include 'includes/footer.php'; ?>
