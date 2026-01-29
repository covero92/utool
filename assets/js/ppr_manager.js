/**
 * PPR Manager Logic
 * Refactored for stability and clean separation of concerns.
 */

const PPRManager = {
    // State
    year: new URLSearchParams(window.location.search).get('year') || new Date().getFullYear(),
    okrStructure: [],
    pprData: {},
    isAdmin: false, // Will be set from backend config or page variable if needed

    // Initialization
    async init() {
        console.log('PPR Manager Initializing for year:', this.year);
        try {
            await this.fetchData();
            this.renderDashboard();
            this.setupEventListeners();
        } catch (error) {
            console.error('Initialization Failed:', error);
            this.showError('Falha ao carregar dados. Tente recarregar a página.');
        }
    },

    // Data Fetching
    async fetchData() {
        const fdConfig = new FormData();
        fdConfig.append('action', 'get_metrics_config');
        fdConfig.append('year', this.year);

        const fdData = new FormData();
        fdData.append('action', 'get_ppr_data');
        fdData.append('year', this.year);

        const [rConfig, rData] = await Promise.all([
            fetch('includes/portal_actions.php', { method: 'POST', body: fdConfig }),
            fetch('includes/portal_actions.php', { method: 'POST', body: fdData })
        ]);

        const resConfig = await rConfig.json();
        const resData = await rData.json();

        if (resConfig.success) this.okrStructure = resConfig.data;
        if (resData.success) this.pprData = resData.data;

        // Determine Admin Status from page (rendered by PHP) or inferred
        // ideally backend sends this in config, but for now we trust the PHP var if exposed
        if (typeof window.isAdmin !== 'undefined') this.isAdmin = window.isAdmin;
    },

    // Rendering
    renderDashboard() {
        const container = document.getElementById('dashboard');
        if (!container) return;

        // Clear existing tables content before rebuilding
        // Actually, we are injecting into specific tables by ID based on logic

        if (!this.okrStructure || this.okrStructure.length === 0) {
            console.warn('No OKR structure found.');
            return;
        }

        this.okrStructure.forEach(okr => {
            // Map ID '1' -> 'table-okr1'
            let targetId = `table-okr${okr.id}`;
            // Handle potentially different ID formats if needed
            if (String(okr.id).startsWith('okr')) targetId = `table-${okr.id}`;

            const table = document.getElementById(targetId);
            if (!table) {
                console.warn(`Table ${targetId} not found in DOM.`);
                return;
            }

            const tbody = table.querySelector('tbody') || table.appendChild(document.createElement('tbody'));
            tbody.innerHTML = ''; // Clear previous

            okr.metrics.forEach(metric => {
                const tr = document.createElement('tr');
                tr.innerHTML = this.buildMetricRow(metric);
                tbody.appendChild(tr);
            });
        });

        this.calculateScore();
    },

    buildMetricRow(metric) {
        let nameTd = `<td class="text-start ps-4 fw-bold text-secondary text-truncate" style="max-width: 150px;" title="${metric.name}">${metric.name}</td>`;
        let monthsTd = '';

        for (let i = 1; i <= 12; i++) {
            const val = (this.pprData[metric.key] && this.pprData[metric.key][i]) ? this.pprData[metric.key][i] : '';

            // Progress Bar Style
            let cellContent = '';
            let cellClass = 'position-relative'; // Bootstrap utility for positioning

            // If we have a target and a value, visualize it
            let progressBg = '';
            if (val && metric.target > 0) {
                let numVal = parseFloat(String(val).replace(',', '.'));
                let target = parseFloat(metric.target);
                // Clamp between 0 and 100
                let pct = Math.max(0, Math.min((numVal / target) * 100, 100));

                // Absolute div for generic clean look
                progressBg = `<div style="
                    position: absolute;
                    bottom: 0;
                    left: 0;
                    height: 4px; /* Slim bar at bottom */
                    width: ${pct}%;
                    background-color: #198754; /* Success Green */
                    opacity: 0.6;
                    transition: width 0.3s;
                "></div>`;

                // Alternative: Full background fill (uncomment if preferred)
                // progressBg = `<div style="position: absolute; top:0; left:0; width:${pct}%; height:100%; background:#d1e7dd; z-index:0;"></div>`;
            }

            if (typeof window.canEdit !== 'undefined' && window.canEdit) {
                cellContent = `<input type="text" class="form-control form-control-sm text-center ppr-input bg-transparent border-0" 
                                data-key="${metric.key}" data-month="${i}" value="${val}" style="z-index: 1; position: relative;">`;
            } else {
                cellContent = `<span style="z-index: 1; position: relative;">${val || '-'}</span>`;
            }

            monthsTd += `<td class="${cellClass}" style="vertical-align: middle; padding: 0;">
                            ${progressBg}
                            <div class="d-flex align-items-center justify-content-center" style="height: 100%; min-height: 35px;">
                                ${cellContent}
                            </div>
                         </td>`;
        }

        let editIcon = '';
        if (this.isAdmin) {
            // Store data in attributes to avoid inline JS escaping issues
            // Encode desc to be safe in HTML attribute
            const safeDesc = metric.desc ? metric.desc.replace(/"/g, '&quot;') : '';
            editIcon = `<i class="bi bi-pencil-square text-primary ms-2 cursor-pointer edit-metric-btn" 
                          data-key="${metric.key}" 
                          data-desc="${safeDesc}" 
                          data-target="${metric.target || 0}"
                          title="Editar Meta"></i>`;
        }

        let descTd = `<td class="text-start pe-4 small text-muted" style="min-width: 250px; white-space: pre-wrap;">
                        <span id="desc-${metric.key}">${metric.desc}</span>${editIcon}
                      </td>`;

        return nameTd + monthsTd + descTd;
    },

    calculateScore() {
        let lostPoints = 0;
        // Simple logic for now, can be expanded based on rules
        // For visual feedback only as requested

        let currentScore = 100 - lostPoints;
        const scoreEl = document.getElementById('totalScore');
        if (scoreEl) scoreEl.innerText = currentScore;
    },

    // Interaction
    setupEventListeners() {
        // Tab switching lazy load
        const chartsTab = document.getElementById('charts-tab');
        if (chartsTab) chartsTab.addEventListener('click', () => this.loadCharts());

        const auditTab = document.getElementById('audit-tab');
        if (auditTab) auditTab.addEventListener('click', () => this.loadAudit());

        // Delegate event for Edit Icons
        document.getElementById('dashboard').addEventListener('click', (e) => {
            if (e.target.classList.contains('edit-metric-btn')) {
                const btn = e.target;
                this.openEditModal(btn.dataset.key, btn.dataset.desc, btn.dataset.target);
            }
        });
    },

    async savePPR() {
        const inputs = document.querySelectorAll('.ppr-input');
        const data = [];
        inputs.forEach(inp => {
            if (inp.value) {
                data.push({ key: inp.dataset.key, month: inp.dataset.month, value: inp.value });
            }
        });

        const formData = new FormData();
        formData.append('action', 'save_ppr_data');
        formData.append('year', this.year);
        formData.append('entries', JSON.stringify(data));

        try {
            const r = await fetch('includes/portal_actions.php', { method: 'POST', body: formData });
            const res = await r.json();
            if (res.success) {
                // Flash success or reload. Reload is safer for audit updates.
                window.location.reload();
            } else {
                alert('Erro ao salvar: ' + res.message);
            }
        } catch (e) {
            console.error(e);
            alert('Erro de conexão ao salvar.');
        }
    },

    openEditModal(key, desc, target) {
        document.getElementById('editMetricKey').value = key;
        document.getElementById('editMetricDesc').value = desc;
        document.getElementById('editMetricTarget').value = target;
        const modal = new bootstrap.Modal(document.getElementById('descEditModal'));
        modal.show();
    },

    async saveDesc() {
        const key = document.getElementById('editMetricKey').value;
        const newDesc = document.getElementById('editMetricDesc').value;
        const newTarget = document.getElementById('editMetricTarget').value;

        // Helper for sequential save
        const saveAttr = async (col, val) => {
            const fd = new FormData();
            fd.append('action', 'save_metric_attribute');
            fd.append('year', this.year);
            fd.append('key', key);
            fd.append('column', col);
            fd.append('value', val);
            return fetch('includes/portal_actions.php', { method: 'POST', body: fd }).then(r => r.json());
        };

        try {
            const r1 = await saveAttr('target_description', newDesc);
            if (!r1.success) throw new Error(r1.message);

            const r2 = await saveAttr('target_value', newTarget);
            if (!r2.success) throw new Error(r2.message);

            window.location.reload();
        } catch (e) {
            alert('Erro ao salvar: ' + e.message);
        }
    },

    loadAudit() {
        const formData = new FormData();
        formData.append('action', 'get_ppr_audit');
        fetch('includes/portal_actions.php', { method: 'POST', body: formData })
            .then(r => r.json())
            .then(res => {
                const tbody = document.getElementById('auditTableBody');
                if (!tbody) return;
                tbody.innerHTML = '';
                if (res.success) {
                    res.data.forEach(log => {
                        tbody.innerHTML += `<tr>
                        <td class="ps-4 small">${log.created_at}</td>
                        <td class="small fw-bold">${log.user_name || 'System'}</td>
                        <td><span class="badge bg-secondary">${log.action}</span></td>
                        <td class="small">${log.details}</td>
                        <td class="small text-muted">${log.old_value}</td>
                        <td class="small text-primary">${log.new_value}</td>
                    </tr>`;
                    });
                }
            });
    },

    loadCharts() {
        // Implement Chart.js logic here if needed, or keep it simple.
        // For now, checking if Chart is defined
        if (typeof Chart === 'undefined') return;

        // ... Chart logic similar to previous implementation ...
        // Keeping it minimal to focus on the main grid fix first.
    },

    showError(msg) {
        // Simple toaster or ALERT
        console.error(msg);
    }
};

// Initialize
document.addEventListener('DOMContentLoaded', () => PPRManager.init());

// Expose to window for HTML onclick handlers
window.PPRManager = PPRManager;
window.savePPR = () => PPRManager.savePPR();
window.saveDesc = () => PPRManager.saveDesc();
