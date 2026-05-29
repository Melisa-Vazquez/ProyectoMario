<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Column extends Model
{
    use HasFactory;

    protected $fillable = ['board_id', 'name', 'position'];

    // Una Columna pertenece a un Tablero
    public function board()
    {
        return $this->belongsTo(Board::class);
    }

    // Una Columna tiene muchas Tareas
    public function tasks()
    {
        return $this->hasMany(Task::class)->orderBy('position');
    }
}