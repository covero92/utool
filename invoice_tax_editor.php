<?php
session_start();
require_once 'includes/header.php';

// Handle Connection Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'connect') {
        $_SESSION['db_host'] = $_POST['host'];
        $_SESSION['db_port'] = $_POST['port'];
        $_SESSION['db_name'] = $_POST['dbname'];
        $_SESSION['db_user'] = $_POST['user'];
        $_SESSION['db_pass'] = $_POST['password'];
        $message = "Configurações salvas! Tentando conectar...";
        $messageType = "info";
    } elseif ($_POST['action'] === 'disconnect') {
        unset($_SESSION['db_host']);
        unset($_SESSION['db_port']);
        unset($_SESSION['db_name']);
        unset($_SESSION['db_user']);
        unset($_SESSION['db_pass']);
        $message = "Desconectado com sucesso.";
        $messageType = "success";
    }
}

// Try to Connect if credentials exist
$dbconn = null;
$isConnected = false;
if (isset($_SESSION['db_host'])) {
    $host = $_SESSION['db_host'];
    $port = $_SESSION['db_port'];
    $dbname = $_SESSION['db_name'];
    $user = $_SESSION['db_user'];
    $password = $_SESSION['db_pass'];

    $conn_string = "host=$host port=$port dbname=$dbname user=$user password=$password";
    $dbconn = @pg_connect($conn_string);
    
    if ($dbconn) {
        $isConnected = true;
    } else {
        $message = "Falha na conexão: " . error_get_last()['message'];
        $messageType = "danger";
    }
}

$invoice = null;
$items = [];

// Handle Update (Only if connected)
if ($isConnected && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    $invoiceId = $_POST['invoice_id'];
    $itemsData = $_POST['items']; // Array of items
    
    // Header fields
    $frete = floatval($_POST['frete'] ?? 0);
    $seguro = floatval($_POST['seguro'] ?? 0);
    $desconto = floatval($_POST['desconto'] ?? 0);
    $outras = floatval($_POST['outras'] ?? 0);

    // Security Check: Status
    $checkSql = "SELECT status, codigostatusprotocolonfe FROM notafiscal WHERE id = $1";
    $resCheck = pg_query_params($dbconn, $checkSql, array($invoiceId));
    $statusRow = pg_fetch_assoc($resCheck);
    
    if ($statusRow['codigostatusprotocolonfe'] == 100) {
         $message = "Segurança: Não é permitido editar notas com status AUTORIZADA.";
         $messageType = "danger";
    } else {
        pg_query($dbconn, "BEGIN");
        
        try {
            $totalProdutos = 0;
            $totalICMS = 0;
            $totalIPI = 0;
            $totalST = 0;
            $totalPIS = 0;
            $totalCOFINS = 0;

            foreach ($itemsData as $itemId => $data) {
                $cfop = $data['cfop'];
                $baseicms = floatval($data['baseicms']);
                $aliquotaicms = floatval($data['aliquotaicms']);
                $valoricms = floatval($data['valoricms']);
                $basest = floatval($data['basest']);
                $aliquotast = floatval($data['aliquotast']);
                $valorst = floatval($data['valorst']);
                $valoripi = floatval($data['valoripi']);
                $valorpis = floatval($data['valorpis']);
                $valorcofins = floatval($data['valorcofins']);
                $valorunitario = floatval($data['valorunitario']);
                $quant = floatval($data['quant']);
                
                // New fields
                $ncm = !empty($data['ncm']) ? $data['ncm'] : null;
                $idunidademedida = !empty($data['idunidademedida']) ? intval($data['idunidademedida']) : null;
                $info_adicional = !empty($data['info_adicional']) ? $data['info_adicional'] : null;
                $desconto = floatval($data['desconto'] ?? 0);
                $percentualdesconto = floatval($data['percentualdesconto'] ?? 0);
                $total = floatval($data['valortotal'] ?? 0);
                
                // ICMS
                $origem = !empty($data['origem']) ? intval($data['origem']) : null;
                $csticms = !empty($data['cst_icms']) ? $data['cst_icms'] : null;
                $predbc = floatval($data['predbc'] ?? 0);
                
                // ST
                $modbcst = !empty($data['modbcst']) ? intval($data['modbcst']) : null;
                $pmvast = floatval($data['pmvast'] ?? 0);
                $predbcst = floatval($data['predbcst'] ?? 0);
                
                // IPI
                $cstipi = !empty($data['cst_ipi']) ? $data['cst_ipi'] : null;
                
                // PIS/COFINS
                $cstpis = !empty($data['cst_pis']) ? $data['cst_pis'] : null;
                $cstcofins = !empty($data['cst_cofins']) ? $data['cst_cofins'] : null;

                $totalItem = $total > 0 ? $total : ($quant * $valorunitario - $desconto);
                
                $updateItemSql = "UPDATE notafiscalitem SET 
                    cfop = $1, 
                    baseicms = $2, 
                    percentualicms = $3, 
                    icms = $4, 
                    baseicmssubstituicao = $5, 
                    percentualicmssubstituicao = $6, 
                    icmssubstituicao = $7, 
                    ipi = $8, 
                    pis = $9, 
                    cofins = $10,
                    quantidade = $11,
                    precounitario = $12,
                    ncm = $13,
                    idunidademedida = $14,
                    informacaoadicional = $15,
                    desconto = $16,
                    percentualdesconto = $17,
                    total = $18,
                    origem = $19,
                    situacaotributaria = $20,
                    percentualreducaoicms = $21,
                    modalidadeicmsst = $22,
                    margemvaloradicionado = $23,
                    percentualreducaoicmssubstituicao = $24,
                    situacaotributariaipi = $25,
                    cstpis = $26,
                    cstcofins = $27
                    WHERE id = $28";
                
                $resItem = pg_query_params($dbconn, $updateItemSql, array(
                    $cfop, $baseicms, $aliquotaicms, $valoricms, $basest, $aliquotast, $valorst, 
                    $valoripi, $valorpis, $valorcofins, $quant, $valorunitario,
                    $ncm, $idunidademedida, $info_adicional, $desconto, $percentualdesconto, $totalItem,
                    $origem, $csticms, $predbc, $modbcst, $pmvast, $predbcst, $cstipi, $cstpis, $cstcofins,
                    $itemId
                ));
                
                if (!$resItem) throw new Exception("Erro ao atualizar item ID: $itemId - " . pg_last_error($dbconn));

                $totalProdutos += $totalItem;
                $totalICMS += $valoricms;
                $totalIPI += $valoripi;
                $totalST += $valorst;
                $totalPIS += $valorpis;
                $totalCOFINS += $valorcofins;
            }
            
            // Calculate Final Total
            $finalTotalNota = $totalProdutos + $totalIPI + $totalST + $outras + $frete + $seguro - $desconto;

            $updateHeaderSql = "UPDATE notafiscal SET 
                valortotalnota = $1, 
                totalproduto = $2, 
                icms = $3, 
                ipi = $4, 
                icmssubstituicao = $5, 
                pis = $6, 
                cofins = $7,
                frete = $8,
                seguro = $9,
                descontosubtotal = $10,
                outrasdespesas = $11
                WHERE id = $12";
                
            $resHeader = pg_query_params($dbconn, $updateHeaderSql, array(
                $finalTotalNota, $totalProdutos, $totalICMS, $totalIPI, $totalST, $totalPIS, $totalCOFINS, 
                $frete, $seguro, $desconto, $outras, $invoiceId
            ));
            
            if (!$resHeader) throw new Exception("Erro ao atualizar cabeçalho da nota.");

            pg_query($dbconn, "COMMIT");
            $message = "Nota Fiscal atualizada com sucesso!";
            $messageType = "success";
            
            $_POST['search_term'] = $invoiceId; // Force search again
            
        } catch (Exception $e) {
            pg_query($dbconn, "ROLLBACK");
            $message = "Erro na atualização: " . $e->getMessage();
            $messageType = "danger";
        }
    }
}


// Handle XML DPS Generation
$generatedXml = '';
if ($isConnected && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'generate_xml_dps') {
    $invoiceId = $_POST['invoice_id'];
    $_POST['search_term'] = $invoiceId; // Force search to reload page
    
    // Fetch Invoice (Independent fetch for generation)
    $sql = "SELECT * FROM notafiscal WHERE id = $1";
    $res = pg_query_params($dbconn, $sql, array($invoiceId));
    $inv = pg_fetch_assoc($res);
    
    // Fetch Items
    $itemsSql = "SELECT * FROM notafiscalitem WHERE idnotafiscal = $1 ORDER BY id";
    $resItems = pg_query_params($dbconn, $itemsSql, array($invoiceId));
    $dbItems = pg_fetch_all($resItems) ?: [];

    if ($inv) {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;
        
        $root = $dom->createElementNS('http://www.sped.fazenda.gov.br/nfse', 'DPS');
        $dom->appendChild($root);
        $root->setAttribute('versao', '1.00'); 
        
        $infDPS = $dom->createElement('infDPS');
        $root->appendChild($infDPS);
        
        // ID
        $dpsId = "DPS" . str_pad($inv['numeronotafiscal'], 42, '0', STR_PAD_LEFT);
        $infDPS->setAttribute('Id', $dpsId);
        
        // 1. Ambiente (2-Homolog)
        $infDPS->appendChild($dom->createElement('tpAmb', '2')); 
        
        // 2. Data Emissão
        $dhEmi = date('Y-m-d\TH:i:s', strtotime($inv['datahoraemissao']));
        $infDPS->appendChild($dom->createElement('dhEmi', $dhEmi));
        
        // 3. VerAplic
        $infDPS->appendChild($dom->createElement('verAplic', '1.0.0'));
        
        // 4. Série
        $serie = substr($inv['serie'] ?: '1', 0, 5);
        $infDPS->appendChild($dom->createElement('serie', $serie));
        
        // 5. Número
        $infDPS->appendChild($dom->createElement('nDPS', $inv['numeronotafiscal']));
        
        // 6. Competência
        $dCompet = date('Y-m-d', strtotime($inv['datahoraemissao']));
        $infDPS->appendChild($dom->createElement('dCompet', $dCompet));
        
        // 7. Tipo Emitente
        $infDPS->appendChild($dom->createElement('tpEmit', '1'));
        
        // 8. Local Emissão
        $cLocEmi = $inv['idcidadeentrega'] ?: '9999999'; 
        $infDPS->appendChild($dom->createElement('cLocEmi', $cLocEmi));
        
        // 9. Prestador
        $prest = $dom->createElement('prest');
        $infDPS->appendChild($prest);
        $prest->appendChild($dom->createElement('CNPJ', '00000000000000')); 
        
        // 10. Tomador
        $toma = $dom->createElement('toma');
        $infDPS->appendChild($toma);
        $cpfCnpjRaw = preg_replace('/[^0-9]/', '', $inv['cnpjcpf']);
        if (strlen($cpfCnpjRaw) == 11) {
            $toma->appendChild($dom->createElement('CPF', $cpfCnpjRaw));
        } else {
            $toma->appendChild($dom->createElement('CNPJ', $cpfCnpjRaw ?: '00000000000000'));
        }
        $toma->appendChild($dom->createElement('xNome', substr($inv['razaosocial'], 0, 60)));
        
        if ($inv['enderecoentrega']) {
             $end = $dom->createElement('end');
             $toma->appendChild($end);
             $end->appendChild($dom->createElement('xLgr', substr($inv['enderecoentrega'], 0, 255)));
             $end->appendChild($dom->createElement('nro', substr($inv['numeroenderecoentrega'] ?: 'S/N', 0, 60)));
             $end->appendChild($dom->createElement('xBairro', substr($inv['bairroentrega'] ?: 'Centro', 0, 60)));
             $end->appendChild($dom->createElement('cMun', $inv['idcidadeentrega'] ?: '9999999'));
             $end->appendChild($dom->createElement('UF', $inv['idestadoentrega'] ?: 'XX'));
             $end->appendChild($dom->createElement('CEP', preg_replace('/[^0-9]/', '', $inv['cepentrega'] ?: '00000000')));
        }
        
        // 11. Serviço
        $serv = $dom->createElement('serv');
        $infDPS->appendChild($serv);
        
        $locPrest = $dom->createElement('locPrest');
        $serv->appendChild($locPrest);
        $locPrest->appendChild($dom->createElement('cLocPrestacao', $inv['idcidadeentrega'] ?: '9999999'));
        
        $cServ = $dom->createElement('cServ');
        $serv->appendChild($cServ);
        $cServ->appendChild($dom->createElement('cTribNac', '010101')); 
        $cServ->appendChild($dom->createElement('xDescServ', substr($dbItems[0]['descricao'] ?? 'Serviço Prestado', 0, 2000)));
        
        // 12. Valores
        $valores = $dom->createElement('valores');
        $infDPS->appendChild($valores);
        
        $vServPrest = $dom->createElement('vServPrest');
        $valores->appendChild($vServPrest);
        $vServPrest->appendChild($dom->createElement('vServ', number_format((float)$inv['totalproduto'], 2, '.', '')));
        
        $trib = $dom->createElement('trib');
        $valores->appendChild($trib);
        
        $tribMun = $dom->createElement('tribMun');
        $trib->appendChild($tribMun);
        $tribMun->appendChild($dom->createElement('tribISSQN', '1')); 
        $tribMun->appendChild($dom->createElement('tpRetISSQN', '1')); 
        
        $generatedXml = $dom->saveXML();
        
        // Trigger Search to show the interface
        $_POST['search_term'] = $invoiceId; 
    }
}

