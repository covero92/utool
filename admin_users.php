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

<!-- Include Google Fonts -->
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
}

/* Gradients */
.bg-gradient-primary-to-secondary { background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%); }
.bg-danger-gradient { background: linear-gradient(135deg, #dc3545 0%, #b02a37 100%); }
.bg-info-gradient { background: linear-gradient(135deg, #0dcaf0 0%, #0aa2c0 100%); }
.bg-success-gradient { background: linear-gradient(135deg, #198754 0%, #157347 100%); }
.bg-purple-gradient { background: linear-gradient(135deg, #6f42c1 0%, #59359a 100%); }
.bg-indigo-gradient { background: linear-gradient(135deg, #6610f2 0%, #520dc2 100%); }

/* Glass Cards */
.card-glass {
    background: rgba(255, 255, 255, 0.65);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border: 1px solid var(--glass-border);
    box-shadow: var(--glass-shadow);
}

.nav-pills .nav-link { color: #64748b; background: rgba(255,255,255,0.5); border: 1px solid transparent; transition: all 0.2s; font-family: var(--font-primary); }
.nav-pills .nav-link:hover { background: rgba(255,255,255,0.8); }
.nav-pills .nav-link.active { 
    background-color: #0d6efd; 
    background-image: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
    color: #fff; 
    box-shadow: 0 4px 12px rgba(13, 110, 253, 0.3); 
    border-color: transparent;
}

.table-custom th { font-family: var(--font-primary); letter-spacing: 0.5px; opacity: 0.7; }
.avatar-circle { width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; color: white; font-size: 0.85rem; }

.section-title { font-family: var(--font-primary); letter-spacing: -0.5px; }

</style>

<div class="container-fluid py-5 px-lg-5">
    
    <!-- Admin Header -->
    <div class="row align-items-center mb-5 fade-in-up">
        <div class="col">
            <div class="d-inline-flex align-items-center gap-3">
                 <div class="bg-dark text-white rounded-4 d-flex align-items-center justify-content-center shadow-lg" 
                      style="width: 56px; height: 56px; background: linear-gradient(135deg, #212529 0%, #495057 100%);">
                     <i class="bi bi-shield-lock-fill fs-3"></i>
                 </div>
                 <div>
                     <h1 class="display-6 fw-bold mb-0 text-dark section-title">
                         Painel <span class="text-primary">Admin</span>
                     </h1>
                     <p class="text-muted small mb-0 fw-medium opacity-75">Gestão de usuários e sistema.</p>
                 </div>
             </div>
        </div>
        <div class="col-auto">
            <a href="index.php" class="btn btn-white bg-white card-glass shadow-sm rounded-pill px-4 fw-bold text-dark transition-hover">
                <i class="bi bi-arrow-left me-2"></i>Voltar ao Hub
            </a>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <ul class="nav nav-pills mb-4 gap-2" id="adminTabs" role="tablist">
        <?php if(hasCapability('manage_users')): ?>
        <li class="nav-item" role="presentation">
            <button class="nav-link active rounded-pill px-4 py-2 fw-medium" id="users-tab" data-bs-toggle="pill" data-bs-target="#users" type="button" role="tab"><i class="bi bi-people-fill me-2"></i>Usuários</button>
        </li>
        <?php endif; ?>
        
        <?php if(hasCapability('manage_roles')): ?>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-pill px-4 py-2 fw-medium" id="roles-tab" data-bs-toggle="pill" data-bs-target="#roles" type="button" role="tab"><i class="bi bi-shield-fill-check me-2"></i>Permissões</button>
        </li>
        <?php endif; ?>
        
        <?php if(hasCapability('system_config')): ?>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-pill px-4 py-2 fw-medium" id="db-tab" data-bs-toggle="pill" data-bs-target="#db" type="button" role="tab"><i class="bi bi-database-fill-gear me-2"></i>Banco de Dados</button>
        </li>
        <?php endif; ?>
        
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-pill px-4 py-2 fw-medium" id="tools-tab" data-bs-toggle="pill" data-bs-target="#tools" type="button" role="tab"><i class="bi bi-tools me-2"></i>Ferramentas</button>
        </li>
    </ul>

    <div class="tab-content" id="adminTabsContent">
        
        <!-- Tab: Users Management -->
        <div class="tab-pane fade show active" id="users" role="tabpanel" aria-labelledby="users-tab">
            <div class="card card-glass rounded-4 overflow-hidden border-0">
                <div class="card-header bg-transparent border-0 py-4 px-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0 fw-bold section-title text-dark">Usuários Cadastrados</h5>
                            <p class="text-muted small mb-0">Gerencie o acesso dos membros da equipe.</p>
                        </div>
                        <span class="badge bg-white text-primary shadow-sm border rounded-pill px-3 py-2"><?php echo count($users); ?> usuários</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle table-custom">
                            <thead class="bg-light bg-opacity-50">
                                <tr>
                                    <th class="py-3 px-4 border-bottom-0 text-uppercase text-secondary small fw-bold ps-4">Usuário</th>
                                    <th class="py-3 px-4 border-bottom-0 text-uppercase text-secondary small fw-bold">ID</th>
                                    <th class="py-3 px-4 border-bottom-0 text-uppercase text-secondary small fw-bold">Função</th>
                                    <th class="py-3 px-4 border-bottom-0 text-uppercase text-secondary small fw-bold">Status</th>
                                    <th class="py-3 px-4 border-bottom-0 text-end text-uppercase text-secondary small fw-bold pe-4">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $u): ?>
                                <tr class="border-light">
                                    <td class="px-4 py-3 ps-4">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-circle shadow-sm me-3 bg-gradient-primary-to-secondary">
                                                <?php echo strtoupper(substr($u['username'], 0, 1)); ?>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark font-primary"><?php echo htmlspecialchars($u['username']); ?></div>
                                                <div class="small text-muted opacity-75"><?php echo htmlspecialchars($u['full_name']); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 text-muted small font-monospace">#<?php echo str_pad($u['id'], 3, '0', STR_PAD_LEFT); ?></td>
                                    <td class="px-4">
                                        <?php 
                                        $roleBadge = 'bg-light text-secondary border';
                                        $roleIcon = 'bi-person';
                                        $roleName = $u['role_name'] ?? 'Indefinido';
                                        
                                        if(stripos($roleName, 'admin') !== false) { 
                                            $roleBadge = 'bg-danger-gradient text-white shadow-sm border-0'; 
                                            $roleIcon = 'bi-shield-check'; 
                                        } elseif(stripos($roleName, 'suporte') !== false) { 
                                            $roleBadge = 'bg-info-gradient text-white shadow-sm border-0'; 
                                            $roleIcon = 'bi-headset'; 
                                        }
                                        
                                        echo "<span class='badge rounded-pill $roleBadge px-3 py-2 fw-normal'><i class='bi $roleIcon me-1'></i>$roleName</span>";
                                        ?>
                                    </td>
                                    <td class="px-4">
                                        <?php 
                                        $statusClass = 'text-muted';
                                        $statusBg = 'bg-secondary';
                                        
                                        if($u['status'] === 'active') { 
                                            $statusClass = 'text-success'; 
                                            $statusBg = 'bg-success';
                                            $statusText = 'Ativo';
                                        } elseif($u['status'] === 'blocked') { 
                                            $statusClass = 'text-danger'; 
                                            $statusBg = 'bg-danger';
                                            $statusText = 'Bloqueado';
                                        } else {
                                            $statusClass = 'text-warning'; 
                                            $statusBg = 'bg-warning';
                                            $statusText = 'Pendente';
                                        }
                                        
                                        echo "<div class='d-flex align-items-center gap-2'>
                                                <span class='rounded-circle $statusBg' style='width: 8px; height: 8px;'></span>
                                                <span class='small fw-bold $statusClass'>$statusText</span>
                                              </div>";
                                        ?>
                                    </td>
                                    <td class="px-4 text-end pe-4">
                                        <div class="dropdown">
                                            <button class="btn btn-white btn-sm rounded-circle shadow-sm border-0" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="width: 32px; height: 32px;">
                                                <i class="bi bi-three-dots text-muted"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-4 p-2" style="z-index: 1050;">
                                                <?php 
                                                $targetIsAdmin = (stripos($u['role_name'], 'admin') !== false);
                                                $canEdit = true;
                                                if ($targetIsAdmin && !hasCapability('system_config')) {
                                                    $canEdit = false;
                                                }
                                                ?>
                                                
                                                <?php if($canEdit): ?>
                                                    <li><h6 class="dropdown-header small text-uppercase fw-bold opacity-50">Gerenciar</h6></li>
                                                    <?php foreach($allRoles as $r): ?>
                                                    <li><a class="dropdown-item rounded-3 small" href="#" onclick="updateRole(<?php echo $u['id']; ?>, <?php echo $r['id']; ?>, '<?php echo $r['name']; ?>')"><i class="bi bi-person-fill-gear me-2"></i>Mudar para <?php echo $r['name']; ?></a></li>
                                                    <?php endforeach; ?>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li><a class="dropdown-item rounded-3 small" href="#" onclick="resetPassword(<?php echo $u['id']; ?>)"><i class="bi bi-key me-2"></i>Resetar Senha</a></li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li><a class="dropdown-item rounded-3 small text-<?php echo $u['status'] === 'active' ? 'danger' : 'success'; ?>" href="#" onclick="toggleStatus(<?php echo $u['id']; ?>, '<?php echo $u['status']; ?>')">
                                                        <i class="bi <?php echo $u['status'] === 'active' ? 'bi-lock-fill' : 'bi-unlock-fill'; ?> me-2"></i>
                                                        <?php echo $u['status'] === 'active' ? 'Bloquear Acesso' : 'Ativar Acesso'; ?>
                                                    </a></li>
                                                <?php else: ?>
                                                    <li><span class="dropdown-item text-muted disabled small">Ação Restrita</span></li>
                                                <?php endif; ?>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- NEW TAB: ROLES -->
        <div class="tab-pane fade" id="roles" role="tabpanel" aria-labelledby="roles-tab">
            <div class="card card-glass rounded-4 overflow-hidden border-0">
                <div class="card-header bg-transparent border-0 py-4 px-4 d-flex justify-content-between align-items-center">
                     <div>
                        <h5 class="mb-0 fw-bold section-title text-dark">Gerenciar Permissões</h5>
                        <p class="text-muted small mb-0">Defina os níveis de acesso e capacidades.</p>
                     </div>
                     <button class="btn btn-primary rounded-pill fw-bold shadow-sm" onclick="openRoleModal()">
                        <i class="bi bi-plus-lg me-2"></i>Nova Permissão
                     </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle table-custom">
                            <thead class="bg-light bg-opacity-50">
                                <tr>
                                    <th class="py-3 px-4 border-bottom-0 fw-bold text-secondary small text-uppercase ps-4">Nome</th>
                                    <th class="py-3 px-4 border-bottom-0 fw-bold text-secondary small text-uppercase">Descrição</th>
                                    <th class="py-3 px-4 border-bottom-0 text-end fw-bold text-secondary small text-uppercase pe-4">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($allRoles as $role): ?>
                                <tr class="border-light">
                                    <td class="px-4 py-3 ps-4">
                                        <div class="d-flex align-items-center">
                                            <div class="icon-box bg-light text-primary rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                                <i class="bi bi-shield-fill"></i>
                                            </div>
                                            <span class="fw-bold text-dark font-primary"><?php echo htmlspecialchars($role['name']); ?></span>
                                        </div>
                                    </td>
                                    <td class="px-4 text-muted"><?php echo htmlspecialchars($role['description']); ?></td>
                                    <td class="px-4 text-end pe-4">
                                        <?php if(!$role['is_system']): ?>
                                            <button class="btn btn-sm btn-white text-primary shadow-sm rounded-circle me-1 border-0" onclick="editRole(<?php echo htmlspecialchars(json_encode($role)); ?>)" style="width: 32px; height: 32px;"><i class="bi bi-pencil-fill small"></i></button>
                                            <button class="btn btn-sm btn-white text-danger shadow-sm rounded-circle border-0" onclick="deleteRole(<?php echo $role['id']; ?>)" style="width: 32px; height: 32px;"><i class="bi bi-trash-fill small"></i></button>
                                        <?php else: ?>
                                            <span class="badge bg-light text-muted border rounded-pill small"><i class="bi bi-lock-fill me-1"></i>Sistema</span>
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
                    <div class="card card-glass border-0 shadow-sm rounded-4">
                        <div class="card-header bg-transparent border-0 py-4 px-4 text-center">
                            <div class="mx-auto bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px;">
                                <i class="bi bi-database-fill-gear fs-2"></i>
                            </div>
                            <h5 class="mb-1 fw-bold section-title text-dark">Conexão PostgreSQL</h5>
                            <p class="text-muted small mb-0 px-5">Configure os dados de acesso ao banco de dados principal.</p>
                        </div>
                        <div class="card-body p-4 pt-0">
                            <form id="dbConfigForm">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control rounded-4 border-0 bg-light" id="dbHost" name="host" value="<?php echo htmlspecialchars($currentDB['host']); ?>" required placeholder="Host">
                                    <label for="dbHost">Host Address</label>
                                </div>
                                <div class="row g-3 mb-3">
                                    <div class="col-md-8">
                                        <div class="form-floating">
                                            <input type="text" class="form-control rounded-4 border-0 bg-light" id="dbName" name="dbname" value="<?php echo htmlspecialchars($currentDB['dbname']); ?>" required placeholder="Database">
                                            <label for="dbName">Database Name</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-floating">
                                            <input type="text" class="form-control rounded-4 border-0 bg-light" id="dbPort" name="port" value="<?php echo htmlspecialchars($currentDB['port']); ?>" required placeholder="Port">
                                            <label for="dbPort">Port</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control rounded-4 border-0 bg-light" id="dbUser" name="user" value="<?php echo htmlspecialchars($currentDB['user']); ?>" required placeholder="User">
                                    <label for="dbUser">Username</label>
                                </div>
                                <div class="form-floating mb-4">
                                    <input type="password" class="form-control rounded-4 border-0 bg-light" id="dbPass" name="password" value="<?php echo htmlspecialchars($currentDB['password']); ?>" required placeholder="Password">
                                    <label for="dbPass">Password</label>
                                </div>
                                <button type="button" class="btn btn-primary w-100 rounded-pill fw-bold py-3 shadow-sm" onclick="saveDBConfig()">
                                    Salvar Configuração
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab: Tools -->
        <div class="tab-pane fade" id="tools" role="tabpanel" aria-labelledby="tools-tab">
            <div class="text-center py-5 opacity-50">
                <div class="mb-3">
                     <i class="bi bi-cone-striped fs-1 text-muted"></i>
                </div>
                <h5 class="fw-bold font-primary text-muted">Em Construção</h5>
                <p class="small">Ferramentas de manutenção em breve.</p>
            </div>
        </div>
    </div>
</div>

<!-- Role Modal -->
<div class="modal fade" id="roleModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
      <div class="modal-header border-0 pb-0 pt-4 px-4">
        <div>
            <h5 class="modal-title fw-bold font-primary text-dark" id="roleModalTitle">Nova Permissão</h5>
            <p class="text-muted small mb-0">Defina os acessos desta função.</p>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-4">
        <form id="roleForm">
            <input type="hidden" name="id" id="roleId">
            <div class="form-floating mb-3">
                <input type="text" class="form-control rounded-4 bg-light border-0 fw-bold" name="name" id="roleName" required placeholder="Nome">
                <label for="roleName">Nome da Função</label>
            </div>
            <div class="form-floating mb-4">
                <textarea class="form-control rounded-4 bg-light border-0" name="description" id="roleDesc" style="height: 80px" placeholder="Desc"></textarea>
                <label for="roleDesc">Descrição</label>
            </div>
            
            <div class="mb-3">
                <label class="form-label small fw-bold text-uppercase text-muted opacity-75 footer-label mb-3">Permissões de Acesso</label>
                <div class="row g-3">
                    <div class="col-6">
                        <div class="form-check p-3 bg-light rounded-3 h-100">
                            <input class="form-check-input role-cap" type="checkbox" value="access_admin_panel" id="cap_access_admin">
                            <label class="form-check-label small fw-bold text-dark" for="cap_access_admin">Acesso Admin</label>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-check p-3 bg-light rounded-3 h-100">
                            <input class="form-check-input role-cap" type="checkbox" value="view_restricted" id="cap_view_restricted">
                            <label class="form-check-label small fw-bold text-dark" for="cap_view_restricted">Ver Restritos</label>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-check p-3 bg-light rounded-3 h-100">
                            <input class="form-check-input role-cap" type="checkbox" value="manage_users" id="cap_manage_users">
                            <label class="form-check-label small fw-bold text-dark" for="cap_manage_users">Gerenciar Usuários</label>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-check p-3 bg-light rounded-3 h-100">
                            <input class="form-check-input role-cap" type="checkbox" value="edit_tools" id="cap_edit_tools">
                            <label class="form-check-label small fw-bold text-dark" for="cap_edit_tools">Editor Ferramentas</label>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-check p-3 bg-light rounded-3 h-100">
                            <input class="form-check-input role-cap" type="checkbox" value="manage_roles" id="cap_manage_roles">
                            <label class="form-check-label small fw-bold text-dark" for="cap_manage_roles">Gerenciar Roles</label>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-check p-3 bg-danger bg-opacity-10 rounded-3 h-100 border border-danger border-opacity-25">
                            <input class="form-check-input role-cap p-danger" type="checkbox" value="system_config" id="cap_system_config">
                            <label class="form-check-label small fw-bold text-danger" for="cap_system_config">System Config</label>
                        </div>
                    </div>
                </div>
            </div>
        </form>
      </div>
      <div class="modal-footer border-0 pt-0 px-4 pb-4">
        <button type="button" class="btn btn-light rounded-pill px-4 fw-bold" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary rounded-pill fw-bold px-4 shadow-sm" onclick="saveRole()">Salvar Alterações</button>
      </div>
    </div>
  </div>
</div>

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
