<?php
// Define a URL base dinamicamente para garantir que os assets e as chamadas de API funcionem corretamente,
// independentemente de a aplicação estar na raiz do servidor ou em um subdiretório.
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'];
$script_name = $_SERVER['SCRIPT_NAME']; // ex: /cidadesnfse/index.php
$base_path = str_replace('/index.php', '', $script_name);
$baseUrl = rtrim($protocol . $host . $base_path, '/') . '/';
?>
<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard de Gestão de NFS-e</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    
    <!-- Leaflet.js (Mapas) -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <!-- Leaflet.markercluster -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.Default.css" />
    
    <!-- Choices.js (Dropdowns) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css"/>
    
    <!-- Estilos Personalizados -->
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>static/css/style.css">

    <script>
        // Gera dinamicamente a URL base para ser usada pelo JavaScript.
        // Isso torna a aplicação portátil entre diferentes ambientes.
        window.BASE_URL = '<?php echo $baseUrl; ?>';
    </script>
</head>
<body>

    <header class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top">
        <div class="container-fluid">
            <a class="btn btn-outline-light border-0 me-3" href="../index.php" title="Voltar ao Hub">
                <i class="bi bi-arrow-left"></i>
            </a>

            <a class="navbar-brand" href="#">
                <i class="bi bi-file-earmark-text-fill"></i>
                Gestão de Municípios NFS-e
            </a>
            <span id="admin-mode-indicator" class="badge bg-warning text-dark ms-2 d-none">
                <i class="bi bi-pencil-fill"></i> Modo de Edição
            </span>
            
            <div class="ms-auto d-flex align-items-center">
                <form id="login-form" class="d-flex me-2">
                    <input class="form-control form-control-sm me-1" type="password" id="admin-password" placeholder="Senha Admin" required>
                    <button class="btn btn-light btn-sm" type="submit">Login</button>
                </form>
                <button id="logout-btn" class="btn btn-warning btn-sm d-none">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </button>
            </div>
        </div>
    </header>

    <main class="container-fluid mt-3">
        <ul class="nav nav-tabs" id="main-tabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="dashboard-tab-link" data-bs-toggle="tab" data-bs-target="#dashboard-tab-pane" type="button" role="tab" aria-controls="dashboard-tab-pane" aria-selected="true">
                    <i class="bi bi-grid-1x2-fill"></i> Dashboard
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="municipios-tab-link" data-bs-toggle="tab" data-bs-target="#municipios-tab-pane" type="button" role="tab" aria-controls="municipios-tab-pane" aria-selected="false">
                    <i class="bi bi-building"></i> Municípios
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="providers-tab-link" data-bs-toggle="tab" data-bs-target="#providers-tab-pane" type="button" role="tab" aria-controls="providers-tab-pane" aria-selected="false">
                    <i class="bi bi-hdd-stack-fill"></i> Provedores
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="info-tab-link" data-bs-toggle="tab" data-bs-target="#info-tab-pane" type="button" role="tab" aria-controls="info-tab-pane" aria-selected="false">
                    <i class="bi bi-info-circle-fill"></i> Informações
                </button>
            </li>
        </ul>

        <div class="tab-content" id="myTabContent">
            <!-- Conteúdo das abas -->
            <div class="tab-pane fade show active" id="dashboard-tab-pane" role="tabpanel" aria-labelledby="dashboard-tab-link" tabindex="0">
                <?php include 'templates/_dashboard_tab.html'; ?>
            </div>
            <div class="tab-pane fade" id="municipios-tab-pane" role="tabpanel" aria-labelledby="municipios-tab-link" tabindex="0">
                 <?php include 'templates/_municipios_tab.html'; ?>
            </div>
            <div class="tab-pane fade" id="providers-tab-pane" role="tabpanel" aria-labelledby="providers-tab-link" tabindex="0">
                 <?php include 'templates/_providers_tab.html'; ?>
            </div>
            <div class="tab-pane fade" id="info-tab-pane" role="tabpanel" aria-labelledby="info-tab-link" tabindex="0">
                 <?php include 'templates/_informacoes_tab.html'; ?>
            </div>
        </div>
    </main>

    <!-- Modal para Provedores (CRUD) -->
    <?php include 'templates/_provider_modal.html'; ?>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Leaflet.js (Mapas) -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <!-- Leaflet.markercluster -->
    <script src="https://unpkg.com/leaflet.markercluster@1.4.1/dist/leaflet.markercluster.js"></script>
    <!-- Choices.js (Dropdowns) -->
    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
    <!-- Lógica Principal da Aplicação -->
    <script src="<?php echo $baseUrl; ?>static/js/main.js"></script>
</body>
</html>