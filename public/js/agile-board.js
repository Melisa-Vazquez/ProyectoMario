// ============================================================
//  agile-board.js  — KanbanFlow (versión corregida)
// ============================================================

const API_URL      = '/api/boards/1';
const TASK_API_URL = '/api/tasks';
let currentActiveTask      = null;
let originalBoardData      = null;
let isDragging             = false;
let velocityChartInstance  = null;
let currentVelocityPeriod  = 'week';
let usersCache             = [];

// ────────────────────────────────────────────────────────────
// 1. Navegación entre pestañas
// ────────────────────────────────────────────────────────────
function switchView(viewName) {
    document.querySelectorAll('.view-section').forEach(s => s.classList.add('hidden'));
    document.querySelectorAll('nav > button').forEach(b => {
        b.classList.remove('border-indigo-500', 'text-indigo-400', 'font-bold');
        b.classList.add('border-transparent', 'text-slate-400', 'font-semibold');
    });

    document.getElementById(`view-${viewName}`).classList.remove('hidden');
    const activeTab = document.getElementById(`tab-${viewName}`);
    activeTab.classList.remove('border-transparent', 'text-slate-400', 'font-semibold');
    activeTab.classList.add('border-indigo-500', 'text-indigo-400', 'font-bold');

    if (viewName === 'metrics') renderAdvancedMetrics();
    if (viewName === 'history') renderGlobalHistory();
}

// ────────────────────────────────────────────────────────────
// 2. Carga del tablero
// ────────────────────────────────────────────────────────────
async function loadBoard() {
    try {
        const response = await fetch(`${API_URL}?_=${Date.now()}`);
        originalBoardData = await response.json();

        document.getElementById('board-name').innerText = originalBoardData.name;
        document.getElementById('board-desc').innerText = originalBoardData.description;

        let totalTasks = 0, todoTasks = 0, doneTasks = 0;
        originalBoardData.columns.forEach(column => {
            totalTasks += column.tasks.length;
            if (column.position === 1 || column.name.toLowerCase() === 'por hacer') todoTasks = column.tasks.length;
            if (column.name.toLowerCase() === 'terminado' || column.name.toLowerCase() === 'listo') doneTasks = column.tasks.length;
        });

        const progressPercentage = totalTasks > 0 ? Math.round((doneTasks / totalTasks) * 100) : 0;
        document.getElementById('metric-total').innerText   = totalTasks;
        document.getElementById('metric-todo').innerText    = todoTasks;
        document.getElementById('metric-progress').innerText = `${progressPercentage}%`;

        const container = document.getElementById('kanban-container');
        container.innerHTML = '';

        originalBoardData.columns.forEach(column => {
            const columnEl = document.createElement('div');
            columnEl.className = "bg-slate-800 p-4 rounded-xl w-80 shrink-0 shadow-md border border-slate-700 flex flex-col max-h-[72vh]";
            columnEl.setAttribute('data-column-card', column.id);
            columnEl.innerHTML = `
                <div class="column-drag-handle flex justify-between items-center mb-4 shrink-0 cursor-grab active:cursor-grabbing select-none">
                    <h3 class="font-bold text-sm text-slate-200 tracking-wide uppercase">${column.name}</h3>
                    <div class="flex items-center gap-1.5">
                        <span class="bg-slate-700/80 text-[10px] px-2 py-0.5 rounded-full text-slate-300 font-bold">${column.tasks.length}</span>
                        <button onclick="deleteColumn(${column.id}, '${column.name}')" class="text-slate-500 hover:text-red-400 text-xs transition p-1 cursor-pointer" title="Eliminar columna">🗑️</button>
                    </div>
                </div>
                <div id="column-${column.id}" data-column-id="${column.id}"
                     class="task-list space-y-3 overflow-y-auto flex-1 p-1 rounded-lg min-h-[200px]">
                    ${column.tasks.map(task => createCardHtml(task, column.name)).join('')}
                </div>
                <button onclick="openCreateModal(${column.id})"
                        class="w-full mt-3 py-2 border border-dashed border-slate-600 hover:border-indigo-500 hover:text-indigo-400 rounded-lg text-xs text-slate-400 flex items-center justify-center gap-1 transition shrink-0">
                    + Añadir tarjeta
                </button>
            `;
            container.appendChild(columnEl);
            initSortable(column.id);
        });

        initColumnSortable();

        // Botón "añadir columna"
        const addColumnCard = document.createElement('div');
        addColumnCard.className = "w-80 shrink-0 add-col-btn";
        addColumnCard.innerHTML = `
            <button onclick="openCreateColumnPrompt()"
                    class="w-full py-4 bg-slate-800/30 hover:bg-slate-800/70 border-2 border-dashed border-slate-700
                           hover:border-indigo-500/50 text-slate-400 hover:text-indigo-400 font-semibold rounded-xl
                           text-xs uppercase tracking-wider flex items-center justify-center gap-2 transition shadow-md min-h-[100px]">
                ➕ Añadir Nueva Columna
            </button>
        `;
        container.appendChild(addColumnCard);

    } catch (error) {
        console.error("Error al sincronizar datos:", error);
    }
}

// ────────────────────────────────────────────────────────────
// 3. HTML de tarjeta
// ────────────────────────────────────────────────────────────
function createCardHtml(task, columnName = '') {
    const isDone = columnName.toLowerCase() === 'terminado';
    const priorityColors = { low: 'border-green-500', medium: 'border-yellow-500', high: 'border-red-500' };
    const tagsHtml = task.tags
        ? task.tags.split(',').map(t =>
            `<span class="bg-indigo-950/80 text-indigo-300 border border-indigo-800/60 text-[9px] px-1.5 py-0.5 rounded font-medium">${t.trim()}</span>`
          ).join(' ')
        : '';

    let dateBadge = '';
    if (task.duedate) {
        const today = new Date(); today.setHours(0,0,0,0);
        const due   = new Date(task.duedate + 'T00:00:00');
        const diffDays = Math.ceil((due - today) / (1000 * 60 * 60 * 24));
        let badgeColor = 'bg-slate-800 text-slate-400';
        if (diffDays < 0) badgeColor = 'bg-red-950 text-red-400 border border-red-800';
        else if (diffDays <= 1) badgeColor = 'bg-amber-950 text-amber-400 border border-amber-800';
        dateBadge = `<span class="text-[10px] px-1.5 py-0.5 rounded font-bold ${badgeColor}">📅 ${task.duedate}</span>`;
    }

    let assignedHtml = '';
    if (task.assigned_to) {
        const initials = task.assigned_to.trim().split(' ')
            .map(w => w[0]).slice(0, 2).join('').toUpperCase();
        assignedHtml = `
            <span class="flex items-center gap-1.5 text-[10px] font-bold text-violet-300 bg-violet-500/10 border border-violet-500/20 px-1.5 py-0.5 rounded-full" title="${task.assigned_to}">
                <span class="w-4 h-4 rounded-full bg-violet-600 flex items-center justify-center text-[8px] font-black text-white shrink-0">${initials}</span>
                ${task.assigned_to.split(' ')[0]}
            </span>`;
    }

    return `
        <div data-task-id="${task.id}"
             onclick="openDetailsModal(${task.id})"
             class="bg-slate-700 p-4 rounded-xl shadow cursor-pointer hover:bg-slate-600 transition border-l-4 ${priorityColors[task.priority] || 'border-slate-500'}">
            <h4 class="font-bold text-sm tracking-wide ${isDone ? 'line-through text-slate-400' : 'text-white'}">${task.title}</h4>
            <p class="text-xs text-slate-400 mt-1 line-clamp-2">${task.description || ''}</p>
            <div class="flex flex-wrap items-center justify-between gap-2 mt-3">
                <div class="flex flex-wrap gap-1">${tagsHtml}</div>
                <div class="flex items-center gap-2 ml-auto">
                    ${assignedHtml}
                    ${dateBadge}
                </div>
            </div>
        </div>
    `;
}

