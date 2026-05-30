<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Plataforma de Gestión Ágil</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body class="bg-slate-900 text-white font-sans min-h-screen flex flex-col">

    <header class="px-6 py-4 bg-slate-800 border-b border-slate-700 shadow-lg shrink-0">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">

            {{-- Nombre del proyecto --}}
            <div>
                <h1 id="board-name" class="text-3xl font-bold text-indigo-400">Cargando Proyecto...</h1>
                <p id="board-desc" class="text-slate-400 text-sm mt-1"></p>
            </div>

            {{-- KPIs + usuario --}}
            <div class="flex flex-wrap items-center gap-3">
                <div class="bg-slate-900/60 border border-slate-700 px-4 py-2 rounded-xl text-center min-w-[100px]">
                    <span class="block text-[10px] uppercase font-bold text-slate-400 tracking-wider">Total</span>
                    <span id="metric-total" class="text-xl font-black text-indigo-400">0</span>
                </div>
                <div class="bg-slate-900/60 border border-slate-700 px-4 py-2 rounded-xl text-center min-w-[100px]">
                    <span class="block text-[10px] uppercase font-bold text-slate-400 tracking-wider">Por Hacer</span>
                    <span id="metric-todo" class="text-xl font-black text-yellow-500">0</span>
                </div>
                <div class="bg-slate-900/60 border border-slate-700 px-4 py-2 rounded-xl text-center min-w-[120px]">
                    <span class="block text-[10px] uppercase font-bold text-slate-400 tracking-wider">Progreso</span>
                    <span id="metric-progress" class="text-xl font-black text-green-400">0%</span>
                </div>

                {{-- Usuario actual --}}
                <div class="flex items-center gap-2 bg-slate-900/60 border border-slate-700 px-3 py-2 rounded-xl">
                    <div class="w-7 h-7 rounded-full bg-indigo-600 flex items-center justify-center text-xs font-black text-white shrink-0">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <span class="text-sm font-semibold text-slate-200">{{ auth()->user()->name }}</span>
                    <form method="POST" action="/logout" class="ml-1">
                        @csrf
                        <button type="submit" title="Cerrar sesión"
                            class="text-slate-500 hover:text-red-400 transition text-xs font-bold px-1">
                            ✕
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Barra de colaboradores --}}
        <div id="collaborators-bar" class="flex items-center gap-3 mt-3 pt-3 border-t border-slate-700/60 flex-wrap">
            <span class="text-[10px] font-bold text-slate-600 uppercase tracking-widest shrink-0">Equipo:</span>
            {{-- Populado por JS --}}
        </div>
    </header>

    @if(session('login_success'))
    <div id="login-toast"
         class="fixed top-5 right-5 z-50 flex items-center gap-3 bg-emerald-900/90 border border-emerald-500/40 text-emerald-300 px-5 py-3.5 rounded-2xl shadow-2xl text-sm font-semibold backdrop-blur-sm">
        <span class="text-lg">✅</span>
        <span>{{ session('login_success') }}</span>
        <button onclick="document.getElementById('login-toast').remove()"
            class="ml-2 text-emerald-500 hover:text-emerald-300 transition font-black text-base">✕</button>
    </div>
    <script>setTimeout(() => document.getElementById('login-toast')?.remove(), 4000);</script>
    @endif

    <x-navigation />

    <div class="flex-1 overflow-auto bg-slate-900">
        <x-view-board />
        <x-view-metrics />
        <x-view-history />
    </div>

    <x-modals />

    <script>
        window.currentUser = @json(['id' => auth()->id(), 'name' => auth()->user()->name]);
    </script>
    <script src="{{ asset('js/agile-board.js') }}"></script>
</body>
</html>
