<?php include 'includes/header.php'; ?>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h1 class="h2 fw-bold text-dark mb-1">Pool de Conversores</h1>
            <p class="text-muted mb-0">Ferramentas úteis para cálculos e conversões do dia a dia.</p>
        </div>
        <a href="index.php" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-2"></i>Voltar
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-bottom-0 pt-4 px-4 pb-0">
            <ul class="nav nav-tabs nav-tabs-custom card-header-tabs" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-xml">
                        <i class="bi bi-file-earmark-spreadsheet me-2"></i>Conversor de Unidades NF-e
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-manual">
                        <i class="bi bi-calculator me-2"></i>Simulador Manual
                    </button>
                </li>
            </ul>
        </div>
        <div class="card-body p-4">
            <div class="tab-content">
                <!-- XML Import Helper Tab -->
                <div class="tab-pane fade show active" id="tab-xml">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h5 class="fw-bold mb-1">Auxiliar de Importação de XML (NF-e)</h5>
                            <p class="text-muted small mb-0">Calcule fatores de conversão a partir de um XML de Nota Fiscal.</p>
                        </div>
                        <div>
                            <button class="btn btn-primary btn-sm" onclick="document.getElementById('xmlFile').click()">
                                <i class="bi bi-upload me-2"></i>Carregar XML
                            </button>
                            <input type="file" id="xmlFile" accept=".xml" style="display: none;" onchange="handleXmlUpload(this)">
                        </div>
                    </div>

                    <div id="xmlEmptyState" class="text-center py-5 text-muted bg-light rounded-3 border border-dashed">
                        <i class="bi bi-cloud-upload display-4 mb-3 opacity-25"></i>
                        <p class="mb-0">Carregue um arquivo XML para visualizar os itens e calcular conversões.</p>
                    </div>

                    <div id="xmlContent" style="display: none;">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="small text-muted text-uppercase ps-3">Produto</th>
                                        <th class="small text-muted text-uppercase text-center">Qtd. Nota</th>
                                        <th class="small text-muted text-uppercase text-center">Un. Nota</th>
                                        <th class="small text-muted text-uppercase text-center" style="width: 120px;">Un. Estoque</th>
                                        <th class="small text-muted text-uppercase text-center" style="width: 140px;">Itens p/ Un. (Fator)</th>
                                        <th class="small text-muted text-uppercase text-center" style="width: 140px;">Qtd. Estoque</th>
                                        <th class="small text-muted text-uppercase text-center pe-3">Fator Sistema</th>
                                    </tr>
                                </thead>
                                <tbody id="xmlItemsTable">
                                    <!-- Items injected by JS -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Manual Simulator Tab -->
                <div class="tab-pane fade" id="tab-manual">
                    <div class="row justify-content-center">
                        <div class="col-md-8 col-lg-6">
                            <div class="text-center mb-4">
                                <h5 class="fw-bold">Conversor de Unidades (Manual)</h5>
                                <p class="text-muted small">Simule a conversão de entrada de estoque (ex: Caixas para Unidades).</p>
                            </div>

                            <div class="bg-light p-4 rounded-3 border">
                                <form id="unitConverterForm">
                                    <div class="row g-3">
                                        <div class="col-6">
                                            <label class="form-label small fw-bold text-muted">Quantidade Entrada</label>
                                            <input type="number" class="form-control" id="inputQty" value="1" min="0" step="any" oninput="calculateUnit()">
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label small fw-bold text-muted">Unidade Entrada</label>
                                            <input type="text" class="form-control" id="inputUnit" value="CX" placeholder="Ex: CX" oninput="calculateUnit()">
                                        </div>
                                        
                                        <div class="col-12">
                                            <div class="p-3 bg-white rounded-3 border d-flex align-items-center justify-content-center my-2">
                                                <div class="text-center">
                                                    <label class="form-label small fw-bold text-muted mb-1">Fator de Conversão</label>
                                                    <div class="input-group input-group-sm" style="max-width: 150px;">
                                                        <span class="input-group-text bg-light">x</span>
                                                        <input type="number" class="form-control text-center fw-bold" id="conversionFactor" value="12" min="0" step="any" oninput="calculateUnit()">
                                                    </div>
                                                    <div class="form-text small mt-1">Itens por unidade de entrada</div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12 text-center">
                                            <i class="bi bi-arrow-down fs-4 text-muted"></i>
                                        </div>

                                        <div class="col-12">
                                            <label class="form-label small fw-bold text-muted">Resultado (Estoque)</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control fw-bold text-primary fs-5" id="outputResult" readonly>
                                                <input type="text" class="form-control" id="outputUnit" value="UN" placeholder="Ex: UN" style="max-width: 80px;" oninput="calculateUnit()">
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Manual Converter Logic
function calculateUnit() {
    const qty = parseFloat(document.getElementById('inputQty').value) || 0;
    const factor = parseFloat(document.getElementById('conversionFactor').value) || 0;
    const result = qty * factor;
    const formattedResult = Number.isInteger(result) ? result : result.toFixed(4).replace(/\.?0+$/, '');
    document.getElementById('outputResult').value = formattedResult;
}