// Handle Search (Only if connected)
if ($isConnected && ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['id'])) && (isset($_POST['search_term']) || isset($_GET['id']))) {
    $term = isset($_POST['search_term']) ? $_POST['search_term'] : $_GET['id'];
    
    // Fetch Units of Measure
    $unitsQuery = pg_query($dbconn, "SELECT id, codigo as sigla, nome FROM unidademedida ORDER BY codigo");
    $units = $unitsQuery ? pg_fetch_all($unitsQuery) : [];

    // Select all relevant fields with correct names
    $sql = "SELECT id, numeronotafiscal, datahoraemissao, identidade, status, codigostatusprotocolonfe, 
            valortotalnota, totalproduto, icms, ipi, icmssubstituicao, pis, cofins,
            frete, seguro, descontosubtotal, outrasdespesas,
            -- Fiscal Info
            serie, modelo, chavenfe, finalidadeemissaonfe, datahoraentradasaida, idoperacaofiscal,
            -- Parties
            razaosocial, cnpjcpf,
            -- Transporte
            idtransportadora, placaveiculo, estadoveiculo, especie, marca, pesobruto, pesoliquido,
            -- Entrega
            enderecoentrega, numeroenderecoentrega, bairroentrega, idcidadeentrega, idestadoentrega, cepentrega, cnpjcpfentrega, razaosocialnomeentrega,
            -- Financeiro
            idcondicaopagto, avista, aprazo, pix, cheque, financiado, outros, carteiradigital,
            -- Info Adicional
            informacoescomplementarespersonalizadas, infofisco
            FROM notafiscal WHERE id::text = $1 OR numeronotafiscal::text = $1 LIMIT 1";
            
    $res = pg_query_params($dbconn, $sql, array($term));
    $invoice = pg_fetch_assoc($res);
    
    if (!$invoice) {
        $message = "Nota Fiscal não encontrada.";
        $messageType = "info";
    } else {
        // Map DB columns to variables for easier use in HTML
        $invoice['dataemissao'] = $invoice['datahoraemissao'];
        $invoice['idcliente'] = $invoice['identidade']; // Map for display
        $invoice['valoricms'] = $invoice['icms'];
        $invoice['valoripi'] = $invoice['ipi'];
        $invoice['valorst'] = $invoice['icmssubstituicao'];
        $invoice['valorpis'] = $invoice['pis'];
        $invoice['valorcofins'] = $invoice['cofins'];
        $invoice['valorfrete'] = $invoice['frete'];
        $invoice['valorseguro'] = $invoice['seguro'];
        $invoice['valordesconto'] = $invoice['descontosubtotal'];
        $invoice['valoroutrasdespesas'] = $invoice['outrasdespesas'];

        $itemsSql = "SELECT i.*, u.codigo as unidade_sigla 
                     FROM notafiscalitem i 
                     LEFT JOIN unidademedida u ON i.idunidademedida = u.id 
                     WHERE i.idnotafiscal = $1 ORDER BY i.id";
        $resItems = pg_query_params($dbconn, $itemsSql, array($invoice['id']));
        $itemsRaw = pg_fetch_all($resItems);
        
        // Map item columns
        $items = [];
        if ($itemsRaw) {
             // DEBUG: Check columns
             // echo "<pre>"; print_r(array_keys($itemsRaw[0])); echo "</pre>";



            foreach ($itemsRaw as $item) {
                $item['quant'] = number_format((float)$item['quantidade'], 4, '.', '');
                $item['valorunitario'] = number_format((float)$item['precounitario'], 2, '.', '');
                $item['aliquotaicms'] = number_format((float)$item['percentualicms'], 2, '.', '');
                $item['valoricms'] = number_format((float)$item['icms'], 2, '.', '');
                $item['basest'] = number_format((float)$item['baseicmssubstituicao'], 2, '.', '');
                $item['aliquotast'] = number_format((float)$item['percentualicmssubstituicao'], 2, '.', '');
                $item['valorst'] = number_format((float)$item['icmssubstituicao'], 2, '.', '');
                $item['valoripi'] = number_format((float)$item['ipi'], 2, '.', '');
                $item['valorpis'] = number_format((float)$item['pis'], 2, '.', '');
                $item['valorcofins'] = number_format((float)$item['cofins'], 2, '.', '');
                $item['baseicms'] = number_format((float)$item['baseicms'], 2, '.', '');
                
                // Map product details
                $item['xprod'] = $item['descricao'];
                $item['cprod'] = $item['produto'];
                $item['unidade'] = $item['unidade_sigla'] ?: $item['unidade']; // Fallback to text column
                
                $items[] = $item;
            }
        }
    }
}
?>

<?php if (!empty($generatedXml)): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var tab = new bootstrap.Tab(document.querySelector('#xmldps-tab'));
    tab.show();
});
</script>
<?php endif; ?>

