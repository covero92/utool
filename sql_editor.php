<?php
include 'includes/header.php';
session_start();
?>
</div> <!-- Close Header Container -->
<!-- Bootstrap Bundle JS (includes Popper) - Ensure it's loaded -->
<!-- Bootstrap Bundle moved to bottom -->

<!-- Ace Editor -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.4.12/ace.js" type="text/javascript" charset="utf-8"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.4.12/ext-language_tools.js"></script>

<style>
    /* Custom SQL Editor Styles */
    :root {
        --color-panel-bg: #ffffff;
        --color-border-contrast: #cbd5e1;
    }

    .glass-panel {
        background: var(--color-panel-bg) !important;
        border: 1px solid var(--color-border-contrast) !important;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }

    #editor-container {
        height: 300px;
        border-radius: 8px;
        border: 1px solid #94a3b8; /* Darker border for editor */
    }
    
    #results-container {
        height: 400px;
        overflow: auto;
        border-radius: 8px;
        background: #fff;
        border-top: 1px solid var(--color-border-contrast);
    }

    /* Form controls contrast */
    .form-control, .form-select {
        border-color: #cbd5e1 !important;
        background-color: #f8fafc !important;
    }
    .form-control:focus, .form-select:focus {
        border-color: #3b82f6 !important;
        background-color: #fff !important;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2) !important;
    }

    .table-result th {
        position: sticky;
        top: 0;
        background: #f8f9fa;
        z-index: 10;
        font-size: 0.8rem;
        padding: 8px;
    }

    .table-result td {
        font-size: 0.8rem;
        padding: 4px 8px;
        white-space: nowrap;
        max-width: 300px;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    .sidebar-list {
        max-height: calc(100vh - 200px);
        overflow-y: auto;
    }
    
    .db-item, .table-item {
        cursor: pointer;
        padding: 4px 8px;
        border-radius: 4px;
        transition: background 0.2s;
        font-size: 0.85rem;
    }
    
    .db-item:hover, .table-item:hover {
        background: rgba(59, 130, 246, 0.1);
        color: var(--color-accent);
    }
    
    .db-item:hover, .table-item:hover {
        background: rgba(59, 130, 246, 0.1);
        color: var(--color-accent);
    }
    
    .table-icon { font-size: 0.8rem; margin-right: 6px; color: #64748b; }
    .view-icon { font-size: 0.8rem; margin-right: 6px; color: #059669; }
    .info-icon { font-size: 0.8rem; margin-left: auto; color: #94a3b8; opacity: 0.5; }
    .table-item:hover .info-icon { opacity: 1; color: var(--color-accent); }

    /* Loading Overlay */
    #loading-overlay {
        position: fixed; top:0; left:0; width:100%; height:100%;
        background: rgba(255,255,255,0.7);
        z-index: 9999;
        display: none;
        align-items: center;
        justify-content: center;
        backdrop-filter: blur(2px);
    }
</style>

<div id="loading-overlay">
    <div class="spinner-border text-primary" role="status"></div>
</div>

<div class="container-fluid px-4 pt-3">
    <!-- Header -->
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center gap-3">
            <a href="index.php" class="btn btn-light border btn-sm rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" title="Voltar">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div>
                <h5 class="fw-bold mb-0 text-dark">Editor SQL Postgres</h5>
                <div class="small text-muted d-flex align-items-center" id="connection-status">
                     <span class="text-danger"><i class="bi bi-circle-fill small me-1" style="font-size: 0.6em;"></i>Desconectado</span>
                </div>
            </div>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-sm btn-primary shadow-sm" onclick="showConnectModal()">
                <i class="bi bi-plug-fill me-1"></i> Nova Conexão
            </button>
             <button class="btn btn-sm btn-white border shadow-sm text-danger" onclick="disconnect()">
                <i class="bi bi-power me-1"></i> Desconectar
            </button>
        </div>
    </div>

    <div class="row g-3">
        <!-- Sidebar: Object Browser -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 glass-panel">
                <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold">Navegador</h6>
                    <button class="btn btn-sm btn-link text-decoration-none p-0" onclick="refreshMetadata()"><i class="bi bi-arrow-clockwise"></i></button>
                </div>
                <div class="card-body p-2 d-flex flex-column gap-3">
                    <!-- Database Select -->
                    <div>
                        <label class="small text-muted fw-bold mb-1">Database</label>
                        <select id="db-select" class="form-select form-select-sm" onchange="changeDatabase(this.value)">
                            <option value="">Selecione...</option>
                        </select>
                    </div>
                    
                    <!-- Table List -->
                    <div class="flex-grow-1">
                         <label class="small text-muted fw-bold mb-1">Tabelas / Views</label>
                         <input type="text" class="form-control form-control-sm mb-2" placeholder="Filtrar..." onkeyup="filterTables(this.value)">
                         <div id="table-list" class="sidebar-list">
                             <div class="text-muted small text-center mt-4">Conecte-se para ver tabelas.</div>
                         </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-md-9">
            <!-- Query Editor -->
            <div class="card border-0 shadow-sm mb-3 glass-panel">
                <div class="card-body p-2">
                    <div id="editor-container">SELECT * FROM information_schema.tables LIMIT 10;</div>
                    <div class="d-flex justify-content-between align-items-center mt-2">
                        <div class="small text-muted">Ctrl+Enter para executar</div>
                        <div class="d-flex gap-2">
                            <input type="file" id="file-input" accept=".sql" style="display: none;" onchange="loadSqlFile(this)">
                            <button class="btn btn-sm btn-white text-secondary" onclick="document.getElementById('file-input').click()">
                                <i class="bi bi-folder2-open me-1"></i> Carregar
                            </button>
                            <button class="btn btn-sm btn-white text-secondary" onclick="showHistory()">
                                <i class="bi bi-clock-history me-1"></i> Histórico
                            </button>
                            <button class="btn btn-sm btn-white text-secondary" onclick="editor.setValue(''); editor.focus();">Limpar</button>
                            <button class="btn btn-sm btn-primary px-4 fw-bold" onclick="executeQuery()">
                                <i class="bi bi-play-fill me-1"></i> Executar
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Results -->
            <div class="card border-0 shadow-sm glass-panel">
                 <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center py-2">
                    <h6 class="mb-0 fw-bold small text-uppercase text-muted"><i class="bi bi-table me-2"></i>Resultados</h6>
                    <div class="d-flex align-items-center gap-2">
                         <div id="pagination-controls" class="d-none">
                            <button class="btn btn-sm btn-light py-0 px-2 border" onclick="changePage(-1)"><i class="bi bi-chevron-left"></i></button>
                            <span class="small mx-2 text-muted" id="page-display">1/1</span>
                            <button class="btn btn-sm btn-light py-0 px-2 border" onclick="changePage(1)"><i class="bi bi-chevron-right"></i></button>
                        </div>
                        <button class="btn btn-sm btn-white text-success border py-0" onclick="exportCsv()" id="btn-export" disabled>
                            <i class="bi bi-file-earmark-spreadsheet me-1"></i> CSV
                        </button>
                        <span class="badge bg-light text-secondary border" id="result-stats">0 linhas</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div id="results-container">
                        <div class="d-flex flex-column align-items-center justify-content-center h-100 text-muted opacity-50">
                            <i class="bi bi-terminal fs-1 mb-2"></i>
                            <p class="small">Execute uma query para ver os resultados.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Connection Modal -->
<div class="modal fade" id="connectModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Conexão Postgres</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="connectForm" onsubmit="handleConnect(event)">
                    <div class="row g-2">
                        <div class="col-md-9">
                            <label class="form-label small fw-bold">Host</label>
                            <input type="text" class="form-control" name="host" placeholder="localhost" required value="localhost">
                            <div class="form-text x-small text-muted"><i class="bi bi-info-circle me-1"></i> 'localhost' refere-se ao servidor (PC do Léo), não ao seu PC.</div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Porta</label>
                            <input type="text" class="form-control" name="port" value="5432" required>
                        </div>
                    </div>
                    <div class="row g-2 mt-2">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Usuário</label>
                            <input type="text" class="form-control" name="user" required value="postgres">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Senha</label>
                            <input type="password" class="form-control" name="password" required>
                        </div>
                    </div>
                    <div class="mt-3">
                        <label class="form-label small fw-bold">Database (Opcional)</label>
                        <div class="input-group">
                            <input type="text" class="form-control" name="dbname" list="db-list" placeholder="Nome da base...">
                            <button type="button" class="btn btn-outline-secondary bg-light text-secondary" onclick="testConnection()" title="Testar conexão e listar bancos">
                                <i class="bi bi-arrow-repeat"></i> Testar / Listar
                            </button>
                        </div>
                        <datalist id="db-list"></datalist>
                        <div id="server-stats" class="mt-2 small text-muted d-none"></div>
                    </div>
                    
                    <div class="mt-4 d-grid">
                        <button type="submit" class="btn btn-primary fw-bold">Conectar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- History Modal -->
<div class="modal fade" id="historyModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Histórico de Queries</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="history-list" class="list-group list-group-flush">
                    <!-- Items -->
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button class="btn btn-sm btn-link text-danger text-decoration-none" onclick="clearHistory()">Limpar Histórico</button>
            </div>
        </div>
    </div>
</div>

    </div>
</div>

<!-- Schema Modal -->
<div class="modal fade" id="schemaModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="schemaModalTitle">Estrutura da Tabela</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div id="schema-content" class="table-responsive p-3">
                    <!-- Schema Table -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap Bundle JS (includes Popper) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // App State
    const backendUrl = 'includes/sql_backend.php';
    let currentDb = '';
    var editor; // Define globally, init later

    // Result State
    let allRows = [];
    let allCols = [];
    let currentPage = 1;
    let itemsPerPage = 50; // Defined here
    
    // Sorting State
    let sortColumn = null;
    let sortAsc = true;
    
    // Global Data
    let availableTables = [];
    
    // Check connection on load
    document.addEventListener('DOMContentLoaded', () => {
        // Defines global completer here so it is available
        window.sqlCompleter = {
            getCompletions: function(editor, session, pos, prefix, callback) {
                if (prefix.length === 0) { callback(null, []); return; }
                
                var suggestions = [];
                
                // Add Tables
                availableTables.forEach(t => {
                    suggestions.push({
                        caption: t,
                        value: t,
                        meta: "Table",
                        score: 1000
                    });
                });
                
                // Keywords (basic set)
                var keywords = ["SELECT", "FROM", "WHERE", "UPDATE", "DELETE", "INSERT", "INTO", "VALUES", "GROUP BY", "ORDER BY", "HAVING", "LIMIT", "OFFSET", "JOIN", "LEFT JOIN", "RIGHT JOIN", "INNER JOIN", "ON", "AND", "OR", "NOT", "NULL", "IS", "IN", "BETWEEN", "LIKE", "AS", "DISTINCT", "COUNT", "SUM", "AVG", "MIN", "MAX"];
                
                keywords.forEach(k => {
                    suggestions.push({
                        caption: k,
                        value: k,
                        meta: "Keyword",
                        score: 500
                    });
                });

                callback(null, suggestions);
            }
        };

        // Initialize Ace Editor
        editor = ace.edit("editor-container");
        editor.setTheme("ace/theme/tomorrow");
        editor.session.setMode("ace/mode/pgsql");
        editor.setShowPrintMargin(false);
        
        // Enable Autocomplete
        editor.setOptions({
            fontSize: "10pt",
            enableBasicAutocompletion: true,
            enableLiveAutocompletion: true,
            enableSnippets: true
        });

        // Add completer
        if (ace.require("ace/ext/language_tools")) {
            ace.require("ace/ext/language_tools").addCompleter(window.sqlCompleter);
        }

        // Run command shortcut
        editor.commands.addCommand({
            name: 'run',
            bindKey: {win: 'Ctrl-Enter', mac: 'Command-Enter'},
            exec: function(editor) {
                executeQuery();
            }
        });

        checkConnection();
    });

    function showLoading(show) {
        document.getElementById('loading-overlay').style.display = show ? 'flex' : 'none';
    }

    // --- Connection Handling ---
    
    function showConnectModal() {
        console.log('Open Connect Modal called');
        try {
            const el = document.getElementById('connectModal');
            console.log('Modal Element:', el);
            if (!el) {
                alert('Modal element not found!');
                return;
            }
            
            const modal = bootstrap.Modal.getOrCreateInstance(el);
            console.log('Modal Instance:', modal);
            modal.show();
        } catch (e) {
            console.error('Modal Error:', e);
            alert('Erro ao abrir modal: ' + e.message);
        }
    }

    function checkConnection() {
        // Just try to list tables. If it fails due to no auth, we show disconnected.
        const formData = new FormData();
        formData.append('action', 'list_dbs'); // Use list_dbs as ping

        fetch(backendUrl, {method: 'POST', body: formData})
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                updateConnectionUI(true, res.data.meta); // res.data is object {dbs: [], meta: {}}
                refreshMetadata(res.data.dbs); // Pass dbs if available to avoid double fetch
            } else {
                updateConnectionUI(false);
                showConnectModal();
            }
        });
    }

    function handleConnect(e) {
        e.preventDefault();
        const form = document.getElementById('connectForm');
        const formData = new FormData(form);
        formData.append('action', 'connect');

        showLoading(true);
        fetch(backendUrl, {method: 'POST', body: formData})
        .then(r => r.json())
        .then(res => {
            showLoading(false);
            if (res.success) {
                const el = document.getElementById('connectModal');
                const modal = bootstrap.Modal.getInstance(el);
                if (modal) modal.hide();
                
                // If dbname was provided, it's already set. If not, we fetch list.
                refreshMetadata();
                updateConnectionUI(true, res.data.meta);
            } else {
                alert('Erro: ' + res.message);
            }
        });
    }

    function testConnection() {
        const form = document.getElementById('connectForm');
        const formData = new FormData(form);
        
        // Remove dbname from check if empty, or just let it be.
        // We want list_dbs action
        formData.append('action', 'list_dbs');
        
        const btn = form.querySelector('button[onclick="testConnection()"]');
        const icon = btn.querySelector('i');
        const originalIcon = icon.className;
        
        // Spin icon
        icon.className = 'spinner-border spinner-border-sm';
        btn.disabled = true;
        
        // Clear previous stats
        const statsDiv = document.getElementById('server-stats');
        statsDiv.classList.add('d-none');
        statsDiv.innerHTML = '';

        fetch(backendUrl, {method: 'POST', body: formData})
        .then(r => r.json())
        .then(res => {
            // Restore icon
            icon.className = originalIcon;
            btn.disabled = false;

            if (res.success) {
                // Populate datalist
                const datalist = document.getElementById('db-list');
                datalist.innerHTML = '';
                
                const dbs = res.data.dbs || [];
                // Check if items are objects (new backend) or strings (fallback)
                dbs.forEach(db => {
                    const opt = document.createElement('option');
                    if (typeof db === 'object') {
                        opt.value = db.name; // Value is just name
                        opt.label = `${db.name} (${db.size_pretty})`; // Label shows size
                        // Note: datalist support for label varies, putting size in value might be messy but visible
                        // Let's try value = name only for clean input
                    } else {
                        opt.value = db;
                    }
                    datalist.appendChild(opt);
                });
                
                // Show Stats
                if (res.data.stats) {
                    const s = res.data.stats;
                    statsDiv.innerHTML = `<div class="d-flex justify-content-between border rounded p-2 bg-light">
                        <span><i class="bi bi-database me-1"></i>Bases: <strong>${s.total_count}</strong></span>
                        <span><i class="bi bi-hdd me-1"></i>Total: <strong>${s.total_size_pretty}</strong></span>
                    </div>`;
                    statsDiv.classList.remove('d-none');
                }
                
                // Feedback
                // alert(`Conexão OK! ${dbs.length} banco(s) encontrados.\nSelecione um na lista.`);
                // Replacing alert with visual feedback only since we show the list and stats now
                
                form.querySelector('[name="dbname"]').focus();
            } else {
                alert('Falha na conexão:\n' + res.message);
            }
        })
        .catch(err => {
            icon.className = originalIcon;
            btn.disabled = false;
            alert('Erro na requisição: ' + err);
        });
    }

    function disconnect() {
        if(!confirm('Desconectar?')) return;
        const formData = new FormData();
        formData.append('action', 'disconnect');
        fetch(backendUrl, {method: 'POST', body: formData})
        .then(() => {
            location.reload();
        });
    }

    function updateConnectionUI(connected, meta = null) {
        const statusEl = document.getElementById('connection-status');
        if (connected) {
            let html = '<span class="text-success"><i class="bi bi-circle-fill small me-1" style="font-size: 0.6em;"></i>Conectado</span>';
            if (meta) {
                 html += ` <span class="text-muted mx-1">|</span> <span class="fw-bold text-dark">${meta.host}</span> <span class="text-muted">/</span> <span class="fw-bold text-primary">${meta.dbname}</span>`;
            }
            statusEl.innerHTML = html;
        } else {
            statusEl.innerHTML = '<span class="text-danger"><i class="bi bi-circle-fill small me-1" style="font-size: 0.6em;"></i>Desconectado</span>';
        }
    }

    // --- Metadata & Navigation ---

    function refreshMetadata(preloadedDbs = null) {
        loadDatabases(preloadedDbs);
        // loadTables() is now called by populateDbSelect or changeDatabase
    }

    function loadDatabases(preloadedDbs) {
        if (preloadedDbs) {
             // We don't have meta here easily unless we pass it too, or stored it globally
             // For now, let's rely on the fetch below for initial load, or handle this better.
             // Actually, refreshMetadata calls this with res.data.dbs. 
             // We need to change how we call it or store currentDb globally.
             populateDbSelect(preloadedDbs);
             return;
        }

        const formData = new FormData();
        formData.append('action', 'list_dbs');
        fetch(backendUrl, {method: 'POST', body: formData})
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                const dbs = res.data.dbs || res.data;
                const current = res.data.meta ? res.data.meta.dbname : '';
                populateDbSelect(dbs, current);
                if (res.data.meta) updateConnectionUI(true, res.data.meta);
            }
        });
    }

    function populateDbSelect(dbs, currentDb = '') {
        const sel = document.getElementById('db-select');
        sel.innerHTML = '<option value="">Selecione...</option>';
        dbs.forEach(db => {
            const opt = document.createElement('option');
            // Handle new object structure or fallback string
            const dbName = (typeof db === 'object') ? db.name : db;
            opt.value = dbName;
            opt.text = dbName;
            if (dbName === currentDb) opt.selected = true;
            sel.appendChild(opt);
        });
        
        // Load tables for selected db (or if just one)
        if (dbs.length > 0) {
             loadTables();
        }
    }

    function changeDatabase(dbname) {
        if (!dbname) return;
        
        showLoading(true);
        const formData = new FormData();
        formData.append('action', 'change_db');
        formData.append('new_dbname', dbname);

        fetch(backendUrl, {method: 'POST', body: formData})
        .then(r => r.json())
        .then(res => {
            showLoading(false);
            if (res.success) {
                updateConnectionUI(true, res.data.meta);
                loadTables(); // Refresh tables for new db
            } else {
                alert(res.message);
            }
        });
    }

    function loadTables() {
        const listContainer = document.getElementById('table-list'); // Correct ID
        if(!listContainer) { console.error('table-list not found'); return; }
        
        listContainer.innerHTML = '<div class="text-center text-muted small py-2">Carregando...</div>';
        
        const formData = new FormData();
        formData.append('action', 'list_tables');
        
        fetch(backendUrl, {method: 'POST', body: formData})
        .then(r => r.json())
        .then(res => {
            listContainer.innerHTML = '';
            if (res.success) {
                availableTables = []; // Reset autocomplete list
                
                if (res.data.length === 0) {
                     listContainer.innerHTML = '<div class="text-center text-muted small py-2">Nenhuma tabela encontrada.</div>';
                } else {
                    res.data.forEach(t => {
                        availableTables.push(t.name); // Add to autocomplete
                        
                        const div = document.createElement('div');
                        const icon = t.type === 'view' ? 'bi-eye' : 'bi-table';
                        div.className = 'table-item d-flex align-items-center';
                        
                        // Main click inserts select
                        const spanName = document.createElement('span');
                        spanName.innerHTML = `<i class="bi ${icon} ${t.type === 'view' ? 'view-icon' : 'table-icon'}"></i> ${t.name}`;
                        spanName.className = 'flex-grow-1';
                        spanName.onclick = () => insertSelect(t.name);
                        
                        // Info icon for schema
                        const spanInfo = document.createElement('span');
                        spanInfo.className = 'info-icon p-1';
                        spanInfo.innerHTML = '<i class="bi bi-info-circle"></i>';
                        spanInfo.onclick = (e) => { e.stopPropagation(); showSchema(t.name); }; // Stop prop to avoid inserting select
                        
                        div.appendChild(spanName);
                        div.appendChild(spanInfo);
                        listContainer.appendChild(div);
                    });
                }
            } else {
                listContainer.innerHTML = '<div class="text-danger small p-2">Erro ao carregar tabelas.</div>';
            }
        });
    }
    
    function filterTables(val) {
        // Debugging filter
        console.log('Filtering:', val);
        const items = document.querySelectorAll('.table-item');
        val = val.toLowerCase().trim();
        
        items.forEach(item => {
            const text = item.textContent.toLowerCase();
            if (text.includes(val)) {
                item.classList.remove('d-none');
                item.classList.add('d-flex');
            } else {
                item.classList.remove('d-flex');
                item.classList.add('d-none');
            }
        });
    }

    function insertSelect(tableName) {
        const sql = `SELECT * FROM ${tableName} LIMIT 100;`;
        editor.setValue(sql);
        executeQuery();
    }

    // --- Execution ---

    function executeQuery() {
        const sql = editor.getValue();
        if (!sql.trim()) return;

        addToHistory(sql); // Save to history

        showLoading(true);
        const formData = new FormData();
        formData.append('action', 'execute');
        formData.append('sql', sql);
        
        fetch(backendUrl, {method: 'POST', body: formData})
        .then(r => r.json())
        .then(res => {
            showLoading(false);
            const container = document.getElementById('results-container');
            const stats = document.getElementById('result-stats');
            
            if (res.success) {
                const rows = res.data.rows;
                const cols = res.data.columns;
                const duration = res.data.duration;
                const affected = res.data.affected;

                stats.innerText = `${rows ? rows.length : 0} linhas | ${duration}ms`;

                if (!rows || rows.length === 0) {
                     if (affected !== null) {
                         container.innerHTML = `<div class="p-3 text-success"><i class="bi bi-check-circle me-2"></i>Comando executado com sucesso. Linhas afetadas: ${affected}</div>`;
                     } else {
                         container.innerHTML = `<div class="p-3 text-muted">Nenhum resultado retornado.</div>`;
                     }
                     allRows = [];
                     allCols = [];
                     updatePaginationControls();
                     return;
                }

                // Store data for pagination
                allRows = rows;
                allCols = cols;
                currentPage = 1;

                renderTable();
                updatePaginationControls();
                
            } else {
                stats.innerText = 'Erro';
                container.innerHTML = `<div class="p-3 text-danger font-monospace"><i class="bi bi-exclamation-triangle me-2"></i>${res.message}</div>`;
                allRows = [];
                updatePaginationControls();
            }
        });
    }

    // --- Pagination & Export ---
    // itemsPerPage declared at top
    
    // Sorting State
    // sortColumn, sortAsc declared at top

    function renderTable() {
        const container = document.getElementById('results-container');
        if (allRows.length === 0) {
            container.innerHTML = '<div class="text-center text-muted p-4">Sem resultados.</div>';
            return;
        }

        // Apply sorting if set
        if (sortColumn !== null) {
            allRows.sort((a, b) => {
                let valA = a[sortColumn];
                let valB = b[sortColumn];
                
                // Handle nulls
                if (valA === valB) return 0;
                if (valA === null) return 1;
                if (valB === null) return -1;
                
                // Numeric sort
                if (!isNaN(valA) && !isNaN(valB)) {
                    valA = parseFloat(valA);
                    valB = parseFloat(valB);
                } else {
                    valA = String(valA).toLowerCase();
                    valB = String(valB).toLowerCase();
                }
                
                if (valA < valB) return sortAsc ? -1 : 1;
                if (valA > valB) return sortAsc ? 1 : -1;
                return 0;
            });
        }

        const start = (currentPage - 1) * itemsPerPage;
        const end = start + itemsPerPage;
        const pageRows = allRows.slice(start, end);

        let html = '<table class="table table-sm table-hover table-result mb-0">';
        html += '<thead><tr>';
        allCols.forEach(c => {
             let icon = '';
             if (c === sortColumn) {
                 icon = sortAsc ? ' <i class="bi bi-sort-down-alt"></i>' : ' <i class="bi bi-sort-up"></i>';
             }
             html += `<th style="cursor: pointer;" onclick="toggleSort('${c}')">${c}${icon}</th>`;
        });
        html += '</tr></thead><tbody>';
        
        pageRows.forEach(r => {
            html += '<tr>';
            allCols.forEach(c => {
                let val = r[c];
                if (val === null) {
                    val = '<span class="text-muted fst-italic">NULL</span>';
                } else if (typeof val === 'string') {
                    val = val.replace(/</g, "&lt;").replace(/>/g, "&gt;");
                }
                html += `<td>${val}</td>`;
            });
            html += '</tr>';
        });
        html += '</tbody></table>';
        container.innerHTML = html;
        container.scrollTop = 0; // scroll to top on page change
    }
    
    function toggleSort(col) {
        if (sortColumn === col) {
            sortAsc = !sortAsc;
        } else {
            sortColumn = col;
            sortAsc = true;
        }
        renderTable();
    }

    function changePage(delta) {
        const maxPage = Math.ceil(allRows.length / itemsPerPage);
        const newPage = currentPage + delta;
        if (newPage >= 1 && newPage <= maxPage) {
            currentPage = newPage;
            renderTable();
            updatePaginationControls();
        }
    }

    function updatePaginationControls() {
        const div = document.getElementById('pagination-controls');
        const display = document.getElementById('page-display');
        const btnExport = document.getElementById('btn-export');
        
        if (allRows.length > 0) {
            const maxPage = Math.ceil(allRows.length / itemsPerPage);
            div.classList.remove('d-none');
            display.innerText = `${currentPage} / ${maxPage}`;
            btnExport.disabled = false;
        } else {
            div.classList.add('d-none');
            btnExport.disabled = true;
        }
    }

    // --- File & Export Utils ---

    function loadSqlFile(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                editor.setValue(e.target.result);
                // Clear input so same file can be selected again
                input.value = '';
            };
            reader.readAsText(input.files[0]);
        }
    }

    function exportCsv() {
        if (allRows.length === 0) return;

        let csvContent = "data:text/csv;charset=utf-8,";
        
        // Header
        csvContent += allCols.join(",") + "\n";

        // Body
        allRows.forEach(row => {
            const rowData = allCols.map(col => {
                let val = row[col];
                if (val === null) return "";
                // Escape quotes
                val = String(val).replace(/"/g, '""');
                // Wrap in quotes if contains comma, newline or quotes
                if (val.search(/("|,|\n)/g) >= 0) {
                    val = `"${val}"`;
                }
                return val;
            });
            csvContent += rowData.join(",") + "\n";
        });

        const encodedUri = encodeURI(csvContent);
        const link = document.createElement("a");
        link.setAttribute("href", encodedUri);
        link.setAttribute("download", `export_${new Date().toISOString().slice(0,19).replace(/[:-]/g,"")}.csv`);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    // --- History Logic ---

    function addToHistory(sql) {
        let history = JSON.parse(localStorage.getItem('sql_history') || '[]');
        // Remove duplicate if exists to move to top
        history = history.filter(item => item.sql !== sql);
        // Add new
        history.unshift({sql: sql, time: new Date().toLocaleString()});
        // Limit
        if (history.length > 50) history.pop();
        localStorage.setItem('sql_history', JSON.stringify(history));
    }

    function showHistory() {
        const list = document.getElementById('history-list');
        const history = JSON.parse(localStorage.getItem('sql_history') || '[]');
        
        list.innerHTML = '';
        if (history.length === 0) {
            list.innerHTML = '<div class="text-center text-muted p-4">Nenhum histórico encontrado.</div>';
        } else {
            history.forEach(item => {
                const div = document.createElement('a');
                div.className = 'list-group-item list-group-item-action cursor-pointer py-3';
                div.style.cursor = 'pointer';
                div.onclick = () => {
                    editor.setValue(item.sql);
                    bootstrap.Modal.getInstance(document.getElementById('historyModal')).hide();
                };
                div.innerHTML = `
                    <div class="d-flex w-100 justify-content-between mb-1">
                        <small class="text-muted">${item.time}</small>
                    </div>
                    <code class="text-dark d-block text-truncate" style="max-height: 50px; overflow: hidden;">${item.sql}</code>
                `;
                list.appendChild(div);
            });
        }
        new bootstrap.Modal(document.getElementById('historyModal')).show();
    }

    function clearHistory() {
        if(confirm('Limpar todo o histórico?')) {
            localStorage.removeItem('sql_history');
            showHistory();
        }
    }

    function showSchema(tableName) {
        const formData = new FormData();
        formData.append('action', 'describe_table');
        formData.append('table', tableName);
        
        showLoading(true);
        fetch(backendUrl, {method: 'POST', body: formData})
        .then(r => r.json())
        .then(res => {
            showLoading(false);
            if(res.success) {
                document.getElementById('schemaModalTitle').innerText = 'Estrutura: ' + tableName;
                
                let html = '<table class="table table-sm table-striped mb-0 small">';
                html += '<thead class="table-light"><tr><th>Coluna</th><th>Tipo</th><th>Tamanho</th><th>Nulo?</th><th>Default</th></tr></thead><tbody>';
                
                res.data.forEach(col => {
                    html += `<tr>
                        <td class="fw-bold text-primary">${col.column_name}</td>
                        <td>${col.data_type}</td>
                        <td>${col.character_maximum_length || '-'}</td>
                        <td>${col.is_nullable}</td>
                        <td class="text-muted fst-italic">${col.column_default || ''}</td>
                    </tr>`;
                });
                html += '</tbody></table>';
                
                document.getElementById('schema-content').innerHTML = html;
                new bootstrap.Modal(document.getElementById('schemaModal')).show();
            } else {
                alert('Erro ao carregar esquema: ' + res.message);
            }
        });
    }

</script>