// ────────────────────────────────────────────────────────────
// 4. SortableJS
// ────────────────────────────────────────────────────────────
function initSortable(columnId) {
    const el = document.getElementById(`column-${columnId}`);
    if (!el) return;

    new Sortable(el, {
        group:      'agile-tasks',
        animation:  150,
        ghostClass: 'opacity-40',
        onStart: () => { isDragging = true; },
        onEnd: async function (evt) {
            setTimeout(() => { isDragging = false; }, 100);

            const taskId    = evt.item.getAttribute('data-task-id');
            const fromColId = evt.from.getAttribute('data-column-id');
            const toColId   = evt.to.getAttribute('data-column-id');
            const isSameCol = fromColId === toColId;

            // Nombre de la columna destino (para historial)
            const colNameEl = evt.to.parentElement?.querySelector('h3');
            const colName   = colNameEl ? colNameEl.innerText : '';

            try {
                // Actualizar posiciones en columna destino
                const updates = [...evt.to.querySelectorAll('[data-task-id]')].map((card, idx) =>
                    fetch(`${TASK_API_URL}/${card.getAttribute('data-task-id')}`, {
                        method:  'PUT',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                        body:    JSON.stringify({ column_id: parseInt(toColId), position: idx + 1 }),
                    })
                );

                // Si cambió de columna, actualizar también la columna origen
                if (!isSameCol) {
                    [...evt.from.querySelectorAll('[data-task-id]')].forEach((card, idx) => {
                        updates.push(
                            fetch(`${TASK_API_URL}/${card.getAttribute('data-task-id')}`, {
                                method:  'PUT',
                                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                                body:    JSON.stringify({ column_id: parseInt(fromColId), position: idx + 1 }),
                            })
                        );
                    });

                    if (colName) logHistoryEvent(taskId, `Movida a la columna "${colName}"`);
                    if (colName.toLowerCase() === 'terminado') {
                        logHistoryEvent(taskId, 'Tarea marcada como Completada ✅');
                        logCompletionToServer(taskId);
                    }
                }

                await Promise.all(updates);
            } catch (err) {
                console.error('Error al reordenar tarea:', err);
            } finally {
                loadBoard();
            }
        },
    });
}

function initColumnSortable() {
    const container = document.getElementById('kanban-container');
    if (!container) return;

    new Sortable(container, {
        animation:   200,
        handle:      '.column-drag-handle',
        filter:      '.add-col-btn',
        draggable:   '[data-column-card]',
        ghostClass:  'opacity-30',
        onEnd: async function (evt) {
            const columnCards = [...container.querySelectorAll('[data-column-card]')];
            try {
                await Promise.all(
                    columnCards.map((card, idx) =>
                        fetch(`/api/columns/${card.getAttribute('data-column-card')}`, {
                            method:  'PUT',
                            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                            body:    JSON.stringify({ position: idx + 1 }),
                        })
                    )
                );
            } catch (err) {
                console.error('Error al reordenar columna:', err);
            } finally {
                loadBoard();
            }
        },
    });
}

// ────────────────────────────────────────────────────────────
// 5. Columnas: crear y eliminar
// ────────────────────────────────────────────────────────────
function openCreateColumnPrompt() {
    if (originalBoardData?.columns?.length >= 10) {
        alert('Límite alcanzado: el tablero no puede tener más de 10 columnas.');
        return;
    }
    const modal = document.getElementById('column-prompt-modal');
    const input = document.getElementById('column-name-input');
    const acceptBtn = document.getElementById('column-prompt-accept');
    const cancelBtn = document.getElementById('column-prompt-cancel');

    input.value = '';
    input.placeholder = 'Nombre de la columna...';
    input.classList.remove('border-red-500');
    modal.classList.remove('hidden');
    input.focus();

    const close = () => {
        modal.classList.add('hidden');
        acceptBtn.removeEventListener('click', onAccept);
        cancelBtn.removeEventListener('click', onCancel);
        input.removeEventListener('keydown', onKeydown);
    };

    const onAccept = async () => {
        const columnName = input.value.trim();
        if (!columnName) return;

        const duplicado = originalBoardData?.columns?.some(
            c => c.name.toLowerCase() === columnName.toLowerCase()
        );
        if (duplicado) {
            input.classList.add('border-red-500');
            input.placeholder = 'Ese nombre ya existe, elige otro...';
            input.value = '';
            return;
        }

        close();

        const currentPosition = originalBoardData?.columns?.length + 1 || 1;
        try {
            const response = await fetch('/api/columns', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ board_id: 1, name: columnName, position: currentPosition })
            });
            if (response.ok) {
                await loadBoard();
            } else {
                const data = await response.json().catch(() => ({}));
                alert(data.error || 'No se pudo guardar la columna.');
            }
        } catch (error) {
            alert("Error de conexión con el servidor.");
        }
    };

    const onCancel = () => close();

    const onKeydown = (e) => {
        if (e.key === 'Enter') onAccept();
        if (e.key === 'Escape') close();
    };

    acceptBtn.addEventListener('click', onAccept);
    cancelBtn.addEventListener('click', onCancel);
    input.addEventListener('keydown', onKeydown);
}

async function deleteColumn(columnId, columnName) {
    if (!confirm(`¿Eliminar la columna "${columnName}"?\nSe borrarán todas las tareas que contenga.`)) return;

    try {
        const response = await fetch(`/api/columns/${columnId}`, {
            method: 'DELETE',
            headers: { 'Accept': 'application/json' }
        });
        if (response.ok) {
            await loadBoard();
        } else {
            alert("El servidor no permitió borrar la columna.");
        }
    } catch (error) {
        alert("Error de conexión al intentar borrar.");
    }
}

// ────────────────────────────────────────────────────────────
// 6. Modal de CREAR / EDITAR tarea
// ────────────────────────────────────────────────────────────
function setDateMins() {
    const today = new Date().toISOString().split('T')[0];
    ['task-duedate', 'task-plan-start', 'task-plan-end'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.min = today;
    });
}

