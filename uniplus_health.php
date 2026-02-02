<?php
include 'includes/header.php';

// --- CONFIGURAÇÃO ---
$defaultPath = 'c:/xampp/htdocs/utool/font/uniplus.properties';
$jarPath = 'c:/xampp/htdocs/utool/font/';
if (session_status() == PHP_SESSION_NONE) session_start();
$propFile = $_SESSION['uniplus_config_path'] ?? $defaultPath;

// --- FUNÇÃO DE DIAGNÓSTICO ---
function runDiagnostics($propFile, $jarPath) {
    $results = [
        'files' => [],
        'db' => [],
        'network' => [],
        'process' => []
    ];

    // 1. CHECAGEM DE ARQUIVOS
    $filesToCheck = [
        $propFile => 'Arquivo de Configuração (Properties)',
        $jarPath . 'uniplus.jar' => 'Core (uniplus.jar)',
        $jarPath . 'yoda.jar' => 'Serviço (yoda.jar)',
        $jarPath . 'uniplusweb-comclient.jar' => 'Comunicação Web'
    ];

    foreach ($filesToCheck as $path => $label) {
        if (file_exists($path)) {
            $size = filesize($path);
            $sizeMB = round($size / 1024 / 1024, 2);
            $results['files'][] = ['status' => 'ok', 'label' => $label, 'msg' => "Encontrado ($sizeMB MB)", 'path' => $path];
        } else {
            $results['files'][] = ['status' => 'error', 'label' => $label, 'msg' => 'Arquivo ausente!', 'path' => $path];
        }
    }

    // 2. CHECAGEM DE BANCO DE DADOS
    if (file_exists($propFile)) {
        $props = parse_ini_file($propFile); // Warning: ini_file fails with comments sometimes, manual logic preferred but trying simple first
        // Manual parse if ini fails or just simple grep
        if (!$props) {
            $content = file_get_contents($propFile);
            preg_match_all('/^([^#=]+)=(.*)$/m', $content, $matches, PREG_SET_ORDER);
            $props = [];
            foreach ($matches as $m) $props[trim($m[1])] = trim($m[2]);
        }

        $host = $props['base.ip'] ?? 'localhost';
        $port = $props['base.porta'] ?? '5432';
        $dbname = $props['base.nome'] ?? '';
        $user = 'postgres'; // Default guess
        $pass = 'postgres'; // Default guess

        // Try raw socket connect first (simplest check if DB is UP)
        $fp = @fsockopen($host, $port, $errno, $errstr, 2);
        if ($fp) {
            $results['db'][] = ['status' => 'ok', 'label' => 'Conectividade TCP', 'msg' => "Conectado a $host:$port"];
            fclose($fp);
            
            // Try PG Connect (Extension Required)
            if (function_exists('pg_connect')) {
                // Try standard user/pass or infer? Usually uniplus uses postgres/postgres or similar
                // We won't bruteforce, just report if PHP has driver
                $results['db'][] = ['status' => 'info', 'label' => 'Driver PHP', 'msg' => 'Extensão pgsql instalada'];
            } else {
                $results['db'][] = ['status' => 'warning', 'label' => 'Driver PHP', 'msg' => 'Extensão pdo_pgsql/pgsql não detectada no PHP'];
            }

        } else {
            $results['db'][] = ['status' => 'error', 'label' => 'Conectividade TCP', 'msg' => "Falha ao conectar $host:$port ($errstr)"];
        }
    } else {
        $results['db'][] = ['status' => 'error', 'label' => 'Configuração', 'msg' => 'Impossível ler uniplus.properties'];
    }

    // 3. CHECAGEM DE REDE (PORTAS ÚTEIS)
    $ports = [
        1099 => 'RMI Registry (Concentrador)',
        8080 => 'Dashboard Web',
        8081 => 'API (Opcional)'
    ];

    foreach ($ports as $p => $desc) {
        $fp = @fsockopen('localhost', $p, $errno, $errstr, 1);
        if ($fp) {
            $results['network'][] = ['status' => 'ok', 'label' => "Porta $p", 'msg' => "$desc - Aberta/Ouvindo"];
            fclose($fp);
        } else {
             // Not necessarily valid error, service might be down
             $results['network'][] = ['status' => 'warning', 'label' => "Porta $p", 'msg' => "$desc - Fechada"];
        }
    }

    // 4. PROCESSOS
    $tasklist = shell_exec('tasklist /FI "IMAGENAME eq java.exe" /FI "IMAGENAME eq javaw.exe"');
    if (strpos($tasklist, 'java') !== false) {
        $count = substr_count(strtolower($tasklist), 'java');
        $results['process'][] = ['status' => 'ok', 'label' => 'Processos Java', 'msg' => "$count processo(s) Java detectado(s)."];
        // Analyze memory if possible? regex the output
    } else {
        $results['process'][] = ['status' => 'error', 'label' => 'Processos Java', 'msg' => 'Nenhum processo Java rodando! O sistema está parado?'];
    }

    return $results;
}

