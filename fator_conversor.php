<?php
include 'includes/header.php';
require_once 'includes/portal_auth.php';

// Standard Units
$commonUnits = ['UN', 'CX', 'KG', 'M', 'L', 'PCT'];
$allUnits = [
    'UN' => 'Unidade', 'CX' => 'Caixa', 'KG' => 'Quilo', 'M' => 'Metro', 'L' => 'Litro',
    'PCT' => 'Pacote', 'FD' => 'Fardo', 'G' => 'Grama', 'ML' => 'Mililitro', 
    'CM' => 'Centímetro', 'M2' => 'Metro Quad.', 'M3' => 'Metro Cúb.', 
    'SC' => 'Saco', 'TON' => 'Tonelada', 'PC' => 'Peça', 'RL' => 'Rolo'
];
?>

<div class="container-fluid px-5 py-5">
    
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-5">
        <div>
            <h1 class="fw-bold text-dark display-6 mb-1" style="font-family: 'Outfit', sans-serif;">
                <i class="bi bi-calculator-fill text-primary me-2"></i>Fator de Conversão
            </h1>
            <p class="text-muted small mb-0 fw-medium letter-spacing-1">
                FERRAMENTA DE PRECISÃO PARA CADASTRO DE ESTOQUE
            </p>
        </div>
        <a href="index.php" class="btn btn-light rounded-pill px-4 border shadow-sm fw-bold small text-secondary">
            <i class="bi bi-x-lg me-2"></i>Fechar
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            
            <div class="card border-0 shadow-lg rounded-5 overflow-hidden">
                <div class="card-body p-5 bg-white">
                    <form id="ux-form">
                        
                        <!-- STEP 1: UNITS -->
                        <div class="step-container mb-5">
                            <div class="d-flex align-items-center mb-3">
                                <div class="step-badge me-3">1</div>
                                <h6 class="fw-bold text-uppercase text-dark mb-0 letter-spacing-1">Definir Unidades</h6>
                            </div>
                            
                            <div class="row g-4 ps-5">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-primary small text-uppercase mb-2"><i class="bi bi-cart-fill me-1"></i> Compra (XML)</label>
                                    <div class="unit-selector d-flex flex-wrap gap-2" id="buy-selector">
                                        <?php foreach($commonUnits as $u): ?>
                                            <input type="radio" class="btn-check" name="buyUnit" id="buy-<?php echo $u; ?>" value="<?php echo $u; ?>" <?php echo $u === 'UN' ? 'checked' : ''; ?> onchange="updateUI()">
                                            <label class="btn btn-outline-light text-dark border fw-bold rounded-pill btn-sm px-3 unit-btn" for="buy-<?php echo $u; ?>"><?php echo $u; ?></label>
                                        <?php endforeach; ?>
                                        <select class="form-select form-select-sm rounded-pill d-inline-block w-auto fw-bold" id="buy-more" onchange="selectMore('buy', this)">
                                            <option value="" selected>+ Outros</option>
                                            <?php foreach($allUnits as $k => $v): if(!in_array($k, $commonUnits)): ?><option value="<?php echo $k; ?>"><?php echo $k; ?></option><?php endif; endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-warning small text-uppercase mb-2"><i class="bi bi-box-seam-fill me-1"></i> Estoque (Base)</label>
                                    <div class="unit-selector d-flex flex-wrap gap-2" id="stock-selector">
                                        <?php foreach($commonUnits as $u): ?>
                                            <input type="radio" class="btn-check" name="stockUnit" id="stock-<?php echo $u; ?>" value="<?php echo $u; ?>" <?php echo $u === 'CX' ? 'checked' : ''; ?> onchange="updateUI()">
                                            <label class="btn btn-outline-light text-dark border fw-bold rounded-pill btn-sm px-3 unit-btn-stock" for="stock-<?php echo $u; ?>"><?php echo $u; ?></label>
                                        <?php endforeach; ?>
                                        <select class="form-select form-select-sm rounded-pill d-inline-block w-auto fw-bold" id="stock-more" onchange="selectMore('stock', this)">
                                            <option value="" selected>+ Outros</option>
                                            <?php foreach($allUnits as $k => $v): if(!in_array($k, $commonUnits)): ?><option value="<?php echo $k; ?>"><?php echo $k; ?></option><?php endif; endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- STEP 2: LOGIC & QTY -->
                        <div class="step-container mb-5">
                            <div class="d-flex align-items-center mb-3">
                                <div class="step-badge me-3">2</div>
                                <h6 class="fw-bold text-uppercase text-dark mb-0 letter-spacing-1">Definir Relação</h6>
                            </div>

                            <div class="ps-5">
                                <div class="row g-4">
                                    <!-- Logic Selection -->
                                    <div class="col-12">
                                        <div class="d-flex flex-column gap-3">
                                            
                                            <!-- Logic A -->
                                            <label class="logic-card d-flex align-items-center p-3 border rounded-4 cursor-pointer position-relative" for="logic-a">
                                                <div class="position-absolute start-0 top-0 h-100 logic-indicator" style="width: 4px; border-radius: 4px 0 0 4px; background: transparent;"></div>
                                                <div class="me-3"><input class="form-check-input fs-4" type="radio" name="logicType" id="logic-a" value="A" checked onchange="updateUI()"></div>
                                                <div class="w-100">
                                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                                        <input type="number" class="form-control form-control-lg fw-bold border text-primary text-center px-1 input-highlight" style="width: 80px;" id="val-a-1" value="1" oninput="calculate()">
                                                        <span class="badge bg-primary text-white buy-badge fs-6">UN</span>
                                                        <span class="fw-bold text-muted small mx-1">CONTÉM</span>
                                                        <input type="number" class="form-control form-control-lg fw-bold border text-warning text-center px-1 input-highlight" style="width: 80px;" id="val-a-2" value="12" oninput="calculate()">
                                                        <span class="badge bg-warning text-dark stock-badge fs-6">CX</span>
                                                    </div>
                                                    <small class="text-muted d-block mt-2 ms-1">Use quando a compra (Ex: Caixa) é MAIOR que o estoque (Ex: Unidade).</small>
                                                </div>
                                            </label>

                                            <!-- Logic B -->
                                            <label class="logic-card d-flex align-items-center p-3 border rounded-4 cursor-pointer position-relative" for="logic-b">
                                                 <div class="position-absolute start-0 top-0 h-100 logic-indicator" style="width: 4px; border-radius: 4px 0 0 4px; background: transparent;"></div>
                                                <div class="me-3"><input class="form-check-input fs-4" type="radio" name="logicType" id="logic-b" value="B" onchange="updateUI()"></div>
                                                <div class="w-100">
                                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                                        <input type="number" class="form-control form-control-lg fw-bold border text-warning text-center px-1 input-highlight" style="width: 80px;" id="val-b-1" value="1" oninput="calculate()">
                                                        <span class="badge bg-warning text-dark stock-badge fs-6">CX</span>
                                                        <span class="fw-bold text-muted small mx-1">CONTÉM</span>
                                                        <input type="number" class="form-control form-control-lg fw-bold border text-primary text-center px-1 input-highlight" style="width: 80px;" id="val-b-2" value="12" oninput="calculate()">
                                                        <span class="badge bg-primary text-white buy-badge fs-6">UN</span>
                                                    </div>
                                                    <small class="text-muted d-block mt-2 ms-1">Use quando o estoque (Ex: Quilo) é MAIOR que a compra (Ex: Grama).</small>
                                                </div>
                                            </label>

                                        </div>
                                    </div>
                                    
                                    <!-- Validation / Simulation Inline -->
                                    <div class="col-12 mt-2">
                                        <div class="bg-light p-3 rounded-4 border">
                                            <div class="row align-items-end g-3">
                                                <div class="col-md-5">
                                                    <label class="small text-muted fw-bold d-block mb-1">PROVA REAL: Qtd. Nota Fiscal</label>
                                                    <div class="input-group">
                                                        <input type="number" class="form-control fw-bold text-primary bg-white" id="sim-buy-qty" value="10" oninput="calculate()">
                                                        <span class="input-group-text bg-white fw-bold text-primary buy-badge-text">UN</span>
                                                    </div>
                                                </div>
                                                <div class="col-md-2 text-center d-none d-md-block">
                                                    <i class="bi bi-arrow-right fs-4 text-muted"></i>
                                                </div>
                                                <div class="col-md-5">
                                                    <label class="small text-muted fw-bold d-block mb-1">Entra no Estoque</label>
                                                    <div class="bg-warning bg-opacity-10 text-dark fw-bold px-3 py-2 rounded-3 text-center border border-warning border-opacity-25">
                                                        <span id="sim-stock-res" class="fs-5">--</span> <small class="stock-badge-text opacity-75">CX</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <!-- STEP 3: RESULT -->
                        <div class="step-container mb-5">
                            <div class="d-flex align-items-center mb-3">
                                <div class="step-badge me-3 bg-success border-success text-white">3</div>
                                <h6 class="fw-bold text-uppercase text-dark mb-0 letter-spacing-1">Resultado Final</h6>
                            </div>
                            
                            <div class="ps-5">
                                <div class="position-relative">
                                    <div class="bg-white rounded-5 border border-success border-2 shadow-sm overflow-hidden text-center p-5">
                                        
                                        <!-- Decorative Background -->
                                        <div class="position-absolute top-0 end-0 p-3 opacity-10">
                                            <i class="bi bi-calculator text-success" style="font-size: 5rem; opacity: 0.1;"></i>
                                        </div>

                                        <p class="text-secondary fw-bold text-uppercase small letter-spacing-1 mb-3">
                                            Valor para o campo <span class="text-success">"Fator de Conversão"</span>
                                        </p>
                                        
                                        <div class="d-flex justify-content-center align-items-center mb-4">
                                            <h1 class="display-2 fw-bold text-success mb-0 me-3" id="final-factor">--</h1>
                                        </div>

                                        <button type="button" class="btn btn-success btn-lg rounded-pill shadow-sm px-5 fw-bold" onclick="copyValue()">
                                            <i class="bi bi-clipboard-check me-2"></i> Copiar Valor
                                        </button>

                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- HOW IT WORKS (STATIC EXAMPLES) -->
                        <div class="ps-5 pt-4 border-top">
                            <h6 class="fw-bold text-secondary text-uppercase small mb-3"><i class="bi bi-info-circle me-2"></i>Entenda o Cálculo (Exemplos Fixos)</h6>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="p-3 bg-light rounded-3 border h-100">
                                        <div class="fw-bold small text-dark mb-1">Caixa p/ Unidade</div>
                                        <div class="text-muted small lh-sm">
                                            1 CX tem 12 UN.<br>
                                            Fator = Qtd. Estoque / Qtd. Compra<br>
                                            Fator = 12 / 1 = <strong>12</strong>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="p-3 bg-light rounded-3 border h-100">
                                        <div class="fw-bold small text-dark mb-1">Unidade p/ Caixa</div>
                                        <div class="text-muted small lh-sm">
                                            12 UN formam 1 CX.<br>
                                            Fator = Qtd. Estoque / Qtd. Compra<br>
                                            Fator = 1 / 12 = <strong>0.083333</strong>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="p-3 bg-light rounded-3 border h-100">
                                        <div class="fw-bold small text-dark mb-1">Rolo p/ Metros</div>
                                        <div class="text-muted small lh-sm">
                                            1 RL tem 50 M.<br>
                                            Fator = 50 / 1 = <strong>50</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
            
        </div>
    </div>