function updatePlanEndMin() {
    const start = document.getElementById('task-plan-start')?.value;
    const endEl = document.getElementById('task-plan-end');
    if (endEl && start) {
        endEl.min = start;
        if (endEl.value && endEl.value < start) endEl.value = start;
    }
}

function openCreateModal(columnId) {
    document.getElementById('modal-task-id').value  = '';
    document.getElementById('modal-column-id').value = columnId;
    document.getElementById('task-form').reset();
    document.getElementById('modal-form-title').innerText = 'Nueva Tarea';
    populateAssignedSelect();
    setDateMins();
    document.getElementById('task-modal').classList.remove('hidden');
}

function openEditFromDetails() {
    if (!currentActiveTask) return;
    document.getElementById('modal-task-id').value      = currentActiveTask.id;
    document.getElementById('modal-column-id').value    = currentActiveTask.column_id;
    document.getElementById('task-title').value         = currentActiveTask.title;
    document.getElementById('task-desc').value          = currentActiveTask.description || '';
    document.getElementById('task-priority').value      = currentActiveTask.priority;
    document.getElementById('task-tags').value          = currentActiveTask.tags || '';
    document.getElementById('task-duedate').value       = currentActiveTask.duedate || '';
    document.getElementById('task-plan-start').value    = currentActiveTask.plan_start || '';
    document.getElementById('task-plan-end').value      = currentActiveTask.plan_end || '';
    populateAssignedSelect(currentActiveTask.assigned_to || '');
    document.getElementById('modal-form-title').innerText = 'Editar Tarea';
    setDateMins();
    document.getElementById('details-modal').classList.add('hidden');
    document.getElementById('task-modal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('task-modal').classList.add('hidden');
}

async function saveTask(event) {
    event.preventDefault();
    const taskId = document.getElementById('modal-task-id').value;
    const payload = {
        column_id:   document.getElementById('modal-column-id').value,
        title:       document.getElementById('task-title').value,
        description: document.getElementById('task-desc').value,
        priority:    document.getElementById('task-priority').value,
        tags:        document.getElementById('task-tags').value,
        duedate:     document.getElementById('task-duedate').value    || null,
        plan_start:  document.getElementById('task-plan-start').value || null,
        plan_end:    document.getElementById('task-plan-end').value   || null,
        assigned_to: document.getElementById('task-assigned').value   || null,
    };

    const url    = taskId ? `${TASK_API_URL}/${taskId}` : TASK_API_URL;
    const method = taskId ? 'PUT' : 'POST';

    try {
        const response = await fetch(url, {
            method,
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify(payload)
        });
        if (response.ok) {
            const resData = await response.json();
            const id = taskId || resData.id;
            logHistoryEvent(id, taskId ? 'Campos internos modificados.' : 'Tarea registrada inicialmente.');
            closeModal();
            loadBoard();
        }
    } catch (error) { console.error(error); }
}

// ────────────────────────────────────────────────────────────
// 7. Modal de DETALLES  ← aquí estaban los bugs
// ────────────────────────────────────────────────────────────

function openDetailsModal(taskId) {
    if (isDragging) return;
    let foundTask = null;
    originalBoardData.columns.forEach(col => {
        const t = col.tasks.find(tk => tk.id === taskId);
        if (t) foundTask = t;
    });
    if (!foundTask) return;
    currentActiveTask = foundTask;

    const fmt = d => d
        ? new Date(d + 'T00:00:00').toLocaleDateString('es-MX', { day: '2-digit', month: 'short', year: 'numeric' })
        : '—';

    // Título e ID
    const titleEl = document.getElementById('detail-title');
    titleEl.innerText = foundTask.title;

    const isDone = originalBoardData.columns.find(c => c.id === foundTask.column_id)?.name.toLowerCase() === 'terminado';
    if (isDone) {
        titleEl.classList.add('line-through', 'text-slate-400');
        titleEl.classList.remove('text-white');
    } else {
        titleEl.classList.remove('line-through', 'text-slate-400');
        titleEl.classList.add('text-white');
    }

    document.getElementById('detail-task-id').innerText = `#${foundTask.id}`;
    document.getElementById('detail-desc').innerText    = foundTask.description || 'Sin descripción añadida.';

    // Prioridad — dot + badge
    const prioColors = {
        high:   { dot: 'bg-red-500',    badge: 'bg-red-500/15 text-red-400 border border-red-500/30' },
        medium: { dot: 'bg-yellow-500', badge: 'bg-yellow-500/15 text-yellow-400 border border-yellow-500/30' },
        low:    { dot: 'bg-green-500',  badge: 'bg-green-500/15 text-green-400 border border-green-500/30' },
    };
    const pc = prioColors[foundTask.priority] || prioColors.low;
    document.getElementById('detail-color-dot').className = `w-3 h-3 rounded-full mt-2 shrink-0 ${pc.dot}`;
    const prioEl = document.getElementById('detail-priority');
    prioEl.innerText   = foundTask.priority.toUpperCase();
    prioEl.className   = `text-[11px] font-bold uppercase px-2.5 py-1 rounded-full ${pc.badge}`;

    // Fecha límite
    const dateEl = document.getElementById('detail-duedate');
    dateEl.innerText = foundTask.duedate ? `📅 ${fmt(foundTask.duedate)}` : '📅 Sin fecha límite';

    // Chip de plan
    const chipPlan = document.getElementById('chip-plan');
    if (chipPlan) {
        if (foundTask.plan_start || foundTask.plan_end) {
            chipPlan.innerHTML = `🗓️ ${fmt(foundTask.plan_start)} → ${fmt(foundTask.plan_end)}`;
            chipPlan.classList.remove('hidden');
        } else {
            chipPlan.innerHTML = '🗓️ Sin plan de trabajo';
        }
    }

    // Plan de trabajo detallado
    const planEl = document.getElementById('detail-plan');
    if (planEl) {
        planEl.innerHTML = `
            <div>
                <span class="text-slate-600 text-xs block mb-1">Inicio planeado</span>
                <span class="text-indigo-300 font-bold">${fmt(foundTask.plan_start)}</span>
            </div>
            <div>
                <span class="text-slate-600 text-xs block mb-1">Fin planeado</span>
                <span class="text-indigo-300 font-bold">${fmt(foundTask.plan_end)}</span>
            </div>
        `;
    }

    // Responsable
    const assignedEl     = document.getElementById('detail-assigned');
    const assignedNameEl = document.getElementById('detail-assigned-name');
    if (assignedEl && assignedNameEl) {
        if (foundTask.assigned_to) {
            assignedNameEl.innerText = foundTask.assigned_to;
            assignedEl.classList.remove('hidden');
            assignedEl.classList.add('flex');
        } else {
            assignedEl.classList.add('hidden');
            assignedEl.classList.remove('flex');
        }
    }

    // Selector de columnas
    const colSelect = document.getElementById('detail-column-select');
    if (colSelect && originalBoardData?.columns) {
        colSelect.innerHTML = originalBoardData.columns.map(col => `
            <option value="${col.id}" ${col.id === foundTask.column_id ? 'selected' : ''}>${col.name}</option>
        `).join('');
    }

    // Botón completar
    const terminadoCol = originalBoardData?.columns?.find(c => c.name.toLowerCase() === 'terminado');
    const btnComplete  = document.getElementById('btn-complete-task');
    if (btnComplete) {
        btnComplete.classList.toggle('hidden', !!(terminadoCol && foundTask.column_id === terminadoCol.id));
    }

    renderSubtasks(taskId);
    renderComments(taskId);
    renderHistory(taskId, foundTask.created_at);

    // Abrir panel con animación
    const modal = document.getElementById('details-modal');
    const panel = document.getElementById('details-panel');
    modal.classList.remove('hidden');
    requestAnimationFrame(() => panel.classList.replace('translate-x-full', 'translate-x-0'));
}

function closeDetailsModal() {
    const panel = document.getElementById('details-panel');
    panel.classList.replace('translate-x-0', 'translate-x-full');
    setTimeout(() => {
        document.getElementById('details-modal').classList.add('hidden');
        currentActiveTask = null;
    }, 300);
}

// ────────────────────────────────────────────────────────────
// Subtareas (localStorage)
// ────────────────────────────────────────────────────────────
function renderSubtasks(taskId) {
    const list    = document.getElementById('subtask-list');
    const counter = document.getElementById('subtask-counter');
    const bar     = document.getElementById('subtask-bar');
    if (!list) return;

    const items = JSON.parse(localStorage.getItem(`subtasks_${taskId}`)) || [];
    const done  = items.filter(i => i.done).length;
    const total = items.length;

    counter.innerText = `${done} / ${total}`;
    bar.style.width   = total > 0 ? `${Math.round((done / total) * 100)}%` : '0%';

    if (items.length === 0) {
        list.innerHTML = '<p class="text-xs text-slate-600 italic">No hay subtareas aún.</p>';
        return;
    }

    list.innerHTML = items.map((item, idx) => `
        <div class="flex items-center gap-3 group py-1 px-2 rounded-lg hover:bg-slate-800/60 transition">
            <input type="checkbox" ${item.done ? 'checked' : ''}
                onchange="toggleSubtask(${taskId}, ${idx})"
                class="w-4 h-4 rounded accent-indigo-500 cursor-pointer shrink-0">
            <span class="flex-1 text-sm ${item.done ? 'line-through text-slate-600' : 'text-slate-200'}">${item.text}</span>
            <button onclick="deleteSubtask(${taskId}, ${idx})"
                class="opacity-0 group-hover:opacity-100 text-slate-600 hover:text-red-400 text-xs transition shrink-0">✕</button>
        </div>
    `).join('');
}

function addSubtask() {
    if (!currentActiveTask) return;
    const input = document.getElementById('subtask-input');
    const text  = input?.value.trim();
    if (!text) return;

    const items = JSON.parse(localStorage.getItem(`subtasks_${currentActiveTask.id}`)) || [];
    items.push({ text, done: false });
    localStorage.setItem(`subtasks_${currentActiveTask.id}`, JSON.stringify(items));
    input.value = '';
    renderSubtasks(currentActiveTask.id);
}

function toggleSubtask(taskId, idx) {
    const items = JSON.parse(localStorage.getItem(`subtasks_${taskId}`)) || [];
    if (items[idx] !== undefined) {
        items[idx].done = !items[idx].done;
        localStorage.setItem(`subtasks_${taskId}`, JSON.stringify(items));
        renderSubtasks(taskId);
    }
}

function deleteSubtask(taskId, idx) {
    const items = JSON.parse(localStorage.getItem(`subtasks_${taskId}`)) || [];
    items.splice(idx, 1);
    localStorage.setItem(`subtasks_${taskId}`, JSON.stringify(items));
    renderSubtasks(taskId);
}

async function moveTaskToColumn(newColumnId) {
    if (!currentActiveTask) return;
    newColumnId = parseInt(newColumnId);
    if (newColumnId === currentActiveTask.column_id) return;

    const targetCol = originalBoardData?.columns?.find(c => c.id === newColumnId);
    const colName   = targetCol ? targetCol.name : 'otra columna';

    try {
        const response = await fetch(`${TASK_API_URL}/${currentActiveTask.id}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({ column_id: newColumnId })
        });

        if (response.ok) {
            logHistoryEvent(currentActiveTask.id, `Movida a la columna "${colName}"`);
            if (targetCol && targetCol.name.toLowerCase() === 'terminado') {
                logHistoryEvent(currentActiveTask.id, 'Tarea marcada como Completada ✅');
                logCompletionToServer(currentActiveTask.id);
            }
            currentActiveTask.column_id = newColumnId;

            // Actualizar botón Completar según nueva columna
            const terminadoCol = originalBoardData?.columns?.find(
                c => c.name.toLowerCase() === 'terminado'
            );
            const btnComplete = document.getElementById('btn-complete-task');
            if (btnComplete) {
                btnComplete.classList.toggle('hidden', terminadoCol && newColumnId === terminadoCol.id);
            }

            // Actualizar título (tachado o no)
            const isDoneNow = terminadoCol && newColumnId === terminadoCol.id;
            const titleEl = document.getElementById('detail-title');
            if (isDoneNow) {
                titleEl.classList.add('line-through', 'text-slate-400');
                titleEl.classList.remove('text-white');
            } else {
                titleEl.classList.remove('line-through', 'text-slate-400');
                titleEl.classList.add('text-white');
            }

            await loadBoard();
        } else {
            // Revertir el select al valor anterior si falla
            const colSelect = document.getElementById('detail-column-select');
            if (colSelect) colSelect.value = currentActiveTask.column_id;
            alert('No se pudo mover la tarea.');
        }
    } catch (error) {
        console.error('Error al mover tarea:', error);
    }
}

async function completeTask() {
    if (!currentActiveTask) return;

    const terminadoCol = originalBoardData?.columns?.find(
        c => c.name.toLowerCase() === 'terminado'
    );

    if (!terminadoCol) {
        alert("No existe una columna 'Terminado'. Créala primero.");
        return;
    }

    if (currentActiveTask.column_id === terminadoCol.id) return;

    try {
        const response = await fetch(`${TASK_API_URL}/${currentActiveTask.id}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({ column_id: terminadoCol.id })
        });

        if (response.ok) {
            logHistoryEvent(currentActiveTask.id, 'Tarea marcada como Completada ✅');
            await logCompletionToServer(currentActiveTask.id);
            closeDetailsModal();
            await loadBoard();
        } else {
            alert('No se pudo completar la tarea.');
        }
    } catch (error) {
        console.error('Error al completar tarea:', error);
    }
}

