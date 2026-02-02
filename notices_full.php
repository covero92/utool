<?php
include 'includes/header.php';
require_once 'includes/portal_helpers.php';
require_once 'includes/portal_auth.php';

$portal = new SupportPortal();
$notices = $portal->getNotices();
$isAdmin = isAdmin();
?>

<div class="container py-5">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center">
            <a href="index.php" class="btn btn-sm btn-outline-secondary rounded-circle shadow-sm me-3" title="Voltar">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1">
                        <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Mural de Avisos</li>
                    </ol>
                </nav>
                <h1 class="display-6 fw-bold text-dark mb-0">Mural de Avisos <span class="badge bg-warning text-dark fs-6 align-middle ms-2"><?php echo count($notices); ?></span></h1>
            </div>
        </div>
        <?php if($isAdmin): ?>
        <button class="btn btn-primary rounded-pill shadow-sm" onclick="openNoticeModal()">
            <i class="bi bi-plus-lg me-2"></i>Novo Aviso
        </button>
        <?php endif; ?>
    </div>

    <!-- Search/Filter (Future Implementation) -->
    <!--
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body">
            <input type="text" class="form-control border-0 bg-light rounded-pill px-3" placeholder="Pesquisar avisos...">
        </div>
    </div>
    -->

    <!-- Notices List -->
    <div class="row g-4">
        <?php if(empty($notices)): ?>
            <div class="col-12 text-center py-5 opacity-50">
                <i class="bi bi-clipboard-x fs-1 mb-3 d-block text-muted"></i>
                <h4 class="text-muted fw-bold">Nenhum aviso encontrado</h4>
                <p class="text-muted">O mural está vazio no momento.</p>
            </div>
        <?php else: ?>
            <?php foreach($notices as $notice): 
                $date = isset($notice['date']) ? date('d/m/Y H:i', strtotime($notice['date'])) : 'Data desconhecida';
                $title = $notice['title'] ?? 'Aviso';
                $type = $notice['type'] ?? 'info';
                
                // Color mapping
                $bgClass = 'bg-light';
                $borderClass = 'border-secondary';
                $iconClass = 'bi-info-circle-fill';
                $textClass = 'text-dark';

                switch($type) {
                    case 'info': $borderClass='border-info'; $iconClass='text-info'; break;
                    case 'warning': $borderClass='border-warning'; $iconClass='text-warning'; break;
                    case 'danger': $borderClass='border-danger'; $iconClass='text-danger'; break;
                    case 'success': $borderClass='border-success'; $iconClass='text-success'; break;
                }
            ?>
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-4 h-100 hover-card">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="d-flex">
                                <div class="me-3">
                                    <i class="bi bi-circle-fill <?php echo $iconClass; ?> fs-5"></i>
                                </div>
                                <div>
                                    <h5 class="fw-bold mb-1"><?php echo htmlspecialchars($title); ?></h5>
                                    <div class="mb-2 text-muted small">
                                        <i class="bi bi-clock me-1"></i> <?php echo $date; ?>
                                        <span class="mx-2">•</span>
                                        <span class="text-uppercase badge bg-light text-dark border"><?php echo $type; ?></span>
                                    </div>
                                    <div class="text-muted mb-0" style="white-space: pre-wrap;"><?php echo $notice['description']; ?></div>
                                </div>
                            </div>
                            <?php if($isAdmin): ?>
                            <div class="dropdown">
                                <button class="btn btn-light btn-sm rounded-circle" data-bs-toggle="dropdown">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end border-0 shadow rounded-3">
                                    <li><button class="dropdown-item small" onclick='editNotice(<?php echo json_encode($notice); ?>)'><i class="bi bi-pencil me-2"></i>Editar</button></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><button class="dropdown-item small text-danger" onclick="deleteNotice('<?php echo $notice['id']; ?>')"><i class="bi bi-trash me-2"></i>Excluir</button></li>
                                </ul>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Notice Modal -->
