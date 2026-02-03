<?php
// profile.php
require_once 'includes/portal_auth.php';
require_once 'includes/header.php';

if (!isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$updateMsg = '';
$updateType = '';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="d-flex align-items-center mb-4">
                <a href="index.php" class="btn btn-white rounded-circle shadow-sm me-3"><i class="bi bi-arrow-left"></i></a>
                <h2 class="fw-bold mb-0 font-primary">Meu Perfil</h2>
            </div>

            <div class="card border-0 shadow-lg rounded-4 overflow-hidden card-glass">
                <div class="card-body p-0">
                    <div class="row g-0">
                        <!-- Left Panel: User Info & Image -->
                        <div class="col-md-4 bg-light bg-opacity-50 p-4 text-center border-end border-white">
                            <div class="position-relative d-inline-block mb-3">
                                <form id="imageForm">
                                    <div class="profile-image-container rounded-circle shadow-sm overflow-hidden bg-white d-flex align-items-center justify-content-center" 
                                         style="width: 120px; height: 120px; cursor: pointer;"
                                         onclick="document.getElementById('imgInput').click()">
                                        <?php if (!empty($_SESSION['user_image'])): ?>
                                            <img src="<?php echo $_SESSION['user_image']; ?>" id="previewImg" class="w-100 h-100 object-fit-cover">
                                        <?php else: ?>
                                            <span id="initialsPlaceholder" class="display-4 fw-bold text-primary opacity-50">
                                                <?php echo strtoupper(substr($_SESSION['user_name'], 0, 1)); ?>
                                            </span>
                                            <img src="" id="previewImg" class="w-100 h-100 object-fit-cover d-none">
                                        <?php endif; ?>
                                        
                                        <div class="overlay position-absolute top-0 start-0 w-100 h-100 bg-dark bg-opacity-50 d-flex align-items-center justify-content-center opacity-0 hover-opacity-100 transition-all">
                                            <i class="bi bi-camera-fill text-white fs-4"></i>
                                        </div>
                                    </div>
                                    <input type="file" name="profile_image" id="imgInput" class="d-none" accept="image/*" onchange="previewImage(this)">
                                </form>
                            </div>
                            
                            <h5 class="fw-bold text-dark mb-1"><?php echo htmlspecialchars($_SESSION['user_name']); ?></h5>
                            <p class="text-muted small mb-3">@<?php echo htmlspecialchars($_SESSION['username']); ?></p>
                            <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3 py-1">
                                <?php echo htmlspecialchars($_SESSION['user_role_label']); ?>
                            </span>
                        </div>

                        <!-- Right Panel: Edit Form -->
                        <div class="col-md-8 p-5">
                            <h5 class="fw-bold mb-4 text-secondary text-uppercase small ls-1">Dados Pessoais</h5>
                            
                            <form id="profileForm" onsubmit="saveProfile(event)">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <div class="form-floating">
                                            <input type="text" class="form-control rounded-4 bg-light border-0" id="fullName" name="full_name" value="<?php echo htmlspecialchars($_SESSION['user_name']); ?>" required placeholder="Nome">
                                            <label for="fullName">Nome Completo</label>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-floating">
                                            <input type="text" class="form-control rounded-4 bg-light border-0" id="nickname" name="nickname" value="<?php echo htmlspecialchars($_SESSION['user_nickname'] ?? ''); ?>" placeholder="Apelido">
                                            <label for="nickname">Como quer ser chamado (Apelido)</label>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-floating">
                                            <textarea class="form-control rounded-4 bg-light border-0" id="bio" name="bio" style="height: 100px" placeholder="Bio"><?php echo htmlspecialchars($_SESSION['user_bio'] ?? ''); ?></textarea>
                                            <label for="bio">Biografia / Sobre você</label>
                                        </div>
                                    </div>
                                    
                                    <div class="col-12 mt-4">
                                        <h5 class="fw-bold mb-3 text-secondary text-uppercase small ls-1">Segurança</h5>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-floating">
                                            <input type="password" class="form-control rounded-4 bg-light border-0" id="newPass" name="new_password" placeholder="Senha">
                                            <label for="newPass">Nova Senha (deixe em branco para manter)</label>
                                        </div>
                                    </div>

                                    <div class="col-12 mt-4 text-end">
                                        <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">
                                            Salvar Alterações
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function previewImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            const img = document.getElementById('previewImg');
            img.src = e.target.result;
            img.classList.remove('d-none');
            const ph = document.getElementById('initialsPlaceholder');
            if(ph) ph.classList.add('d-none');
        }
        reader.readAsDataURL(input.files[0]);
    }
}

function saveProfile(e) {
    e.preventDefault();
    const btn = e.target.querySelector('button[type="submit"]');
    const originalText = btn.innerText;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Salvando...';

    const formData = new FormData(document.getElementById('profileForm'));
    const imgInput = document.getElementById('imgInput');
    
    // Add image if selected
    if(imgInput.files[0]) {
        formData.append('profile_image', imgInput.files[0]);
    }
    
    formData.append('portal_action', 'update_profile');

    fetch('includes/portal_actions.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        btn.innerText = originalText;
        if(data.success) {
            alert('Perfil atualizado com sucesso!');
            location.reload();
        } else {
            alert('Erro: ' + (data.message || 'Falha desconhecida'));
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerText = originalText;
        alert('Erro de conexão.');
        console.error(err);
    });
}
</script>

<style>
.hover-opacity-100:hover { opacity: 1 !important; cursor: pointer; }
.ls-1 { letter-spacing: 1px; }
.card-glass {
    background: rgba(255, 255, 255, 0.8);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
}
</style>
<?php include 'includes/footer.php'; ?>