/** ✅ FUNCIÓN CORREGIDA — eliminaba la tarea pero no existía */
async function deleteCurrentTask() {
    if (!currentActiveTask) return;

    if (!confirm(`¿Eliminar la tarea "${currentActiveTask.title}"? Esta acción no se puede deshacer.`)) return;

    try {
        const response = await fetch(`${TASK_API_URL}/${currentActiveTask.id}`, {
            method:  'DELETE',
            headers: { 'Accept': 'application/json' }
        });

        if (response.ok) {
            // Borrar historial y comentarios locales de esa tarea
            localStorage.removeItem(`history_${currentActiveTask.id}`);
            localStorage.removeItem(`comments_${currentActiveTask.id}`);

            closeDetailsModal();
            loadBoard();
        } else {
            alert("El servidor no pudo eliminar la tarea.");
        }
    } catch (error) {
        console.error("Error al eliminar tarea:", error);
        alert("Error de conexión al intentar eliminar.");
    }
}

/** ✅ FUNCIÓN CORREGIDA — guardaba comentarios pero no existía */
function addComment() {
    if (!currentActiveTask) return;

    const input = document.getElementById('comment-input');
    const text  = input ? input.value.trim() : '';

    if (!text) {
        alert("Escribe un comentario antes de enviar.");
        return;
    }

    const comments = JSON.parse(localStorage.getItem(`comments_${currentActiveTask.id}`)) || [];
    comments.push({
        text,
        author: window.currentUser?.name ?? 'Usuario',
        date:   new Date().toLocaleString('es-MX'),
    });
    localStorage.setItem(`comments_${currentActiveTask.id}`, JSON.stringify(comments));

    // Registrar en historial
    logHistoryEvent(currentActiveTask.id, `Comentario añadido: "${text.substring(0, 40)}..."`);

    input.value = '';
    renderComments(currentActiveTask.id);
}

