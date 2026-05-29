<?php

namespace App\Http\Controllers;
use App\Models\Column;
use App\Models\Board;
use Illuminate\Http\Request;


class BoardController extends Controller
{
    public function show($id)
    {
        $board = Board::with([
            'columns' => fn($q) => $q->orderBy('position'),
            'columns.tasks' => fn($q) => $q->orderBy('position'),
        ])->findOrFail($id);
        
        return response()->json($board);
    }

public function storeColumn(Request $request)
{
    $request->validate([
        'board_id' => 'required|integer',
        'name' => 'required|string|max:255',
        'position' => 'required|integer'
    ]);

    $column = Column::create([
        'board_id' => $request->board_id,
        'name' => $request->name,
        'position' => $request->position
    ]);

    return response()->json($column, 201);
}
}