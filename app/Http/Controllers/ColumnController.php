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

        $column = Column::create([
            'board_id' => $request->board_id,
            'name'     => $request->name,
            'position' => $request->position,
        ]);

        return response()->json($column, 201);
    }

    public function destroy($id)
    {
        $column = Column::findOrFail($id);
        $column->delete();

        return response()->json(['message' => 'Columna eliminada'], 200);
    }
}