// ────────────────────────────────────────────────────────────
// 8. Métricas del modal de tarea
// ────────────────────────────────────────────────────────────
function renderTaskModalMetrics(task) {
    const metricsContainer = document.getElementById('task-modal-metrics');
    if (!metricsContainer) return;

    const historyLogs = JSON.parse(localStorage.getItem(`history_${task.id}`)) || [];
    const totalMoves  = historyLogs.filter(h => h.log.includes('Movido')).length;

    metricsContainer.innerHTML = `
        <div class="grid grid-cols-2 gap-3 bg-slate-900/60 p-3 rounded-xl border border-slate-700/40 text-xs">
            <div>
                <span class="text-slate-400 block mb-0.5">Actividad total:</span>
                <span class="text-indigo-400 font-bold">${historyLogs.length + 1} registros</span>
            </div>
            <div>
                <span class="text-slate-400 block mb-0.5">Interacciones (WIP):</span>
                <span class="text-emerald-400 font-bold">🔄 Moved ${totalMoves} veces</span>
            </div>
        </div>
    `;
}

// ────────────────────────────────────────────────────────────
// 9. Comentarios e historial por tarea (localStorage)
// ────────────────────────────────────────────────────────────
function renderComments(taskId) {
    const container = document.getElementById('comments-list');
    if (!container) return;
    const comments = JSON.parse(localStorage.getItem(`comments_${taskId}`)) || [];

    if (comments.length === 0) {
        container.innerHTML = '<p class="text-xs text-slate-500 italic p-2 bg-slate-900/30 rounded border border-dashed border-slate-700/50">No hay comentarios en esta tarea aún.</p>';
        return;
    }

    container.innerHTML = comments.map(c => {
        const author   = c.author ?? 'Usuario';
        const initials = author.trim().split(' ').map(w => w[0]).slice(0, 2).join('').toUpperCase();
        const isMe     = window.currentUser && c.author === window.currentUser.name;
        return `
        <div class="bg-slate-900/80 p-2.5 rounded-lg border border-slate-700/50 text-xs text-slate-200">
            <div class="flex items-center gap-2 mb-1.5">
                <div class="w-5 h-5 rounded-full ${isMe ? 'bg-indigo-600' : 'bg-slate-600'} flex items-center justify-center text-[9px] font-black text-white shrink-0">
                    ${initials}
                </div>
                <span class="font-bold ${isMe ? 'text-indigo-400' : 'text-slate-300'} text-[11px]">${author}</span>
                <span class="text-[9px] text-slate-600 ml-auto">${c.date}</span>
            </div>
            <p class="leading-relaxed text-slate-300 pl-7">${c.text}</p>
        </div>`;
    }).join('');
}

function renderHistory(taskId, createdAt) {
    const container = document.getElementById('history-list');
    if (!container) return;

    // Filtramos la creación duplicada e invertimos el orden para mostrar los más recientes primero
    const history = (JSON.parse(localStorage.getItem(`history_${taskId}`)) || [])
        .filter(h => h.log !== 'Tarea registrada inicialmente.')
        .reverse();

    let html = '';

    // Agregamos primero los eventos del historial local (los más nuevos)
    html += history.map(h => `
        <div class="flex items-start gap-2 text-xs text-slate-300 border-l-2 border-indigo-500 pl-3 py-1 bg-indigo-500/5 my-1 rounded-r">
            <span class="mt-0.5">🔄</span>
            <p class="flex-1">${h.log}</p>
            <span class="text-[9px] text-slate-500 shrink-0 mt-0.5">${h.date}</span>
        </div>
    `).join('');

    // Agregamos el registro inicial (el evento más antiguo) al final
    html += `
        <div class="flex items-center gap-2 text-xs text-slate-400 border-l-2 border-emerald-500 pl-3 py-1 bg-emerald-500/5 my-1 rounded-r">
            <span>🟢 Tarea registrada en el sistema</span>
            <span class="text-[10px] text-slate-600 ml-auto">${new Date(createdAt).toLocaleString('es-MX')}</span>
        </div>
    `;

    container.innerHTML = html;
}

// ────────────────────────────────────────────────────────────
// 10. Log de historial en localStorage
// ────────────────────────────────────────────────────────────
function logHistoryEvent(taskId, message) {
    const key     = `history_${taskId}`;
    const history = JSON.parse(localStorage.getItem(key)) || [];
    const now     = new Date();
    history.push({
        log:  message,
        date: now.toLocaleString('es-MX'),
        iso:  now.toISOString(),
    });
    localStorage.setItem(key, JSON.stringify(history));
}