</div> <!-- Close header container -->
<div class="container-fluid px-4 py-4 bg-light min-vh-100">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Editor de Nota Fiscal</h1>
            <p class="text-muted small mb-0">Correção manual de valores fiscais em notas de devolução.</p>
        </div>
        <a href="index.php" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-2"></i>Voltar
        </a>
    </div>

    <?php if (isset($message)): ?>
        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
            <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Tabs -->
    <ul class="nav nav-tabs mb-4" id="myTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link <?php echo !$isConnected ? 'active' : ''; ?>" id="connection-tab" data-bs-toggle="tab" data-bs-target="#connection" type="button" role="tab" aria-controls="connection" aria-selected="<?php echo !$isConnected ? 'true' : 'false'; ?>">
                <i class="bi bi-hdd-network me-2"></i>Conexão
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link <?php echo $isConnected ? 'active' : ''; ?>" id="editor-tab" data-bs-toggle="tab" data-bs-target="#editor" type="button" role="tab" aria-controls="editor" aria-selected="<?php echo $isConnected ? 'true' : 'false'; ?>" <?php echo !$isConnected ? 'disabled' : ''; ?>>
                <i class="bi bi-pencil-square me-2"></i>Editor
            </button>
        </li>
    </ul>

    <div class="tab-content" id="myTabContent">
        
        <!-- Connection Tab -->
        <div class="tab-pane fade <?php echo !$isConnected ? 'show active' : ''; ?>" id="connection" role="tabpanel" aria-labelledby="connection-tab">
            <div class="card shadow-sm" style="max-width: 600px;">
                <div class="card-body">
                    <h5 class="card-title mb-4">Configuração do Banco de Dados</h5>
                    
                    <?php if ($isConnected): ?>
                        <div class="alert alert-success d-flex align-items-center">
                            <i class="bi bi-check-circle-fill me-2 fs-4"></i>
                            <div>
                                <strong>Conectado!</strong><br>
                                Host: <?php echo htmlspecialchars($_SESSION['db_host']); ?><br>
                                Banco: <?php echo htmlspecialchars($_SESSION['db_name']); ?>
                            </div>
                        </div>
                        <form method="POST">
                            <input type="hidden" name="action" value="disconnect">
                            <button type="submit" class="btn btn-danger">
                                <i class="bi bi-power me-2"></i>Desconectar
                            </button>
                        </form>
                    <?php else: ?>
                        <form method="POST">
                            <input type="hidden" name="action" value="connect">
                            <div class="row g-3">
                                <div class="col-md-8">
                                    <label for="host" class="form-label">Host</label>
                                    <input type="text" class="form-control" id="host" name="host" value="<?php echo isset($_SESSION['db_host']) ? $_SESSION['db_host'] : 'localhost'; ?>" required>
                                </div>
                                <div class="col-md-4">
                                    <label for="port" class="form-label">Porta</label>
                                    <input type="text" class="form-control" id="port" name="port" value="<?php echo isset($_SESSION['db_port']) ? $_SESSION['db_port'] : '5432'; ?>" required>
                                </div>
                                <div class="col-md-12">
                                    <label for="dbname" class="form-label">Nome do Banco</label>
                                    <input type="text" class="form-control" id="dbname" name="dbname" value="<?php echo isset($_SESSION['db_name']) ? $_SESSION['db_name'] : 'unico'; ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="user" class="form-label">Usuário</label>
                                    <input type="text" class="form-control" id="user" name="user" value="<?php echo isset($_SESSION['db_user']) ? $_SESSION['db_user'] : 'postgres'; ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="password" class="form-label">Senha</label>
                                    <input type="password" class="form-control" id="password" name="password" value="<?php echo isset($_SESSION['db_pass']) ? $_SESSION['db_pass'] : 'postgres'; ?>" required>
                                </div>
                                <div class="col-12 mt-4">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="bi bi-plug me-2"></i>Conectar
                                    </button>
                                </div>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Editor Tab -->
        <div class="tab-pane fade <?php echo $isConnected ? 'show active' : ''; ?>" id="editor" role="tabpanel" aria-labelledby="editor-tab">
            <?php if (!$isConnected): ?>
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Por favor, configure a conexão com o banco de dados na aba "Conexão" primeiro.
                </div>
            <?php else: ?>
                <!-- Search Card -->
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <form method="POST" class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label for="search_term" class="form-label">ID ou Número da Nota</label>
                                <input type="text" class="form-control" id="search_term" name="search_term" placeholder="Ex: 12345" required value="<?php echo isset($term) ? htmlspecialchars($term) : ''; ?>">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-search me-2"></i>Buscar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <?php if ($invoice): ?>
                    <form method="POST" id="invoiceForm">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="invoice_id" value="<?php echo $invoice['id']; ?>">

                        <!-- Comprehensive Header Info -->
                        <div class="bg-white shadow-sm rounded p-3 mb-4">
                            <!-- Row 1: Main Identification -->
                            <div class="d-flex justify-content-between align-items-start mb-3 pb-3 border-bottom">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="bg-primary text-white rounded p-2 text-center" style="min-width: 60px;">
                                        <div class="small text-uppercase" style="font-size: 0.65rem;">Série</div>
                                        <div class="fw-bold"><?php echo $invoice['modelo'] ?: '-'; ?></div>
                                    </div>
                                    <div>
                                        <h4 class="mb-0 fw-bold text-dark">Nota #<?php echo $invoice['numeronotafiscal']; ?></h4>
                                        <div class="d-flex gap-2 mt-1">
                                            <span class="badge bg-light text-secondary border">ID: <?php echo $invoice['id']; ?></span>
                                            <span class="badge bg-light text-secondary border">Mod: <?php echo $invoice['serie'] ?: '-'; ?></span>
                                            <span class="badge bg-light text-secondary border">Finalidade: <?php echo $invoice['finalidadeemissaonfe'] ?: '-'; ?></span>
                                            <span class="badge bg-light text-secondary border">Op: <?php echo $invoice['idoperacaofiscal'] ?: '-'; ?></span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="text-end">
                                    <?php 
                                        $statusMap = [
                                            0 => ['label' => 'Normal', 'class' => 'bg-secondary'],
                                            1 => ['label' => 'Cancelada', 'class' => 'bg-danger'],
                                            2 => ['label' => 'Em edição', 'class' => 'bg-warning text-dark'],
                                            3 => ['label' => 'Autorizada', 'class' => 'bg-success'],
                                            4 => ['label' => 'Cancelada', 'class' => 'bg-danger'],
                                            5 => ['label' => 'NF Negada', 'class' => 'bg-danger'],
                                            6 => ['label' => 'NFS-e autorizada', 'class' => 'bg-success'],
                                            7 => ['label' => 'NFS-e rejeitada', 'class' => 'bg-danger'],
                                            8 => ['label' => 'NF-e emitida em contingência', 'class' => 'bg-warning text-dark'],
                                            9 => ['label' => 'Aguardando processamento', 'class' => 'bg-info text-dark'],
                                            10 => ['label' => 'Problemas no processamento', 'class' => 'bg-danger'],
                                            11 => ['label' => 'Problemas no envio do lote', 'class' => 'bg-danger'],
                                            12 => ['label' => 'Aguardando recibo', 'class' => 'bg-info text-dark'],
                                            13 => ['label' => 'Problemas ao consultar recibo', 'class' => 'bg-danger'],
                                            14 => ['label' => 'Aguardando retorno', 'class' => 'bg-info text-dark'],
                                            15 => ['label' => 'Conferencia pendente', 'class' => 'bg-warning text-dark'],
                                            16 => ['label' => 'NF-e emitida via contingencia EPEC', 'class' => 'bg-warning text-dark'],
                                            17 => ['label' => 'NF-e pendente de estorno', 'class' => 'bg-danger']
                                        ];

                                        $statusCode = (int)$invoice['codigostatusprotocolonfe'];
                                        if (!isset($statusMap[$statusCode]) && is_numeric($invoice['status'])) {
                                             $statusCode = (int)$invoice['status'];
                                        }
                                        
                                        $statusInfo = isset($statusMap[$statusCode]) ? $statusMap[$statusCode] : ['label' => $invoice['status'], 'class' => 'bg-secondary'];
                                        
                                        echo "<span class='badge {$statusInfo['class']} px-3 py-2 rounded-pill mb-2'>{$statusCode} - {$statusInfo['label']}</span>";
                                    ?>
                                    <div class="small text-muted">
                                        <i class="bi bi-key me-1"></i> <?php echo $invoice['chavenfe'] ?: 'Sem Chave de Acesso'; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Row 2: Dates & Parties -->
                            <div class="row g-3 mb-3 pb-3 border-bottom">
                                <div class="col-md-3">
                                    <label class="small text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Emissão</label>
                                    <div class="fw-bold"><?php echo date('d/m/Y H:i', strtotime($invoice['datahoraemissao'])); ?></div>
                                </div>
                                <div class="col-md-3">
                                    <label class="small text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Saída/Entrada</label>
                                    <div class="fw-bold"><?php echo $invoice['datahoraentradasaida'] ? date('d/m/Y H:i', strtotime($invoice['datahoraentradasaida'])) : '-'; ?></div>
                                </div>
                                <div class="col-md-6">
                                    <label class="small text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Destinatário / Remetente</label>
                                    <div class="d-flex justify-content-between">
                                        <div class="fw-bold text-truncate" title="<?php echo $invoice['razaosocial']; ?>"><?php echo $invoice['razaosocial'] ?: 'Consumidor Final'; ?></div>
                                        <div class="text-muted small ms-2"><?php echo $invoice['cnpjcpf']; ?></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Row 3: Values Summary -->
                            <div class="row g-2 align-items-center">
                                <div class="col-md-2">
                                    <div class="p-2 bg-light rounded">
                                        <div class="small text-muted" style="font-size: 0.7rem;">Produtos</div>
                                        <div class="fw-bold">R$ <?php echo number_format($invoice['totalproduto'], 2, ',', '.'); ?></div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="p-2 bg-light rounded">
                                        <div class="small text-muted" style="font-size: 0.7rem;">Frete</div>
                                        <div class="fw-bold">R$ <?php echo number_format($invoice['frete'], 2, ',', '.'); ?></div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="p-2 bg-light rounded">
                                        <div class="small text-muted" style="font-size: 0.7rem;">Seguro</div>
                                        <div class="fw-bold">R$ <?php echo number_format($invoice['seguro'], 2, ',', '.'); ?></div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="p-2 bg-light rounded">
                                        <div class="small text-muted" style="font-size: 0.7rem;">Desconto</div>
                                        <div class="fw-bold text-danger">- R$ <?php echo number_format($invoice['descontosubtotal'], 2, ',', '.'); ?></div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="p-2 bg-light rounded">
                                        <div class="small text-muted" style="font-size: 0.7rem;">Outras</div>
                                        <div class="fw-bold">R$ <?php echo number_format($invoice['outrasdespesas'], 2, ',', '.'); ?></div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="p-2 bg-primary text-white rounded text-end">
                                        <div class="small text-white-50 text-uppercase fw-bold" style="font-size: 0.7rem;">Total Nota</div>
                                        <div class="h5 mb-0 fw-bold" id="headerTotalDisplay">R$ <?php echo number_format($invoice['valortotalnota'], 2, ',', '.'); ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tabs Navigation -->
                        <form method="POST" action="">
                            <input type="hidden" name="action" value="update_invoice">
                            <input type="hidden" name="invoice_id" value="<?php echo $invoice['id']; ?>">

                            <ul class="nav nav-tabs mb-4" id="editorTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="itens-tab" data-bs-toggle="tab" data-bs-target="#itens" type="button" role="tab" aria-controls="itens" aria-selected="true">
                                        <i class="bi bi-list-check me-2"></i>Itens da Nota
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="totais-tab" data-bs-toggle="tab" data-bs-target="#totais" type="button" role="tab" aria-controls="totais" aria-selected="false">
                                        <i class="bi bi-calculator me-2"></i>Totais e Impostos
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="transporte-tab" data-bs-toggle="tab" data-bs-target="#transporte" type="button" role="tab" aria-controls="transporte" aria-selected="false">
                                        <i class="bi bi-truck me-2"></i>Transporte
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="entrega-tab" data-bs-toggle="tab" data-bs-target="#entrega" type="button" role="tab" aria-controls="entrega" aria-selected="false">
                                        <i class="bi bi-geo-alt me-2"></i>Entrega/Retirada
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="financeiro-tab" data-bs-toggle="tab" data-bs-target="#financeiro" type="button" role="tab" aria-controls="financeiro" aria-selected="false">
                                        <i class="bi bi-cash-coin me-2"></i>Financeiro
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="info-tab" data-bs-toggle="tab" data-bs-target="#info" type="button" role="tab" aria-controls="info" aria-selected="false">
                                        <i class="bi bi-info-circle me-2"></i>Info Adicional
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="espelho-tab" data-bs-toggle="tab" data-bs-target="#espelho" type="button" role="tab" aria-controls="espelho" aria-selected="false">
                                        <i class="bi bi-file-earmark-pdf me-2"></i>Comparar (PDF/XML)
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="sql-tab" data-bs-toggle="tab" data-bs-target="#sql" type="button" role="tab" aria-controls="sql" aria-selected="false">
                                        <i class="bi bi-code-slash me-2"></i>Gerador SQL
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="xmldps-tab" data-bs-toggle="tab" data-bs-target="#xmldps" type="button" role="tab" aria-controls="xmldps" aria-selected="false">
                                        <i class="bi bi-filetype-xml me-2"></i>XML DPS
                                    </button>
                                </li>
                            </ul>

                            <div class="tab-content" id="editorTabsContent">
                                
                                <!-- Tab: Itens -->
                                <div class="tab-pane fade show active" id="itens" role="tabpanel" aria-labelledby="itens-tab">
                                    <div class="card shadow-sm mb-4">
                                        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                                            <h5 class="mb-0">Itens da Nota</h5>
                                            <span class="badge bg-light text-dark border"><?php echo count($items); ?> itens</span>
                                        </div>
                                        <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                                            <table class="table table-bordered table-hover mb-0 align-middle" style="font-size: 0.85rem;">
                                                <thead class="table-light sticky-top" style="z-index: 1;">
                                                    <tr>
                                                        <th style="width: 40px;">#</th>
                                                        <th style="width: 50px;" class="text-center"><i class="bi bi-gear"></i></th>
                                                        <th style="width: 250px;">Produto</th>
                                                        <th style="width: 60px;">Und</th>
                                                        <th style="width: 60px;">CFOP</th>
                                                        <th style="width: 80px;">Qtd</th>
                                                        <th style="width: 100px;">Vl. Unit</th>
                                                        <th style="width: 100px;">Base ICMS</th>
                                                        <th style="width: 60px;">% ICMS</th>
                                                        <th style="width: 90px;">Vl. ICMS</th>
                                                        <th style="width: 90px;">Base ST</th>
                                                        <th style="width: 60px;">% ST</th>
                                                        <th style="width: 90px;">Vl. ST</th>
                                                        <th style="width: 90px;">Vl. IPI</th>
                                                        <th style="width: 90px;">Vl. PIS</th>
                                                        <th style="width: 90px;">Vl. COFINS</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if ($items): foreach ($items as $idx => $item): ?>
                                                        <tr data-id="<?php echo $item['id']; ?>">
                                                            <td class="text-center text-muted"><?php echo $idx + 1; ?></td>
                                                            <td class="text-center">
                                                                <button type="button" class="btn btn-sm btn-outline-primary btn-edit-item" 
                                                                    data-bs-toggle="modal" 
                                                                    data-bs-target="#itemModal"
                                                                    data-item='<?php echo json_encode($item, JSON_HEX_APOS | JSON_HEX_QUOT); ?>'>
                                                                    <i class="bi bi-pencil"></i>
                                                                </button>
                                                                <!-- Hidden inputs for full edit -->
                                                                <input type="hidden" name="items[<?php echo $item['id']; ?>][ncm]" value="<?php echo $item['ncm'] ?? ''; ?>">
                                                                <input type="hidden" name="items[<?php echo $item['id']; ?>][cest]" value="<?php echo $item['cest'] ?? ''; ?>">
                                                                <input type="hidden" name="items[<?php echo $item['id']; ?>][cst_icms]" value="<?php echo $item['csticms'] ?? ''; ?>">
                                                                <input type="hidden" name="items[<?php echo $item['id']; ?>][origem]" value="<?php echo $item['origem'] ?? ''; ?>">
                                                                <input type="hidden" name="items[<?php echo $item['id']; ?>][modbc]" value="<?php echo $item['modbc'] ?? ''; ?>">
                                                                <input type="hidden" name="items[<?php echo $item['id']; ?>][predbc]" value="<?php echo $item['predbc'] ?? ''; ?>">
                                                                <input type="hidden" name="items[<?php echo $item['id']; ?>][modbcst]" value="<?php echo $item['modbcst'] ?? ''; ?>">
                                                                <input type="hidden" name="items[<?php echo $item['id']; ?>][pmvast]" value="<?php echo $item['pmvast'] ?? ''; ?>">
                                                                <input type="hidden" name="items[<?php echo $item['id']; ?>][predbcst]" value="<?php echo $item['predbcst'] ?? ''; ?>">
                                                                <input type="hidden" name="items[<?php echo $item['id']; ?>][cst_ipi]" value="<?php echo $item['cstipi'] ?? ''; ?>">
                                                                <input type="hidden" name="items[<?php echo $item['id']; ?>][cenq]" value="<?php echo $item['cenq'] ?? ''; ?>">
                                                                <input type="hidden" name="items[<?php echo $item['id']; ?>][cst_pis]" value="<?php echo $item['cstpis'] ?? ''; ?>">
                                                                <input type="hidden" name="items[<?php echo $item['id']; ?>][cst_cofins]" value="<?php echo $item['cstcofins'] ?? ''; ?>">
                                                                <input type="hidden" name="items[<?php echo $item['id']; ?>][info_adicional]" value="<?php echo htmlspecialchars($item['informacaoadicional'] ?? ''); ?>">
                                                                <input type="hidden" name="items[<?php echo $item['id']; ?>][idunidademedida]" value="<?php echo $item['idunidademedida'] ?? ''; ?>">
                                                                <input type="hidden" name="items[<?php echo $item['id']; ?>][desconto]" value="<?php echo $item['desconto'] ?? ''; ?>">
                                                                <input type="hidden" name="items[<?php echo $item['id']; ?>][percentualdesconto]" value="<?php echo $item['percentualdesconto'] ?? ''; ?>">
                                                                <input type="hidden" name="items[<?php echo $item['id']; ?>][valortotal]" value="<?php echo $item['total'] ?? ''; ?>">
                                                            </td>
                                                            <td>
                                                                <div class="fw-bold" title="<?php echo htmlspecialchars($item['xprod']); ?>">
                                                                    <?php echo htmlspecialchars($item['xprod']); ?>
                                                                </div>
                                                                <div class="small text-muted">Cód: <?php echo $item['cprod']; ?></div>
                                                            </td>
                                                            <td class="text-center small"><?php echo $item['unidade']; ?></td>
                                                            <td><input type="text" class="form-control form-control-sm" name="items[<?php echo $item['id']; ?>][cfop]" value="<?php echo $item['cfop']; ?>"></td>
                                                            <td>
                                                                <input type="number" step="0.0001" class="form-control form-control-sm calc-trigger text-end" name="items[<?php echo $item['id']; ?>][quant]" value="<?php echo $item['quant']; ?>">
                                                            </td>
                                                            <td>
                                                                <input type="number" step="0.01" class="form-control form-control-sm calc-trigger text-end" name="items[<?php echo $item['id']; ?>][valorunitario]" value="<?php echo $item['valorunitario']; ?>">
                                                            </td>
                                                            
                                                            <!-- ICMS -->
                                                            <td><input type="number" step="0.01" class="form-control form-control-sm calc-trigger text-end bg-light" name="items[<?php echo $item['id']; ?>][baseicms]" value="<?php echo $item['baseicms']; ?>" readonly></td>
                                                            <td><input type="number" step="0.01" class="form-control form-control-sm calc-trigger text-end" name="items[<?php echo $item['id']; ?>][aliquotaicms]" value="<?php echo $item['aliquotaicms']; ?>"></td>
                                                            <td><input type="number" step="0.01" class="form-control form-control-sm calc-trigger text-end" name="items[<?php echo $item['id']; ?>][valoricms]" value="<?php echo $item['valoricms']; ?>"></td>
                                                            
                                                            <!-- ICMS ST -->
                                                            <td><input type="number" step="0.01" class="form-control form-control-sm calc-trigger text-end" name="items[<?php echo $item['id']; ?>][basest]" value="<?php echo $item['basest']; ?>"></td>
                                                            <td><input type="number" step="0.01" class="form-control form-control-sm calc-trigger text-end" name="items[<?php echo $item['id']; ?>][aliquotast]" value="<?php echo $item['aliquotast']; ?>"></td>
                                                            <td><input type="number" step="0.01" class="form-control form-control-sm calc-trigger text-end" name="items[<?php echo $item['id']; ?>][valorst]" value="<?php echo $item['valorst']; ?>"></td>
                                                            
                                                            <!-- IPI/PIS/COFINS -->
                                                            <td><input type="number" step="0.01" class="form-control form-control-sm calc-trigger text-end" name="items[<?php echo $item['id']; ?>][valoripi]" value="<?php echo $item['valoripi']; ?>"></td>
                                                            <td><input type="number" step="0.01" class="form-control form-control-sm calc-trigger text-end" name="items[<?php echo $item['id']; ?>][valorpis]" value="<?php echo $item['valorpis']; ?>"></td>
                                                            <td><input type="number" step="0.01" class="form-control form-control-sm calc-trigger text-end" name="items[<?php echo $item['id']; ?>][valorcofins]" value="<?php echo $item['valorcofins']; ?>"></td>
                                                        </tr>
                                                    <?php endforeach; endif; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <!-- Tab: Totais -->
                                <div class="tab-pane fade" id="totais" role="tabpanel" aria-labelledby="totais-tab">
                                    <div class="card shadow-sm mb-4">
                                        <div class="card-header bg-white py-3">
                                            <h5 class="mb-0">Totais e Impostos (Cabeçalho)</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="row g-3">
                                                <div class="col-md-2">
                                                    <label class="form-label small fw-bold">Total Produtos</label>
                                                    <input type="number" step="0.01" class="form-control bg-light" id="totalproduto" name="totalproduto" value="<?php echo $invoice['totalproduto']; ?>" readonly>
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label small fw-bold">Total ICMS</label>
                                                    <input type="number" step="0.01" class="form-control" id="valoricms" name="valoricms" value="<?php echo $invoice['valoricms']; ?>">
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label small fw-bold">Total IPI</label>
                                                    <input type="number" step="0.01" class="form-control" id="valoripi" name="valoripi" value="<?php echo $invoice['valoripi']; ?>">
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label small fw-bold">Total ST</label>
                                                    <input type="number" step="0.01" class="form-control" id="valorst" name="valorst" value="<?php echo $invoice['valorst']; ?>">
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label small fw-bold">Total PIS</label>
                                                    <input type="number" step="0.01" class="form-control" id="valorpis" name="valorpis" value="<?php echo $invoice['valorpis']; ?>">
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label small fw-bold">Total COFINS</label>
                                                    <input type="number" step="0.01" class="form-control" id="valorcofins" name="valorcofins" value="<?php echo $invoice['valorcofins']; ?>">
                                                </div>
                                                
                                                <div class="col-12"><hr class="my-2"></div>
                                                
                                                <div class="col-md-2">
                                                    <label class="form-label small fw-bold">Frete</label>
                                                    <input type="number" step="0.01" class="form-control calc-trigger" id="valorfrete" name="valorfrete" value="<?php echo $invoice['valorfrete']; ?>">
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label small fw-bold">Seguro</label>
                                                    <input type="number" step="0.01" class="form-control calc-trigger" id="valorseguro" name="valorseguro" value="<?php echo $invoice['valorseguro']; ?>">
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label small fw-bold">Desconto</label>
                                                    <input type="number" step="0.01" class="form-control calc-trigger" id="valordesconto" name="valordesconto" value="<?php echo $invoice['valordesconto']; ?>">
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label small fw-bold">Outras Desp.</label>
                                                    <input type="number" step="0.01" class="form-control calc-trigger" id="valoroutrasdespesas" name="valoroutrasdespesas" value="<?php echo $invoice['valoroutrasdespesas']; ?>">
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label small fw-bold text-primary">Total da Nota (Calculado)</label>
                                                    <input type="number" step="0.01" class="form-control border-primary fw-bold text-primary" id="valortotalnota" name="valortotalnota" value="<?php echo $invoice['valortotalnota']; ?>" readonly>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Tab: Transporte -->
                                <div class="tab-pane fade" id="transporte" role="tabpanel" aria-labelledby="transporte-tab">
                                    <div class="card shadow-sm mb-4">
                                        <div class="card-header bg-white py-3">
                                            <h5 class="mb-0">Dados de Transporte</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="row g-3">
                                                <div class="col-md-3">
                                                    <label class="form-label small fw-bold">ID Transportadora</label>
                                                    <input type="text" class="form-control" name="idtransportadora" value="<?php echo $invoice['idtransportadora']; ?>">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label small fw-bold">Placa Veículo</label>
                                                    <input type="text" class="form-control" name="placaveiculo" value="<?php echo $invoice['placaveiculo']; ?>">
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label small fw-bold">UF Veículo</label>
                                                    <input type="text" class="form-control" name="estadoveiculo" value="<?php echo $invoice['estadoveiculo']; ?>">
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label small fw-bold">Espécie</label>
                                                    <input type="text" class="form-control" name="especie" value="<?php echo $invoice['especie']; ?>">
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label small fw-bold">Marca</label>
                                                    <input type="text" class="form-control" name="marca" value="<?php echo $invoice['marca']; ?>">
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label small fw-bold">Peso Bruto</label>
                                                    <input type="number" step="0.001" class="form-control" name="pesobruto" value="<?php echo $invoice['pesobruto']; ?>">
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label small fw-bold">Peso Líquido</label>
                                                    <input type="number" step="0.001" class="form-control" name="pesoliquido" value="<?php echo $invoice['pesoliquido']; ?>">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Tab: Entrega -->
                                <div class="tab-pane fade" id="entrega" role="tabpanel" aria-labelledby="entrega-tab">
                                    <div class="card shadow-sm mb-4">
                                        <div class="card-header bg-white py-3">
                                            <h5 class="mb-0">Dados de Entrega</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label class="form-label small fw-bold">Razão Social / Nome</label>
                                                    <input type="text" class="form-control" name="razaosocialnomeentrega" value="<?php echo $invoice['razaosocialnomeentrega']; ?>">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label small fw-bold">CNPJ / CPF</label>
                                                    <input type="text" class="form-control" name="cnpjcpfentrega" value="<?php echo $invoice['cnpjcpfentrega']; ?>">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label small fw-bold">Logradouro</label>
                                                    <input type="text" class="form-control" name="enderecoentrega" value="<?php echo $invoice['enderecoentrega']; ?>">
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label small fw-bold">Número</label>
                                                    <input type="text" class="form-control" name="numeroenderecoentrega" value="<?php echo $invoice['numeroenderecoentrega']; ?>">
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label small fw-bold">Bairro</label>
                                                    <input type="text" class="form-control" name="bairroentrega" value="<?php echo $invoice['bairroentrega']; ?>">
                                                </div>
                                                <div class="col-md-5">
                                                    <label class="form-label small fw-bold">ID Cidade</label>
                                                    <input type="text" class="form-control" name="idcidadeentrega" value="<?php echo $invoice['idcidadeentrega']; ?>">
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label small fw-bold">ID UF</label>
                                                    <input type="text" class="form-control" name="idestadoentrega" value="<?php echo $invoice['idestadoentrega']; ?>">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label small fw-bold">CEP</label>
                                                    <input type="text" class="form-control" name="cepentrega" value="<?php echo $invoice['cepentrega']; ?>">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Tab: Financeiro -->
                                <div class="tab-pane fade" id="financeiro" role="tabpanel" aria-labelledby="financeiro-tab">
                                    <div class="card shadow-sm mb-4">
                                        <div class="card-header bg-white py-3">
                                            <h5 class="mb-0">Dados Financeiros</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="row g-3">
                                                <div class="col-md-4">
                                                    <label class="form-label small fw-bold">ID Condição Pagto</label>
                                                    <input type="text" class="form-control" name="idcondicaopagto" value="<?php echo $invoice['idcondicaopagto']; ?>">
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label small fw-bold">À Vista</label>
                                                    <input type="number" step="0.01" class="form-control" name="avista" value="<?php echo $invoice['avista']; ?>">
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label small fw-bold">A Prazo</label>
                                                    <input type="number" step="0.01" class="form-control" name="aprazo" value="<?php echo $invoice['aprazo']; ?>">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label small fw-bold">PIX</label>
                                                    <input type="number" step="0.01" class="form-control" name="pix" value="<?php echo $invoice['pix']; ?>">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label small fw-bold">Cheque</label>
                                                    <input type="number" step="0.01" class="form-control" name="cheque" value="<?php echo $invoice['cheque']; ?>">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label small fw-bold">Financiado</label>
                                                    <input type="number" step="0.01" class="form-control" name="financiado" value="<?php echo $invoice['financiado']; ?>">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label small fw-bold">Carteira Digital</label>
                                                    <input type="number" step="0.01" class="form-control" name="carteiradigital" value="<?php echo $invoice['carteiradigital']; ?>">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label small fw-bold">Outros</label>
                                                    <input type="number" step="0.01" class="form-control" name="outros" value="<?php echo $invoice['outros']; ?>">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Tab: Info Adicional -->
                                <div class="tab-pane fade" id="info" role="tabpanel" aria-labelledby="info-tab">
                                    <div class="card shadow-sm mb-4">
                                        <div class="card-header bg-white py-3">
                                            <h5 class="mb-0">Informações Adicionais</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label class="form-label small fw-bold">Informações Complementares (Personalizadas)</label>
                                                <textarea class="form-control" name="informacoescomplementarespersonalizadas" rows="4"><?php echo htmlspecialchars($invoice['informacoescomplementarespersonalizadas']); ?></textarea>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label small fw-bold">Informações Fisco</label>
                                                <textarea class="form-control" name="infofisco" rows="4"><?php echo htmlspecialchars($invoice['infofisco']); ?></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Tab: Gerador SQL -->
                                <div class="tab-pane fade" id="sql" role="tabpanel" aria-labelledby="sql-tab">
                                    <div class="card shadow-sm mb-4">
                                        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                                            <h5 class="mb-0">Gerador de SQL</h5>
                                            <button type="button" class="btn btn-primary" id="btnGenerateSQL">
                                                <i class="bi bi-lightning-charge me-2"></i>Gerar SQL
                                            </button>
                                        </div>
                                        <div class="card-body">
                                            <div class="alert alert-info">
                                                <i class="bi bi-info-circle me-2"></i>
                                                Gera os comandos SQL UPDATE com base nos valores atuais do formulário. Útil para correções manuais diretas no banco.
                                            </div>
                                            <textarea class="form-control font-monospace" id="sqlOutput" rows="15" readonly style="background-color: #f8f9fa;"></textarea>
                                            <div class="mt-3 text-end">
                                                <button type="button" class="btn btn-secondary" onclick="navigator.clipboard.writeText(document.getElementById('sqlOutput').value)">
                                                    <i class="bi bi-clipboard me-2"></i>Copiar SQL
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Tab: Espelho PDF/XML -->
                                <div class="tab-pane fade" id="espelho" role="tabpanel" aria-labelledby="espelho-tab">
                                    <div class="card shadow-sm mb-4">
                                        <div class="card-header bg-white py-3">
                                            <h5 class="mb-0">Comparar com Espelho (PDF) ou XML</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-12 mb-4">
                                                    <label for="fileUpload" class="form-label">Carregar Arquivo (PDF ou XML)</label>
                                                    <div class="input-group">
                                                        <input class="form-control" type="file" id="fileUpload" accept=".pdf, .xml">
                                                        <button class="btn btn-primary" type="button" id="btnProcessFile">
                                                            <i class="bi bi-magic me-2"></i>Ler e Comparar
                                                        </button>
                                                    </div>
                                                    <div class="form-text">Carregue o PDF do espelho ou o XML da nota para comparar os valores.</div>
                                                </div>
                                                
                                                <div class="col-md-12 d-none" id="comparisonContainer">
                                                    <h6 class="mb-3">Comparativo de Valores</h6>
                                                    <div class="table-responsive">
                                                        <table class="table table-bordered table-hover align-middle">
                                                            <thead class="table-light">
                                                                <tr>
                                                                    <th>Campo</th>
                                                                    <th class="text-end">Valor Atual (Sistema)</th>
                                                                    <th class="text-end">Valor Importado (Arquivo)</th>
                                                                    <th class="text-center" style="width: 100px;">Ação</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody id="comparisonTableBody">
                                                                <!-- Rows will be populated by JS -->
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                </div>
                                </div>

                                <!-- Tab: XML DPS -->
                                <div class="tab-pane fade" id="xmldps" role="tabpanel" aria-labelledby="xmldps-tab">
                                    <div class="card shadow-sm mb-4">
                                        <div class="card-header bg-white py-3">
                                            <h5 class="mb-0">Gerador XML DPS (NFS-e Nacional)</h5>
                                        </div>
                                        <div class="card-body">
                                            <p class="text-muted">Gere o XML do DPS para integração com o sistema nacional NFS-e.</p>
                                            <div class="d-grid gap-2 d-md-block mb-3">
                                                 <button type="submit" class="btn btn-primary" onclick="this.form.querySelector('input[name=action]').value = 'generate_xml_dps';">
                                                     <i class="bi bi-filetype-xml me-2"></i>Gerar XML DPS
                                                 </button>
                                            </div>
                                            
                                            <?php if (!empty($generatedXml)): ?>
                                                <div class="alert alert-success mt-3">
                                                    <i class="bi bi-check-circle me-2"></i>XML Gerado com sucesso!
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label font-monospace small">Conteúdo XML:</label>
                                                    <textarea class="form-control font-monospace bg-light" rows="15" readonly onclick="this.select()"><?php echo htmlspecialchars($generatedXml); ?></textarea>
                                                </div>
                                                 <div class="text-end">
                                                    <button type="button" class="btn btn-secondary btn-sm" onclick="navigator.clipboard.writeText(this.parentElement.previousElementSibling.querySelector('textarea').value); showToast('Copiado!', 'success');">
                                                        <i class="bi bi-clipboard me-2"></i>Copiar
                                                    </button>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                </div>
                            </div>
                            
                            <div class="card-footer bg-white py-3">
                                <div class="d-flex justify-content-end gap-2">
                                    <button type="button" class="btn btn-secondary" onclick="window.location.reload()">
                                        <i class="bi bi-arrow-clockwise me-2"></i>Atualizar Dados
                                    </button>
                                    <button type="submit" class="btn btn-success" id="btnApplyCorrection">
                                        <i class="bi bi-check-circle me-2"></i>Aplicar Correção
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // SweetAlert2 Feedback for Form Submission
    const form = document.querySelector('form[action=""]');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault(); // Prevent default submission to show confirmation
            
            Swal.fire({
                title: 'Confirmar Atualização?',
                text: "Essa ação atualizará os valores fiscais no banco de dados. Não pode ser desfeita.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#198754',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sim, atualizar!',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading state
                    Swal.fire({
                        title: 'Atualizando...',
                        text: 'Por favor, aguarde.',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    
                    // Submit the form programmatically
                    // We need to append the action input because submit() doesn't include the submit button's value
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'action';
                    input.value = 'update';
                    form.appendChild(input);
                    
                    form.submit();
                }
            });
        });
    }

    // Check for PHP messages and show SweetAlert
    <?php if (isset($message)): ?>
        Swal.fire({
            icon: '<?php echo $messageType == "success" ? "success" : ($messageType == "danger" ? "error" : "info"); ?>',
            title: '<?php echo $messageType == "success" ? "Sucesso!" : "Atenção"; ?>',
            text: '<?php echo addslashes($message); ?>',
            confirmButtonColor: '#0d6efd'
        });
    <?php endif; ?>

    // Set worker source
    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

    const itemInputs = document.querySelectorAll('.calc-trigger');
    const headerInputs = document.querySelectorAll('.calc-trigger-header');
    
    // Elements mapping for easy access
    const fieldsMap = {
        'valortotalnota': document.getElementById('valortotalnota'),
        'totalproduto': document.getElementById('totalproduto'),
        'valoricms': document.getElementById('valoricms'),
        'valoripi': document.getElementById('valoripi'),
        'valorst': document.getElementById('valorst'),
        'valorpis': document.getElementById('valorpis'),
        'valorcofins': document.getElementById('valorcofins'),
        'valorfrete': document.getElementById('valorfrete'),
        'valorseguro': document.getElementById('valorseguro'),
        'valordesconto': document.getElementById('valordesconto'),
        'valoroutrasdespesas': document.getElementById('valoroutrasdespesas')
    };

    function formatCurrency(value) {
        return value.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
    }
    
    function parseCurrency(str) {
        if (!str) return 0;
        if (typeof str === 'number') return str;
        // Remove dots (thousands separator) and replace comma with dot (decimal separator)
        // Example: 1.234,56 -> 1234.56
        return parseFloat(str.replace(/\./g, '').replace(',', '.')) || 0;
    }

    // Comparison Logic
    const btnProcessFile = document.getElementById('btnProcessFile');
    if (btnProcessFile) {
        btnProcessFile.addEventListener('click', async () => {
            const fileInput = document.getElementById('fileUpload');
            const container = document.getElementById('comparisonContainer');
            const tableBody = document.getElementById('comparisonTableBody');
            
            if (!fileInput.files[0]) {
                alert('Por favor, selecione um arquivo (PDF ou XML).');
                return;
            }

            const file = fileInput.files[0];
            const fileType = file.name.split('.').pop().toLowerCase();
            
            let extractedData = {};

            try {
                if (fileType === 'pdf') {
                    extractedData = await processPDF(file);
                } else if (fileType === 'xml') {
                    extractedData = await processXML(file);
                } else {
                    alert('Formato de arquivo não suportado. Use PDF ou XML.');
                    return;
                }
                
                renderComparisonTable(extractedData, tableBody, container);

            } catch (error) {
                console.error(error);
                alert('Erro ao processar arquivo: ' + error.message);
            }
        });
    }

    async function processPDF(file) {
        const arrayBuffer = await file.arrayBuffer();
        const pdf = await pdfjsLib.getDocument(new Uint8Array(arrayBuffer)).promise;
        let fullText = '';

        for (let i = 1; i <= pdf.numPages; i++) {
            const page = await pdf.getPage(i);
            const textContent = await page.getTextContent();
            const pageText = textContent.items.map(item => item.str).join(' ');
            fullText += pageText + ' ';
        }

        fullText = fullText.replace(/\s+/g, ' ');
        console.log("PDF Text:", fullText);

        const patterns = {
            'valortotalnota': /VALOR TOTAL DA NOTA\s*([\d\.,]+)/i,
            'totalproduto': /TOTAL DOS PRODUTOS\s*([\d\.,]+)/i,
            'valoricms': /VALOR DO ICMS\s*([\d\.,]+)/i,
            'valoripi': /VALOR DO IPI\s*([\d\.,]+)/i,
            'valorst': /VALOR DO ICMS ST\s*([\d\.,]+)/i,
            'valorpis': /VALOR DO PIS\s*([\d\.,]+)/i,
            'valorcofins': /VALOR DA COFINS\s*([\d\.,]+)/i,
            'valorfrete': /VALOR DO FRETE\s*([\d\.,]+)/i,
            'valorseguro': /VALOR DO SEGURO\s*([\d\.,]+)/i,
            'valordesconto': /DESCONTO\s*([\d\.,]+)/i,
            'valoroutrasdespesas': /OUTRAS DESPESAS\s*([\d\.,]+)/i
        };

        let data = {};
        for (const [key, regex] of Object.entries(patterns)) {
            const match = fullText.match(regex);
            if (match && match[1]) {
                data[key] = parseCurrency(match[1]);
            }
        }
        return data;
    }

    async function processXML(file) {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.onload = (e) => {
                try {
                    const parser = new DOMParser();
                    const xmlDoc = parser.parseFromString(e.target.result, "text/xml");
                    
                    // Helper to get value safely
                    const getVal = (tag, parent = xmlDoc) => {
                        const el = parent.getElementsByTagName(tag)[0];
                        return el ? parseFloat(el.textContent) : 0;
                    };

                    // NFe structure usually has ICMSTot under total
                    const total = xmlDoc.getElementsByTagName('total')[0];
                    const icmsTot = total ? total.getElementsByTagName('ICMSTot')[0] : null;

                    if (!icmsTot) {
                        throw new Error("Estrutura XML inválida ou não é uma NFe padrão.");
                    }

                    const data = {
                        'valortotalnota': getVal('vNF', icmsTot),
                        'totalproduto': getVal('vProd', icmsTot),
                        'valoricms': getVal('vICMS', icmsTot),
                        'valoripi': getVal('vIPI', icmsTot),
                        'valorst': getVal('vST', icmsTot),
                        'valorpis': getVal('vPIS', icmsTot),
                        'valorcofins': getVal('vCOFINS', icmsTot),
                        'valorfrete': getVal('vFrete', icmsTot),
                        'valorseguro': getVal('vSeg', icmsTot),
                        'valordesconto': getVal('vDesc', icmsTot),
                        'valoroutrasdespesas': getVal('vOutro', icmsTot)
                    };
                    resolve(data);
                } catch (err) {
                    reject(err);
                }
            };
            reader.readAsText(file);
        });
    }

    function renderComparisonTable(importedData, tableBody, container) {
        tableBody.innerHTML = '';
        
        const labels = {
            'valortotalnota': 'Total da Nota',
            'totalproduto': 'Total Produtos',
            'valoricms': 'Total ICMS',
            'valoripi': 'Total IPI',
            'valorst': 'Total ST',
            'valorpis': 'Total PIS',
            'valorcofins': 'Total COFINS',
            'valorfrete': 'Frete',
            'valorseguro': 'Seguro',
            'valordesconto': 'Desconto',
            'valoroutrasdespesas': 'Outras Despesas'
        };

        let hasData = false;

        for (const [key, label] of Object.entries(labels)) {
            const importedVal = importedData[key];
            
            // Only show rows where we found data in the imported file
            if (importedVal !== undefined) {
                hasData = true;
                const currentInput = fieldsMap[key];
                const currentVal = currentInput ? parseFloat(currentInput.value) || 0 : 0;
                
                const diff = Math.abs(currentVal - importedVal);
                const isDifferent = diff > 0.01; // Tolerance for float precision
                
                const row = document.createElement('tr');
                if (isDifferent) row.classList.add('table-warning');

                row.innerHTML = `
                    <td><strong>${label}</strong></td>
                    <td class="text-end ${isDifferent ? 'text-danger fw-bold' : 'text-success'}">
                        R$ ${currentVal.toLocaleString('pt-BR', {minimumFractionDigits: 2})}
                    </td>
                    <td class="text-end fw-bold">
                        R$ ${importedVal.toLocaleString('pt-BR', {minimumFractionDigits: 2})}
                    </td>
                    <td class="text-center">
                        ${isDifferent ? `
                        <button type="button" class="btn btn-sm btn-outline-success apply-val" data-target="${key}" data-value="${importedVal}">
                            <i class="bi bi-check"></i> Usar
                        </button>` : '<i class="bi bi-check-circle-fill text-success"></i>'}
                    </td>
                `;
                tableBody.appendChild(row);
            }
        }

        if (!hasData) {
            tableBody.innerHTML = '<tr><td colspan="4" class="text-center text-muted">Nenhum valor identificável encontrado no arquivo.</td></tr>';
        }

        container.classList.remove('d-none');

        // Attach listeners to new buttons
        document.querySelectorAll('.apply-val').forEach(btn => {
            btn.addEventListener('click', function() {
                const targetId = this.getAttribute('data-target');
                const val = parseFloat(this.getAttribute('data-value'));
                
                if (targetId && fieldsMap[targetId]) {
                    fieldsMap[targetId].value = val.toFixed(2);
                    
                    // Trigger recalculation if it's a field that affects totals
                    recalculate(); 
                    
                    // Visual feedback
                    this.closest('tr').classList.remove('table-warning');
                    this.closest('tr').classList.add('table-success');
                    this.innerHTML = '<i class="bi bi-check"></i> Feito';
                    this.disabled = true;
                    
                    // Update the "Current Value" cell
                    this.closest('tr').querySelector('td:nth-child(2)').textContent = `R$ ${val.toLocaleString('pt-BR', {minimumFractionDigits: 2})}`;
                    this.closest('tr').querySelector('td:nth-child(2)').classList.remove('text-danger');
                    this.closest('tr').querySelector('td:nth-child(2)').classList.add('text-success');
                }
            });
        });
    }

    function recalculate() {
        let totalProd = 0;
        let totalICMS = 0;
        let totalIPI = 0;
        let totalST = 0;
        let totalPIS = 0;
        let totalCOFINS = 0;

        // Sum Items
        document.querySelectorAll('tbody tr').forEach(row => {
            const qty = parseFloat(row.querySelector('input[name*="[quant]"]').value) || 0;
            const unit = parseFloat(row.querySelector('input[name*="[valorunitario]"]').value) || 0;
            
            const icms = parseFloat(row.querySelector('input[name*="[valoricms]"]').value) || 0;
            const ipi = parseFloat(row.querySelector('input[name*="[valoripi]"]').value) || 0;
            const st = parseFloat(row.querySelector('input[name*="[valorst]"]').value) || 0;
            const pis = parseFloat(row.querySelector('input[name*="[valorpis]"]').value) || 0;
            const cofins = parseFloat(row.querySelector('input[name*="[valorcofins]"]').value) || 0;

            totalProd += (qty * unit);
            totalICMS += icms;
            totalIPI += ipi;
            totalST += st;
            totalPIS += pis;
            totalCOFINS += cofins;
        });

        // Update Header Inputs from Item Sums
        if(fieldsMap['totalproduto']) fieldsMap['totalproduto'].value = totalProd.toFixed(2);
        if(fieldsMap['valoricms']) fieldsMap['valoricms'].value = totalICMS.toFixed(2);
        if(fieldsMap['valoripi']) fieldsMap['valoripi'].value = totalIPI.toFixed(2);
        if(fieldsMap['valorst']) fieldsMap['valorst'].value = totalST.toFixed(2);
        if(fieldsMap['valorpis']) fieldsMap['valorpis'].value = totalPIS.toFixed(2);
        if(fieldsMap['valorcofins']) fieldsMap['valorcofins'].value = totalCOFINS.toFixed(2);

        // Get Header Values for Final Calculation
        const frete = parseFloat(fieldsMap['valorfrete']?.value) || 0;
        const seguro = parseFloat(fieldsMap['valorseguro']?.value) || 0;
        const outras = parseFloat(fieldsMap['valoroutrasdespesas']?.value) || 0;
        const desconto = parseFloat(fieldsMap['valordesconto']?.value) || 0;

        // Update Total Note
        const totalNota = totalProd + totalIPI + totalST + frete + seguro + outras - desconto;
        if (fieldsMap['valortotalnota']) fieldsMap['valortotalnota'].value = totalNota.toFixed(2);
        
        // Update Display
        const displayEl = document.getElementById('headerTotalDisplay');
        if (displayEl) {
            displayEl.textContent = 'R$ ' + totalNota.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }
    }

    // Attach listeners
    itemInputs.forEach(input => input.addEventListener('input', recalculate));
    headerInputs.forEach(input => input.addEventListener('input', recalculate));

    // Item Edit Modal Logic
    const itemModal = document.getElementById('itemModal');
    if (itemModal) {
        const modalBody = itemModal.querySelector('.modal-body');
        
        // Calculation Logic
        const calculateTotal = () => {
            const qty = parseFloat(modalBody.querySelector('#modal_quant').value) || 0;
            const unit = parseFloat(modalBody.querySelector('#modal_valorunitario').value) || 0;
            const discountVal = parseFloat(modalBody.querySelector('#modal_desconto').value) || 0;
            
            const total = (qty * unit) - discountVal;
            modalBody.querySelector('#modal_valortotal').value = total.toFixed(2);
        };

        const updateDiscountFromPercent = () => {
            const qty = parseFloat(modalBody.querySelector('#modal_quant').value) || 0;
            const unit = parseFloat(modalBody.querySelector('#modal_valorunitario').value) || 0;
            const percent = parseFloat(modalBody.querySelector('#modal_percentualdesconto').value) || 0;
            
            const totalGross = qty * unit;
            const discountVal = totalGross * (percent / 100);
            
            modalBody.querySelector('#modal_desconto').value = discountVal.toFixed(2);
            calculateTotal();
        };

        const updatePercentFromDiscount = () => {
            const qty = parseFloat(modalBody.querySelector('#modal_quant').value) || 0;
            const unit = parseFloat(modalBody.querySelector('#modal_valorunitario').value) || 0;
            const discountVal = parseFloat(modalBody.querySelector('#modal_desconto').value) || 0;
            
            const totalGross = qty * unit;
            let percent = 0;
            if (totalGross > 0) {
                percent = (discountVal / totalGross) * 100;
            }
            
            modalBody.querySelector('#modal_percentualdesconto').value = percent.toFixed(2);
            calculateTotal();
        };

        // Attach listeners to modal inputs
        modalBody.querySelector('#modal_quant').addEventListener('input', calculateTotal);
        modalBody.querySelector('#modal_valorunitario').addEventListener('input', calculateTotal);
        modalBody.querySelector('#modal_desconto').addEventListener('input', updatePercentFromDiscount);
        modalBody.querySelector('#modal_percentualdesconto').addEventListener('input', updateDiscountFromPercent);

        itemModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const itemData = JSON.parse(button.getAttribute('data-item'));
            
            // Set Title Code
            document.getElementById('modalTitleCode').textContent = itemData.cprod || '';

            // Helper to set value safely
            const setVal = (id, val) => {
                const el = modalBody.querySelector(`#modal_${id}`);
                if (el) el.value = val !== null && val !== undefined ? val : '';
            };

            // General
            setVal('id', itemData.id);
            setVal('xprod', itemData.xprod);
            setVal('cprod', itemData.cprod);
            setVal('idunidademedida', itemData.idunidademedida);
            setVal('ncm', itemData.ncm);
            // cest removed
            setVal('cfop', itemData.cfop);
            setVal('quant', itemData.quant);
            setVal('valorunitario', itemData.valorunitario);
            setVal('desconto', itemData.desconto);
            setVal('percentualdesconto', itemData.percentualdesconto);
            setVal('valortotal', itemData.total); // Initial load
            
            // Calculate total if missing or just to be sure
            if (!itemData.total) calculateTotal();

            setVal('info_adicional', itemData.informacaoadicional);

            // ICMS
            setVal('origem', itemData.origem);
            setVal('cst_icms', itemData.csticms); // or csosn
            setVal('modbc', itemData.modbc);
            setVal('predbc', itemData.predbc); // % Red. BC
            setVal('baseicms', itemData.baseicms);
            setVal('aliquotaicms', itemData.aliquotaicms);
            setVal('valoricms', itemData.valoricms);
            
            setVal('modbcst', itemData.modbcst);
            setVal('pmvast', itemData.pmvast);
            setVal('predbcst', itemData.predbcst);
            setVal('basest', itemData.basest);
            setVal('aliquotast', itemData.aliquotast);
            setVal('valorst', itemData.valorst);

            // IPI
            setVal('cst_ipi', itemData.cstipi);
            setVal('cenq', itemData.cenq);
            setVal('valoripi', itemData.valoripi);

            // PIS/COFINS
            setVal('cst_pis', itemData.cstpis);
            setVal('valorpis', itemData.valorpis);
            setVal('cst_cofins', itemData.cstcofins);
            setVal('valorcofins', itemData.valorcofins);

            // Store the current item ID in the save button for reference
            document.getElementById('btnSaveItem').setAttribute('data-id', itemData.id);
        });

        document.getElementById('btnSaveItem').addEventListener('click', function() {
            const itemId = this.getAttribute('data-id');
            
            // Helper to get value from modal and update main form
            const updateField = (modalId, formNameSuffix) => {
                const val = modalBody.querySelector(`#modal_${modalId}`).value;
                
                // Update visible grid input if exists
                const gridInput = document.querySelector(`input[name="items[${itemId}][${formNameSuffix}]"]`);
                if (gridInput) {
                    gridInput.value = val;
                    // Trigger input event if it's a calc trigger
                    if (gridInput.classList.contains('calc-trigger')) {
                        gridInput.dispatchEvent(new Event('input'));
                    }
                }
                
                // Update hidden input if exists (for non-grid fields)
                const hiddenInput = document.querySelector(`input[type="hidden"][name="items[${itemId}][${formNameSuffix}]"]`);
                if (hiddenInput) {
                    hiddenInput.value = val;
                }
            };

            // Update all mapped fields
            updateField('cfop', 'cfop');
            updateField('quant', 'quant');
            updateField('valorunitario', 'valorunitario');
            updateField('desconto', 'desconto');
            updateField('percentualdesconto', 'percentualdesconto');
            updateField('valortotal', 'valortotal');
            updateField('ncm', 'ncm');
            // cest removed
            updateField('info_adicional', 'info_adicional');
            updateField('idunidademedida', 'idunidademedida');

            // ICMS
            updateField('cst_icms', 'cst_icms');
            updateField('origem', 'origem');
            updateField('modbc', 'modbc');
            updateField('predbc', 'predbc');
            updateField('baseicms', 'baseicms');
            updateField('aliquotaicms', 'aliquotaicms');
            updateField('valoricms', 'valoricms');
            
            updateField('modbcst', 'modbcst');
            updateField('pmvast', 'pmvast');
            updateField('predbcst', 'predbcst');
            updateField('basest', 'basest');
            updateField('aliquotast', 'aliquotast');
            updateField('valorst', 'valorst');

            // IPI
            updateField('cst_ipi', 'cst_ipi');
            updateField('cenq', 'cenq');
            updateField('valoripi', 'valoripi');

            // PIS/COFINS
            updateField('cst_pis', 'cst_pis');
            updateField('valorpis', 'valorpis');
            updateField('cst_cofins', 'cst_cofins');
            updateField('valorcofins', 'valorcofins');

            // Close modal
            const modalInstance = bootstrap.Modal.getInstance(itemModal);
            modalInstance.hide();
            
            // Recalculate totals
            recalculate();
        });

        // SQL Generator
        document.getElementById('btnGenerateSQL').addEventListener('click', function() {
            const invoiceId = <?php echo $invoice['id']; ?>;
            let sql = `-- Atualização da Nota Fiscal #${invoiceId}\n\n`;
            
            // Helper to get value safely
            const getVal = (name) => {
                const el = document.querySelector(`[name="${name}"]`);
                return el ? el.value : null;
            };

            const formatVal = (val, isNumeric = false) => {
                if (val === null || val === '') return 'NULL';
                if (isNumeric) return val.replace(',', '.'); // Ensure dot decimal
                return `'${val.replace(/'/g, "''")}'`; // Escape single quotes
            };

            // 1. Update Header
            const headerFields = [
                { name: 'valortotalnota', type: 'num' },
                { name: 'totalproduto', type: 'num' },
                { name: 'icms', db: 'icms', type: 'num' }, // Mapped from valoricms
                { name: 'ipi', db: 'ipi', type: 'num' },
                { name: 'icmssubstituicao', db: 'icmssubstituicao', type: 'num' },
                { name: 'pis', db: 'pis', type: 'num' },
                { name: 'cofins', db: 'cofins', type: 'num' },
                { name: 'frete', db: 'frete', type: 'num' },
                { name: 'seguro', db: 'seguro', type: 'num' },
                { name: 'descontosubtotal', db: 'descontosubtotal', type: 'num' },
                { name: 'outrasdespesas', db: 'outrasdespesas', type: 'num' },
                // Transporte
                { name: 'idtransportadora', type: 'int' },
                { name: 'placaveiculo', type: 'str' },
                { name: 'estadoveiculo', type: 'str' },
                { name: 'especie', type: 'str' },
                { name: 'marca', type: 'str' },
                { name: 'pesobruto', type: 'num' },
                { name: 'pesoliquido', type: 'num' },
                // Entrega
                { name: 'razaosocialnomeentrega', type: 'str' },
                { name: 'cnpjcpfentrega', type: 'str' },
                { name: 'enderecoentrega', type: 'str' },
                { name: 'numeroenderecoentrega', type: 'str' },
                { name: 'bairroentrega', type: 'str' },
                { name: 'idcidadeentrega', type: 'int' },
                { name: 'idestadoentrega', type: 'int' },
                { name: 'cepentrega', type: 'str' },
                // Financeiro
                { name: 'idcondicaopagto', type: 'int' },
                { name: 'avista', type: 'num' },
                { name: 'aprazo', type: 'num' },
                { name: 'pix', type: 'num' },
                { name: 'cheque', type: 'num' },
                { name: 'financiado', type: 'num' },
                { name: 'carteiradigital', type: 'num' },
                { name: 'outros', type: 'num' },
                // Info
                { name: 'informacoescomplementarespersonalizadas', type: 'str' },
                { name: 'infofisco', type: 'str' }
            ];

            let headerUpdates = [];
            headerFields.forEach(f => {
                // Handle mapped names (e.g. valoricms -> icms)
                // In the form, some are named 'valoricms' (readonlys) but we want to update 'icms'
                // Actually, the readonly inputs have names like 'valoricms' but the DB column is 'icms'.
                // I need to check the input names in the HTML.
                // The readonly inputs in 'Totais' tab have names like 'valoricms'.
                // The editable inputs in 'Transporte' have names like 'idtransportadora'.
                
                let inputName = f.name;
                // Map input names to DB columns if different
                if (f.name === 'icms') inputName = 'valoricms';
                if (f.name === 'ipi') inputName = 'valoripi';
                if (f.name === 'icmssubstituicao') inputName = 'valorst';
                if (f.name === 'pis') inputName = 'valorpis';
                if (f.name === 'cofins') inputName = 'valorcofins';
                if (f.name === 'frete') inputName = 'valorfrete';
                if (f.name === 'seguro') inputName = 'valorseguro';
                if (f.name === 'descontosubtotal') inputName = 'valordesconto';
                if (f.name === 'outrasdespesas') inputName = 'valoroutrasdespesas';

                let val = getVal(inputName);
                if (val !== null) {
                    headerUpdates.push(`${f.db || f.name} = ${formatVal(val, f.type !== 'str')}`);
                }
            });

            if (headerUpdates.length > 0) {
                sql += `UPDATE notafiscal SET \n    ${headerUpdates.join(',\n    ')} \nWHERE id = ${invoiceId};\n\n`;
            }

            // 2. Update Items
            const itemRows = document.querySelectorAll('tr[data-id]');
            itemRows.forEach(row => {
                const itemId = row.getAttribute('data-id');
                let itemUpdates = [];
                
                // Helper to get item field value
                const getItemVal = (field) => {
                    // Try visible input first
                    let el = row.querySelector(`input[name="items[${itemId}][${field}]"]`);
                    if (!el) {
                        // Try hidden input
                        el = document.querySelector(`input[name="items[${itemId}][${field}]"]`);
                    }
                    return el ? el.value : null;
                };

                const itemFields = [
                    { name: 'cfop', type: 'str' },
                    { name: 'quantidade', input: 'quant', type: 'num' },
                    { name: 'precounitario', input: 'valorunitario', type: 'num' },
                    { name: 'ncm', type: 'str' },
                    // cest removed as it does not exist
                    { name: 'idunidademedida', type: 'int' },
                    { name: 'informacaoadicional', input: 'info_adicional', type: 'str' },
                    { name: 'desconto', type: 'num' },
                    { name: 'percentualdesconto', type: 'num' },
                    { name: 'total', input: 'valortotal', type: 'num' },
                    // ICMS
                    { name: 'origem', type: 'int' },
                    { name: 'situacaotributaria', input: 'cst_icms', type: 'str' }, // Fixed: csticms -> situacaotributaria
                    // { name: 'modbc', type: 'int' }, // Removed: not found
                    { name: 'percentualreducaoicms', input: 'predbc', type: 'num' }, // Fixed: percentualreducaobc -> percentualreducaoicms
                    { name: 'baseicms', type: 'num' },
                    { name: 'percentualicms', input: 'aliquotaicms', type: 'num' },
                    { name: 'icms', input: 'valoricms', type: 'num' },
                    // ST
                    { name: 'modalidadeicmsst', input: 'modbcst', type: 'int' }, // Fixed: modbcst -> modalidadeicmsst
                    { name: 'margemvaloradicionado', input: 'pmvast', type: 'num' }, // Fixed: pmvast -> margemvaloradicionado
                    { name: 'percentualreducaoicmssubstituicao', input: 'predbcst', type: 'num' }, // Fixed: percentualreducaobcst -> percentualreducaoicmssubstituicao
                    { name: 'baseicmssubstituicao', input: 'basest', type: 'num' },
                    { name: 'percentualicmssubstituicao', input: 'aliquotast', type: 'num' },
                    { name: 'icmssubstituicao', input: 'valorst', type: 'num' },
                    // IPI
                    { name: 'situacaotributariaipi', input: 'cst_ipi', type: 'str' }, // Fixed: cstipi -> situacaotributariaipi
                    // { name: 'cenq', type: 'str' }, // Removed: not found
                    { name: 'ipi', input: 'valoripi', type: 'num' },
                    // PIS
                    { name: 'cstpis', input: 'cst_pis', type: 'str' },
                    { name: 'pis', input: 'valorpis', type: 'num' },
                    // COFINS
                    { name: 'cstcofins', input: 'cst_cofins', type: 'str' },
                    { name: 'cofins', input: 'valorcofins', type: 'num' }
                ];

                itemFields.forEach(f => {
                    let val = getItemVal(f.input || f.name);
                    if (val !== null) {
                        itemUpdates.push(`${f.name} = ${formatVal(val, f.type !== 'str')}`);
                    }
                });

                if (itemUpdates.length > 0) {
                    sql += `UPDATE notafiscalitem SET \n    ${itemUpdates.join(',\n    ')} \nWHERE id = ${itemId};\n`;
                }
            });

            document.getElementById('sqlOutput').value = sql;
        });
    }
});
</script>

