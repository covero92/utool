<?php
// admin_users.php
require_once 'includes/portal_auth.php';
require_once 'includes/header.php';

// Check if user has access to the panel at all
if (!hasCapability('access_admin_panel')) {
    header('Location: index.php');
    exit;
}

$auth = new PortalAuth();
$pdo = $auth->getPDO();
$stmt = $pdo->query("
    SELECT u.*, r.name as role_name 
    FROM users u 
    LEFT JOIN roles r ON u.role_id = r.id 
    ORDER BY u.id ASC
");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmtRoles = $pdo->query("SELECT * FROM roles ORDER BY id ASC");
$allRoles = $stmtRoles->fetchAll(PDO::FETCH_ASSOC);

// Parse DB Config for display (Admin Only)
$currentDB = [];
if (hasCapability('system_config')) {
    $dbConfigFile = 'includes/db_connection.php';
    $dbContent = file_exists($dbConfigFile) ? file_get_contents($dbConfigFile) : '';
    preg_match("/\\\$host\s*=\s*'([^']+)';/", $dbContent, $mHost);
    preg_match("/\\\$port\s*=\s*'([^']+)';/", $dbContent, $mPort);
    preg_match("/\\\$dbname\s*=\s*'([^']+)';/", $dbContent, $mDb);
    preg_match("/\\\$user\s*=\s*'([^']+)';/", $dbContent, $mUser);
    preg_match("/\\\$password\s*=\s*'([^']+)';/", $dbContent, $mPass);

    $currentDB = [
        'host' => $mHost[1] ?? '',
        'port' => $mPort[1] ?? '5432',
        'dbname' => $mDb[1] ?? '',
        'user' => $mUser[1] ?? '',
        'password' => $mPass[1] ?? ''
    ];
}
?>

<div class="container-fluid py-4 px-4">
    <div class="row mb-4 align-items-center">
        <div class="col">
            <h6 class="text-uppercase text-muted fw-bold mb-1" style="font-size: 0.8rem; letter-spacing: 1px;">Administração</h6>
            <h2 class="fw-bold text-dark mb-0">Painel de Controle</h2>
        </div>
        <div class="col-auto">
            <a href="index.php" class="btn btn-light bg-white shadow-sm rounded-pill px-4">
                <i class="bi bi-arrow-left me-2"></i>Voltar
            </a>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <ul class="nav nav-pills mb-4" id="adminTabs" role="tablist">
        <?php if(hasCapability('manage_users')): ?>
        <li class="nav-item" role="presentation">
            <button class="nav-link active rounded-pill px-4 fw-bold" id="users-tab" data-bs-toggle="pill" data-bs-target="#users" type="button" role="tab"><i class="bi bi-people me-2"></i>Usuários</button>
        </li>
        <?php endif; ?>
        
        <?php if(hasCapability('manage_roles')): ?>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-pill px-4 fw-bold" id="roles-tab" data-bs-toggle="pill" data-bs-target="#roles" type="button" role="tab"><i class="bi bi-shield-lock me-2"></i>Permissões</button>
        </li>
        <?php endif; ?>
        
        <?php if(hasCapability('system_config')): ?>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-pill px-4 fw-bold" id="db-tab" data-bs-toggle="pill" data-bs-target="#db" type="button" role="tab"><i class="bi bi-database-gear me-2"></i>Banco de Dados</button>
        </li>
        <?php endif; ?>
        
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-pill px-4 fw-bold" id="tools-tab" data-bs-toggle="pill" data-bs-target="#tools" type="button" role="tab"><i class="bi bi-tools me-2"></i>Ferramentas</button>
        </li>
    </ul>

    <div class="tab-content" id="adminTabsContent">
        
        <!-- Tab: Users Management -->
        <div class="tab-pane fade show active" id="users" role="tabpanel" aria-labelledby="users-tab">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-white border-0 py-4 px-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold">Usuários Cadastrados</h5>
                        <span class="badge bg-light text-dark border rounded-pill"><?php echo count($users); ?> Total</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="overflow-y: visible; overflow-x: auto;">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th class="py-3 px-4 border-bottom-0 text-uppercase text-muted small fw-bold">ID</th>
                                    <th class="py-3 px-4 border-bottom-0 text-uppercase text-muted small fw-bold">Usuário</th>
                                    <th class="py-3 px-4 border-bottom-0 text-uppercase text-muted small fw-bold">Função</th>
                                    <th class="py-3 px-4 border-bottom-0 text-uppercase text-muted small fw-bold">Status</th>
                                    <th class="py-3 px-4 border-bottom-0 text-end text-uppercase text-muted small fw-bold">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $u): ?>
                                <tr>
                                    <td class="px-4 text-muted" style="width: 50px;">#<?php echo $u['id']; ?></td>
                                    <td class="px-4">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar rounded-circle bg-primary-gradient text-white d-flex align-items-center justify-content-center shadow-sm me-3" style="width: 40px; height: 40px; font-weight: bold;">
                                                <?php 
                                                $initials = strtoupper(substr($u['username'], 0, 1));
                                                echo $initials;
                                                ?>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark"><?php echo htmlspecialchars($u['username']); ?></div>
                                                <div class="small text-muted"><?php echo htmlspecialchars($u['full_name']); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4">
                                        <?php 
                                        $roleBadge = 'bg-secondary';
                                        $roleIcon = 'bi-person';
                                        $roleName = $u['role_name'] ?? 'indefinido';
                                        
                                        if(stripos($roleName, 'admin') !== false) { $roleBadge = 'bg-danger-gradient text-white'; $roleIcon = 'bi-shield-lock-fill'; }
                                        elseif(stripos($roleName, 'suporte') !== false) { $roleBadge = 'bg-info-gradient text-white'; $roleIcon = 'bi-headset'; }
                                        else { $roleBadge = 'bg-light text-dark border'; }
                                        
                                        echo "<span class='badge rounded-pill $roleBadge px-3 py-2 fw-normal'><i class='bi $roleIcon me-1'></i>$roleName</span>";
                                        ?>
                                    </td>
                                    <td class="px-4">
                                        <?php 
                                        $statusClass = 'text-muted';
                                        $statusIcon = 'bi-circle';
                                        if($u['status'] === 'active') { $statusClass = 'text-success fw-bold'; $statusIcon = 'bi-check-circle-fill'; }
                                        if($u['status'] === 'blocked') { $statusClass = 'text-danger fw-bold'; $statusIcon = 'bi-x-circle-fill'; }
                                        if($u['status'] === 'pending') { $statusClass = 'text-warning fw-bold'; $statusIcon = 'bi-exclamation-circle-fill'; }
                                        
                                        echo "<div class='d-flex align-items-center $statusClass'><i class='bi $statusIcon me-2'></i>" . ucfirst($u['status']) . "</div>";
                                        ?>
                                    </td>
                                    <td class="px-4 text-end">
                                        <div class="dropdown">
                                            <button class="btn btn-light btn-sm rounded-circle shadow-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="bi bi-three-dots-vertical text-muted"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3" style="z-index: 1050;">
                                                <?php 
                                                // Security Check: Leaders cannot edit Admins
                                                $targetIsAdmin = (stripos($u['role_name'], 'admin') !== false);
                                                $canEdit = true;
                                                
                                                if ($targetIsAdmin && !hasCapability('system_config')) {
                                                    $canEdit = false;
                                                }
                                                ?>
                                                
                                                <?php if($canEdit): ?>
                                                    <li><h6 class="dropdown-header small text-uppercase">Alterar Função</h6></li>
                                                    <?php foreach($allRoles as $r): ?>
                                                    <li><a class="dropdown-item" href="#" onclick="updateRole(<?php echo $u['id']; ?>, <?php echo $r['id']; ?>, '<?php echo $r['name']; ?>')"><i class="bi bi-person-fill-gear me-2"></i><?php echo $r['name']; ?></a></li>
                                                    <?php endforeach; ?>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li><a class="dropdown-item" href="#" onclick="resetPassword(<?php echo $u['id']; ?>)"><i class="bi bi-key me-2"></i>Resetar Senha</a></li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li><h6 class="dropdown-header small text-uppercase">Status</h6></li>
                                                    <li><a class="dropdown-item <?php echo $u['status'] === 'active' ? 'text-danger' : 'text-success'; ?>" href="#" onclick="toggleStatus(<?php echo $u['id']; ?>, '<?php echo $u['status']; ?>')">
                                                        <i class="bi <?php echo $u['status'] === 'active' ? 'bi-lock-fill' : 'bi-unlock-fill'; ?> me-2"></i>
                                                        <?php echo $u['status'] === 'active' ? 'Bloquear Acesso' : 'Ativar Acesso'; ?>
                                                    </a></li>
                                                <?php else: ?>
                                                    <li><span class="dropdown-item text-muted disabled small">Você não pode editar um Administrador.</span></li>
                                                <?php endif; ?>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <tr style="height: 100px; border:0;"><td colspan="5" style="border:0;"></td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- NEW TAB: ROLES -->
        <div class="tab-pane fade" id="roles" role="tabpanel" aria-labelledby="roles-tab">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-white border-0 py-4 px-4 d-flex justify-content-between align-items-center">
                     <div>
                        <h5 class="mb-0 fw-bold">Gerenciar Permissões</h5>
                        <p class="text-muted small mb-0">Crie e edite as funções de acesso do sistema.</p>
                     </div>
                     <button class="btn btn-primary rounded-pill fw-bold" onclick="openRoleModal()"><i class="bi bi-plus-lg me-2"></i>Nova Permissão</button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th class="py-3 px-4 border-bottom-0 fw-bold text-muted small text-uppercase">Nome</th>
                                    <th class="py-3 px-4 border-bottom-0 fw-bold text-muted small text-uppercase">Descrição</th>
                                    <th class="py-3 px-4 border-bottom-0 text-end fw-bold text-muted small text-uppercase">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($allRoles as $role): ?>
                                <tr>
                                    <td class="px-4 fw-bold text-dark"><?php echo htmlspecialchars($role['name']); ?></td>
                                    <td class="px-4 text-muted"><?php echo htmlspecialchars($role['description']); ?></td>
                                    <td class="px-4 text-end">
                                        <?php if(!$role['is_system']): ?>
                                            <button class="btn btn-sm btn-outline-primary rounded-pill me-1" onclick="editRole(<?php echo htmlspecialchars(json_encode($role)); ?>)"><i class="bi bi-pencil-fill"></i></button>
                                            <button class="btn btn-sm btn-outline-danger rounded-pill" onclick="deleteRole(<?php echo $role['id']; ?>)"><i class="bi bi-trash-fill"></i></button>
                                        <?php else: ?>
                                            <span class="badge bg-light text-muted border rounded-pill"><i class="bi bi-lock-fill me-1"></i>Sistema</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab: DB Configuration -->
        <div class="tab-pane fade" id="db" role="tabpanel" aria-labelledby="db-tab">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6">
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-header bg-white border-0 py-4 px-4">
                            <h5 class="mb-0 fw-bold">Configuração de Conexão</h5>
                            <p class="text-muted small mb-0">Edite os dados de conexão com o PostgreSQL.</p>
                        </div>
                        <div class="card-body p-4">
                            <form id="dbConfigForm">
                                <div class="mb-3">
                                    <label class="form-label small fw-bold">Host</label>
                                    <input type="text" class="form-control" name="host" value="<?php echo htmlspecialchars($currentDB['host']); ?>" required>
                                </div>
                                <div class="row">
                                    <div class="col-md-8 mb-3">
                                        <label class="form-label small fw-bold">Database Name</label>
                                        <input type="text" class="form-control" name="dbname" value="<?php echo htmlspecialchars($currentDB['dbname']); ?>" required>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label small fw-bold">Port</label>
                                        <input type="text" class="form-control" name="port" value="<?php echo htmlspecialchars($currentDB['port']); ?>" required>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small fw-bold">User</label>
                                    <input type="text" class="form-control" name="user" value="<?php echo htmlspecialchars($currentDB['user']); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small fw-bold">Password</label>
                                    <input type="password" class="form-control" name="password" value="<?php echo htmlspecialchars($currentDB['password']); ?>" required>
                                </div>
                                <hr class="my-4">
                                <button type="button" class="btn btn-primary w-100 rounded-pill fw-bold" onclick="saveDBConfig()">Salvar Configuração</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab: Tools -->
        <div class="tab-pane fade" id="tools" role="tabpanel" aria-labelledby="tools-tab">
            <div class="text-center py-5 opacity-50">
                <i class="bi bi-tools fs-1 mb-3 d-block"></i>
                <h5 class="fw-bold">Ferramentas Administrativas</h5>
                <p>Em desenvolvimento...</p>
            </div>
        </div>
    </div>