<div class="modal fade" id="noticeModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <form id="noticeForm" class="modal-content border-0 shadow">
            <input type="hidden" name="id" id="noticeId">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="modalTitle">Novo Aviso</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted">Título</label>
                    <input type="text" class="form-control" name="title" id="noticeTitle" required placeholder="Ex: Manutenção Programada">
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted">Tipo</label>
                    <select class="form-select" name="type" id="noticeType">
                        <option value="info">Informação (Azul)</option>
                        <option value="warning">Atenção (Amarelo)</option>
                        <option value="danger">Alerta (Vermelho)</option>
                        <option value="success">Sucesso (Verde)</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted">Equipe</label>
                    <select class="form-select" name="team" id="noticeTeam">
                        <option value="Suporte (geral)">Suporte (geral)</option>
                        <option value="PDV">PDV</option>
                        <option value="Retaguarda">Retaguarda</option>
                        <option value="Fiscal">Fiscal</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted">Descrição</label>
                    <div id="editor-container" class="bg-white"></div>
                    <!-- Hidden input to store content for FormData if needed, but we append manually -->
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary rounded-pill px-4" onclick="saveNotice()">Salvar</button>
            </div>
        </form>
<?php include 'includes/footer.php'; ?>
<!-- Quill JS -->
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
<style>
    .ql-editor { min-height: 300px; font-size: 1rem; }
    .ql-container { border-bottom-left-radius: 10px; border-bottom-right-radius: 10px; }
    .ql-toolbar { border-top-left-radius: 10px; border-top-right-radius: 10px; }
</style>

<script>
// Quill Instance
let quill;
document.addEventListener('DOMContentLoaded', () => {
    quill = new Quill('#editor-container', {
        theme: 'snow',
        modules: {
            toolbar: [
                [{ 'header': [1, 2, false] }],
                ['bold', 'italic', 'underline'],
                [{ 'color': [] }, { 'background': [] }],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                ['link', 'clean']
            ]
        },
        placeholder: 'Detalhes do aviso...'
    });
});

function getModal() {
    // Check if element exists
    const el = document.getElementById('noticeModal');
    if(!el) {
        console.error('Modal element not found!');
        alert('Erro: Modal não encontrado.');
        return null;
    }
    return new bootstrap.Modal(el);
}

function openNoticeModal() {
    console.log('openNoticeModal called');
    document.getElementById('noticeId').value = '';
    document.getElementById('noticeTitle').value = '';
    // Reset Quill
    if(quill) quill.root.innerHTML = ''; 
    document.getElementById('noticeType').value = 'info';
    document.getElementById('noticeTeam').value = 'Suporte (geral)';
    document.getElementById('modalTitle').innerText = 'Novo Aviso';
    const modal = getModal();
    if(modal) modal.show();
}

function editNotice(notice) {
    console.log('editNotice called', notice);
    document.getElementById('noticeId').value = notice.id;
    document.getElementById('noticeTitle').value = notice.title || '';
    if(quill) quill.root.innerHTML = notice.description || '';
    document.getElementById('noticeType').value = notice.type;
    document.getElementById('noticeTeam').value = notice.team || 'Suporte (geral)';
    document.getElementById('modalTitle').innerText = 'Editar Aviso';
    const modal = getModal();
    if(modal) modal.show();
}

function saveNotice() {
    const formData = new FormData(document.getElementById('noticeForm'));
    formData.append('action', 'save_notice');
    // Get Quill HTML
    const desc = quill.root.innerHTML;
    formData.append('description', desc);

    fetch('includes/portal_actions.php', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
        if(data.success) location.reload();
        else alert('Erro ao salvar.');
    })
    .catch(err => {
        console.error(err);
        alert('Erro de comunicação com o servidor.');
    });
}

function deleteNotice(id) {
    if(!confirm("Tem certeza que deseja excluir este aviso?")) return;
    const formData = new FormData();
    formData.append('action', 'delete_notice');
    formData.append('notice_id', id);

    fetch('includes/portal_actions.php', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
        if(data.success) location.reload();
    })
    .catch(err => console.error(err));
}
</script>
