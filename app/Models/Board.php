<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Board extends Model
{
    use HasFactory;

    // Campos que permitimos llenar mediante código
    protected $fillable = ['name', 'description'];

    // Un Tablero tiene muchas Columnas
    public function columns()
    {
        return $this->hasMany(Column::class)->orderBy('position');
    }
}