</div>

<!-- Role Modal -->
<div class="modal fade" id="roleModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content border-0 rounded-4 shadow">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold" id="roleModalTitle">Nova Permissão</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="roleForm">
            <input type="hidden" name="id" id="roleId">
            <div class="mb-3">
                <label class="form-label small fw-bold">Nome da Permissão</label>
                <input type="text" class="form-control" name="name" id="roleName" required placeholder="Ex: Analista Fiscal">
            </div>
            <div class="mb-3">
                <label class="form-label small fw-bold">Descrição</label>
                <textarea class="form-control" name="description" id="roleDesc" rows="2" placeholder="Breve descrição do acesso..."></textarea>
            </div>
            
            <div class="mb-3">
                <label class="form-label small fw-bold d-block mb-2">Capacidades de Acesso</label>
                <div class="row g-2">
                    <div class="col-6">
                        <div class="form-check">
                            <input class="form-check-input role-cap" type="checkbox" value="access_admin_panel" id="cap_access_admin">
                            <label class="form-check-label small" for="cap_access_admin">Acesso ao Painel</label>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-check">
                            <input class="form-check-input role-cap" type="checkbox" value="view_restricted" id="cap_view_restricted">
                            <label class="form-check-label small" for="cap_view_restricted">Ver Restritos</label>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-check">
                            <input class="form-check-input role-cap" type="checkbox" value="manage_users" id="cap_manage_users">
                            <label class="form-check-label small" for="cap_manage_users">Gerenciar Usuários</label>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-check">
                            <input class="form-check-input role-cap" type="checkbox" value="edit_tools" id="cap_edit_tools">
                            <label class="form-check-label small" for="cap_edit_tools">Editar Ferramentas</label>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-check">
                            <input class="form-check-input role-cap" type="checkbox" value="manage_roles" id="cap_manage_roles">
                            <label class="form-check-label small" for="cap_manage_roles">Gerenciar Permissões</label>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-check">
                            <input class="form-check-input role-cap p-danger" type="checkbox" value="system_config" id="cap_system_config">
                            <label class="form-check-label small text-danger fw-bold" for="cap_system_config">Config. Sistema</label>
                        </div>
                    </div>
                </div>
            </div>
        </form>
      </div>
      <div class="modal-footer border-0 pt-0">
        <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary rounded-pill fw-bold px-4" onclick="saveRole()">Salvar</button>
      </div>
    </div>
  </div>
