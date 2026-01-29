<?php include 'includes/header.php'; ?>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h1 class="h2 fw-bold text-dark mb-1">Calculadoras de Porcentagem</h1>
            <p class="text-muted mb-0">Ferramentas práticas para cálculos percentuais diversos.</p>
        </div>
        <a href="index.php" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-2"></i>Voltar
        </a>
    </div>

    <div class="row g-4">
        <!-- 1. Quanto é X% de Y? -->
        <div class="col-md-6 col-lg-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <h6 class="fw-bold text-primary mb-3">Quanto é % de um valor?</h6>
                    <div class="mb-3">
                        <label class="form-label small text-muted">Quanto é</label>
                        <div class="input-group input-group-sm">
                            <input type="number" class="form-control" id="calc1_percent" placeholder="Ex: 10">
                            <span class="input-group-text">%</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-muted">de</label>
                        <input type="number" class="form-control form-control-sm" id="calc1_value" placeholder="Ex: 100">
                    </div>
                    <button class="btn btn-primary btn-sm w-100 mb-3" onclick="calc1()">Calcular</button>
                    <div class="p-2 bg-light rounded text-center fw-bold text-dark" id="calc1_result">-</div>
                </div>
            </div>
        </div>

        <!-- 2. O valor X é qual porcentagem de Y? -->
        <div class="col-md-6 col-lg-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <h6 class="fw-bold text-primary mb-3">O valor X é qual % de Y?</h6>
                    <div class="mb-3">
                        <label class="form-label small text-muted">O valor</label>
                        <input type="number" class="form-control form-control-sm" id="calc2_part" placeholder="Ex: 20">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-muted">é qual porcentagem de</label>
                        <input type="number" class="form-control form-control-sm" id="calc2_total" placeholder="Ex: 100">
                    </div>
                    <button class="btn btn-primary btn-sm w-100 mb-3" onclick="calc2()">Calcular</button>
                    <div class="p-2 bg-light rounded text-center fw-bold text-dark" id="calc2_result">-</div>
                </div>
            </div>
        </div>

        <!-- 3. Aumento Percentual (X para Y) -->
        <div class="col-md-6 col-lg-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <h6 class="fw-bold text-primary mb-3">Aumento Percentual</h6>
                    <div class="mb-3">
                        <label class="form-label small text-muted">Valor inicial</label>
                        <input type="number" class="form-control form-control-sm" id="calc3_initial" placeholder="Ex: 50">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-muted">Aumentou para</label>
                        <input type="number" class="form-control form-control-sm" id="calc3_final" placeholder="Ex: 75">
                    </div>
                    <button class="btn btn-primary btn-sm w-100 mb-3" onclick="calc3()">Calcular</button>
                    <div class="p-2 bg-light rounded text-center fw-bold text-dark" id="calc3_result">-</div>
                </div>
            </div>
        </div>

        <!-- 4. Diminuição Percentual (X para Y) -->
        <div class="col-md-6 col-lg-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <h6 class="fw-bold text-primary mb-3">Diminuição Percentual</h6>
                    <div class="mb-3">
                        <label class="form-label small text-muted">Valor inicial</label>
                        <input type="number" class="form-control form-control-sm" id="calc4_initial" placeholder="Ex: 100">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-muted">Diminuiu para</label>
                        <input type="number" class="form-control form-control-sm" id="calc4_final" placeholder="Ex: 80">
                    </div>
                    <button class="btn btn-primary btn-sm w-100 mb-3" onclick="calc4()">Calcular</button>
                    <div class="p-2 bg-light rounded text-center fw-bold text-dark" id="calc4_result">-</div>
                </div>
            </div>
        </div>

        <!-- 5. X sobre Y é quantos %? -->
        <div class="col-md-6 col-lg-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <h6 class="fw-bold text-primary mb-3">X sobre Y é quantos %?</h6>
                    <div class="mb-3">
                        <label class="form-label small text-muted">O valor</label>
                        <input type="number" class="form-control form-control-sm" id="calc5_part" placeholder="Ex: 30">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-muted">sobre o valor</label>
                        <input type="number" class="form-control form-control-sm" id="calc5_total" placeholder="Ex: 150">
                    </div>
                    <button class="btn btn-primary btn-sm w-100 mb-3" onclick="calc5()">Calcular</button>
                    <div class="p-2 bg-light rounded text-center fw-bold text-dark" id="calc5_result">-</div>
                </div>
            </div>
        </div>

        <!-- 6. Aumentar X em Y% -->
        <div class="col-md-6 col-lg-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <h6 class="fw-bold text-primary mb-3">Aumentar um valor em %</h6>
                    <div class="mb-3">
                        <label class="form-label small text-muted">Tenho o valor</label>
                        <input type="number" class="form-control form-control-sm" id="calc6_value" placeholder="Ex: 100">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-muted">Quero aumentar</label>
                        <div class="input-group input-group-sm">
                            <input type="number" class="form-control" id="calc6_percent" placeholder="Ex: 10">
                            <span class="input-group-text">%</span>
                        </div>
                    </div>
                    <button class="btn btn-primary btn-sm w-100 mb-3" onclick="calc6()">Calcular</button>
                    <div class="p-2 bg-light rounded text-center fw-bold text-dark" id="calc6_result">-</div>
                </div>
            </div>
        </div>

        <!-- 7. Diminuir X em Y% -->
        <div class="col-md-6 col-lg-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <h6 class="fw-bold text-primary mb-3">Diminuir um valor em %</h6>
                    <div class="mb-3">
                        <label class="form-label small text-muted">Tenho o valor</label>
                        <input type="number" class="form-control form-control-sm" id="calc7_value" placeholder="Ex: 100">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-muted">Quero diminuir</label>
                        <div class="input-group input-group-sm">
                            <input type="number" class="form-control" id="calc7_percent" placeholder="Ex: 10">
                            <span class="input-group-text">%</span>
                        </div>
                    </div>
                    <button class="btn btn-primary btn-sm w-100 mb-3" onclick="calc7()">Calcular</button>
                    <div class="p-2 bg-light rounded text-center fw-bold text-dark" id="calc7_result">-</div>
                </div>
            </div>
        </div>

        <!-- 8. Descobrir valor inicial (Aumento) -->
        <div class="col-md-6 col-lg-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <h6 class="fw-bold text-primary mb-3">Descobrir valor inicial (Aumento)</h6>
                    <div class="mb-3">
                        <label class="form-label small text-muted">Aumentou em</label>
                        <div class="input-group input-group-sm">
                            <input type="number" class="form-control" id="calc8_percent" placeholder="Ex: 20">
                            <span class="input-group-text">%</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-muted">E passou para</label>
                        <input type="number" class="form-control form-control-sm" id="calc8_final" placeholder="Ex: 120">
                    </div>
                    <button class="btn btn-primary btn-sm w-100 mb-3" onclick="calc8()">Calcular</button>
                    <div class="p-2 bg-light rounded text-center fw-bold text-dark" id="calc8_result">-</div>
                </div>
            </div>
        </div>

        <!-- 9. Descobrir valor inicial (Diminuição) -->
        <div class="col-md-6 col-lg-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <h6 class="fw-bold text-primary mb-3">Descobrir valor inicial (Diminuição)</h6>
                    <div class="mb-3">
                        <label class="form-label small text-muted">Diminuiu em</label>
                        <div class="input-group input-group-sm">
                            <input type="number" class="form-control" id="calc9_percent" placeholder="Ex: 20">
                            <span class="input-group-text">%</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-muted">E passou para</label>
                        <input type="number" class="form-control form-control-sm" id="calc9_final" placeholder="Ex: 80">
                    </div>
                    <button class="btn btn-primary btn-sm w-100 mb-3" onclick="calc9()">Calcular</button>
                    <div class="p-2 bg-light rounded text-center fw-bold text-dark" id="calc9_result">-</div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function formatNum(num) {
    return Number.isInteger(num) ? num : num.toFixed(2).replace(/\.?0+$/, '');
}

