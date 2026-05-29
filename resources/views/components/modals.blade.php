{{-- Modal: Panel de Administración --}}
<div id="admin-panel-modal" class="fixed inset-0 bg-black/60 backdrop-blur-sm hidden flex items-center justify-center p-4 z-50">
    <div class="bg-slate-800 border border-slate-700 rounded-2xl w-full max-w-lg shadow-2xl flex flex-col max-h-[90vh]">

        {{-- Header --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-700 shrink-0">
            <div>
                <h3 class="text-lg font-bold text-amber-400">⚙️ Panel de Administración</h3>
                <p class="text-xs text-slate-500 mt-0.5">Gestión de roles del equipo</p>
            </div>
            <button onclick="closeAdminPanel()"
                class="text-slate-400 hover:text-white w-8 h-8 flex items-center justify-center rounded-lg hover:bg-slate-700 transition text-xl">
                &times;
            </button>
        </div>

        {{-- Formulario nuevo miembro --}}
        <div class="px-6 pt-4 pb-2 border-b border-slate-700 shrink-0">
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Agregar miembro</p>
            <div id="admin-add-error" class="hidden bg-red-500/10 border border-red-500/30 text-red-400 text-xs rounded-lg px-3 py-2 mb-3"></div>
            <div class="grid grid-cols-3 gap-2 mb-2">
                <input type="text" id="new-member-name" placeholder="Nombre"
                    class="bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500 transition placeholder:text-slate-600">
                <input type="email" id="new-member-email" placeholder="Correo"
                    class="bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500 transition placeholder:text-slate-600">
                <input type="password" id="new-member-password" placeholder="Contraseña"
                    class="bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500 transition placeholder:text-slate-600">
            </div>
            <button onclick="addMember()"
                class="w-full py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold rounded-lg transition">
                ➕ Agregar al equipo
            </button>
        </div>

        {{-- Lista de usuarios --}}
        <div class="flex-1 overflow-y-auto px-6 py-4">
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Miembros del equipo</p>
            <div id="admin-users-list" class="space-y-3">
                <p class="text-slate-500 text-sm text-center py-4">Cargando usuarios...</p>
            </div>
        </div>

        {{-- Leyenda --}}
        <div class="px-6 py-3 border-t border-slate-700 shrink-0">
            <p class="text-[11px] text-slate-600">
                ★ Admin — gestiona columnas y cambia roles &nbsp;|&nbsp;
                Miembro — crea y trabaja en tareas
            </p>
        </div>
    </div>
</div>

{{-- Modal: Nueva Columna --}}
<div id="column-prompt-modal" class="fixed inset-0 bg-black/60 backdrop-blur-sm hidden flex items-center justify-center p-4 z-50">
    <div class="bg-slate-800 border border-slate-700 p-6 rounded-2xl w-full max-w-sm shadow-2xl">
        <h3 class="text-lg font-bold text-indigo-400 mb-1">Nueva Columna</h3>
        <p class="text-xs text-slate-400 mb-4">Introduce el nombre de la nueva columna (Ej: QA, Bloqueado)</p>

        <input
            type="text"
            id="column-name-input"
            placeholder="Nombre de la columna..."
            class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-white text-sm focus:outline-none focus:border-indigo-500 transition placeholder:text-slate-600 mb-5"
        >

        <div class="flex justify-end gap-3">
            <button id="column-prompt-cancel" class="px-4 py-2 rounded-lg bg-slate-700 hover:bg-slate-600 transition text-sm font-medium text-slate-200">Cancelar</button>
            <button id="column-prompt-accept" class="px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-500 transition text-sm font-semibold text-white shadow-lg shadow-indigo-600/20">Aceptar</button>
        </div>
    </div>
</div>

<div id="task-modal" class="fixed inset-0 bg-black/60 backdrop-blur-sm hidden flex items-center justify-center p-4 z-50">
    <div class="bg-slate-800 border border-slate-700 p-6 rounded-2xl w-full max-w-md shadow-2xl">
        <h3 id="modal-form-title" class="text-xl font-bold text-indigo-400 mb-4">Nueva Tarea</h3>
        
        <form id="task-form" onsubmit="saveTask(event)">
            <input type="hidden" id="modal-column-id">
            <input type="hidden" id="modal-task-id">
            
            <div class="mb-4">
                <label for="task-title" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Título de la Tarjeta</label>
                <input type="text" id="task-title" required placeholder="Ej: Implementar UI en Auth" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white focus:outline-none focus:border-indigo-500 text-sm placeholder:text-slate-600">
            </div>

            <div class="mb-4">
                <label for="task-desc" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Descripción o Criterios de Aceptación</label>
                <textarea id="task-desc" rows="3" placeholder="Describe brevemente los entregables..." class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white focus:outline-none focus:border-indigo-500 text-sm placeholder:text-slate-600"></textarea>
            </div>

            <div class="mb-4">
                <label for="task-duedate" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Fecha Límite (Sprint Due Date)</label>
                <input type="date" id="task-duedate" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white focus:outline-none focus:border-indigo-500 text-sm">
            </div>

            <div class="mb-4 p-3 rounded-xl border border-slate-700/60 bg-slate-900/40">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3 flex items-center gap-1">
                    🗓️ Plan de Trabajo
                </p>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="task-plan-start" class="block text-[10px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Inicio planeado</label>
                        <input type="date" id="task-plan-start" onchange="updatePlanEndMin()" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white focus:outline-none focus:border-indigo-500 text-sm">
                    </div>
                    <div>
                        <label for="task-plan-end" class="block text-[10px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Fin planeado</label>
                        <input type="date" id="task-plan-end" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white focus:outline-none focus:border-indigo-500 text-sm">
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <label for="task-assigned" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Responsable</label>
                <select id="task-assigned" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white focus:outline-none focus:border-indigo-500 text-sm">
                    <option value="">Sin asignar</option>
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-6">
                <div>
                    <label for="task-priority" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Prioridad Agil</label>
                    <select id="task-priority" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white focus:outline-none focus:border-indigo-500 text-sm">
                        <option value="low">🟢 Baja</option>
                        <option value="medium" selected>🟡 Media</option>
                        <option value="high">🔴 Alta</option>
                    </select>
                </div>
                <div>
                    <label for="task-tags" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Etiquetas (Separadas por coma)</label>
                    <input type="text" id="task-tags" placeholder="Ej: Frontend, API, Bug" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white focus:outline-none focus:border-indigo-500 text-sm placeholder:text-slate-600">
                </div>
            </div>

            <div class="flex justify-end gap-3 border-t border-slate-700/50 pt-4">
                <button type="button" onclick="closeModal()" class="px-4 py-2 rounded-lg bg-slate-700 hover:bg-slate-600 transition text-sm font-medium text-slate-200">Cancelar</button>
                <button type="submit" class="px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-500 transition text-sm font-semibold text-white shadow-lg shadow-indigo-600/20">Guardar Cambios</button>
            </div>
        </form>
    </div>
</div>

{{-- Panel lateral de detalles --}}
<div id="details-modal" class="fixed inset-0 z-50 hidden">

    {{-- Overlay --}}
    <div onclick="closeDetailsModal()" class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>

    {{-- Panel deslizable --}}
    <div id="details-panel"
         class="absolute inset-y-0 right-0 w-full max-w-[580px] bg-[#1a1d23] flex flex-col shadow-2xl border-l border-slate-800 translate-x-full transition-transform duration-300 ease-out">

        {{-- Top bar --}}
        <div class="flex items-center justify-between px-5 py-3 border-b border-slate-800 shrink-0">
            <div class="flex items-center gap-2 text-xs text-slate-400 min-w-0">
                <span class="text-slate-600 shrink-0">en lista:</span>
                <div class="relative shrink-0">
                    <select id="detail-column-select" onchange="moveTaskToColumn(this.value)"
                        class="appearance-none bg-slate-800 border border-slate-700 text-slate-200 text-xs font-bold rounded-lg pl-3 pr-6 py-1.5 focus:outline-none focus:border-indigo-500 transition cursor-pointer max-w-[150px]">
                    </select>
                    <span class="pointer-events-none absolute right-2 top-1/2 -translate-y-1/2 text-slate-500 text-[9px]">▼</span>
                </div>
                <span id="detail-task-id" class="text-slate-700 font-mono shrink-0"></span>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <button id="btn-complete-task" onclick="completeTask()"
                    class="flex items-center gap-1.5 text-xs font-bold text-emerald-400 border border-emerald-500/30 bg-emerald-500/10 hover:bg-emerald-500 hover:text-white px-3 py-1.5 rounded-lg transition">
                    ✅ Completar
                </button>
                <button onclick="closeDetailsModal()"
                    class="text-slate-400 hover:text-white w-8 h-8 flex items-center justify-center rounded-lg hover:bg-slate-700 transition text-xl leading-none">
                    &times;
                </button>
            </div>
        </div>

        {{-- Área scrolleable --}}
        <div class="flex-1 overflow-y-auto custom-scroll">

            {{-- Título + prioridad --}}
            <div class="px-6 pt-6 pb-5 border-b border-slate-800">
                <div class="flex items-start gap-3 mb-3">
                    <div id="detail-color-dot" class="w-3 h-3 rounded-full mt-2 shrink-0"></div>
                    <h3 id="detail-title" class="text-2xl font-bold text-white leading-snug flex-1"></h3>
                </div>
                <div class="flex items-center gap-2 flex-wrap pl-6">
                    <span id="detail-priority" class="text-[11px] font-bold uppercase px-2.5 py-1 rounded-full"></span>
                    <span id="detail-duedate" class="text-[11px] font-semibold px-2.5 py-1 rounded-full bg-slate-800 text-slate-400 border border-slate-700/60"></span>
                    <span id="detail-assigned" class="hidden items-center gap-1.5 text-[11px] font-semibold px-2.5 py-1 rounded-full bg-violet-500/10 text-violet-300 border border-violet-500/20">
                        👤 <span id="detail-assigned-name"></span>
                    </span>
                </div>
            </div>

            {{-- Chips de acción --}}
            <div class="px-6 py-4 border-b border-slate-800 flex flex-wrap gap-2">
                <button onclick="openEditFromDetails()"
                    class="flex items-center gap-1.5 text-xs font-semibold text-slate-300 bg-slate-800 hover:bg-slate-700 border border-slate-700 px-3 py-1.5 rounded-full transition">
                    ✏️ Editar
                </button>
                <span id="chip-plan"
                    class="flex items-center gap-1.5 text-xs font-semibold text-slate-400 bg-slate-800 border border-slate-700 px-3 py-1.5 rounded-full">
                </span>
                <button onclick="deleteCurrentTask()"
                    class="flex items-center gap-1.5 text-xs font-semibold text-red-400 bg-red-500/10 hover:bg-red-600 hover:text-white border border-red-500/20 px-3 py-1.5 rounded-full transition ml-auto">
                    🗑️ Eliminar
                </button>
            </div>

            {{-- Descripción --}}
            <div class="px-6 py-5 border-b border-slate-800">
                <p class="text-[11px] font-semibold text-slate-600 uppercase tracking-widest mb-3">Descripción</p>
                <p id="detail-desc" class="text-slate-300 text-sm leading-relaxed whitespace-pre-wrap min-h-[56px]"></p>
            </div>

            {{-- Subtareas --}}
            <div class="px-6 py-5 border-b border-slate-800">
                <div class="flex items-center justify-between mb-4">
                    <p class="text-[11px] font-semibold text-slate-600 uppercase tracking-widest">Subtareas</p>
                    <div class="flex items-center gap-2">
                        <span id="subtask-counter" class="text-xs text-slate-400 font-mono tabular-nums">0 / 0</span>
                        <div class="w-28 h-1.5 bg-slate-800 rounded-full overflow-hidden">
                            <div id="subtask-bar" class="h-full bg-indigo-500 rounded-full transition-all duration-300" style="width:0%"></div>
                        </div>
                    </div>
                </div>
                <div id="subtask-list" class="space-y-1.5 mb-3"></div>
                <div class="flex gap-2">
                    <input type="text" id="subtask-input" placeholder="Nuevo ítem..."
                        onkeydown="if(event.key==='Enter') addSubtask()"
                        class="flex-1 bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-200 focus:outline-none focus:border-indigo-500 transition placeholder:text-slate-600">
                    <button onclick="addSubtask()"
                        class="bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold px-4 py-2 rounded-lg transition shrink-0">
                        Crear
                    </button>
                </div>
            </div>

            {{-- Plan de trabajo --}}
            <div class="px-6 py-5 border-b border-slate-800">
                <p class="text-[11px] font-semibold text-slate-600 uppercase tracking-widest mb-3">Plan de Trabajo</p>
                <div id="detail-plan" class="flex gap-8 text-sm"></div>
            </div>

            {{-- Historial --}}
            <div class="px-6 py-5 border-b border-slate-800">
                <p class="text-[11px] font-semibold text-slate-600 uppercase tracking-widest mb-3">Historial</p>
                <div id="history-list" class="space-y-1.5 text-slate-300 max-h-52 overflow-y-auto custom-scroll pr-1"></div>
            </div>

        </div>

        {{-- Footer fijo: comentarios --}}
        <div class="shrink-0 border-t border-slate-800 bg-[#1a1d23] px-5 py-4">
            <div id="comments-list" class="space-y-2 max-h-32 overflow-y-auto custom-scroll mb-3"></div>
            <div class="flex gap-2">
                <label for="comment-input" class="sr-only">Escribe un comentario</label>
                <input type="text" id="comment-input" placeholder="Escribe un comentario..."
                    onkeydown="if(event.key==='Enter') addComment()"
                    class="flex-1 bg-slate-800 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-slate-200 focus:outline-none focus:border-indigo-500 transition placeholder:text-slate-500">
                <button onclick="addComment()"
                    class="bg-indigo-600 hover:bg-indigo-500 text-sm font-bold text-white px-5 py-2.5 rounded-xl transition shrink-0">
                    Enviar
                </button>
            </div>
        </div>

    </div>
</div>