</div>

<style>
.bg-primary-gradient { background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%); }
.bg-danger-gradient { background: linear-gradient(135deg, #dc3545 0%, #b02a37 100%); }
.bg-info-gradient { background: linear-gradient(135deg, #0dcaf0 0%, #0aa2c0 100%); }
.nav-pills .nav-link { color: #6c757d; background: #fff; }
.nav-pills .nav-link.active { background-color: #0d6efd; color: #fff; box-shadow: 0 4px 6px rgba(13, 110, 253, 0.2); }
.p-danger:checked { background-color: #dc3545; border-color: #dc3545; }
</style>

<script>
// Roles Logic
let roleModalInstance = null;

function getRoleModal() {
    if (!roleModalInstance) {
        roleModalInstance = new bootstrap.Modal(document.getElementById('roleModal'));
    }
    return roleModalInstance;
}

function openRoleModal() {
    document.getElementById('roleId').value = '';
    document.getElementById('roleName').value = '';
    document.getElementById('roleDesc').value = '';
    document.querySelectorAll('.role-cap').forEach(c => c.checked = false); // clear checks
    document.getElementById('roleModalTitle').innerText = 'Nova Permissão';
    getRoleModal().show();
}

function editRole(role) {
    document.getElementById('roleId').value = role.id;
    document.getElementById('roleName').value = role.name;
    document.getElementById('roleDesc').value = role.description;
    
    // Set capabilities
    const caps = role.capabilities ? JSON.parse(role.capabilities) : [];
    document.querySelectorAll('.role-cap').forEach(c => {
        c.checked = caps.includes(c.value);
    });

    document.getElementById('roleModalTitle').innerText = 'Editar Permissão';
    getRoleModal().show();
}

function saveRole() {
    const id = document.getElementById('roleId').value;
    const name = document.getElementById('roleName').value;
    const desc = document.getElementById('roleDesc').value;
    
    // Get Checked Capabilities
    const caps = [];
    document.querySelectorAll('.role-cap:checked').forEach(c => caps.push(c.value));
    
    performAction('admin_save_role', { 
        id: id, 
        name: name, 
        description: desc,
        capabilities: JSON.stringify(caps)
    });
}

function deleteRole(id) {
    if(!confirm("Tem certeza que deseja excluir esta permissão?")) return;
    performAction('admin_delete_role', { id: id });
}

function updateRole(userId, roleId, roleName) {
    if(!confirm("Alterar função deste usuário para " + roleName + "?")) return;
    performAction('admin_update_user_role_id', { user_id: userId, role_id: roleId });
}

function resetPassword(id) {
    const newPass = prompt("Digite a nova senha para este usuário:");
    if(newPass === null) return; // Cancelled
    if(newPass.trim() === "") { alert("Senha não pode ser vazia."); return; }
    
    performAction('admin_reset_password', { user_id: id, new_password: newPass });
}

function toggleStatus(id, currentStatus) {
    const newStatus = currentStatus === 'active' ? 'blocked' : 'active';
    if(!confirm("Alterar status para " + newStatus + "?")) return;
    performAction('update_user_status', { user_id: id, status: newStatus });
}

function saveDBConfig() {
    if(!confirm("ATENÇÃO: Alterar a configuração do banco pode quebrar a conexão do sistema. Deseja continuar?")) return;
    
    const form = document.getElementById('dbConfigForm');
    const formData = new FormData(form);
    formData.append('action', 'update_db_config');
    
    fetch('includes/portal_actions.php', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
        if(data.success) {
            alert('Configuração salva com sucesso!');
            location.reload();
        } else {
            alert('Erro ao salvar: ' + (data.message || 'Erro desconhecido'));
        }
    });
}

function performAction(action, data) {
    const formData = new FormData();
    formData.append('action', action);
    for (const key in data) {
        formData.append(key, data[key]);
    }

    fetch('includes/portal_actions.php', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
        if(data.success) location.reload();
        else alert('Erro: ' + (data.message || 'Falha na requisição'));
    });
}
</script>

<?php include 'includes/footer.php'; ?>
