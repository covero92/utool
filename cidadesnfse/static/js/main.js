document.addEventListener('DOMContentLoaded', () => {
    // -------------------------------------------------------------------------
    //  ESTADO DA APLICAÇÃO E VARIÁVEIS GLOBAIS
    // -------------------------------------------------------------------------
    let allMunicipalities = [];
    let allProviders = [];
    let map;
    let markerClusterGroup;
    let providerChoices;
    let providerModalInstance;

    const ufMap = { 'AC': 'Acre', 'AL': 'Alagoas', 'AP': 'Amapá', 'AM': 'Amazonas', 'BA': 'Bahia', 'CE': 'Ceará', 'DF': 'Distrito Federal', 'ES': 'Espírito Santo', 'GO': 'Goiás', 'MA': 'Maranhão', 'MT': 'Mato Grosso', 'MS': 'Mato Grosso do Sul', 'MG': 'Minas Gerais', 'PA': 'Pará', 'PB': 'Paraíba', 'PR': 'Paraná', 'PE': 'Pernambuco', 'PI': 'Piauí', 'RJ': 'Rio de Janeiro', 'RN': 'Rio Grande do Norte', 'RS': 'Rio Grande do Sul', 'RO': 'Rondônia', 'RR': 'Roraima', 'SC': 'Santa Catarina', 'SP': 'São Paulo', 'SE': 'Sergipe', 'TO': 'Tocantins' };

    // -------------------------------------------------------------------------
    //  API HELPER
    // -------------------------------------------------------------------------
    const api = {
        get: (endpoint) => fetch(`${window.BASE_URL}api/${endpoint}`).then(res => res.json()),
        post: (endpoint, data) => fetch(`${window.BASE_URL}api/${endpoint}`, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(data) }).then(res => res.json()),
        put: (endpoint, data) => fetch(`${window.BASE_URL}api/${endpoint}`, { method: 'PUT', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(data) }).then(res => res.json()),
        delete: (endpoint) => fetch(`${window.BASE_URL}api/${endpoint}`, { method: 'DELETE' }).then(res => res.json()),
    };

    // -------------------------------------------------------------------------
    //  LÓGICA DO MAPA
    // -------------------------------------------------------------------------
    const createColoredIcon = (color) => new L.Icon({ iconUrl: `https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-${color}.png`, shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png', iconSize: [25, 41], iconAnchor: [12, 41], popupAnchor: [1, -34], shadowSize: [41, 41] });
    const icons = { red: createColoredIcon('red'), green: createColoredIcon('green'), blue: createColoredIcon('blue'), orange: createColoredIcon('orange') };

    const updateMapMarkers = () => {
        if (!map || !markerClusterGroup) return;
        markerClusterGroup.clearLayers();
        const filters = { nfseNacional: document.getElementById('filter-nfse-nacional').checked, provedorMapeado: document.getElementById('filter-provedor-mapeado').checked, reformaTributaria: document.getElementById('filter-reforma-tributaria').checked, uniplusAtivo: document.getElementById('filter-uniplus-ativo').checked };
        const markersToAdd = [];
        allMunicipalities.forEach(city => {
            if (!city.latitude || !city.longitude) return;
            let icon = null; let addMarker = false;
            if (filters.uniplusAtivo && city.cliente_uniplus_ativo) { icon = icons.red; addMarker = true; }
            else if (filters.reformaTributaria && city.reforma_tributaria) { icon = icons.green; addMarker = true; }
            else if (filters.nfseNacional && (city.aderenteambientenacional === 'Sim' || city.aderenteemissornacional === 'Sim')) { icon = icons.orange; addMarker = true; }
            else if (filters.provedorMapeado && city.provedor && city.provedor.trim() !== '') { icon = icons.blue; addMarker = true; }
            if (addMarker) { const marker = L.marker([city.latitude, city.longitude], { icon: icon }).bindPopup(`<b>${city.nomemunicipio} - ${city.uf}</b><br>Provedor: ${city.provedor || 'N/A'}`); markersToAdd.push(marker); }
        });
        markerClusterGroup.addLayers(markersToAdd);
    };

    // -------------------------------------------------------------------------
    //  FUNÇÕES DE RENDERIZAÇÃO
    // -------------------------------------------------------------------------
    const renderDashboard = () => {
        document.getElementById('kpi-total-municipios').textContent = allMunicipalities.length;
        document.getElementById('kpi-aderiram-padrao').textContent = allMunicipalities.filter(m => m.aderenteambientenacional === 'Sim' || m.aderenteemissornacional === 'Sim').length;
        document.getElementById('kpi-uniplus-ativo').textContent = allMunicipalities.filter(m => m.cliente_uniplus_ativo).length;
        document.getElementById('kpi-provedor-mapeado').textContent = allMunicipalities.filter(m => m.provedor && m.provedor.trim() !== '').length;
        document.getElementById('kpi-reforma-tributaria').textContent = allMunicipalities.filter(m => m.reforma_tributaria).length;
        if (!map) {
            map = L.map('map').setView([-14.235, -51.925], 4);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors' }).addTo(map);
            markerClusterGroup = L.markerClusterGroup();
            map.addLayer(markerClusterGroup);
        }
        updateMapMarkers();
    };

    const renderMunicipalities = (municipalitiesToRender = allMunicipalities) => {
        const list = document.getElementById('municipios-list');
        if (!list) return;
        list.innerHTML = '';
        const groupedByUf = municipalitiesToRender.reduce((acc, city) => { (acc[city.uf] = acc[city.uf] || []).push(city); return acc; }, {});
        Object.keys(groupedByUf).sort().forEach(uf => {
            const cities = groupedByUf[uf];
            const ufFullName = ufMap[uf] || uf;
            const ufHtml = `<div class="accordion-item"><h2 class="accordion-header" id="heading-${uf}"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-${uf}" aria-expanded="false" aria-controls="collapse-${uf}">${ufFullName} (${cities.length})</button></h2><div id="collapse-${uf}" class="accordion-collapse collapse" aria-labelledby="heading-${uf}" data-bs-parent="#municipios-list"><div class="list-group list-group-flush">${cities.map(city => `<a href="#" class="list-group-item list-group-item-action" data-id="${city.codigomunicipio}">${city.nomemunicipio}${city.homologado_uniplus ? '<span class="badge bg-success float-end">Homologado</span>' : ''}</a>`).join('')}</div></div></div>`;
            list.insertAdjacentHTML('beforeend', ufHtml);
        });
    };

    const renderProviders = (providersToRender = allProviders) => {
        const tableBody = document.getElementById('providers-table-body');
        const countEl = document.getElementById('provider-count');
        if (!tableBody || !countEl) return;
        tableBody.innerHTML = '';
        if (providersToRender.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="5" class="text-center">Nenhum provedor encontrado.</td></tr>';
        } else {
            providersToRender.forEach(p => {
                const row = `<tr data-id="${p.id}"><td>${p.descricao || 'N/A'}</td><td>${p.provedor || 'N/A'}</td><td class="text-center"><span class="badge bg-${p.homologado ? 'success' : 'danger'}">${p.homologado ? 'Sim' : 'Não'}</span></td><td class="text-center is-in-use-${p.is_in_use}"><i class="bi bi-${p.is_in_use ? 'check-circle-fill' : 'x-circle-fill'}"></i></td><td class="text-center"><button class="btn btn-sm btn-outline-primary admin-only edit-provider-btn" data-id="${p.id}"><i class="bi bi-pencil-fill"></i></button> <button class="btn btn-sm btn-outline-danger admin-only delete-provider-btn" data-id="${p.id}" ${p.is_in_use ? 'disabled' : ''}><i class="bi bi-trash-fill"></i></button></td></tr>`;
                tableBody.insertAdjacentHTML('beforeend', row);
            });
        }
        countEl.textContent = providersToRender.length;
        updateAdminControlsVisibility();
    };

    // -------------------------------------------------------------------------
    //  MANIPULAÇÃO DE DADOS E FORMULÁRIOS
    // -------------------------------------------------------------------------
    const updateProviderDocLink = (providerName) => {
        const docLink = document.getElementById('provider-doc-link');
        if (!docLink) return;
        const provider = allProviders.find(p => p.provedor === providerName);
        if (provider && provider.url_documentacao) {
            docLink.href = provider.url_documentacao;
            docLink.classList.remove('d-none');
        } else {
            docLink.href = '#';
            docLink.classList.add('d-none');
        }
    };

    const fetchAndRenderHistory = async (municipioId) => {
        const historyList = document.getElementById('municipio-history-list');
        if (!historyList) return;
        historyList.innerHTML = '<li class="list-group-item text-center">Carregando histórico...</li>';
        try {
            const historyData = await api.get(`municipios.php?history_for_id=${municipioId}`);
            if (historyData.length === 0) {
                historyList.innerHTML = '<li class="list-group-item text-center">Nenhuma alteração registrada.</li>';
                return;
            }
            historyList.innerHTML = historyData.map(item => `<li class="list-group-item"><div class="d-flex w-100 justify-content-between"><h6 class="mb-1 text-capitalize">${item.campo_alterado.replace(/_/g, ' ')}</h6><small>${new Date(item.timestamp).toLocaleString('pt-BR')}</small></div><p class="mb-1">De: <span class="text-danger">${item.valor_antigo || 'vazio'}</span><br>Para: <span class="text-success">${item.valor_novo || 'vazio'}</span></p></li>`).join('');
        } catch (error) {
            console.error('Erro ao buscar histórico:', error);
            historyList.innerHTML = '<li class="list-group-item text-center text-danger">Erro ao carregar histórico.</li>';
        }
    };

    const showMunicipioDetails = (id) => {
        const city = allMunicipalities.find(m => m.codigomunicipio == id);
        if (!city) return;
        const detailsTab = new bootstrap.Tab(document.getElementById('details-tab-link'));
        detailsTab.show();
        document.getElementById('municipio-history-list').innerHTML = '';
        document.getElementById('municipio-details-card').style.display = 'block';
        document.getElementById('municipio-detail-title').textContent = `${city.nomemunicipio} - ${city.uf}`;
        for (const key in city) {
            const el = document.getElementById(`form-municipio-${key}`);
            if (el) {
                if (el.type === 'checkbox') el.checked = !!city[key];
                else if (el.tagName === 'SELECT') {
                    if (key === 'provedor' && providerChoices) providerChoices.setChoiceByValue(city[key] || '');
                    else el.value = city[key] || '';
                } else {
                    el.value = city[key] || '';
                }
            }
        }
        document.getElementById('form-municipio-codigomunicipio').value = city.codigomunicipio;
        document.getElementById('form-municipio-url_webservice').value = city.url_webservice || '';
        updateProviderDocLink(city.provedor);
    };

    const handleMunicipioFormSubmit = async (e) => {
        e.preventDefault();
        const form = e.target;
        const id = form.codigomunicipio.value;
        const data = {};
        const originalCity = allMunicipalities.find(m => m.codigomunicipio == id);
        const fields = ['provedor', 'urlprovedor', 'url_webservice', 'observacao', 'homologado_uniplus', 'reforma_tributaria', 'cliente_uniplus_ativo'];
        fields.forEach(key => {
            const el = form.querySelector(`[name="${key}"]`);
            if (!el) return;
            let currentValue = el.type === 'checkbox' ? el.checked : el.value;
            let originalValue = originalCity[key];
            if (el.type === 'checkbox') originalValue = !!originalValue; else originalValue = originalValue || '';
            if (currentValue !== originalValue) data[key] = currentValue;
        });
        if (Object.keys(data).length === 0) { alert('Nenhuma alteração detectada.'); return; }
        const result = await api.put(`municipios.php?id=${id}`, data);
        if (result.success) { alert('Município atualizado com sucesso!'); init(); }
        else { alert(`Erro: ${result.error}`); }
    };

    const getProviderModal = () => { if (!providerModalInstance) { providerModalInstance = new bootstrap.Modal(document.getElementById('provider-modal')); } return providerModalInstance; };

    /**
     * ATUALIZADO: Armazena um "snapshot" do estado original do provedor ao abrir o modal.
     */
    const openProviderModal = (id = null) => {
        const form = document.getElementById('provider-form');
        form.reset();
        document.getElementById('provider-modal-title').textContent = id ? 'Editar Provedor' : 'Novo Provedor';
        form.dataset.id = id || '';

        if (id) {
            const provider = allProviders.find(p => p.id == id);
            if (provider) {
                form.dataset.originalData = JSON.stringify(provider);
                for (const key in provider) {
                    const el = form.querySelector(`[name="${key}"]`);
                    if (el) {
                        if (el.type === 'checkbox') el.checked = !!provider[key];
                        else el.value = provider[key] || '';
                    }
                }
            }
        } else {
            form.dataset.originalData = JSON.stringify({});
        }
        getProviderModal().show();
    };

    /**
     * VERSÃO CORRIGIDA E ROBUSTA: Detecta corretamente todas as alterações, incluindo checkboxes desmarcados.
     */
    const handleProviderFormSubmit = async (e) => {
        e.preventDefault();
        const form = e.target;
        const id = form.dataset.id;

        const originalData = JSON.parse(form.dataset.originalData || '{}');
        const dataToSend = {}; // Objeto que conterá apenas os campos alterados

        // Lista de todos os campos possíveis no formulário
        const allFields = [
            'descricao', 'provedor', 'url_documentacao', 'observacao', 'homologado',
            'envio_em_lote', 'usa_certificado', 'possui_inutilizacao', 'possui_impressao_propria'
        ];

        allFields.forEach(key => {
            const el = form.querySelector(`[name="${key}"]`);
            if (!el) return;

            // Pega o valor atual diretamente do elemento do formulário
            const currentValue = el.type === 'checkbox' ? el.checked : el.value;

            // Pega o valor original do snapshot, definindo um padrão se não existir
            const originalValue = originalData[key] ?? (el.type === 'checkbox' ? false : '');

            // Compara o valor atual com o original
            if (currentValue !== originalValue) {
                dataToSend[key] = currentValue;
            }
        });

        if (Object.keys(dataToSend).length === 0) {
            alert('Nenhuma alteração foi feita.');
            getProviderModal().hide();
            return;
        }

        let result;
        if (id) {
            result = await api.put(`providers.php?id=${id}`, dataToSend);
        } else {
            // Para um novo provedor, enviamos todos os dados, não apenas os alterados.
            const allData = {};
            allFields.forEach(key => {
                const el = form.querySelector(`[name="${key}"]`);
                if (el) allData[key] = el.type === 'checkbox' ? el.checked : el.value;
            });
            result = await api.post('providers.php', allData);
        }

        if (result.success) {
            getProviderModal().hide();
            await init();
        } else {
            alert(`Erro: ${result.error || 'Ocorreu um erro desconhecido.'}`);
        }
    };

    const handleDeleteProvider = async (id) => { const provider = allProviders.find(p => p.id == id); if (provider.is_in_use) { alert('Este provedor não pode ser excluído porque está em uso.'); return; } if (confirm(`Tem certeza que deseja excluir o provedor "${provider.descricao}"?`)) { const result = await api.delete(`providers.php?id=${id}`); if (result.success) { await init(); } else { alert(`Erro: ${result.error}`); } } };

    // -------------------------------------------------------------------------
    //  AUTENTICAÇÃO E VISIBILIDADE
    // -------------------------------------------------------------------------
    const updateAdminControlsVisibility = () => {
        const isAdmin = document.body.classList.contains('admin-mode');
        document.querySelectorAll('.admin-only').forEach(el => {
            el.style.display = isAdmin ? '' : 'none';
        });
        document.querySelectorAll('#form-municipio-details input, #form-municipio-details textarea')
            .forEach(el => {
                el.disabled = !isAdmin;
            });
        if (providerChoices) {
            if (isAdmin) {
                providerChoices.enable();
            } else {
                providerChoices.disable();
            }
        }
        document.querySelectorAll('#provider-form input, #provider-form select, #provider-form textarea')
            .forEach(el => {
                el.disabled = !isAdmin;
            });
    };

    const setAdminMode = (isAdmin) => {
        document.body.classList.toggle('admin-mode', isAdmin);
        document.getElementById('login-form').classList.toggle('d-none', isAdmin);
        document.getElementById('logout-btn').classList.toggle('d-none', !isAdmin);
        const indicator = document.getElementById('admin-mode-indicator');
        if (indicator) {
            indicator.classList.toggle('d-none', !isAdmin);
        }
        updateAdminControlsVisibility();
    };

    const handleLogin = async (e) => {
        e.preventDefault();
        const password = document.getElementById('admin-password').value;
        const result = await api.post('admin.php?action=login', { password });
        if (result.success) {
            setAdminMode(true);
            document.getElementById('admin-password').value = '';
        } else {
            alert(result.message || 'Login falhou.');
        }
    };

    const handleLogout = async () => {
        const result = await api.post('admin.php?action=logout');
        if (result.success) {
            setAdminMode(false);
        }
    };

    // -------------------------------------------------------------------------
    //  FILTROS AVANÇADOS E BUSCA
    // -------------------------------------------------------------------------

    const filterMunicipalities = () => {
        const searchTerm = document.getElementById('municipio-search').value.toLowerCase();
        const selectedUfs = Array.from(document.querySelectorAll('#uf-filter-options .form-check-input:checked')).map(cb => cb.value);
        const adesaoFilter = document.querySelector('input[name="filter-adesao"]:checked').value;
        const homologadoFilter = document.querySelector('input[name="filter-homologado"]:checked').value;

        const filtered = allMunicipalities.filter(m => {
            const matchesSearch = m.nomemunicipio.toLowerCase().includes(searchTerm);
            const matchesUf = selectedUfs.length === 0 || selectedUfs.includes(m.uf);
            let matchesAdesao = true;
            if (adesaoFilter !== 'todos') {
                const hasAdesao = m.aderenteambientenacional === 'Sim' || m.aderenteemissornacional === 'Sim';
                matchesAdesao = (adesaoFilter === 'sim') ? hasAdesao : !hasAdesao;
            }
            let matchesHomologado = true;
            if (homologadoFilter !== 'todos') {
                matchesHomologado = (homologadoFilter === 'sim') ? m.homologado_uniplus : !m.homologado_uniplus;
            }
            return matchesSearch && matchesUf && matchesAdesao && matchesHomologado;
        });

        renderMunicipalities(filtered);
    };

    const populateUfFilters = () => {
        const container = document.getElementById('uf-filter-options');
        if (!container) return;
        const ufs = Object.keys(ufMap).sort();
        let html = '<div class="row">';
        ufs.forEach(uf => {
            html += `
                <div class="col-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="${uf}" id="uf-${uf}">
                        <label class="form-check-label" for="uf-${uf}">${uf}</label>
                    </div>
                </div>
            `;
        });
        html += '</div>';
        container.innerHTML = html;
    };

    const filterProviders = () => {
        const searchTerm = document.getElementById('provider-search').value.toLowerCase();
        const filtered = allProviders.filter(p => (p.descricao || '').toLowerCase().includes(searchTerm) || (p.provedor || '').toLowerCase().includes(searchTerm));
        renderProviders(filtered);
    };

    // -------------------------------------------------------------------------
    //  CONFIGURAÇÕES E INICIALIZAÇÃO
    // -------------------------------------------------------------------------
    const setupEventListeners = () => {
        const addSafeListener = (id, event, handler) => {
            const element = document.getElementById(id);
            if (element) {
                element.addEventListener(event, handler);
            } else {
                console.warn(`Elemento com ID '${id}' não encontrado para adicionar listener.`);
            }
        };

        addSafeListener('login-form', 'submit', handleLogin);
        addSafeListener('logout-btn', 'click', handleLogout);

        addSafeListener('municipio-search', 'input', filterMunicipalities);
        const advancedFilters = document.getElementById('advanced-filters-accordion');
        if (advancedFilters) {
            advancedFilters.addEventListener('change', filterMunicipalities);
        }

        addSafeListener('provider-search', 'input', filterProviders);
        addSafeListener('map-filters', 'change', (e) => { if (e.target.type === 'checkbox') updateMapMarkers(); });

        const municipiosList = document.getElementById('municipios-list');
        if (municipiosList) {
            municipiosList.addEventListener('click', (e) => {
                const target = e.target.closest('.list-group-item-action');
                if (target) {
                    e.preventDefault();
                    showMunicipioDetails(target.dataset.id);
                }
            });
        }

        addSafeListener('form-municipio-provedor', 'change', (e) => updateProviderDocLink(e.target.value));
        addSafeListener('history-tab-link', 'click', () => {
            const municipioId = document.getElementById('form-municipio-codigomunicipio').value;
            if (municipioId) fetchAndRenderHistory(municipioId);
        });

        addSafeListener('form-municipio-details', 'submit', handleMunicipioFormSubmit);
        addSafeListener('provider-form', 'submit', handleProviderFormSubmit);
        addSafeListener('add-provider-btn', 'click', () => openProviderModal());

        const providerPane = document.getElementById('providers-tab-pane');
        if (providerPane) {
            providerPane.addEventListener('click', (e) => {
                const editBtn = e.target.closest('.edit-provider-btn');
                const deleteBtn = e.target.closest('.delete-provider-btn');
                if (editBtn) openProviderModal(editBtn.dataset.id);
                else if (deleteBtn) handleDeleteProvider(deleteBtn.dataset.id);
            });
        }

        // **NOVO**: Listener para o botão de mostrar/esconder filtros
        const toggleBtn = document.getElementById('toggle-filters-btn');
        if (toggleBtn) {
            toggleBtn.addEventListener('click', () => {
                const filtersPanel = document.getElementById('filters-panel');
                const isHidden = filtersPanel.classList.toggle('d-none');

                toggleBtn.innerHTML = isHidden
                    ? '<i class="bi bi-chevron-down"></i> Mostrar Filtros'
                    : '<i class="bi bi-chevron-up"></i> Esconder Filtros';
            });
        }

        addSafeListener('theme-switcher', 'click', () => {
            const currentTheme = document.documentElement.getAttribute('data-bs-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-bs-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            const switcher = document.getElementById('theme-switcher');
            if (switcher) switcher.innerHTML = newTheme === 'dark' ? '<i class="bi bi-sun-fill"></i> Tema Claro' : '<i class="bi bi-moon-stars-fill"></i> Tema Escuro';
        });
    };

    const applyInitialTheme = () => {
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-bs-theme', savedTheme);
        const themeSwitcher = document.getElementById('theme-switcher');
        if (themeSwitcher) {
            themeSwitcher.innerHTML = savedTheme === 'dark' ? '<i class="bi bi-sun-fill"></i> Tema Claro' : '<i class="bi bi-moon-stars-fill"></i> Tema Escuro';
        }
    };

    const init = async () => {
        populateUfFilters();

        try {
            const adminStatus = await api.get('admin.php?action=status');
            setAdminMode(adminStatus.loggedIn);

            const [municipalities, providers] = await Promise.all([
                api.get('municipios.php'),
                api.get('providers.php')
            ]);
            allMunicipalities = municipalities;
            allProviders = providers;

            const providerSelect = document.getElementById('form-municipio-provedor');
            if (providerSelect) {
                providerSelect.innerHTML = '<option value="">Selecione...</option>';
                allProviders.sort((a, b) => a.descricao.localeCompare(b.descricao)).forEach(p => {
                    providerSelect.innerHTML += `<option value="${p.provedor}">${p.descricao}</option>`;
                });

                if (providerChoices) providerChoices.destroy();
                providerChoices = new Choices(providerSelect, {
                    searchResultLimit: 100,
                    itemSelectText: 'Pressione para selecionar',
                });
            }

            renderDashboard();
            renderMunicipalities();
            renderProviders();
            updateAdminControlsVisibility();

        } catch (error) {
            console.error("Erro ao inicializar a aplicação:", error);
            document.body.innerHTML = `<div class="alert alert-danger m-5"><strong>Erro Crítico:</strong> Não foi possível carregar os dados da aplicação. Verifique a conexão com a API e o console para mais detalhes.</div>`;
        }
    };

    // --- PONTO DE ENTRADA ---
    applyInitialTheme();
    setupEventListeners();
    init();
});