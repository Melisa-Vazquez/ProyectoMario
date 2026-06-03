<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;

class TaskController extends Controller
{
    // 1. Guardar una nueva tarea
    public function store(Request $request)
    {
        $validated = $request->validate([
            'column_id'  => 'required|exists:columns,id',
            'title'      => 'required|string|max:255',
            'description'=> 'nullable|string',
            'priority'   => 'in:low,medium,high',
            'tags'       => 'nullable|string',
            'duedate'     => 'nullable|date|after_or_equal:today',
            'plan_start'  => 'nullable|date|after_or_equal:today',
            'plan_end'    => 'nullable|date|after_or_equal:plan_start',
            'assigned_to' => 'nullable|string|max:100',
        ]);

        // Contar cuántas tareas hay para poner la nueva al final
        $validated['position'] = Task::where('column_id', $request->column_id)->count() + 1;

        $task = Task::create($validated);

        return response()->json($task, 201);
    }

    // 2. Actualizar o Mover una tarea (¡Vital para el Drag and Drop!)
    public function update(Request $request, $id)
    {
        $task = Task::findOrFail($id);

        // Validamos solo lo que venga en la petición (puede ser solo cambio de columna o de texto)
        $validated = $request->validate([
            'column_id'  => 'sometimes|required|exists:columns,id',
            'title'      => 'sometimes|required|string|max:255',
            'description'=> 'nullable|string',
            'priority'   => 'sometimes|in:low,medium,high',
            'tags'       => 'nullable|string',
            'position'   => 'sometimes|integer',
            'duedate'     => 'nullable|date|after_or_equal:today',
            'plan_start'  => 'nullable|date|after_or_equal:today',
            'plan_end'    => 'nullable|date|after_or_equal:plan_start',
            'assigned_to' => 'nullable|string|max:100',
        ]);

        $task->update($validated);

        // Si la tarea se movió a una columna distinta de "Terminado", eliminar su registro de completado
        if (isset($validated['column_id'])) {
            $terminadoCol = \App\Models\Column::whereRaw('LOWER(name) = ?', ['terminado'])->first();
            if ($terminadoCol && $validated['column_id'] != $terminadoCol->id) {
                \App\Models\TaskCompletion::where('task_id', $task->id)->delete();
            }
        }

        return response()->json($task);
    }

    // 3. Eliminar una tarea
    public function destroy($id)
    {
        $task = Task::findOrFail($id);
        $task->delete();

        return response()->json(['message' => 'Tarea eliminada correctamente']);
    }
}