function parseHistoryDate(entry) {
    if (!entry) return new Date(0);
    if (entry.iso) return new Date(entry.iso);
    
    const dateStr = entry.date || '';
    try {
        const parts = dateStr.split(',');
        if (parts.length >= 1) {
            const dateParts = parts[0].trim().split('/');
            if (dateParts.length === 3) {
                const day = parseInt(dateParts[0], 10);
                const month = parseInt(dateParts[1], 10) - 1;
                const year = parseInt(dateParts[2], 10);
                
                let hours = 0, minutes = 0, seconds = 0;
                if (parts.length >= 2) {
                    const timeStr = parts[1].trim().toLowerCase();
                    const timeMatch = timeStr.match(/(\d+):(\d+):?(\d+)?/);
                    if (timeMatch) {
                        hours = parseInt(timeMatch[1], 10);
                        minutes = parseInt(timeMatch[2], 10);
                        seconds = timeMatch[3] ? parseInt(timeMatch[3], 10) : 0;
                        
                        if (timeStr.includes('p') && hours < 12) hours += 12;
                        else if (timeStr.includes('a') && hours === 12) hours = 0;
                    }
                }
                return new Date(year, month, day, hours, minutes, seconds);
            }
        }
    } catch (e) {
        console.error("Error parsing history date:", e);
    }

    const parsed = Date.parse(dateStr);
    if (!isNaN(parsed)) return new Date(parsed);

    return new Date(0);
}

// ────────────────────────────────────────────────────────────
// 11. Filtros
// ────────────────────────────────────────────────────────────
function applyFilters() {
    const text = document.getElementById('search-input').value.toLowerCase().trim();
    const prio = document.getElementById('filter-priority').value;

    document.querySelectorAll('.task-list > div[data-task-id]').forEach(card => {
        const title    = card.querySelector('h4').innerText.toLowerCase();
        let cardPrio   = 'medium';
        if (card.classList.contains('border-green-500')) cardPrio = 'low';
        if (card.classList.contains('border-red-500'))   cardPrio = 'high';

        const matchText = title.includes(text);
        const matchPrio = prio === 'all' || cardPrio === prio;
        card.classList.toggle('hidden', !(matchText && matchPrio));
    });
}

function clearFilters() {
    document.getElementById('search-input').value   = '';
    document.getElementById('filter-priority').value = 'all';
    applyFilters();
}

// ────────────────────────────────────────────────────────────
// 12. Métricas avanzadas e historial global
// ────────────────────────────────────────────────────────────
function renderAdvancedMetrics() {
    if (!originalBoardData) return;

    const today = new Date(); today.setHours(0, 0, 0, 0);
    const terminadoCol = originalBoardData.columns.find(
        c => c.name.toLowerCase() === 'terminado'
    );
    const terminadoId = terminadoCol?.id;

    let total = 0, done = 0, overdue = 0;
    let low = 0, med = 0, high = 0;

    const colCounts = originalBoardData.columns.map(col => {
        total += col.tasks.length;
        if (col.id === terminadoId) done = col.tasks.length;

        col.tasks.forEach(t => {
            if (t.priority === 'low')       low++;
            else if (t.priority === 'high') high++;
            else                            med++;

            if (t.duedate && col.id !== terminadoId) {
                const due = new Date(t.duedate + 'T00:00:00');
                if (due < today) overdue++;
            }
        });

        return { name: col.name, count: col.tasks.length, isDone: col.id === terminadoId };
    });

    const pending  = total - done;
    const donePct  = total > 0 ? Math.round((done / total) * 100) : 0;
    const pct      = v => total > 0 ? Math.round((v / total) * 100) : 0;

    // KPI cards
    const set = (id, val) => { const el = document.getElementById(id); if (el) el.innerText = val; };
    set('m-total',   total);
    set('m-done',    done);
    set('m-pending', pending);
    set('m-overdue', overdue);
    set('m-done-pct', donePct + '% del total');
    const doneBar = document.getElementById('m-done-bar');
    if (doneBar) doneBar.style.width = donePct + '%';

    // Header metrics
    set('metric-total',    total);
    set('metric-progress', donePct + '%');

    // Column distribution
    const colDist  = document.getElementById('m-col-dist');
    const maxCount = Math.max(...colCounts.map(c => c.count), 1);
    if (colDist) {
        colDist.innerHTML = colCounts.map(c => {
            const barPct   = Math.round((c.count / maxCount) * 100);
            const barColor = c.isDone ? 'bg-emerald-500' : 'bg-indigo-500';
            return `
                <div class="flex items-center gap-3">
                    <span class="text-xs text-slate-300 w-32 shrink-0 truncate font-medium">${c.name}</span>
                    <div class="flex-1 bg-slate-700 h-2 rounded-full overflow-hidden">
                        <div class="${barColor} h-full rounded-full transition-all duration-500" style="width:${barPct}%"></div>
                    </div>
                    <span class="text-xs font-bold text-slate-400 w-6 text-right shrink-0">${c.count}</span>
                </div>
            `;
        }).join('');
    }

    // Priority distribution
    const distEl = document.getElementById('metrics-priority-distribution');
    if (distEl) {
        const row = (label, cls, barCls, count) => `
            <div class="flex items-center gap-3">
                <span class="text-xs font-bold ${cls} w-12 shrink-0">${label}</span>
                <div class="flex-1 bg-slate-700 h-2 rounded-full overflow-hidden">
                    <div class="${barCls} h-full rounded-full" style="width:${pct(count)}%"></div>
                </div>
                <span class="text-xs text-slate-500 w-20 text-right shrink-0">${count} (${pct(count)}%)</span>
            </div>
        `;
        distEl.innerHTML =
            row('Alta',  'text-red-400',    'bg-red-500',    high) +
            row('Media', 'text-yellow-400', 'bg-yellow-500', med)  +
            row('Baja',  'text-green-400',  'bg-green-500',  low);
    }

    // Velocity chart
    buildVelocityChart(currentVelocityPeriod);
}

async function logCompletionToServer(taskId) {
    try {
        await fetch('/api/completions', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({
                task_id: taskId,
                user_id: window.currentUser?.id ?? null,
            }),
        });
    } catch (e) { console.error('Error registrando completado:', e); }
}

function setVelocityPeriod(period) {
    currentVelocityPeriod = period;
    const active   = 'text-xs font-bold px-3 py-1.5 rounded-lg transition bg-indigo-600 text-white';
    const inactive = 'text-xs font-bold px-3 py-1.5 rounded-lg transition bg-slate-700 text-slate-300 hover:bg-slate-600';
    const wBtn = document.getElementById('btn-period-week');
    const mBtn = document.getElementById('btn-period-month');
    if (wBtn) wBtn.className = period === 'week'  ? active : inactive;
    if (mBtn) mBtn.className = period === 'month' ? active : inactive;
    buildVelocityChart(period);
}

