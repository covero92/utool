<?php
include 'includes/header.php';
require_once 'includes/portal_helpers.php';
require_once 'includes/portal_auth.php';

$portal = new SupportPortal();
$meetings = $portal->getMeetings();
$isAdmin = isAdmin();
?>

<!-- FullCalendar -->
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>

<!-- Pass data safely to JS -->
<script>
    const meetingsData = <?php echo json_encode($meetings); ?>;
    const isAdmin = <?php echo $isAdmin ? 'true' : 'false'; ?>;
</script>

<div class="container-fluid py-4 px-4 h-100" style="min-height: calc(100vh - 80px);">
    <div class="row h-100 g-0">
        
        <!-- Left: Meeting List -->
        <div class="col-lg-3 border-end bg-white d-flex flex-column h-100 shadow-sm z-1">
            <div class="d-flex justify-content-between align-items-center mb-3 px-4 pt-3">
                <div class="d-flex align-items-center">
                    <a href="index.php" class="btn btn-sm btn-light rounded-circle shadow-sm me-2" title="Voltar">
                        <i class="bi bi-arrow-left"></i>
                    </a>
                    <h4 class="fw-bold mb-0">Reuniões</h4>
                </div>
                <?php if($isAdmin): ?>
                <button class="btn btn-primary rounded-pill btn-sm" onclick="openMeetingModal()">
                    <i class="bi bi-plus-lg me-1"></i>Nova
                </button>
                <?php endif; ?>
            </div>

            <div class="flex-grow-1 overflow-auto px-2">
                <div class="list-group list-group-flush">
                    <?php if(empty($meetings)): ?>
                        <div class="text-center py-5 opacity-50">
                            <i class="bi bi-calendar-x fs-1 mb-2 d-block"></i>
                            <p class="small">Nenhuma reunião.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach($meetings as $meeting): 
                            $dateObj = new DateTime($meeting['date']);
                        ?>
                        <a href="#" class="list-group-item list-group-item-action p-3 border-0 border-bottom rounded-3 mb-1" 
                           onclick="selectMeeting('<?php echo $meeting['id']; ?>'); return false;">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <h6 class="fw-bold mb-0 text-truncate"><?php echo htmlspecialchars($meeting['title']); ?></h6>
                                <span class="badge bg-light text-dark border"><?php echo $dateObj->format('d/m H:i'); ?></span>
                            </div>
                            <small class="text-muted d-block text-truncate">
                                <i class="bi bi-geo-alt me-1"></i> <?php echo htmlspecialchars($meeting['location'] ?: 'Online'); ?>
                            </small>
                        </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Right: Calendar & Details -->
        <div class="col-lg-9 bg-light d-flex flex-column position-relative h-100">
            
            <!-- Calendar View -->
            <div id="calendar-view" class="h-100 p-4">
                <div class="card border-0 shadow-sm h-100 rounded-4">
                    <div class="card-body p-4">
                        <div id='calendar'></div>
                    </div>
                </div>
            </div>

            <!-- Detail View -->
            <div id="detail-view" class="d-none h-100 flex-column bg-white">
                <!-- Header -->
                <div class="px-5 py-4 border-bottom shadow-sm bg-white sticky-top">
                     <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <button class="btn btn-sm btn-outline-secondary mb-3 rounded-pill" onclick="showCalendar()">
                                <i class="bi bi-arrow-left me-1"></i> Voltar ao Calendário
                            </button>
                            <h2 class="fw-bold mb-2" id="detail-title"></h2>
                            <div class="d-flex align-items-center text-muted gap-3">
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 rounded-pill">
                                    <i class="bi bi-calendar3 me-2"></i><span id="detail-date"></span>
                                </span>
                                <span><i class="bi bi-geo-alt me-1"></i> <span id="detail-location"></span></span>
                            </div>
                            <div class="mt-3 d-none" id="detail-link-container">
                                <a href="#" target="_blank" id="detail-link" class="btn btn-sm btn-outline-primary rounded-pill">
                                    <i class="bi bi-link-45deg me-1"></i> Link da Reunião
                                </a>
                            </div>
                        </div>
                        <?php if($isAdmin): ?>
                        <div id="admin-controls" class="d-none">
                            <button class="btn btn-outline-secondary btn-sm rounded-pill me-1" onclick="editCurrentMeeting()">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-outline-danger btn-sm rounded-pill" onclick="deleteCurrentMeeting()">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Scrollable Content -->
                <div class="flex-grow-1 overflow-auto px-5 py-4">
                    <h5 class="fw-bold mb-3 border-bottom pb-2">Pauta / Agenda</h5>
                    
                    <!-- Quick Add Form (Admin) -->
                    <?php if($isAdmin): ?>
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body p-2 d-flex gap-2">
                            <input type="text" id="quick-agenda-input" class="form-control border-0 bg-light" placeholder="Adicionar item à pauta..." onkeypress="handleQuickAdd(event)">
                            <button class="btn btn-primary rounded-circle shadow-sm" style="width: 38px; height: 38px;" onclick="addQuickAgenda()">
                                <i class="bi bi-plus-lg"></i>
                            </button>
                        </div>
                    </div>
                    <?php endif; ?>

                    <ul class="list-group list-group-flush" id="agenda-list">
                        <!-- Items -->
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Meeting Modal -->
<div class="modal fade" id="meetingModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form id="meetingForm" class="modal-content border-0 shadow">
            <input type="hidden" name="id" id="meetingId">
             <input type="hidden" name="agenda" id="meetingAgendaJson"> 

            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="modalTitle">Nova Reunião</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted">Título</label>
                    <input type="text" class="form-control" name="title" id="meetingTitle" required>
                </div>
                <div class="row">
                    <div class="col-6 mb-3">
                        <label class="form-label small fw-bold text-muted">Data e Hora</label>
                        <input type="datetime-local" class="form-control" name="date" id="meetingDate" required>
                    </div>
                    <div class="col-6 mb-3">
                        <label class="form-label small fw-bold text-muted">Local</label>
                        <input type="text" class="form-control" name="location" id="meetingLocation" placeholder="Sala 1 / Teams">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted">Link (Opcional)</label>
                    <input type="url" class="form-control" name="link" id="meetingLink" placeholder="https://...">
                </div>
                
                <div class="mb-3" id="agenda-input-group">
                    <label class="form-label small fw-bold text-muted">Pauta Inicial (Itens separados por linha)</label>
                    <textarea class="form-control" id="meetingAgendaInput" rows="4" placeholder="- Assunto 1&#10;- Assunto 2"></textarea>
                    <div class="form-text">Você pode gerenciar os itens detalhadamente após criar.</div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary rounded-pill px-4" onclick="saveMeeting()">Salvar</button>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
