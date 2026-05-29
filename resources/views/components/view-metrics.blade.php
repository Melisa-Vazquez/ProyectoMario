<div id="view-metrics" class="view-section hidden">
<div class="p-6 max-w-6xl mx-auto space-y-6">

    {{-- Header --}}
    <div class="flex items-center gap-3">
        <div class="bg-indigo-600/20 text-indigo-400 p-2.5 rounded-xl border border-indigo-500/20 text-xl">📊</div>
        <div>
            <h2 class="text-2xl font-bold text-white tracking-wide">Métricas del Proyecto</h2>
            <p class="text-xs text-slate-500 mt-0.5">Velocidad del equipo e indicadores clave de rendimiento.</p>
        </div>
    </div>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">

        <div class="bg-slate-800 border border-slate-700 p-5 rounded-2xl">
            <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Total Tareas</span>
            <p id="m-total" class="text-4xl font-black text-white mt-2 tabular-nums">0</p>
            <span class="text-xs text-slate-600 mt-1 block">en el tablero</span>
        </div>

        <div class="bg-slate-800 border border-emerald-900/40 bg-emerald-950/10 p-5 rounded-2xl">
            <span class="text-[11px] font-bold text-emerald-600 uppercase tracking-wider">Completadas</span>
            <p id="m-done" class="text-4xl font-black text-emerald-400 mt-2 tabular-nums">0</p>
            <div class="w-full bg-slate-700 h-1 rounded-full mt-2 overflow-hidden">
                <div id="m-done-bar" class="bg-emerald-500 h-full rounded-full transition-all duration-700" style="width:0%"></div>
            </div>
            <span id="m-done-pct" class="text-xs text-slate-500 mt-1 block">0% del total</span>
        </div>

        <div class="bg-slate-800 border border-slate-700 p-5 rounded-2xl">
            <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Sin Completar</span>
            <p id="m-pending" class="text-4xl font-black text-indigo-400 mt-2 tabular-nums">0</p>
            <span class="text-xs text-slate-600 mt-1 block">pendientes o en curso</span>
        </div>

        <div class="bg-slate-800 border border-red-900/40 bg-red-950/10 p-5 rounded-2xl">
            <span class="text-[11px] font-bold text-red-500/70 uppercase tracking-wider">Vencidas</span>
            <p id="m-overdue" class="text-4xl font-black text-red-400 mt-2 tabular-nums">0</p>
            <span class="text-xs text-slate-600 mt-1 block">fecha límite superada</span>
        </div>

    </div>

    {{-- Gráfica de flujo --}}
    <div class="bg-slate-800 border border-slate-700 p-6 rounded-2xl">
        <div class="flex items-start justify-between flex-wrap gap-4 mb-6">
            <div>
                <h3 class="text-sm font-bold text-white uppercase tracking-wider">Flujo de Velocidad</h3>
                <p class="text-xs text-slate-500 mt-0.5">Tareas completadas por período — mide la cadencia del equipo.</p>
            </div>
            <div class="flex gap-2 shrink-0">
                <button id="btn-period-week" onclick="setVelocityPeriod('week')"
                    class="text-xs font-bold px-3 py-1.5 rounded-lg transition bg-indigo-600 text-white">
                    Última semana
                </button>
                <button id="btn-period-month" onclick="setVelocityPeriod('month')"
                    class="text-xs font-bold px-3 py-1.5 rounded-lg transition bg-slate-700 text-slate-300 hover:bg-slate-600">
                    Último mes
                </button>
            </div>
        </div>

        <div class="relative" style="height:260px">
            <canvas id="velocity-chart"></canvas>
        </div>

        <p class="text-[11px] text-slate-600 mt-4 text-center">
            Datos compartidos del equipo — cada miembro contribuye al flujo al completar tareas.
        </p>
    </div>


</div>
</div>
