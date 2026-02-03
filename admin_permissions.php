<?php
// admin_permissions.php
session_start();
require_once 'includes/header.php';
require_once 'includes/portal_auth.php';
require_once 'includes/permission_manager.php';
require_once 'includes/portal_helpers.php'; // For getRoles() if available, else we fetch.

// 1. Auth Check
if (!isLoggedIn() || !isAdmin()) {
    header("Location: index.php");
    exit;
}

$pm = new PermissionManager();
$pdo = $pm->getPDO(); // Helper access or direct

// Fetch Roles
$stmt = $pdo->query("SELECT * FROM roles ORDER BY id");
$roles = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Define Known Cards (Hardcoded list for now, or discovered)
$availableCards = [
    'technical_password' => 'Senha Técnica',
    'weather' => 'Clima & Tempo',
    'nexus_version' => 'Versão Uniplus',
    'intranet' => 'Intranet Suporte',
    'ppr' => 'Gestão PPR',
    'meetings' => 'Reuniões & Pautas',
    'blog' => 'Blog Suporte',
    'retaguarda_services' => 'Serviços Retaguarda', // Calculadora, etc
    'status_suporte' => 'Status Suporte (Novo)',
    // Add more as needed
];

// Handle Updates
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Expected format: perms[card_slug][role_id] = 1 (on) or missing (off)
    // Actually checkboxes usually send only if checked.
    // Better strategy: Loop through all cards and roles and set value based on existence in POST.
    
    foreach ($availableCards as $slug => $label) {
        foreach ($roles as $role) {
            // "perm_CARD_ROLE"
            $key = "perm_{$slug}_{$role['id']}";
            $canView = isset($_POST[$key]);
            
            $pm->setPermission($slug, $role['id'], $canView);
        }
    }
    $message = "Permissões atualizadas com sucesso!";
}

// Fetch Current Permissions to pre-fill
$currentPerms = $pm->getAllPermissions();
// Repack for easier lookup: [slug][role_id] = bool
$permMap = [];
foreach ($currentPerms as $p) {
    $permMap[$p['card_slug']][$p['role_id']] = $p['can_view'];
}

?>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="fw-bold text-dark mb-0">Gerenciar Permissões</h1>
            <p class="text-secondary small">Defina quem pode ver cada card no dashboard.</p>
        </div>
        <a href="index.php" class="btn btn-light shadow-sm rounded-pill px-4"><i class="bi bi-arrow-left me-2"></i>Voltar</a>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-success rounded-4 shadow-sm border-0 mb-4">
            <i class="bi bi-check-circle-fill me-2"></i><?php echo $message; ?>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <form method="POST" class="p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 py-3 text-secondary text-uppercase small" style="min-width: 200px;">Card / Ferramenta</th>
                            <?php foreach ($roles as $role): ?>
                                <th class="text-center text-secondary text-uppercase small py-3"><?php echo htmlspecialchars($role['name']); ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($availableCards as $slug => $label): ?>
                            <tr>
                                <th class="ps-4 py-3 text-dark fw-bold">
                                    <?php echo htmlspecialchars($label); ?>
                                    <div class="small text-muted fw-normal font-monospace"><?php echo $slug; ?></div>
                                </th>
                                <?php foreach ($roles as $role): 
                                    // Check existing permission. Default TRUE if not set.
                                    $hasPerm = isset($permMap[$slug][$role['id']]) ? $permMap[$slug][$role['id']] : true;
                                    $checkName = "perm_{$slug}_{$role['id']}";
                                ?>
                                    <td class="text-center">
                                        <div class="form-check d-inline-block form-switch">
                                            <input class="form-check-input" type="checkbox" name="<?php echo $checkName; ?>" 
                                                id="<?php echo $checkName; ?>" 
                                                <?php echo $hasPerm ? 'checked' : ''; ?>>
                                        </div>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="p-4 bg-light border-top">
                <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">
                    <i class="bi bi-save me-2"></i>Salvar Alterações
                </button>
            </div>
        </form>
    </div>
</div>

<?php 
// No footer needed really for admin page but good for closing tags
echo '<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script></body></html>';
?>
