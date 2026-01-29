<?php
// manifestador.php
$pageTitle = "Manifestador de Documentos";
include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
            <div class="position-sticky pt-3">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link active" href="#dashboard" data-bs-toggle="tab">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#empresas" data-bs-toggle="tab">
                            <i class="bi bi-building"></i> Empresas
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#consulta-lote" data-bs-toggle="tab">
                            <i class="bi bi-cloud-download"></i> Consulta em Lote
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#gerenciamento" data-bs-toggle="tab">
                            <i class="bi bi-table"></i> Gerenciamento
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Manifestador de Documentos</h1>
                <a href="index.php" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left"></i> Voltar
                </a>
            </div>

            <div class="tab-content">
                <!-- Dashboard Tab -->
                <div class="tab-pane fade show active" id="dashboard">
                    <div class="alert alert-info">
                        Bem-vindo ao Manifestador Web. Selecione uma opção no menu lateral.
                    </div>
                </div>

                <!-- Empresas Tab -->
                <div class="tab-pane fade" id="empresas">
                    <h3>Gerenciar Empresas</h3>
                    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#modalEmpresa">
                        <i class="bi bi-plus-circle"></i> Nova Empresa
                    </button>
                    <div id="lista-empresas" class="row">
                        <!-- Lista de empresas será carregada via AJAX -->
                    </div>
                </div>

                <!-- Consulta Lote Tab -->
                <div class="tab-pane fade" id="consulta-lote">
                    <h3>Consulta e Download em Lote</h3>
                    <div class="card">
                        <div class="card-body">
                            <form id="form-consulta-lote">
                                <div class="mb-3">
                                    <label class="form-label">Selecione a Empresa:</label>
                                    <select class="form-select" id="select-empresa-lote"></select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Certificado Digital (.pfx):</label>
                                    <input type="file" class="form-control" id="cert-lote" accept=".pfx" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Senha do Certificado:</label>
                                    <input type="password" class="form-control" id="senha-cert-lote" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Último NSU (0 para iniciar do zero):</label>
                                    <input type="number" class="form-control" id="nsu-lote" value="0">
                                </div>
                                <button type="submit" class="btn btn-success" id="btn-iniciar-lote">
                                    <i class="bi bi-play-fill"></i> Iniciar Download
                                </button>
                                <button type="button" class="btn btn-danger d-none" id="btn-parar-lote">
                                    <i class="bi bi-stop-fill"></i> Parar
                                </button>
                            </form>
                            <div class="mt-3">
                                <label>Log de Processamento:</label>
                                <textarea class="form-control" id="log-lote" rows="10" readonly></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Gerenciamento Tab -->
                <div class="tab-pane fade" id="gerenciamento">
                    <h3>Documentos Baixados</h3>
                    <div class="mb-3">
                        <select class="form-select w-auto d-inline-block" id="select-empresa-gerenciamento"></select>
                        <button class="btn btn-secondary" onclick="carregarDocumentos()">Atualizar</button>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped table-sm" id="tabela-docs">
                            <thead>
                                <tr>
                                    <th>NSU</th>
                                    <th>Tipo</th>
                                    <th>Chave</th>
                                    <th>Emissão</th>
                                    <th>Valor</th>
                                    <th>Emitente</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Modal Empresa -->
<div class="modal fade" id="modalEmpresa" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nova Empresa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="form-empresa">
                    <div class="mb-3">
                        <label class="form-label">Razão Social</label>
                        <input type="text" class="form-control" name="razao_social" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">CNPJ</label>
                        <input type="text" class="form-control" name="cnpj" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">UF</label>
                        <input type="text" class="form-control" name="uf" required maxlength="2">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="salvarEmpresa()">Salvar</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Lógica JS básica para navegação e carregamento (placeholder)
document.addEventListener('DOMContentLoaded', function() {
    carregarEmpresas();
});

function carregarEmpresas() {
    fetch('api_manifestador.php?action=list_empresas')
        .then(response => response.json())
        .then(data => {
            const lista = document.getElementById('lista-empresas');
            const selectLote = document.getElementById('select-empresa-lote');
            const selectGer = document.getElementById('select-empresa-gerenciamento');
            
            lista.innerHTML = '';
            selectLote.innerHTML = '';
            selectGer.innerHTML = '';

            data.forEach(empresa => {
                // Card na lista
                lista.innerHTML += `
                    <div class="col-md-4 mb-3">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">${empresa.razao_social}</h5>
                                <h6 class="card-subtitle mb-2 text-muted">${empresa.cnpj} - ${empresa.uf}</h6>
                            </div>
                        </div>
                    </div>
                `;
                
                // Opções nos selects
                const option = `<option value="${empresa.id}">${empresa.razao_social}</option>`;
                selectLote.innerHTML += option;
                selectGer.innerHTML += option;
            });
        });
}

function salvarEmpresa() {
    const form = document.getElementById('form-empresa');
    const formData = new FormData(form);
    const data = Object.fromEntries(formData.entries());

    fetch('api_manifestador.php?action=save_empresa', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            alert('Empresa salva com sucesso!');
            bootstrap.Modal.getInstance(document.getElementById('modalEmpresa')).hide();
            carregarEmpresas();
        } else {
            alert('Erro ao salvar empresa: ' + (result.error || 'Erro desconhecido'));
        }
    });
}

function carregarDocumentos() {
    const empresaId = document.getElementById('select-empresa-gerenciamento').value;
    fetch(`api_manifestador.php?action=list_docs&empresa_id=${empresaId}`)
        .then(response => response.json())
        .then(data => {
            const tbody = document.querySelector('#tabela-docs tbody');
            tbody.innerHTML = '';
            data.forEach(doc => {
                tbody.innerHTML += `
                    <tr>
                        <td>${doc.nsu}</td>
                        <td>${doc.tipo}</td>
                        <td>${doc.chave || ''}</td>
                        <td>${doc.data_emissao || ''}</td>
                        <td>${doc.valor || ''}</td>
                        <td>${doc.emitente_nome || ''}</td>
                        <td><button class="btn btn-sm btn-info">Ver XML</button></td>
                    </tr>
                `;
            });
        });
}

// Lógica de Consulta em Lote
document.getElementById('form-consulta-lote').addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = document.getElementById('btn-iniciar-lote');
    const log = document.getElementById('log-lote');
    const formData = new FormData();
    
    formData.append('empresa_id', document.getElementById('select-empresa-lote').value);
    formData.append('cert', document.getElementById('cert-lote').files[0]);
    formData.append('senha', document.getElementById('senha-cert-lote').value);
    formData.append('nsu', document.getElementById('nsu-lote').value);

    btn.disabled = true;
    log.value += "Iniciando consulta...\n";

    fetch('api_manifestador.php?action=process_lote', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(result => {
        if (result.error) {
            log.value += "ERRO: " + result.error + "\n";
        } else {
            log.value += `Status: ${result.status} - ${result.motivo}\n`;
            if (result.docs && result.docs.length > 0) {
                log.value += `Documentos retornados: ${result.docs.length}\n`;
                document.getElementById('nsu-lote').value = result.ultNSU;
            } else {
                log.value += "Nenhum documento novo.\n";
            }
        }
        btn.disabled = false;
    })
    .catch(err => {
        log.value += "Erro na requisição: " + err + "\n";
        btn.disabled = false;
    });
});
</script>
</body>
</html>
