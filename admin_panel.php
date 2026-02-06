<?php
// admin_panel.php
require_once 'includes/db_connection.php';
require_once 'includes/auth_guard.php';
require_once 'includes/portal_auth.php';

// Authorization Check: Must be Admin
if (!isAdmin()) {
    die("Acesso Negado. Requer privilégios de Administrador.");
}

$pdo = getDBConnection();
$users = $pdo->query("SELECT u.*, r.name as role_name FROM users u LEFT JOIN roles r ON u.role_id = r.id ORDER BY u.status = 'pending' DESC, u.full_name ASC")->fetchAll();
$roles = $pdo->query("SELECT * FROM roles ORDER BY id ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Painel Administrativo | SuporteHub</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 text-gray-900">

<div class="min-h-screen p-8">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Painel Administrativo</h1>
                <p class="text-gray-500 mt-1">Gerenciamento de Usuários e Permissões Globais</p>
            </div>
            <a href="index.php" class="flex items-center gap-2 text-gray-600 hover:text-blue-600 transition-colors">
                <i data-lucide="arrow-left"></i> Voltar ao Hub
            </a>
        </div>

        <!-- Users Section -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-10 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                <h2 class="text-lg font-semibold flex items-center gap-2">
                    <i data-lucide="users" class="text-blue-600"></i> Usuários
                </h2>
                <span class="bg-blue-100 text-blue-700 text-xs font-bold px-2 py-1 rounded-full"><?php echo count($users); ?> cadastrados</span>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 text-gray-500 uppercase text-xs font-semibold">
                        <tr>
                            <th class="px-6 py-3">Usuário</th>
                            <th class="px-6 py-3">Status</th>
                            <th class="px-6 py-3">Cargo (Role)</th>
                            <th class="px-6 py-3 text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php foreach($users as $u): ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center font-bold text-gray-500 overflow-hidden">
                                        <?php if($u['profile_image']): ?>
                                            <img src="<?php echo $u['profile_image']; ?>" class="w-full h-full object-cover">
                                        <?php else: ?>
                                            <?php echo strtoupper(substr($u['full_name'], 0, 2)); ?>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <div class="font-medium text-gray-900"><?php echo htmlspecialchars($u['full_name']); ?></div>
                                        <div class="text-xs text-gray-400">@<?php echo htmlspecialchars($u['username']); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <?php if($u['status'] === 'active'): ?>
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-green-600"></span> Ativo
                                    </span>
                                <?php elseif($u['status'] === 'pending'): ?>
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 animate-pulse">
                                        <span class="w-1.5 h-1.5 rounded-full bg-yellow-600"></span> Pendente
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        Bloqueado
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4">
                                <select onchange="updateRole(<?php echo $u['id']; ?>, this.value)" class="bg-white border border-gray-300 text-gray-700 text-xs rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2">
                                    <?php foreach($roles as $r): ?>
                                        <option value="<?php echo $r['id']; ?>" <?php echo ($u['role_id'] == $r['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($r['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <?php if($u['status'] === 'pending'): ?>
                                        <button onclick="approveUser(<?php echo $u['id']; ?>)" class="bg-green-600 hover:bg-green-700 text-white px-3 py-1.5 rounded-md text-xs font-medium transition-colors shadow-sm">
                                            Aprovar
                                        </button>
                                    <?php endif; ?>
                                    
                                    <button onclick="toggleStatus(<?php echo $u['id']; ?>, '<?php echo $u['status']; ?>')" class="<?php echo $u['status'] === 'blocked' ? 'text-green-600 bg-green-50' : 'text-red-500 bg-red-50'; ?> hover:bg-opacity-80 p-2 rounded-md transition-colors" title="<?php echo $u['status'] === 'blocked' ? 'Desbloquear' : 'Bloquear'; ?>">
                                        <i data-lucide="<?php echo $u['status'] === 'blocked' ? 'unlock' : 'lock'; ?>" width="16"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Roles Section -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Role List -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                    <h2 class="text-lg font-semibold flex items-center gap-2">
                        <i data-lucide="shield" class="text-indigo-600"></i> Cargos & Permissões
                    </h2>
                    <button onclick="openRoleModal()" class="text-xs bg-indigo-50 text-indigo-700 px-3 py-1.5 rounded-md font-medium hover:bg-indigo-100 transition-colors">
                        + Criar Cargo
                    </button>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <?php foreach($roles as $r): ?>
                            <div class="flex items-start justify-between p-4 rounded-lg bg-gray-50 border border-gray-100 hover:border-indigo-200 transition-colors group">
                                <div>
                                    <h3 class="font-bold text-gray-900"><?php echo htmlspecialchars($r['name']); ?></h3>
                                    <p class="text-xs text-gray-500 mt-1"><?php echo htmlspecialchars($r['description']); ?></p>
                                    <div class="flex flex-wrap gap-1 mt-2">
                                        <?php 
                                            $caps = json_decode($r['capabilities'] ?? '[]', true);
                                            foreach($caps as $c): 
                                        ?>
                                            <span class="text-[10px] uppercase bg-white border border-gray-200 px-1.5 py-0.5 rounded text-gray-600 tracking-wide"><?php echo $c; ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button onclick='editRole(<?php echo json_encode($r); ?>)' class="p-1.5 text-blue-600 hover:bg-blue-50 rounded">
                                        <i data-lucide="edit-2" width="14"></i>
                                    </button>
                                    <?php if(!$r['is_system']): ?>
                                    <button onclick="deleteRole(<?php echo $r['id']; ?>)" class="p-1.5 text-red-600 hover:bg-red-50 rounded">
                                        <i data-lucide="trash" width="14"></i>
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Role Editor (Simplified) -->
            <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-6">
                <h3 class="font-bold text-indigo-900 mb-4">Dicas de Permissão</h3>
                <ul class="space-y-2 text-sm text-indigo-800">
                    <li class="flex gap-2"><i data-lucide="check" width="16"></i> <strong>bypass_auth</strong>: Acesso total sem restrições.</li>
                    <li class="flex gap-2"><i data-lucide="check" width="16"></i> <strong>manage_users</strong>: Aprovar/Bloquear usuários.</li>
                    <li class="flex gap-2"><i data-lucide="check" width="16"></i> <strong>system_config</strong>: Editar configurações do sistema.</li>
                    <li class="flex gap-2"><i data-lucide="check" width="16"></i> <strong>edit_ppr</strong>: Editar metas e resultados do PPR.</li>
                    <li class="flex gap-2"><i data-lucide="check" width="16"></i> <strong>view_restricted</strong>: Ver dados sensíveis (Logs, etc).</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Role Editing -->
<div id="roleModal" class="fixed inset-0 bg-black/50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg overflow-hidden transform transition-all scale-95 opacity-0" id="roleModalContent">
        <form id="roleForm" onsubmit="saveRole(event)">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                <h3 class="font-bold text-lg" id="modalTitle">Novo Cargo</h3>
                <button type="button" onclick="closeRoleModal()" class="text-gray-400 hover:text-gray-600"><i data-lucide="x"></i></button>
            </div>
            <div class="p-6 space-y-4">
                <input type="hidden" name="id" id="roleId">
                <input type="hidden" name="portal_action" value="admin_save_role">
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nome do Cargo</label>
                    <input type="text" name="name" id="roleName" required class="w-full border border-gray-300 rounded-lg p-2.5 bg-white focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
                    <input type="text" name="description" id="roleDesc" class="w-full border border-gray-300 rounded-lg p-2.5 bg-white focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Permissões (Capabilities)</label>
                    <div class="grid grid-cols-2 gap-2 text-sm">
                        <?php 
                        $allCaps = ['bypass_auth','manage_users','manage_roles','system_config','edit_tools','edit_ppr','view_restricted','access_admin_panel'];
                        foreach($allCaps as $c): 
                        ?>
                        <label class="flex items-center gap-2 p-2 border border-gray-100 rounded hover:bg-gray-50 cursor-pointer">
                            <input type="checkbox" name="capabilities[]" value="<?php echo $c; ?>" class="rounded text-blue-600 focus:ring-blue-500">
                            <span class="font-mono text-xs"><?php echo $c; ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <div class="px-6 py-4 bg-gray-50 text-right">
                <button type="button" onclick="closeRoleModal()" class="text-gray-600 hover:text-gray-800 font-medium px-4 py-2 mr-2">Cancelar</button>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 rounded-lg shadow-sm">Salvar</button>
            </div>
        </form>
    </div>
</div>

<script>
    lucide.createIcons();

    // API Helpers
    async function apiCall(data) {
        const formData = new FormData();
        for (const key in data) {
            if (Array.isArray(data[key])) {
                formData.append(key, JSON.stringify(data[key]));
            } else {
                formData.append(key, data[key]);
            }
        }
        
        try {
            const res = await fetch('includes/portal_actions.php', { method: 'POST', body: formData });
            const json = await res.json();
            if (json.success) {
                window.location.reload();
            } else {
                alert('Erro: ' + (json.message || 'Desconhecido'));
            }
        } catch (e) {
            console.error(e);
            alert('Erro de conexão');
        }
    }

    function updateRole(userId, roleId) {
        if(!confirm('Alterar cargo do usuário?')) return;
        apiCall({ portal_action: 'admin_update_user_role_id', user_id: userId, role_id: roleId });
    }

    function toggleStatus(userId, currentStatus) {
        // If pending, we treat it as toggle to active? Or approve separate?
        // toggle_status handles active <-> blocked.
        // approveUser handles pending -> active.
        if (currentStatus === 'pending') return approveUser(userId);
        
        if(!confirm(currentStatus === 'active' ? 'Bloquear usuário?' : 'Desbloquear usuário?')) return;
        apiCall({ portal_action: 'admin_toggle_user_status', user_id: userId, current_status: currentStatus });
    }

    function approveUser(userId) {
        if(!confirm('Aprovar cadastro deste usuário?')) return;
        // Approve = set status active. We can reuse toggle logic if we pass 'blocked' (so it becomes active)
        // Or better, explicit 'approve' action?
        // Let's use update_user_status logic: it flips active/blocked. 
        // We probably need a specific 'approve' call or modification to toggle.
        // Let's modify toggle locally: passing 'blocked' as current will set 'active'.
        // Wait, pending is different.
        // Let's assume we implement 'admin_approve_user' or use existing toggle logic smartly.
        // Simple hack: Call toggle with current_status='blocked' (so it becomes active).
        apiCall({ portal_action: 'admin_toggle_user_status', user_id: userId, current_status: 'blocked' }); 
    }

    // Role Modal Logic
    const modal = document.getElementById('roleModal');
    const content = document.getElementById('roleModalContent');
    const form = document.getElementById('roleForm');

    function openRoleModal() {
        modal.classList.remove('hidden');
        setTimeout(() => {
            content.classList.remove('scale-95', 'opacity-0');
            content.classList.add('scale-100', 'opacity-100');
        }, 10);
        document.getElementById('modalTitle').innerText = 'Novo Cargo';
        form.reset();
        document.getElementById('roleId').value = '';
    }

    function closeRoleModal() {
        content.classList.remove('scale-100', 'opacity-100');
        content.classList.add('scale-95', 'opacity-0');
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 200);
    }

    function editRole(role) {
        openRoleModal();
        document.getElementById('modalTitle').innerText = 'Editar Cargo';
        document.getElementById('roleId').value = role.id;
        document.getElementById('roleName').value = role.name;
        document.getElementById('roleDesc').value = role.description || '';
        
        // Capabilities checkboxes
        const caps = JSON.parse(role.capabilities || '[]');
        const boxes = document.getElementsByName('capabilities[]');
        boxes.forEach(box => {
            box.checked = caps.includes(box.value);
        });
    }

    async function saveRole(e) {
        e.preventDefault();
        const fd = new FormData(form);
        const data = Object.fromEntries(fd.entries());
        // Handle checkboxes manually
        const caps = [];
        document.getElementsByName('capabilities[]').forEach(box => {
            if(box.checked) caps.push(box.value);
        });
        
        await apiCall({
            portal_action: 'admin_save_role',
            id: data.id,
            name: data.name,
            description: data.description,
            capabilities: caps
        });
    }

    function deleteRole(id) {
        if(!confirm('Excluir este cargo? Usuários vinculados perderão acesso.')) return;
        apiCall({ portal_action: 'admin_delete_role', id: id });
    }
</script>

</body>
</html>