let currentMeeting = null;
let calendar = null;

document.addEventListener('DOMContentLoaded', () => {
    initCalendar();
});

function initCalendar() {
    const calendarEl = document.getElementById('calendar');
    calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'pt-br',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        buttonText: {
            today: 'Hoje',
            month: 'Mês',
            week: 'Semana',
            day: 'Dia'
        },
        events: meetingsData.map(m => ({
            id: m.id,
            title: m.title,
            start: m.date,
            // color: '#0d6efd'
        })),
        dateClick: function(info) {
            if(isAdmin) {
                openMeetingModal(info.dateStr + 'T09:00');
            }
        },
        eventClick: function(info) {
            selectMeeting(info.event.id);
        }
    });
    calendar.render();
}

function getModal() {
    const el = document.getElementById('meetingModal');
    if(!el) return null;
    return new bootstrap.Modal(el);
}

function showCalendar() {
    document.getElementById('detail-view').classList.add('d-none');
    document.getElementById('calendar-view').classList.remove('d-none');
    // Re-render calendar to fix layout size issues after being hidden
    setTimeout(() => {
        if(calendar) calendar.render();
    }, 50);
}

function selectMeeting(id) {
    const meeting = meetingsData.find(m => m.id === id);
    if(!meeting) return;

    currentMeeting = meeting;
    
    document.getElementById('calendar-view').classList.add('d-none');
    document.getElementById('detail-view').classList.remove('d-none');
    document.getElementById('detail-view').classList.add('d-flex');

    document.getElementById('detail-title').innerText = meeting.title;
    document.getElementById('detail-date').innerText = new Date(meeting.date).toLocaleString('pt-BR');
    document.getElementById('detail-location').innerText = meeting.location || 'Online';
    
    const linkEl = document.getElementById('detail-link');
    if(meeting.link) {
        linkEl.href = meeting.link;
        linkEl.innerText = 'Entrar na Reunião';
        document.getElementById('detail-link-container').classList.remove('d-none');
    } else {
        document.getElementById('detail-link-container').classList.add('d-none');
    }

    renderAgenda(meeting.agenda);

    if(isAdmin) {
        document.getElementById('admin-controls').classList.remove('d-none');
    }
}