async function buildVelocityChart(period) {
    const canvas = document.getElementById('velocity-chart');
    if (!canvas) return;

    // Leer completados del equipo desde la base de datos
    let rawCompletions = [];
    try {
        const res = await fetch(`/api/completions?period=${period}&_=${Date.now()}`);
        rawCompletions = await res.json();
    } catch (e) { console.error('Error cargando completados:', e); }

    const completions = rawCompletions.map(c => new Date(c.completed_at));

    const labels = [];
    const data   = [];

    if (period === 'week') {
        for (let i = 6; i >= 0; i--) {
            const d = new Date();
            d.setDate(d.getDate() - i);
            d.setHours(0, 0, 0, 0);
            const next = new Date(d.getTime() + 86400000);
            
            let dayName = d.toLocaleDateString('es-MX', { weekday: 'short' }).replace('.', '');
            dayName = dayName.charAt(0).toUpperCase() + dayName.slice(1);
            labels.push(`${dayName} ${d.getDate()}`);
            
            data.push(completions.filter(c => c >= d && c < next).length);
        }
    } else {
        const formatLabel = (date) => {
            let s = date.toLocaleDateString('es-MX', { day: 'numeric', month: 'short' }).replace('.', '');
            return s.split(' ').map(w => w === 'de' ? w : w.charAt(0).toUpperCase() + w.slice(1)).join(' ');
        };

        for (let w = 3; w >= 0; w--) {
            const wEnd = new Date();
            wEnd.setDate(wEnd.getDate() - w * 7);
            wEnd.setHours(23, 59, 59, 999);
            const wStart = new Date(wEnd);
            wStart.setDate(wStart.getDate() - 6);
            wStart.setHours(0, 0, 0, 0);

            labels.push(`${formatLabel(wStart)} – ${formatLabel(wEnd)}`);
            data.push(completions.filter(c => c >= wStart && c <= wEnd).length);
        }
    }

    if (velocityChartInstance) {
        velocityChartInstance.destroy();
        velocityChartInstance = null;
    }

    velocityChartInstance = new Chart(canvas, {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                label: 'Tareas completadas',
                data,
                backgroundColor: 'rgba(99, 102, 241, 0.45)',
                borderColor:     'rgba(99, 102, 241, 1)',
                borderWidth: 2,
                borderRadius: 6,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#1e2330',
                    borderColor: '#334155',
                    borderWidth: 1,
                    titleColor: '#94a3b8',
                    bodyColor: '#e2e8f0',
                    padding: 10,
                    callbacks: {
                        label: ctx => {
                            const v = ctx.parsed.y;
                            return `  ${v} tarea${v !== 1 ? 's' : ''} completada${v !== 1 ? 's' : ''}`;
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid:  { color: 'rgba(51, 65, 85, 0.5)' },
                    ticks: { color: '#64748b', font: { size: 11 } }
                },
                y: {
                    grid:       { color: 'rgba(51, 65, 85, 0.5)' },
                    ticks:      { color: '#64748b', font: { size: 11 }, stepSize: 1, precision: 0 },
                    beginAtZero: true,
                    min: 0,
                }
            }
        }
    });
}

function renderGlobalHistory() {
    const container = document.getElementById('global-history-list');
    if (!container) return;

    let globalLogs = [];
    for (let i = 0; i < localStorage.length; i++) {
        const key = localStorage.key(i);
        if (key && key.startsWith('history_')) {
            const id   = key.replace('history_', '');
            const logs = JSON.parse(localStorage.getItem(key)) || [];
            logs.forEach(lg => globalLogs.push({
                text: `Tarea ID #${id}: ${lg.log}`,
                date: lg.date,
                parsedDate: parseHistoryDate(lg)
            }));
        }
    }

    if (globalLogs.length === 0) {
        container.innerHTML = '<p class="text-xs text-slate-500 italic text-center py-4">Sin actividad registrada aún.</p>';
        return;
    }

    globalLogs.sort((a, b) => b.parsedDate - a.parsedDate);
    container.innerHTML = globalLogs.map(g => `
        <div class="bg-slate-900 p-2 rounded text-xs flex justify-between gap-4">
            <span>🔄 ${g.text}</span>
            <span class="text-slate-500 shrink-0">${g.date}</span>
        </div>
    `).join('');
}

function clearGlobalHistory() {
    if (!confirm("¿Limpiar todo el historial global?")) return;
    
    // Recolectamos primero todas las claves para borrar de forma segura
    const keysToRemove = [];
    for (let i = 0; i < localStorage.length; i++) {
        const key = localStorage.key(i);
        if (key && key.startsWith('history_')) {
            keysToRemove.push(key);
        }
    }
    
    // Eliminamos las claves del localStorage
    keysToRemove.forEach(key => localStorage.removeItem(key));
    
    renderGlobalHistory();
}

// ────────────────────────────────────────────────────────────
// 13. Roles
// ────────────────────────────────────────────────────────────
function isAdmin() {
    return window.currentUser?.role === 'admin';
}

async function openAdminPanel() {
    document.getElementById('admin-panel-modal').classList.remove('hidden');
    const list = document.getElementById('admin-users-list');
    list.innerHTML = '<p class="text-slate-500 text-sm text-center py-4">Cargando...</p>';

    try {
        const res   = await fetch('/admin/users');
        const users = await res.json();

        list.innerHTML = users.map(u => {
            const isCurrentUser = u.id === window.currentUser?.id;
            const isUserAdmin   = u.role === 'admin';
            const initials      = u.name.trim().split(' ').map(w => w[0]).slice(0,2).join('').toUpperCase();
            const avatarColor   = isUserAdmin ? 'bg-amber-500' : 'bg-indigo-600';
            const roleBadge     = isUserAdmin
                ? '<span class="text-[10px] font-bold text-amber-400 bg-amber-500/10 border border-amber-500/20 px-2 py-0.5 rounded-full">★ Admin</span>'
                : '<span class="text-[10px] font-bold text-slate-400 bg-slate-700 border border-slate-600 px-2 py-0.5 rounded-full">Miembro</span>';

            const toggleBtn = !isCurrentUser ? `
                <button onclick="toggleUserRole(${u.id}, '${u.role}')"
                    class="text-xs font-bold px-3 py-1.5 rounded-lg transition shrink-0
                           ${isUserAdmin
                               ? 'bg-slate-700 hover:bg-red-500/20 text-slate-300 hover:text-red-400 border border-slate-600 hover:border-red-500/30'
                               : 'bg-amber-500/10 hover:bg-amber-500 text-amber-400 hover:text-white border border-amber-500/20'}">
                    ${isUserAdmin ? 'Quitar admin' : 'Hacer admin'}
                </button>` : '<span class="text-[10px] text-slate-600 italic">Tú</span>';

            return `
                <div class="flex items-center gap-3 p-3 bg-slate-900/50 rounded-xl border border-slate-700/50">
                    <div class="w-9 h-9 rounded-full ${avatarColor} flex items-center justify-center text-sm font-black text-white shrink-0">
                        ${initials}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-white truncate">${u.name}</p>
                        <p class="text-xs text-slate-500 truncate">${u.email}</p>
                    </div>
                    ${roleBadge}
                    ${toggleBtn}
                </div>
            `;
        }).join('');
    } catch (e) {
        list.innerHTML = '<p class="text-red-400 text-sm text-center py-4">Error cargando usuarios.</p>';
    }
}

function closeAdminPanel() {
    document.getElementById('admin-panel-modal').classList.add('hidden');
}

