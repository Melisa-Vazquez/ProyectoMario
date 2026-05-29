<div id="view-history" class="view-section hidden p-6 max-w-3xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-white">Historial de Cambios Global</h2>
        <button onclick="clearGlobalHistory()" class="text-xs bg-red-600/20 text-red-400 hover:bg-red-600 hover:text-white px-3 py-1.5 rounded-lg transition font-semibold">
            🗑️ Limpiar Historial
        </button>
    </div>
    <div class="bg-slate-800 border border-slate-700 rounded-2xl p-6 shadow-md">
        <div id="global-history-list" class="space-y-3 max-h-[60vh] overflow-y-auto pr-2"></div>
    </div>
</div>