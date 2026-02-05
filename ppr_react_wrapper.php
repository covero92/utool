<?php
// ppr_react_wrapper.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Initialize Session & Auth
require_once 'includes/portal_auth.php';

// Check Permissions
if (!$loggedIn) {
    header('Location: index.php');
    exit;
}

// Function to get the latest built asset
function getAssetPath($dir, $extension)
{
    if (!is_dir($dir))
        return '';
    $files = scandir($dir);
    foreach ($files as $file) {
        if (pathinfo($file, PATHINFO_EXTENSION) === $extension && strpos($file, 'index') !== false) {
            return 'ppr_dashboard/dist/assets/' . $file;
        }
    }
    return '';
}

$baseDistDir = __DIR__ . '/ppr_dashboard/dist/assets';
$cssPath = getAssetPath($baseDistDir, 'css');
$jsPath = getAssetPath($baseDistDir, 'js');

?>
<!doctype html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Gestão de PPR</title>
    <!-- Inject PHP Session Info -->
    <script>
        window.USER_INFO = {
            name: "<?php echo $currentUser; ?>",
            role: "<?php echo $currentRoleId; ?>",
            capabilities: <?php echo json_encode($_SESSION['user_capabilities'] ?? []); ?>
        };
    </script>
    <?php if ($cssPath): ?>
        <link rel="stylesheet" href="<?php echo $cssPath; ?>">
    <?php endif; ?>
</head>

<body>
    <div id="root"></div>
    <?php if ($jsPath): ?>
        <script type="module" src="<?php echo $jsPath; ?>"></script>
    <?php else: ?>
        <div style="padding: 20px; color: red;">
            <h2>Erro de Carregamento</h2>
            <p>Arquivos de build não encontrados. Execute "npm run build" em /ppr_dashboard.</p>
        </div>
    <?php endif; ?>
</body>

</html>