// XML Import Helper Logic
function handleXmlUpload(input) {
    const file = input.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = function(e) {
        const parser = new DOMParser();
        const xmlDoc = parser.parseFromString(e.target.result, "text/xml");
        
        const items = xmlDoc.getElementsByTagName("det");
        const tbody = document.getElementById("xmlItemsTable");
        tbody.innerHTML = "";

        if (items.length > 0) {
            document.getElementById("xmlEmptyState").style.display = "none";
            document.getElementById("xmlContent").style.display = "block";
        }

        Array.from(items).forEach((item, index) => {
            const prod = item.getElementsByTagName("prod")[0];
            const cProd = prod.getElementsByTagName("cProd")[0]?.textContent || "";
            const xProd = prod.getElementsByTagName("xProd")[0]?.textContent || "";
            const qCom = parseFloat(prod.getElementsByTagName("qCom")[0]?.textContent || 0);
            const uCom = prod.getElementsByTagName("uCom")[0]?.textContent || "";

            const row = document.createElement("tr");
            row.innerHTML = `
                <td class="ps-3">
                    <div class="fw-bold text-dark small">${cProd}</div>
                    <div class="text-muted small text-truncate" style="max-width: 300px;" title="${xProd}">${xProd}</div>
                </td>
                <td class="text-center fw-medium">${qCom}</td>
                <td class="text-center"><span class="badge bg-light text-dark border">${uCom}</span></td>
                <td>
                    <input type="text" class="form-control form-control-sm text-center" placeholder="Ex: UN">
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm text-center fw-bold text-primary" 
                           placeholder="1" value="1" min="0" step="any" 
                           id="factor_${index}" 
                           oninput="calculateFromFactor(${index}, ${qCom})">
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm text-center fw-bold text-success" 
                           value="${qCom}" min="0" step="any" 
                           id="stockQty_${index}" 
                           oninput="calculateFromStock(${index}, ${qCom})">
                </td>
                <td class="text-center pe-3">
                    <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25" id="finalFactor_${index}">1.0000</span>
                </td>
            `;
            tbody.appendChild(row);
        });
    };
    reader.readAsText(file);
}

function calculateFromFactor(index, qCom) {
    const factorInput = document.getElementById(`factor_${index}`);
    const stockInput = document.getElementById(`stockQty_${index}`);
    
    const factor = parseFloat(factorInput.value) || 0;
    const stockQty = qCom * factor;
    
    // Update Stock Qty Input
    stockInput.value = Number.isInteger(stockQty) ? stockQty : stockQty.toFixed(4).replace(/\.?0+$/, '');
    
    updateFinalFactor(index, factor);
}

function calculateFromStock(index, qCom) {
    const factorInput = document.getElementById(`factor_${index}`);
    const stockInput = document.getElementById(`stockQty_${index}`);
    
    const stockQty = parseFloat(stockInput.value) || 0;
    
    // Avoid division by zero
    if (qCom === 0) return;
    
    const factor = stockQty / qCom;
    
    // Update Factor Input
    factorInput.value = Number.isInteger(factor) ? factor : factor.toFixed(4).replace(/\.?0+$/, '');
    
    updateFinalFactor(index, factor);
}

function updateFinalFactor(index, factor) {
    document.getElementById(`finalFactor_${index}`).textContent = factor.toFixed(4);
}

// Initial calculation for manual tab
document.addEventListener('DOMContentLoaded', calculateUnit);
</script>

<?php include 'includes/footer.php'; ?>
