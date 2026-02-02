<?php include 'includes/header.php'; ?>
</div>

<div class="container-fluid py-4 px-0">
    <div class="row mb-4 mx-0">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <div class="icon-box bg-success-gradient text-white rounded-3 me-3">
                                <i class="bi bi-bank fs-4"></i>
                            </div>
                            <div>
                                <h2 class="fw-bold text-dark mb-0">Reforma Tributária</h2>
                                <p class="text-muted mb-0">Consulte informações sobre a reforma tributária, classificações e créditos.</p>
                            </div>
                        </div>
                        <a href="index.php" class="btn btn-outline-secondary rounded-pill px-4">
                            <i class="bi bi-arrow-left me-2"></i>Voltar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mx-0">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-0">
                    <ul class="nav nav-tabs nav-fill p-3 pb-0 border-bottom-0" id="reformaTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active fw-semibold" id="classificacao-tab" data-bs-toggle="tab" data-bs-target="#classificacao" type="button" role="tab" aria-controls="classificacao" aria-selected="true">
                                <i class="bi bi-list-check me-2"></i>Classificação Tributária
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link fw-semibold" id="credito-tab" data-bs-toggle="tab" data-bs-target="#credito" type="button" role="tab" aria-controls="credito" aria-selected="false">
                                <i class="bi bi-table me-2"></i>Tabela Crédito Presumido
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link fw-semibold" id="guia-tab" data-bs-toggle="tab" data-bs-target="#guia" type="button" role="tab" aria-controls="guia" aria-selected="false">
                                <i class="bi bi-book me-2"></i>Guia da Reforma
                            </button>
                        </li>
                    </ul>
                    
                    <div class="tab-content p-4 bg-light rounded-bottom-4" id="reformaTabsContent">
                        <!-- Classificação Tributária -->
                        <div class="tab-pane fade show active" id="classificacao" role="tabpanel" aria-labelledby="classificacao-tab">
                            <div class="table-responsive bg-white rounded-3 shadow-sm border">
                                <table class="table table-hover mb-0 align-middle">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="py-3 px-4 border-bottom-0" style="width: 50px;"></th>
                                            <th class="py-3 px-4 border-bottom-0 text-center">CÓDIGO</th>
                                            <th class="py-3 px-4 border-bottom-0">DESCRIÇÃO</th>
                                            <th class="py-3 px-4 border-bottom-0 text-center">EXIGE TRIBUTAÇÃO</th>
                                            <th class="py-3 px-4 border-bottom-0 text-center">REDUÇÕES (BC/ALÍQ)</th>
                                            <th class="py-3 px-4 border-bottom-0 text-center">TRANSF. CRÉDITO</th>
                                            <th class="py-3 px-4 border-bottom-0 text-center">DIFERIMENTO</th>
                                            <th class="py-3 px-4 border-bottom-0 text-center">MONOFÁSICA</th>
                                            <th class="py-3 px-4 border-bottom-0 text-center">CRÉD. PRES. IBS ZFM</th>
                                            <th class="py-3 px-4 border-bottom-0 text-center">AJUSTE CRÉDITO</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        include 'reforma_tributaria_data.php';
                                        
                                        foreach ($csts as $cst) {
                                            $checkIcon = '<i class="bi bi-check-circle-fill text-success fs-5"></i>';
                                            $dashIcon = '<i class="bi bi-dash-circle-fill text-secondary fs-5" style="opacity: 0.3;"></i>';
                                            
                                            echo "<tr class='accordion-toggle collapsed' id='cst-row-{$cst['codigo']}' data-bs-toggle='collapse' data-bs-target='#cst-collapse-{$cst['codigo']}' aria-expanded='false' style='cursor: pointer;'>";
                                            echo "<td class='text-center'><button class='btn btn-sm btn-light rounded-circle shadow-sm border' type='button'><i class='bi bi-plus-lg text-primary'></i></button></td>";
                                            echo "<td class='text-center'><span class='badge bg-light text-dark border'>{$cst['codigo']}</span></td>";
                                            echo "<td>{$cst['descricao']}</td>";
                                            
                                            echo "<td class='text-center'>" . ($cst['indicadores']['exige_tributacao'] ? $checkIcon : $dashIcon) . "</td>";
                                            echo "<td class='text-center'>";
                                            echo ($cst['indicadores']['reducao_bc'] ? $checkIcon : $dashIcon) . " ";
                                            echo ($cst['indicadores']['reducao_aliquota'] ? $checkIcon : $dashIcon);
                                            echo "</td>";
                                            echo "<td class='text-center'>" . ($cst['indicadores']['transferencia_credito'] ? $checkIcon : $dashIcon) . "</td>";
                                            echo "<td class='text-center'>" . ($cst['indicadores']['diferimento'] ? $checkIcon : $dashIcon) . "</td>";
                                            echo "<td class='text-center'>" . ($cst['indicadores']['monofasica'] ? $checkIcon : $dashIcon) . "</td>";
                                            echo "<td class='text-center'>" . ($cst['indicadores']['credito_presumido_ibs_zfm'] ? $checkIcon : $dashIcon) . "</td>";
                                            echo "<td class='text-center'>" . ($cst['indicadores']['ajuste_competencia'] ? $checkIcon : $dashIcon) . "</td>";
                                            echo "</tr>";

                                            // Expanded Row (Nested Table)
                                            echo "<tr>";
                                            echo "<td colspan='10' class='p-0 border-0'>";
                                            echo "<div id='cst-collapse-{$cst['codigo']}' class='accordion-collapse collapse bg-light' data-bs-parent='#classificacao'>";
                                            echo "<div class='p-3'>";
                                            
                                            if (!empty($cst['classificacoes'])) {
                                                echo "<div class='table-responsive bg-white rounded-3 shadow-sm border'>";
                                                echo "<table class='table table-sm table-hover mb-0 align-middle small'>";
                                                echo "<thead class='bg-light text-muted'>";
                                                echo "<tr>";
                                                echo "<th class='py-2 px-3'>Código</th>";
                                                echo "<th class='py-2 px-3'>Descrição Reduzida</th>";
                                                echo "<th class='py-2 px-3 text-center'>Reduções % (IBS/CBS)</th>";
                                                echo "<th class='py-2 px-3 text-center'>Trib. Regular</th>";
                                                echo "<th class='py-2 px-3 text-center'>Créd. Presumido</th>";
                                                echo "<th class='py-2 px-3 text-center'>Estorno Créd.</th>";
                                                echo "<th class='py-2 px-3 text-center'>Tipo Alíquota</th>";
                                                echo "<th class='py-2 px-3'>DFes Relacionados</th>";
                                                echo "<th class='py-2 px-3 text-center'>Nº Anexo</th>";
                                                echo "<th class='py-2 px-3 text-center'>Ações</th>";
                                                echo "</tr>";
                                                echo "</thead>";
                                                echo "<tbody>";
                                                
                                                foreach ($cst['classificacoes'] as $classificacao) {
                                                    $subCheck = '<i class="bi bi-check-circle-fill text-success"></i>';
                                                    $subDash = '<i class="bi bi-dash-circle-fill text-secondary" style="opacity: 0.3;"></i>';
                                                    $dfesStr = implode(', ', $classificacao['dfes']);
                                                    
                                                    echo "<tr>";
                                                    echo "<td class='px-3 fw-bold'>{$classificacao['codigo']}</td>";
                                                    echo "<td class='px-3'>{$classificacao['descricao']}</td>";
                                                    echo "<td class='px-3 text-center'>{$classificacao['reducao_ibs']} / {$classificacao['reducao_cbs']}</td>";
                                                    echo "<td class='px-3 text-center'>" . ($classificacao['indicadores']['tributacao_regular'] ? $subCheck : $subDash) . "</td>";
                                                    echo "<td class='px-3 text-center'>" . ($classificacao['indicadores']['credito_presumido'] ? $subCheck : $subDash) . "</td>";
                                                    echo "<td class='px-3 text-center'>" . ($classificacao['indicadores']['estorno_credito'] ? $subCheck : $subDash) . "</td>";
                                                    echo "<td class='px-3 text-center'>{$classificacao['tipo_aliquota']}</td>";
                                                    echo "<td class='px-3 text-truncate' style='max-width: 150px;' title='{$dfesStr}'>{$dfesStr}</td>";
                                                    echo "<td class='px-3 text-center'>" . ($classificacao['anexo'] ? $classificacao['anexo'] : $subDash) . "</td>";
                                                    echo "<td class='px-3 text-center'>";
                                                    if ($classificacao['legislacao']) {
                                                        echo "<a href='{$classificacao['legislacao']}' class='btn btn-sm btn-outline-secondary border-0 me-1' title='Legislação' target='_blank'><i class='bi bi-book'></i></a>";
                                                    }
                                                    echo "<button type='button' class='btn btn-sm btn-outline-primary border-0' data-bs-toggle='modal' data-bs-target='#modalDetalhe' 
                                                            data-cst='{$cst['codigo']} - {$cst['descricao']}'
                                                            data-codigo='{$classificacao['codigo']}'
                                                            data-descricao='{$classificacao['descricao']}'
                                                            data-publicacao='-'
                                                            data-vigencia='-'
                                                            data-dfes='{$dfesStr}'
                                                            title='Detalhes'><i class='bi bi-eye'></i></button>";
                                                    echo "</td>";
                                                    echo "</tr>";
                                                }
                                                
                                                echo "</tbody>";
                                                echo "</table>";
                                                echo "</div>";
                                            } else {
                                                echo "<p class='text-muted text-center mb-0'>Nenhuma classificação encontrada para este CST.</p>";
                                            }
                                            
                                            echo "</div>"; // End p-3
                                            echo "</div>"; // End collapse
                                            echo "</td>";
                                            echo "</tr>";
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Tabela Crédito Presumido -->
                        <div class="tab-pane fade" id="credito" role="tabpanel" aria-labelledby="credito-tab">
                            <div class="table-responsive bg-white rounded-3 shadow-sm border">
                                <table class="table table-hover mb-0 align-middle">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="py-3 px-4 border-bottom-0" style="width: 50px;"></th>
                                            <th class="py-3 px-4 border-bottom-0 text-center">CÓDIGO</th>
                                            <th class="py-3 px-4 border-bottom-0">DESCRIÇÃO</th>
                                            <th class="py-3 px-4 border-bottom-0 text-center">APROPRIA DFE</th>
                                            <th class="py-3 px-4 border-bottom-0 text-center">APROPRIA EVENTO</th>
                                            <th class="py-3 px-4 border-bottom-0 text-center">DEDUZ VALOR TOTAL</th>
                                            <th class="py-3 px-4 border-bottom-0 text-center">TRIBUTOS</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $creditos = [
                                            [
                                                'id' => 1,
                                                'descricao' => 'Crédito presumido da aquisição de bens e serviços de produtor rural e produtor rural integrado não contribuinte, observado o art. 168 da Lei Complementar nº 214, de 2025.',
                                                'config' => ['dfe' => true, 'evento' => true, 'deduz' => false],
                                                'ibs' => ['aplicavel' => true, 'inicio' => '01/01/2027', 'fim' => 'Indeterminado'],
                                                'cbs' => ['aplicavel' => true, 'inicio' => 'Não informado', 'fim' => 'Indeterminado']
                                            ],
                                            [
                                                'id' => 2,
                                                'descricao' => 'Crédito presumido da aquisição de serviço de transportador autônomo de carga pessoa física não contribuinte, observado o art. 169 da Lei Complementar nº 214, de 2025.',
                                                'config' => ['dfe' => false, 'evento' => true, 'deduz' => false],
                                                'ibs' => ['aplicavel' => true, 'inicio' => '01/01/2027', 'fim' => 'Indeterminado'],
                                                'cbs' => ['aplicavel' => true, 'inicio' => 'Não informado', 'fim' => 'Indeterminado']
                                            ],
                                            [
                                                'id' => 3,
                                                'descricao' => 'Crédito presumido da aquisição de resíduos e demais materiais destinados à reciclagem, reutilização ou logística reversa adquiridos de pessoa física, cooperativa ou outra forma de organização popular, observado o art. 170 da Lei Complementar nº 214, de 2025.',
                                                'config' => ['dfe' => true, 'evento' => true, 'deduz' => false],
                                                'ibs' => ['aplicavel' => true, 'inicio' => '01/01/2029', 'fim' => 'Indeterminado'],
                                                'cbs' => ['aplicavel' => true, 'inicio' => 'Não informado', 'fim' => 'Indeterminado']
                                            ],
                                            [
                                                'id' => 4,
                                                'descricao' => 'Crédito presumido da aquisição de bens móveis usados de pessoa física não contribuinte para revenda, observado o art. 171 da Lei Complementar nº 214, de 2025.',
                                                'config' => ['dfe' => true, 'evento' => true, 'deduz' => true],
                                                'ibs' => ['aplicavel' => true, 'inicio' => '01/01/2029', 'fim' => 'Indeterminado'],
                                                'cbs' => ['aplicavel' => true, 'inicio' => 'Não informado', 'fim' => 'Indeterminado']
                                            ],
                                            [
                                                'id' => 5,
                                                'descricao' => 'Crédito presumido no regime automotivo, observado o art. 311 da Lei Complementar nº 214, de 2025.',
                                                'config' => ['dfe' => true, 'evento' => false, 'deduz' => false],
                                                'ibs' => ['aplicavel' => false, 'inicio' => null, 'fim' => null],
                                                'cbs' => ['aplicavel' => true, 'inicio' => 'Não informado', 'fim' => 'Indeterminado']
                                            ],
                                            [
                                                'id' => 6,
                                                'descricao' => 'Crédito presumido no regime automotivo, observado o art. 312 da Lei Complementar nº 214, de 2025.',
                                                'config' => ['dfe' => true, 'evento' => false, 'deduz' => false],
                                                'ibs' => ['aplicavel' => false, 'inicio' => null, 'fim' => null],
                                                'cbs' => ['aplicavel' => true, 'inicio' => 'Não informado', 'fim' => 'Indeterminado']
                                            ],
                                            [
                                                'id' => 7,
                                                'descricao' => 'Crédito presumido na aquisição por contribuinte na Zona Franca de Manaus, observado o art. 444 da Lei Complementar nº 214, de 2025.',
                                                'config' => ['dfe' => true, 'evento' => false, 'deduz' => true],
                                                'ibs' => ['aplicavel' => true, 'inicio' => 'Não informado', 'fim' => 'Indeterminado'],
                                                'cbs' => ['aplicavel' => false, 'inicio' => null, 'fim' => null]
                                            ],
                                            [
                                                'id' => 8,
                                                'descricao' => 'Crédito presumido na aquisição por contribuinte na Zona Franca de Manaus, observado o art. 447 da Lei Complementar nº 214, de 2025.',
                                                'config' => ['dfe' => false, 'evento' => true, 'deduz' => false],
                                                'ibs' => ['aplicavel' => true, 'inicio' => '01/01/2029', 'fim' => 'Indeterminado'],
                                                'cbs' => ['aplicavel' => false, 'inicio' => null, 'fim' => null]
                                            ],
                                            [
                                                'id' => 9,
                                                'descricao' => 'Crédito presumido na aquisição por contribuinte na Zona Franca de Manaus, observado o art. 447 da Lei Complementar nº 214, de 2025.',
                                                'config' => ['dfe' => false, 'evento' => true, 'deduz' => false],
                                                'ibs' => ['aplicavel' => true, 'inicio' => 'Não informado', 'fim' => 'Indeterminado'],
                                                'cbs' => ['aplicavel' => false, 'inicio' => null, 'fim' => null]
                                            ],
                                            [
                                                'id' => 10,
                                                'descricao' => 'Crédito presumido na aquisição por contribuinte na Zona Franca de Manaus, observado o art. 450 da Lei Complementar nº 214, de 2025.',
                                                'config' => ['dfe' => true, 'evento' => false, 'deduz' => false],
                                                'ibs' => ['aplicavel' => false, 'inicio' => null, 'fim' => null],
                                                'cbs' => ['aplicavel' => true, 'inicio' => 'Não informado', 'fim' => 'Indeterminado']
                                            ],
                                            [
                                                'id' => 11,
                                                'descricao' => 'Crédito presumido na aquisição por contribuinte na Área de Livre Comércio, observado o art. 462 da Lei Complementar nº 214, de 2025.',
                                                'config' => ['dfe' => true, 'evento' => false, 'deduz' => true],
                                                'ibs' => ['aplicavel' => true, 'inicio' => 'Não informado', 'fim' => 'Indeterminado'],
                                                'cbs' => ['aplicavel' => false, 'inicio' => null, 'fim' => null]
                                            ],
                                            [
                                                'id' => 12,
                                                'descricao' => 'Crédito presumido na aquisição por contribuinte na Área de Livre Comércio, observado o art. 465 da Lei Complementar nº 214, de 2025.',
                                                'config' => ['dfe' => false, 'evento' => true, 'deduz' => false],
                                                'ibs' => ['aplicavel' => true, 'inicio' => 'Não informado', 'fim' => 'Indeterminado'],
                                                'cbs' => ['aplicavel' => false, 'inicio' => null, 'fim' => null]
                                            ],
                                            [
                                                'id' => 13,
                                                'descricao' => 'Crédito presumido na aquisição pela indústria na Área de Livre Comércio, observado o art. 467 da Lei Complementar nº 214, de 2025.',
                                                'config' => ['dfe' => true, 'evento' => false, 'deduz' => false],
                                                'ibs' => ['aplicavel' => false, 'inicio' => null, 'fim' => null],
                                                'cbs' => ['aplicavel' => true, 'inicio' => 'Não informado', 'fim' => 'Indeterminado']
                                            ],
                                        ];

                                        foreach ($creditos as $credito) {
                                            $checkIcon = '<i class="bi bi-check-circle-fill text-success fs-5"></i>';
                                            $dashIcon = '<i class="bi bi-dash-circle-fill text-secondary fs-5" style="opacity: 0.3;"></i>';
                                            
                                            echo "<tr class='accordion-toggle collapsed' id='row-{$credito['id']}' data-bs-toggle='collapse' data-bs-target='#collapse-{$credito['id']}' aria-expanded='false' style='cursor: pointer;'>";
                                            echo "<td class='text-center'><button class='btn btn-sm btn-light rounded-circle shadow-sm border' type='button'><i class='bi bi-plus-lg text-primary'></i></button></td>";
                                            echo "<td class='text-center'><span class='badge bg-primary rounded-pill'>{$credito['id']}</span></td>";
                                            echo "<td><div class='text-truncate-2' style='max-width: 400px;'>{$credito['descricao']}</div></td>";
                                            
                                            echo "<td class='text-center'>" . ($credito['config']['dfe'] ? $checkIcon : $dashIcon) . "</td>";
                                            echo "<td class='text-center'>" . ($credito['config']['evento'] ? $checkIcon : $dashIcon) . "</td>";
                                            echo "<td class='text-center'>" . ($credito['config']['deduz'] ? $checkIcon : $dashIcon) . "</td>";
                                            
                                            echo "<td class='text-center'>";
                                            if ($credito['ibs']['aplicavel']) echo '<span class="badge bg-success me-1">IBS</span>';
                                            if ($credito['cbs']['aplicavel']) echo '<span class="badge bg-info">CBS</span>';
                                            echo "</td>";
                                            echo "</tr>";

                                            // Expanded Row
                                            echo "<tr>";
                                            echo "<td colspan='7' class='p-0 border-0'>";
                                            echo "<div id='collapse-{$credito['id']}' class='accordion-collapse collapse bg-light' data-bs-parent='#credito'>"; // Added data-bs-parent to act like accordion if desired, or remove to allow multiple open
                                            echo "<div class='p-0'>";
                                            
                                            // Header of details
                                            echo "<div class='bg-primary text-white p-2 px-4 d-flex align-items-center'>";
                                            echo "<i class='bi bi-info-circle-fill me-2'></i>";
                                            echo "<span class='fw-bold'>Detalhes do Crédito Presumido {$credito['id']}</span>";
                                            echo "</div>";

                                            echo "<div class='p-4 row'>";
                                            
                                            // Col 1: Informações Gerais & Configurações
                                            echo "<div class='col-md-6'>";
                                            echo "<h6 class='fw-bold mb-3'>Informações Gerais:</h6>";
                                            echo "<p class='mb-1'><span class='text-muted'>Código:</span> <strong>{$credito['id']}</strong></p>";
                                            echo "<p class='mb-4'><span class='text-muted'>Descrição:</span> {$credito['descricao']}</p>";
                                            
                                            echo "<h6 class='fw-bold mb-3'>Configurações:</h6>";
                                            echo "<p class='mb-1'><span class='text-muted'>Apropria DFE:</span> <span class='" . ($credito['config']['dfe'] ? 'text-success' : 'text-danger') . "'>" . ($credito['config']['dfe'] ? 'Sim' : 'Não') . "</span></p>";
                                            echo "<p class='mb-1'><span class='text-muted'>Apropria Evento:</span> <span class='" . ($credito['config']['evento'] ? 'text-success' : 'text-danger') . "'>" . ($credito['config']['evento'] ? 'Sim' : 'Não') . "</span></p>";
                                            echo "<p class='mb-1'><span class='text-muted'>Deduz Crédito Presumido:</span> <span class='" . ($credito['config']['deduz'] ? 'text-success' : 'text-danger') . "'>" . ($credito['config']['deduz'] ? 'Sim' : 'Não') . "</span></p>";
                                            echo "</div>";

                                            // Col 2: Vigência dos Tributos
                                            echo "<div class='col-md-6'>";
                                            echo "<h6 class='fw-bold mb-3'>Vigência dos Tributos:</h6>";
                                            
                                            // IBS
                                            echo "<div class='mb-4'>";
                                            echo "<p class='fw-bold text-success mb-1'>IBS (Imposto sobre Bens e Serviços)</p>";
                                            echo "<p class='mb-1'><span class='text-muted'>Aplicável:</span> " . ($credito['ibs']['aplicavel'] ? 'Sim' : 'Não') . "</p>";
                                            if ($credito['ibs']['aplicavel']) {
                                                echo "<p class='mb-1'><span class='text-muted'>Início Vigência:</span> {$credito['ibs']['inicio']}</p>";
                                                echo "<p class='mb-1'><span class='text-muted'>Fim Vigência:</span> {$credito['ibs']['fim']}</p>";
                                            }
                                            echo "</div>";

                                            // CBS
                                            echo "<div>";
                                            echo "<p class='fw-bold text-info mb-1'>CBS (Contribuição sobre Bens e Serviços)</p>";
                                            echo "<p class='mb-1'><span class='text-muted'>Aplicável:</span> " . ($credito['cbs']['aplicavel'] ? 'Sim' : 'Não') . "</p>";
                                            if ($credito['cbs']['aplicavel']) {
                                                echo "<p class='mb-1'><span class='text-muted'>Início Vigência:</span> {$credito['cbs']['inicio']}</p>";
                                                echo "<p class='mb-1'><span class='text-muted'>Fim Vigência:</span> {$credito['cbs']['fim']}</p>";
                                            }
                                            echo "</div>";

                                            echo "</div>"; // End col-md-6
                                            echo "</div>"; // End row
                                            echo "</div>"; // End p-0
                                            echo "</div>"; // End collapse
                                            echo "</td>";
                                            echo "</tr>";
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Guia da Reforma -->
                        <div class="tab-pane fade" id="guia" role="tabpanel" aria-labelledby="guia-tab">
                            <div class="row">
                                <div class="col-md-3 mb-4 mb-md-0">
                                    <div class="list-group list-group-flush sticky-top" style="top: 20px;" id="guia-list-tab" role="tablist">
                                        <a class="list-group-item list-group-item-action active rounded-3 mb-1 border-0" id="list-panorama-list" data-bs-toggle="list" href="#list-panorama" role="tab" aria-controls="list-panorama">
                                            <i class="bi bi-globe-americas me-2"></i>Panorama Geral
                                        </a>
                                        <a class="list-group-item list-group-item-action rounded-3 mb-1 border-0" id="list-conceito-list" data-bs-toggle="list" href="#list-conceito" role="tab" aria-controls="list-conceito">
                                            <i class="bi bi-lightbulb me-2"></i>O que é e Prazos
                                        </a>
                                        <a class="list-group-item list-group-item-action rounded-3 mb-1 border-0" id="list-fator-list" data-bs-toggle="list" href="#list-fator" role="tab" aria-controls="list-fator">
                                            <i class="bi bi-calendar-event me-2"></i>Fato Gerador
                                        </a>
                                        <a class="list-group-item list-group-item-action rounded-3 mb-1 border-0" id="list-calculo-list" data-bs-toggle="list" href="#list-calculo" role="tab" aria-controls="list-calculo">
                                            <i class="bi bi-calculator me-2"></i>Base de Cálculo
                                        </a>
                                        <a class="list-group-item list-group-item-action rounded-3 mb-1 border-0" id="list-credito-list" data-bs-toggle="list" href="#list-credito" role="tab" aria-controls="list-credito">
                                            <i class="bi bi-cash-coin me-2"></i>Créditos
                                        </a>
                                        <a class="list-group-item list-group-item-action rounded-3 mb-1 border-0" id="list-split-list" data-bs-toggle="list" href="#list-split" role="tab" aria-controls="list-split">
                                            <i class="bi bi-arrows-angle-contract me-2"></i>Split Payment
                                        </a>
                                        <a class="list-group-item list-group-item-action rounded-3 mb-1 border-0" id="list-regimes-list" data-bs-toggle="list" href="#list-regimes" role="tab" aria-controls="list-regimes">
                                            <i class="bi bi-diagram-3 me-2"></i>Regimes Específicos
                                        </a>
                                        <a class="list-group-item list-group-item-action rounded-3 mb-1 border-0" id="list-dfes-list" data-bs-toggle="list" href="#list-dfes" role="tab" aria-controls="list-dfes">
                                            <i class="bi bi-file-earmark-code me-2"></i>DFes
                                        </a>
                                    </div>
                                </div>
                                <div class="col-md-9">
                                    <div class="tab-content" id="nav-tabContent">
                                        <!-- Panorama Geral -->
                                        <div class="tab-pane fade show active" id="list-panorama" role="tabpanel" aria-labelledby="list-panorama-list">
                                            <div class="card border-0 shadow-sm rounded-4 mb-4">
                                                <div class="card-body p-4">
                                                    <h3 class="fw-bold text-primary mb-3">Panorama Geral e Faseamento</h3>
                                                    <p>A Reforma Tributária no Brasil surge para simplificar um dos sistemas mais complexos do mundo. O objetivo é reduzir a burocracia, aumentar a transparência e estimular o crescimento econômico.</p>
                                                    <p>O projeto será estruturado em etapas, começando pela tributação sobre o consumo. A implementação ocorrerá em três fases planejadas para garantir uma transição ordenada.</p>
                                                    <div class="alert alert-info border-0 rounded-3">
                                                        <i class="bi bi-info-circle-fill me-2"></i>
                                                        <strong>Destaque:</strong> A tributação sobre o consumo será o ponto de partida para a implementação da reforma prevista para os próximos anos.
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- O que é e Prazos -->
                                        <div class="tab-pane fade" id="list-conceito" role="tabpanel" aria-labelledby="list-conceito-list">
                                            <div class="card border-0 shadow-sm rounded-4 mb-4">
                                                <div class="card-body p-4">
                                                    <h3 class="fw-bold text-primary mb-3">O que é a Reforma Tributária sobre o Consumo?</h3>
                                                    <p>A principal mudança é a criação do <strong>IVA (Imposto sobre Valor Adicionado)</strong> Dual, composto por:</p>
                                                    <ul class="list-group list-group-flush mb-3">
                                                        <li class="list-group-item border-0 px-0"><i class="bi bi-check2-circle text-success me-2"></i><strong>CBS (Contribuição Social sobre Bens e Serviços):</strong> Federal, substitui PIS e COFINS.</li>
                                                        <li class="list-group-item border-0 px-0"><i class="bi bi-check2-circle text-success me-2"></i><strong>IBS (Imposto sobre Bens e Serviços):</strong> Estadual/Municipal, substitui ICMS e ISS.</li>
                                                    </ul>
                                                    <p>Além disso, foi criado o <strong>Imposto Seletivo (IS)</strong> para desestimular o consumo de produtos prejudiciais à saúde e ao meio ambiente.</p>
                                                    
                                                    <h4 class="fw-bold mt-4 mb-3">Quando começa a valer?</h4>
                                                    <div class="timeline border-start border-3 border-primary ps-3 ms-2">
                                                        <div class="mb-3">
                                                            <span class="badge bg-primary mb-1">2026</span>
                                                            <p class="mb-0">Início da transição com alíquotas teste (CBS 0,9% e IBS 0,1%).</p>
                                                        </div>
                                                        <div class="mb-3">
                                                            <span class="badge bg-primary mb-1">2027 - 2032</span>
                                                            <p class="mb-0">Período de transição gradual e coexistência dos sistemas.</p>
                                                        </div>
                                                        <div>
                                                            <span class="badge bg-success mb-1">2033</span>
                                                            <p class="mb-0">Conclusão da transição e substituição definitiva dos tributos antigos.</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Fato Gerador -->
                                        <div class="tab-pane fade" id="list-fator" role="tabpanel" aria-labelledby="list-fator-list">
                                            <div class="card border-0 shadow-sm rounded-4 mb-4">
                                                <div class="card-body p-4">
                                                    <h3 class="fw-bold text-primary mb-3">Fato Gerador e Incidência</h3>
                                                    <p>O fato gerador da CBS e do IBS ocorre no momento do <strong>fornecimento de bens ou serviços</strong>, mesmo em operações contínuas.</p>
                                                    <p>Para serviços de execução continuada (água, luz, telecom), o fato gerador ocorre no momento em que o pagamento se torna devido.</p>
                                                    <div class="alert alert-warning border-0 rounded-3">
                                                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                                        <strong>Atenção:</strong> Em 2026, haverá alíquotas de teste e compensação com PIS/COFINS. Contribuintes do Simples Nacional não estão sujeitos a essas alíquotas de teste.
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Base de Cálculo -->
                                        <div class="tab-pane fade" id="list-calculo" role="tabpanel" aria-labelledby="list-calculo-list">
                                            <div class="card border-0 shadow-sm rounded-4 mb-4">
                                                <div class="card-body p-4">
                                                    <h3 class="fw-bold text-primary mb-3">Base de Cálculo e Alíquotas</h3>
                                                    <p>A base de cálculo é o <strong>valor total da operação</strong>, incluindo frete, seguros, e outras despesas.</p>
                                                    <p>A reforma adota o <strong>"cálculo por fora"</strong>: o imposto não compõe sua própria base de cálculo, aumentando a transparência.</p>
                                                    
                                                    <h5 class="fw-bold mt-4">Alíquotas</h5>
                                                    <ul>
                                                        <li><strong>Alíquota de Referência:</strong> Parâmetro para manter a carga tributária equilibrada.</li>
                                                        <li><strong>Alíquota Padrão:</strong> Definida por cada ente (União, Estados, Municípios). O IBS será a soma das alíquotas do destino.</li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Créditos -->
                                        <div class="tab-pane fade" id="list-credito" role="tabpanel" aria-labelledby="list-credito-list">
                                            <div class="card border-0 shadow-sm rounded-4 mb-4">
                                                <div class="card-body p-4">
                                                    <h3 class="fw-bold text-primary mb-3">Novo Modelo de Créditos</h3>
                                                    <p>Apropriação de créditos condicionada ao <strong>efetivo pagamento</strong> do tributo na etapa anterior (check-in financeiro).</p>
                                                    <p>Sistema não cumulativo amplo: permite compensar o imposto devido com o montante cobrado nas operações anteriores.</p>
                                                    <div class="row g-3 mt-2">
                                                        <div class="col-md-6">
                                                            <div class="p-3 bg-light rounded-3 h-100">
                                                                <h6 class="fw-bold"><i class="bi bi-arrow-return-left me-2"></i>Ressarcimento</h6>
                                                                <p class="small mb-0">Devolução de saldos credores acumulados para as empresas.</p>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="p-3 bg-light rounded-3 h-100">
                                                                <h6 class="fw-bold"><i class="bi bi-people me-2"></i>Cashback</h6>
                                                                <p class="small mb-0">Devolução de parte do imposto para famílias de baixa renda.</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Split Payment -->
                                        <div class="tab-pane fade" id="list-split" role="tabpanel" aria-labelledby="list-split-list">
                                            <div class="card border-0 shadow-sm rounded-4 mb-4">
                                                <div class="card-body p-4">
                                                    <h3 class="fw-bold text-primary mb-3">Split Payment</h3>
                                                    <p>Mecanismo automático de recolhimento. No momento do pagamento eletrônico, o valor do imposto é separado e enviado diretamente ao Fisco/Comitê Gestor.</p>
                                                    <p>Isso reduz a sonegação e a inadimplência, além de simplificar o compliance para o vendedor, que recebe o valor líquido.</p>
                                                    
                                                    <hr>
                                                    
                                                    <h4 class="fw-bold text-primary mb-3">Imposto Seletivo (IS)</h4>
                                                    <p>Incide sobre bens prejudiciais à saúde/meio ambiente (fumo, álcool, bebidas açucaradas, veículos poluentes, bens minerais).</p>
                                                    <p>Monofásico (incide uma única vez) e não gera crédito.</p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Regimes Específicos -->
                                        <div class="tab-pane fade" id="list-regimes" role="tabpanel" aria-labelledby="list-regimes-list">
                                            <div class="card border-0 shadow-sm rounded-4 mb-4">
                                                <div class="card-body p-4">
                                                    <h3 class="fw-bold text-primary mb-3">Regimes Diferenciados e Específicos</h3>
                                                    <p>A LC 214/2025 prevê tratamentos especiais para certos setores:</p>
                                                    
                                                    <h5 class="fw-bold mt-3">Regimes Específicos</h5>
                                                    <p class="small text-muted">Adaptados a particularidades de setores como:</p>
                                                    <div class="d-flex flex-wrap gap-2 mb-3">
                                                        <span class="badge bg-light text-dark border">Combustíveis</span>
                                                        <span class="badge bg-light text-dark border">Serviços Financeiros</span>
                                                        <span class="badge bg-light text-dark border">Planos de Saúde</span>
                                                        <span class="badge bg-light text-dark border">Imóveis</span>
                                                        <span class="badge bg-light text-dark border">Cooperativas</span>
                                                        <span class="badge bg-light text-dark border">Hotelaria/Restaurantes</span>
                                                    </div>

                                                    <h5 class="fw-bold mt-3">Regimes Diferenciados</h5>
                                                    <p>Reduções de alíquota (30%, 60%, 100%) para serviços essenciais, educação, saúde, profissionais liberais, etc.</p>
                                                    <p><strong>Cesta Básica Nacional:</strong> Redução a zero das alíquotas para produtos da cesta básica.</p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- DFes -->
                                        <div class="tab-pane fade" id="list-dfes" role="tabpanel" aria-labelledby="list-dfes-list">
                                            <div class="card border-0 shadow-sm rounded-4 mb-4">
                                                <div class="card-body p-4">
                                                    <h3 class="fw-bold text-primary mb-3">Documentos Fiscais Eletrônicos (DFes)</h3>
                                                    <p>Os DFes são centrais na reforma. Novos campos e regras de validação estão sendo implementados para suportar CBS, IBS e IS.</p>
                                                    <p>Notas Técnicas estão sendo publicadas (mesmo antes da regulamentação final) para permitir adaptação dos sistemas.</p>
                                                    <div class="d-grid gap-2 d-md-block mt-3">
                                                        <a href="https://www.nfe.fazenda.gov.br/portal/exibirArquivo.aspx?conteudo=AklZnck3o6I=" target="_blank" class="btn btn-outline-primary">
                                                            <i class="bi bi-box-arrow-up-right me-2"></i>NT 2025.002 (NF-e/NFC-e)
                                                        </a>
                                                        <a href="https://kb.beemore.com/dc/pt-br/domains/suporte/resources/documentacao-do-sistema/categories/fiscal" target="_blank" class="btn btn-outline-primary">
                                                            <i class="bi bi-box-arrow-up-right me-2"></i>Documentação do Sistema
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.icon-box {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.bg-success-gradient {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
}
.nav-tabs .nav-link {
    color: #6c757d;
    border: none;
    border-bottom: 3px solid transparent;
    padding: 1rem 1.5rem;
    transition: all 0.3s ease;
}
.nav-tabs .nav-link:hover {
    color: #198754;
    border-color: transparent;
    background-color: rgba(25, 135, 84, 0.05);
}
.nav-tabs .nav-link.active {
    color: #198754;
    background-color: transparent;
    border-bottom-color: #198754;
}
</style>

<!-- Modal Detalhe Classificação -->
<div class="modal fade" id="modalDetalhe" tabindex="-1" aria-labelledby="modalDetalheLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold" id="modalDetalheLabel">Detalhe</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-4">
                    <div class="col-12">
                        <div class="p-3 bg-light rounded-3">
                            <div class="row mb-2">
                                <div class="col-sm-4 text-muted fw-semibold">Situação Tributária</div>
                                <div class="col-sm-8 fw-bold" id="modal-cst"></div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-sm-4 text-muted fw-semibold">Data de publicação</div>
                                <div class="col-sm-8" id="modal-publicacao"></div>
                            </div>
                            <div class="row">
                                <div class="col-sm-4 text-muted fw-semibold">Início de Vigência</div>
                                <div class="col-sm-8" id="modal-vigencia"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <hr class="text-muted opacity-25">
                        <div class="row mb-3">
                            <div class="col-sm-4 text-muted fw-semibold">Classificação Tributária</div>
                            <div class="col-sm-8" id="modal-codigo"></div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-4 text-muted fw-semibold">Descrição Completa</div>
                            <div class="col-sm-8" id="modal-descricao"></div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-4 text-muted fw-semibold">Data de publicação</div>
                            <div class="col-sm-8" id="modal-publicacao-2"></div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-4 text-muted fw-semibold">Início de Vigência</div>
                            <div class="col-sm-8" id="modal-vigencia-2"></div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4 text-muted fw-semibold">DFes Relacionados</div>
                            <div class="col-sm-8" id="modal-dfes"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var modalDetalhe = document.getElementById('modalDetalhe');
    modalDetalhe.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        
        var cst = button.getAttribute('data-cst');
        var codigo = button.getAttribute('data-codigo');
        var descricao = button.getAttribute('data-descricao');
        var publicacao = button.getAttribute('data-publicacao');
        var vigencia = button.getAttribute('data-vigencia');
        var dfes = button.getAttribute('data-dfes');
        
        modalDetalhe.querySelector('#modal-cst').textContent = cst;
        modalDetalhe.querySelector('#modal-codigo').textContent = codigo + ' - ' + descricao;
        modalDetalhe.querySelector('#modal-descricao').textContent = descricao;
        modalDetalhe.querySelector('#modal-publicacao').textContent = publicacao;
        modalDetalhe.querySelector('#modal-vigencia').textContent = vigencia;
        modalDetalhe.querySelector('#modal-publicacao-2').textContent = publicacao;
        modalDetalhe.querySelector('#modal-vigencia-2').textContent = vigencia;
        modalDetalhe.querySelector('#modal-dfes').textContent = dfes;
    });
});
</script>

<?php include 'includes/footer.php'; ?>