</div>

<style>
    .step-badge {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: #fff;
        border: 2px solid #e2e8f0;
        color: #64748b;
        font-weight: 800;
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: 'Outfit', sans-serif;
        font-size: 0.9rem;
    }
    .letter-spacing-1 { letter-spacing: 0.5px; }
    
    .logic-card {
        background: white;
        transition: all 0.2s ease;
        opacity: 0.7;
    }
    
    /* Interactive States */
    .unit-selector input:checked + .unit-btn {
        background-color: var(--bs-primary) !important;
        color: white !important;
        border-color: var(--bs-primary) !important;
        box-shadow: 0 4px 6px -1px rgba(13, 110, 253, 0.3);
    }
    .unit-selector input:checked + .unit-btn-stock {
        background-color: var(--bs-warning) !important;
        color: #000 !important;
        border-color: var(--bs-warning) !important;
        box-shadow: 0 4px 6px -1px rgba(255, 193, 7, 0.3);
    }

    .form-check-input:checked ~ div .d-flex { opacity: 1; }
    .logic-card input:checked { background-color: #0f172a; border-color: #0f172a; }

    .logic-card:has(input:checked) {
        border-color: var(--bs-primary) !important;
        opacity: 1;
        background-color: #f8fbff;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }
    .logic-card:has(input:checked) .logic-indicator { background-color: var(--bs-primary) !important; }

    .logic-card:has(input:checked) .input-highlight {
        background-color: #fff !important;
        border-color: #86b7fe !important;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
    }
    
    input[type=number]::-webkit-inner-spin-button, input[type=number]::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
    
    .opacity-10 { opacity: 0.1 !important; }
</style>

<script>
    function updateUI() {
        let buyElement = document.querySelector('input[name="buyUnit"]:checked') || document.getElementById('buy-more');
        let stockElement = document.querySelector('input[name="stockUnit"]:checked') || document.getElementById('stock-more');
        
        let buyUnit = buyElement.value || 'UN';
        let stockUnit = stockElement.value || 'CX';

        document.querySelectorAll('.buy-badge').forEach(el => el.innerText = buyUnit);
        document.querySelectorAll('.buy-badge-text').forEach(el => el.innerText = buyUnit);
        document.querySelectorAll('.stock-badge').forEach(el => el.innerText = stockUnit);
        document.querySelectorAll('.stock-badge-text').forEach(el => el.innerText = stockUnit);

        calculate();
    }

    function calculate() {
        let logicType = document.querySelector('input[name="logicType"]:checked').value;
        let finalFactorInfo = document.getElementById('final-factor');
        let factor = 0;

        if (logicType === 'A') {
            let qty1 = parseFloat(document.getElementById('val-a-1').value) || 1;
            let qty2 = parseFloat(document.getElementById('val-a-2').value) || 1;
            if(qty1 > 0) factor = qty2 / qty1;
        } else {
            let qty1 = parseFloat(document.getElementById('val-b-1').value) || 1;
            let qty2 = parseFloat(document.getElementById('val-b-2').value) || 1;
            if(qty2 > 0) factor = qty1 / qty2;
        }

        let factorDisplay = parseFloat(factor.toFixed(6));
        finalFactorInfo.innerText = factorDisplay;

        // Simulation
        let simBuyQty = parseFloat(document.getElementById('sim-buy-qty').value) || 0;
        let simStockRes = simBuyQty * factor;
        document.getElementById('sim-stock-res').innerText = parseFloat(simStockRes.toFixed(4));
    }

    function selectMore(type, select) {
        document.querySelectorAll(`input[name="${type}Unit"]`).forEach(el => el.checked = false);
        updateUI();
    }
    
    function copyValue() {
        let val = document.getElementById('final-factor').innerText;
        navigator.clipboard.writeText(val);
        alert('Copiado: ' + val);
    }

    document.addEventListener('DOMContentLoaded', () => {
        updateUI();
    });
</script>

<?php include 'includes/footer.php'; ?>