<!-- Item Edit Modal -->
<div class="modal fade" id="itemModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="itemModalLabel">Editar Item <span id="modalTitleCode" class="badge bg-secondary ms-2"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="modal_id">
                
                <ul class="nav nav-tabs mb-3" id="itemTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="general-tab" data-bs-toggle="tab" data-bs-target="#general" type="button" role="tab">Geral</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="icms-tab" data-bs-toggle="tab" data-bs-target="#icms" type="button" role="tab">ICMS</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="ipi-tab" data-bs-toggle="tab" data-bs-target="#ipi" type="button" role="tab">IPI</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="piscofins-tab" data-bs-toggle="tab" data-bs-target="#piscofins" type="button" role="tab">PIS/COFINS</button>
                    </li>
                </ul>

                <div class="tab-content">
                    <!-- General Tab -->
                    <div class="tab-pane fade show active" id="general" role="tabpanel">
                        <div class="row g-3 mb-3">
                            <div class="col-md-12">
                                <label class="form-label small fw-bold">Produto</label>
                                <input type="text" class="form-control bg-light" id="modal_xprod" readonly>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Código</label>
                                <input type="text" class="form-control bg-light" id="modal_cprod" readonly>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Unidade</label>
                                <select class="form-select" id="modal_idunidademedida">
                                    <option value="">Selecione...</option>
                                    <?php foreach ($units as $u): ?>
                                        <option value="<?php echo $u['id']; ?>"><?php echo $u['sigla']; ?> - <?php echo $u['nome']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">NCM</label>
                                <input type="text" class="form-control" id="modal_ncm">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">CFOP</label>
                                <input type="text" class="form-control" id="modal_cfop">
                            </div>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-2">
                                <label class="form-label small fw-bold">Quantidade</label>
                                <input type="number" step="0.0001" class="form-control calc-trigger" id="modal_quant">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-bold">Valor Unit.</label>
                                <input type="number" step="0.01" class="form-control calc-trigger" id="modal_valorunitario">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-bold">Desc. (R$)</label>
                                <input type="number" step="0.01" class="form-control calc-trigger" id="modal_desconto">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-bold">Desc. (%)</label>
                                <input type="number" step="0.01" class="form-control calc-trigger" id="modal_percentualdesconto">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-bold">Total</label>
                                <input type="number" step="0.01" class="form-control" id="modal_valortotal" readonly>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Informações Adicionais</label>
                            <textarea class="form-control" id="modal_info_adicional" rows="2"></textarea>
                        </div>
                    </div>

                    <!-- ICMS Tab -->
                    <div class="tab-pane fade" id="icms" role="tabpanel">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Origem</label>
                                <select class="form-select" id="modal_origem">
                                    <option value="0">0 - Nacional</option>
                                    <option value="1">1 - Estrangeira (Imp. Direta)</option>
                                    <option value="2">2 - Estrangeira (Adq. Mercado Int.)</option>
                                    <!-- Add other options as needed -->
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">CST ICMS / CSOSN</label>
                                <input type="text" class="form-control" id="modal_cst_icms">
                            </div>
                            
                            <div class="col-12"><h6 class="mt-3 border-bottom pb-2">ICMS Normal</h6></div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Mod. BC</label>
                                <select class="form-select" id="modal_modbc">
                                    <option value="0">0 - Margem Valor Agregado (%)</option>
                                    <option value="1">1 - Pauta (Valor)</option>
                                    <option value="2">2 - Preço Tabelado Máx. (valor)</option>
                                    <option value="3">3 - Valor da Operação</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">% Red. BC</label>
                                <input type="number" step="0.01" class="form-control" id="modal_predbc">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Base ICMS</label>
                                <input type="number" step="0.01" class="form-control" id="modal_baseicms">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Alíquota (%)</label>
                                <input type="number" step="0.01" class="form-control" id="modal_aliquotaicms">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Valor ICMS</label>
                                <input type="number" step="0.01" class="form-control" id="modal_valoricms">
                            </div>

                            <div class="col-12"><h6 class="mt-3 border-bottom pb-2">ICMS ST</h6></div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Mod. BC ST</label>
                                <select class="form-select" id="modal_modbcst">
                                    <option value="0">0 - Preço tabelado ou máximo sugerido</option>
                                    <option value="1">1 - Lista Negativa (valor)</option>
                                    <option value="2">2 - Lista Positiva (valor)</option>
                                    <option value="3">3 - Índice de Valor Adicionado (%)</option>
                                    <option value="4">4 - Margem Valor Agregado (%)</option>
                                    <option value="5">5 - Pauta (valor)</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">% MVA ST</label>
                                <input type="number" step="0.01" class="form-control" id="modal_pmvast">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">% Red. BC ST</label>
                                <input type="number" step="0.01" class="form-control" id="modal_predbcst">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Base ST</label>
                                <input type="number" step="0.01" class="form-control" id="modal_basest">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Alíquota ST (%)</label>
                                <input type="number" step="0.01" class="form-control" id="modal_aliquotast">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Valor ST</label>
                                <input type="number" step="0.01" class="form-control" id="modal_valorst">
                            </div>
                        </div>
                    </div>

                    <!-- IPI Tab -->
                    <div class="tab-pane fade" id="ipi" role="tabpanel">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">CST IPI</label>
                                <input type="text" class="form-control" id="modal_cst_ipi">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Cód. Enq.</label>
                                <input type="text" class="form-control" id="modal_cenq">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Valor IPI</label>
                                <input type="number" step="0.01" class="form-control" id="modal_valoripi">
                            </div>
                        </div>
                    </div>

                    <!-- PIS/COFINS Tab -->
                    <div class="tab-pane fade" id="piscofins" role="tabpanel">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">CST PIS</label>
                                <input type="text" class="form-control" id="modal_cst_pis">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Valor PIS</label>
                                <input type="number" step="0.01" class="form-control" id="modal_valorpis">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">CST COFINS</label>
                                <input type="text" class="form-control" id="modal_cst_cofins">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Valor COFINS</label>
                                <input type="number" step="0.01" class="form-control" id="modal_valorcofins">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnSaveItem">Salvar Alterações</button>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