function calc1() {
    const p = parseFloat(document.getElementById('calc1_percent').value);
    const v = parseFloat(document.getElementById('calc1_value').value);
    if (isNaN(p) || isNaN(v)) return;
    const res = (p / 100) * v;
    document.getElementById('calc1_result').innerText = formatNum(res);
}

function calc2() {
    const part = parseFloat(document.getElementById('calc2_part').value);
    const total = parseFloat(document.getElementById('calc2_total').value);
    if (isNaN(part) || isNaN(total) || total === 0) return;
    const res = (part / total) * 100;
    document.getElementById('calc2_result').innerText = formatNum(res) + '%';
}

function calc3() {
    const i = parseFloat(document.getElementById('calc3_initial').value);
    const f = parseFloat(document.getElementById('calc3_final').value);
    if (isNaN(i) || isNaN(f) || i === 0) return;
    const res = ((f - i) / i) * 100;
    document.getElementById('calc3_result').innerText = formatNum(res) + '%';
}

function calc4() {
    const i = parseFloat(document.getElementById('calc4_initial').value);
    const f = parseFloat(document.getElementById('calc4_final').value);
    if (isNaN(i) || isNaN(f) || i === 0) return;
    const res = ((i - f) / i) * 100;
    document.getElementById('calc4_result').innerText = formatNum(res) + '%';
}

function calc5() {
    const part = parseFloat(document.getElementById('calc5_part').value);
    const total = parseFloat(document.getElementById('calc5_total').value);
    if (isNaN(part) || isNaN(total) || total === 0) return;
    const res = (part / total) * 100;
    document.getElementById('calc5_result').innerText = formatNum(res) + '%';
}

function calc6() {
    const v = parseFloat(document.getElementById('calc6_value').value);
    const p = parseFloat(document.getElementById('calc6_percent').value);
    if (isNaN(v) || isNaN(p)) return;
    const res = v * (1 + p / 100);
    document.getElementById('calc6_result').innerText = formatNum(res);
}

function calc7() {
    const v = parseFloat(document.getElementById('calc7_value').value);
    const p = parseFloat(document.getElementById('calc7_percent').value);
    if (isNaN(v) || isNaN(p)) return;
    const res = v * (1 - p / 100);
    document.getElementById('calc7_result').innerText = formatNum(res);
}

function calc8() {
    const p = parseFloat(document.getElementById('calc8_percent').value);
    const f = parseFloat(document.getElementById('calc8_final').value);
    if (isNaN(p) || isNaN(f)) return;
    const res = f / (1 + p / 100);
    document.getElementById('calc8_result').innerText = formatNum(res);
}

function calc9() {
    const p = parseFloat(document.getElementById('calc9_percent').value);
    const f = parseFloat(document.getElementById('calc9_final').value);
    if (isNaN(p) || isNaN(f)) return;
    const res = f / (1 - p / 100);
    document.getElementById('calc9_result').innerText = formatNum(res);
}
</script>

<?php include 'includes/footer.php'; ?>