async function addMember() {
    const name     = document.getElementById('new-member-name')?.value.trim();
    const email    = document.getElementById('new-member-email')?.value.trim();
    const password = document.getElementById('new-member-password')?.value;
    const errorEl  = document.getElementById('admin-add-error');

    errorEl.classList.add('hidden');
    errorEl.innerText = '';

    if (!name || !email || !password) {
        errorEl.innerText = 'Completa todos los campos.';
        errorEl.classList.remove('hidden');
        return;
    }

    try {
        const res  = await fetch('/admin/users', {
            method:  'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept':       'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '',
            },
            body: JSON.stringify({ name, email, password }),
        });

        const data = await res.json();

        if (res.ok) {
            // Limpiar campos
            document.getElementById('new-member-name').value     = '';
            document.getElementById('new-member-email').value    = '';
            document.getElementById('new-member-password').value = '';
            // Recargar lista y barra
            openAdminPanel();
            loadUsers();
        } else {
            const msg = data.errors
                ? Object.values(data.errors).flat().join(' ')
                : (data.message || 'Error al agregar miembro.');
            errorEl.innerText = msg;
            errorEl.classList.remove('hidden');
        }
    } catch (e) {
        errorEl.innerText = 'Error de conexión.';
        errorEl.classList.remove('hidden');
    }
}

async function toggleUserRole(userId, currentRole) {
    const newRole = currentRole === 'admin' ? 'member' : 'admin';
    try {
        const res = await fetch(`/admin/users/${userId}/role`, {
            method:  'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '' },
            body:    JSON.stringify({ role: newRole }),
        });
        if (res.ok) {
            openAdminPanel(); // recargar lista
            loadUsers();      // actualizar barra de colaboradores
        } else {
            const data = await res.json();
            alert(data.error || 'Error al cambiar el rol.');
        }
    } catch (e) { console.error('Error:', e); }
}

// ────────────────────────────────────────────────────────────
// 14. Colaboradores y usuarios
// ────────────────────────────────────────────────────────────
async function loadUsers() {
    try {
        const res = await fetch(`/api/users?_=${Date.now()}`);
        usersCache = await res.json();
        populateAssignedSelect();
        renderCollaboratorsBar();
    } catch (e) { console.error('Error cargando usuarios:', e); }
}

function populateAssignedSelect(currentValue = '') {
    const sel = document.getElementById('task-assigned');
    if (!sel) return;
    sel.innerHTML = '<option value="">Sin asignar</option>' +
        usersCache.map(u =>
            `<option value="${u.name}" ${u.name === currentValue ? 'selected' : ''}>${u.name}</option>`
        ).join('');
}

function renderCollaboratorsBar() {
    const bar = document.getElementById('collaborators-bar');
    if (!bar || usersCache.length === 0) return;

    // Calcular tareas por usuario a partir del board
    const tasksByUser = {};
    usersCache.forEach(u => { tasksByUser[u.name] = { total: 0, inProgress: [], active: 0 }; });

    if (originalBoardData) {
        originalBoardData.columns.forEach(col => {
            const isTerminado = col.name.toLowerCase() === 'terminado';
            col.tasks.forEach(t => {
                if (t.assigned_to && tasksByUser[t.assigned_to]) {
                    tasksByUser[t.assigned_to].total++;
                    if (!isTerminado) {
                        tasksByUser[t.assigned_to].active++;
                    }
                    if (col.name.toLowerCase() === 'en progreso') {
                        tasksByUser[t.assigned_to].inProgress.push(t.title);
                    }
                }
            });
        });
    }

    // Paleta de colores por usuario
    const palette = [
        'bg-indigo-600', 'bg-violet-600', 'bg-sky-600',
        'bg-emerald-600', 'bg-amber-600', 'bg-rose-600',
    ];

    const label = bar.querySelector('span') || document.createElement('span');

    const avatarsHtml = usersCache.map((u, idx) => {
        const initials = u.name.trim().split(' ').map(w => w[0]).slice(0, 2).join('').toUpperCase();
        const color    = palette[idx % palette.length];
        const stats    = tasksByUser[u.name];
        const isCurrent = window.currentUser && u.name === window.currentUser.name;
        const inProg   = stats.inProgress.length;
        const hasActive = stats.active > 0;

        return `
            <div class="relative group flex items-center gap-1.5 cursor-default">
                <div class="w-7 h-7 rounded-full ${color} flex items-center justify-center text-[10px] font-black text-white
                            ${isCurrent ? 'ring-2 ring-white ring-offset-1 ring-offset-slate-800' : ''}">
                    ${initials}
                </div>
                <span class="text-xs text-slate-400 font-medium">${u.name.split(' ')[0]}</span>
                ${hasActive ? `<span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>` : ''}

                <div class="absolute bottom-full left-0 mb-2 hidden group-hover:block z-50 pointer-events-none">
                    <div class="bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-200 whitespace-nowrap shadow-xl">
                        <p class="font-bold text-white mb-1">${u.name}</p>
                        <p class="text-slate-400">${stats.total} tarea${stats.total !== 1 ? 's' : ''} asignada${stats.total !== 1 ? 's' : ''}</p>
                        <p class="text-emerald-400">${inProg} en progreso</p>
                    </div>
                </div>
            </div>
        `;
    }).join('');

    bar.innerHTML = `<span class="text-[10px] font-bold text-slate-600 uppercase tracking-widest shrink-0">Equipo:</span>${avatarsHtml}`;
}

// ────────────────────────────────────────────────────────────
// 14. Init — exponer funciones al scope global (onclick en HTML)
// ────────────────────────────────────────────────────────────
window.onload = async function () {
    await loadBoard();
    await loadUsers();

    // Tablero
    window.openCreateColumnPrompt = openCreateColumnPrompt;
    window.deleteColumn           = deleteColumn;
    window.openCreateModal        = openCreateModal;
    window.openDetailsModal       = openDetailsModal;

    // Modal detalles — las 3 que faltaban
    window.closeDetailsModal      = closeDetailsModal;   // ✅ antes inexistente
    window.deleteCurrentTask      = deleteCurrentTask;   // ✅ antes inexistente
    window.addComment             = addComment;          // ✅ antes inexistente

    // Modal editar
    window.openEditFromDetails    = openEditFromDetails;
    window.closeModal             = closeModal;
    window.saveTask               = saveTask;

    // Filtros y vistas
    window.switchView             = switchView;
    window.applyFilters           = applyFilters;
    window.clearFilters           = clearFilters;
    window.clearGlobalHistory     = clearGlobalHistory;
    window.setVelocityPeriod      = setVelocityPeriod;
    window.populateAssignedSelect = populateAssignedSelect;
    window.openAdminPanel         = openAdminPanel;
    window.closeAdminPanel        = closeAdminPanel;
    window.toggleUserRole         = toggleUserRole;
    window.addMember              = addMember;

    console.log("✅ KanbanFlow JS cargado correctamente.");
};