<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plataforma de Gestión Ágil</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body class="bg-slate-900 text-white font-sans min-h-screen flex flex-col">

    <header class="p-6 bg-slate-800 border-b border-slate-700 shadow-lg flex flex-col md:flex-row justify-between items-start md:items-center gap-4 shrink-0">
        <div>
            <h1 id="board-name" class="text-3xl font-bold text-indigo-400">Cargando Proyecto...</h1>
            <p id="board-desc" class="text-slate-400 text-sm mt-1"></p>
        </div>
        <div class="flex gap-4 w-full md:w-auto">
            <div class="bg-slate-900/60 border border-slate-700 px-4 py-2 rounded-xl text-center min-w-[100px]">
                <span class="block text-[10px] uppercase font-bold text-slate-400 tracking-wider">Total Tareas</span>
                <span id="metric-total" class="text-xl font-black text-indigo-400">0</span>
            </div>
            <div class="bg-slate-900/60 border border-slate-700 px-4 py-2 rounded-xl text-center min-w-[100px]">
                <span class="block text-[10px] uppercase font-bold text-slate-400 tracking-wider">Por Hacer</span>
                <span id="metric-todo" class="text-xl font-black text-yellow-500">0</span>
            </div>
            <div class="bg-slate-900/60 border border-slate-700 px-4 py-2 rounded-xl text-center min-w-[120px]">
                <span class="block text-[10px] uppercase font-bold text-slate-400 tracking-wider">Progreso Sprint</span>
                <span id="metric-progress" class="text-xl font-black text-green-400">0%</span>
            </div>
        </div>
    </header>

    <x-navigation />

    <div class="flex-1 overflow-auto bg-slate-900">
        <x-view-board />

        <x-view-metrics />

        <x-view-history />
    </div>

    <x-modals />

    <script src="{{ asset('js/agile-board.js') }}"></script>
</body>
</html>