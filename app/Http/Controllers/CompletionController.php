<?php

namespace App\Http\Controllers;

use App\Models\TaskCompletion;
use Illuminate\Http\Request;

class CompletionController extends Controller
{
    // Devuelve los completados en el período para la gráfica
    public function index(Request $request)
    {
        $days  = $request->get('period', 'week') === 'week' ? 7 : 30;
        $since = now()->subDays($days)->startOfDay();

        // 1. Eventos registrados en la tabla (tareas completadas con el nuevo código)
        $fromTable = TaskCompletion::with('user:id,name')
            ->where('created_at', '>=', $since)
            ->get()
            ->map(fn($c) => [
                'completed_at' => $c->created_at->toISOString(),
                'user_name'    => $c->user?->name ?? 'Equipo',
                'task_id'      => $c->task_id,
            ]);

        // 2. Tareas actualmente en "Terminado" sin registro (datos previos al nuevo código)
        $trackedIds   = $fromTable->pluck('task_id');
        $terminadoCol = \App\Models\Column::whereRaw('LOWER(name) = ?', ['terminado'])->first();

        $fromLegacy = collect();
        if ($terminadoCol) {
            $fromLegacy = \App\Models\Task::where('column_id', $terminadoCol->id)
                ->whereNotIn('id', $trackedIds)
                ->where('updated_at', '>=', $since)
                ->get()
                ->map(fn($t) => [
                    'completed_at' => $t->updated_at->toISOString(),
                    'user_name'    => $t->assigned_to ?? 'Equipo',
                    'task_id'      => $t->id,
                ]);
        }

        return response()->json($fromTable->concat($fromLegacy)->sortBy('completed_at')->values());
    }

    // Registra un completado (llamado desde el frontend)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'task_id' => 'required|exists:tasks,id',
            'user_id' => 'nullable|exists:users,id',
        ]);

        // Evitar duplicados: solo un registro por tarea por día
        $exists = TaskCompletion::where('task_id', $validated['task_id'])
            ->whereDate('created_at', today())
            ->exists();

        if (!$exists) {
            TaskCompletion::create($validated);
        }

        return response()->json(['ok' => true]);
    }
}