$results = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['run'])) {
    $results = runDiagnostics($propFile, $jarPath);
}

?>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="display-6 fw-bold text-dark"><i class="bi bi-heart-pulse me-2 text-success"></i>Uniplus Health Check</h1>
            <p class="text-muted mb-0">Diagnóstico do ambiente e sistema.</p>
        </div>
        <a href="uniplus_toolkit.php" class="btn btn-outline-secondary rounded-pill"><i class="bi bi-arrow-left me-1"></i> Voltar</a>
    </div>

    <?php if (!$results): ?>
        <div class="text-center py-5">
            <div class="mb-4">
                <i class="bi bi-activity fs-1 text-secondary opacity-25" style="font-size: 5rem !important;"></i>
            </div>
            <h4 class="fw-bold text-dark">Pronto para Diagnosticar</h4>
            <p class="text-muted">Clique abaixo para iniciar a varredura do sistema.</p>
            <form method="POST">
                <button type="submit" class="btn btn-success btn-lg rounded-pill px-5 shadow fw-bold">
                    <i class="bi bi-play-fill me-2"></i> Iniciar Check-up
                </button>
            </form>
        </div>
    <?php else: ?>
        
        <div class="row g-4">
            
            <!-- SECTION: FILES -->
            <div class="col-md-6">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header bg-white border-0 pt-4 pb-0">
                        <h5 class="fw-bold"><i class="bi bi-folder2-open me-2 text-primary"></i>Arquivos Críticos</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <?php foreach($results['files'] as $item): ?>
                                <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong class="d-block text-dark"><?php echo $item['label']; ?></strong>
                                        <small class="text-muted text-break" style="font-size: 0.75rem;"><?php echo $item['path']; ?></small>
                                    </div>
                                    <span class="badge rounded-pill <?php echo ($item['status'] == 'ok' ? 'bg-success' : 'bg-danger'); ?>">
                                        <?php echo $item['msg']; ?>
                                    </span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- SECTION: DATABASE & NETWORK -->
            <div class="col-md-6">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header bg-white border-0 pt-4 pb-0">
                        <h5 class="fw-bold"><i class="bi bi-hdd-network me-2 text-info"></i>Rede & Banco de Dados</h5>
                    </div>
                    <div class="card-body">
                        <h6 class="text-uppercase text-muted small fw-bold mt-2">Banco de Dados</h6>
                        <ul class="list-group mb-3">
                             <?php foreach($results['db'] as $item): 
                                $badge = 'bg-secondary';
                                if($item['status']=='ok') $badge='bg-success';
                                if($item['status']=='warning') $badge='bg-warning text-dark';
                                if($item['status']=='error') $badge='bg-danger';
                             ?>
                                <li class="list-group-item border-0 px-0 py-1 d-flex justify-content-between">
                                    <span><?php echo $item['label']; ?></span>
                                    <span class="badge <?php echo $badge; ?>"><?php echo $item['msg']; ?></span>
                                </li>
                             <?php endforeach; ?>
                        </ul>

                        <h6 class="text-uppercase text-muted small fw-bold mt-3">Portas do Sistema</h6>
                        <ul class="list-group">
                             <?php foreach($results['network'] as $item): 
                                $badge = 'bg-secondary';
                                if($item['status']=='ok') $badge='bg-success';
                                if($item['status']=='warning') $badge='bg-warning text-dark'; // Port closed is warning, not fatal error always
                             ?>
                                <li class="list-group-item border-0 px-0 py-1 d-flex justify-content-between">
                                    <span><?php echo $item['label']; ?></span>
                                    <span class="badge <?php echo $badge; ?>"><?php echo $item['msg']; ?></span>
                                </li>
                             <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- SECTION: PROCESSES -->
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-4 ">
                    <div class="card-body d-flex align-items-center justify-content-between p-4">
                        <div class="d-flex align-items-center">
                            <div class="icon-box bg-dark text-white rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; font-size: 1.5rem;">
                                <i class="bi bi-cpu"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-0">Status do Processo</h5>
                                <p class="text-muted mb-0 small">Verificação do Java no Windows Tasklist</p>
                            </div>
                        </div>
                        <div class="text-end">
                            <?php foreach($results['process'] as $item): ?>
                                <h4 class="<?php echo ($item['status']=='ok' ? 'text-success' : 'text-danger'); ?> fw-bold mb-0">
                                    <?php echo ($item['status']=='ok' ? '<i class="bi bi-check-circle-fill me-2"></i>Operacional' : '<i class="bi bi-x-circle-fill me-2"></i>Parado'); ?>
                                </h4>
                                <small class="text-muted"><?php echo $item['msg']; ?></small>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <div class="text-center mt-5">
            <form method="POST">
                <button type="submit" class="btn btn-light rounded-pill px-4 border shadow-sm">
                    <i class="bi bi-arrow-clockwise me-2"></i> Rodar Diagnóstico Novamente
                </button>
            </form>
        </div>

    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
