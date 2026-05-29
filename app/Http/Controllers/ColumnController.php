<?php

namespace App\Http\Controllers;

use App\Models\Column;
use Illuminate\Http\Request;

class ColumnController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'board_id' => 'required|integer',
            'name'     => 'required|string|max:255',
            'position' => 'required|integer',
        ]);

        $count = Column::where('board_id', $request->board_id)->count();
        if ($count >= 10) {
            return response()->json(['error' => 'Límite alcanzado: no se pueden tener más de 10 columnas.'], 422);
        }

        $existe = Column::where('board_id', $request->board_id)
            ->whereRaw('LOWER(name) = ?', [strtolower($request->name)])
            ->exists();
        if ($existe) {
            return response()->json(['error' => 'Ya existe una columna con ese nombre.'], 422);
        }

        $column = Column::create([
            'board_id' => $request->board_id,
            'name'     => $request->name,
            'position' => $request->position,
        ]);

        return response()->json($column, 201);
    }

    public function update(Request $request, $id)
    {
        $column = Column::findOrFail($id);
        $request->validate(['position' => 'required|integer']);
        $column->update(['position' => $request->position]);
        return response()->json($column);
    }

    public function destroy($id)
    {
        $column = Column::findOrFail($id);
        $column->delete();

        return response()->json(['message' => 'Columna eliminada'], 200);
    }
}