function renderAgenda(agenda) {
    const list = document.getElementById('agenda-list');
    list.innerHTML = '';
    
    if(!agenda || agenda.length === 0) {
        list.innerHTML = '<li class="list-group-item bg-transparent text-muted text-center border-0 py-4"><i class="bi bi-journal-x mb-2 d-block fs-4"></i>Nenhum item na pauta.</li>';
        return;
    }

    agenda.forEach((item, index) => {
        const li = document.createElement('li');
        li.className = 'list-group-item bg-transparent border-0 px-0 py-2 d-flex align-items-start';
        
        const checkIcon = item.status === 'done' ? 'bi-check-circle-fill text-success' : 'bi-circle text-muted';
        const cursor = isAdmin ? 'cursor-pointer' : '';
        
        li.innerHTML = `
            <i class="bi ${checkIcon} fs-5 me-3 ${cursor}" onclick="toggleItemStatus(${index})"></i>
            <span class="${item.status === 'done' ? 'text-decoration-line-through text-muted' : ''}">${item.text}</span>
            ${isAdmin ? `<i class="bi bi-x text-danger ms-auto cursor-pointer opacity-50 hover-opacity-100" onclick="deleteItem(${index})"></i>` : ''}
        `;
        list.appendChild(li);
    });
}

function toggleItemStatus(index) {
    if(!isAdmin || !currentMeeting) return;
    const item = currentMeeting.agenda[index];
    item.status = item.status === 'done' ? 'pending' : 'done';
    saveCurrentAgenda();
}

function deleteItem(index) {
    if(!isAdmin || !currentMeeting || !confirm('Remover item?')) return;
    currentMeeting.agenda.splice(index, 1);
    saveCurrentAgenda();
}

function handleQuickAdd(e) {
    if(e.key === 'Enter') addQuickAgenda();
}

function addQuickAgenda() {
    const input = document.getElementById('quick-agenda-input');
    const text = input.value.trim();
    if(!text || !currentMeeting) return;
    
    if(!currentMeeting.agenda) currentMeeting.agenda = [];
    currentMeeting.agenda.push({ text: text, status: 'pending' });
    input.value = '';
    
    saveCurrentAgenda();
}

function saveCurrentAgenda() {
    renderAgenda(currentMeeting.agenda);
    
    const formData = new FormData();
    formData.append('action', 'save_meeting');
    formData.append('id', currentMeeting.id);
    formData.append('title', currentMeeting.title);
    formData.append('date', currentMeeting.date);
    formData.append('location', currentMeeting.location);
    formData.append('link', currentMeeting.link);
    formData.append('agenda', JSON.stringify(currentMeeting.agenda));

    fetch('includes/portal_actions.php', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
        if(!data.success) alert('Erro ao salvar atualização.');
    });
}

function openMeetingModal(defaultDate = '') {
    console.log('openMeetingModal');
    currentMeeting = null;
    document.getElementById('meetingId').value = '';
    document.getElementById('meetingTitle').value = '';
    document.getElementById('meetingDate').value = defaultDate || '';
    document.getElementById('meetingLocation').value = '';
    document.getElementById('meetingLink').value = '';
    document.getElementById('meetingAgendaInput').value = '';
    document.getElementById('meetingAgendaJson').value = '[]';
    
    document.getElementById('agenda-input-group').classList.remove('d-none');
    document.getElementById('modalTitle').innerText = 'Nova Reunião';
    const modal = getModal();
    if(modal) modal.show();
}

function editCurrentMeeting() {
    if(!currentMeeting) return;
    
    document.getElementById('meetingId').value = currentMeeting.id;
    document.getElementById('meetingTitle').value = currentMeeting.title;
    document.getElementById('meetingDate').value = currentMeeting.date; 
    document.getElementById('meetingLocation').value = currentMeeting.location;
    document.getElementById('meetingLink').value = currentMeeting.link;
    
    document.getElementById('meetingAgendaJson').value = JSON.stringify(currentMeeting.agenda || []);
    document.getElementById('agenda-input-group').classList.add('d-none'); 

    document.getElementById('modalTitle').innerText = 'Editar Reunião';
    const modal = getModal();
    if(modal) modal.show();
}

function saveMeeting() {
    const form = document.getElementById('meetingForm');
    const formData = new FormData(form);
    formData.append('action', 'save_meeting');

    if(!document.getElementById('agenda-input-group').classList.contains('d-none')) {
        const text = document.getElementById('meetingAgendaInput').value;
        const lines = text.split('\n').filter(l => l.trim() !== '');
        const agenda = lines.map(l => ({ text: l.trim(), status: 'pending' }));
        formData.set('agenda', JSON.stringify(agenda));
    }

    fetch('includes/portal_actions.php', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
        if(data.success) location.reload();
        else alert('Erro ao salvar.');
    })
    .catch(err => {
        console.error(err);
        alert('Erro de comunicação.');
    });
}

function deleteCurrentMeeting() {
    if(!currentMeeting || !confirm("Excluir esta reunião?")) return;
    
    const formData = new FormData();
    formData.append('action', 'delete_meeting');
    formData.append('meeting_id', currentMeeting.id);

    fetch('includes/portal_actions.php', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
        if(data.success) location.reload();
    })
    .catch(err => console.error(err));
}
</script>
