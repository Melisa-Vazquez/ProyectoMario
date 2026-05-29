<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = ['column_id', 'title', 'description', 'priority', 'tags', 'position', 'duedate', 'plan_start', 'plan_end'];

    // Una Tarea pertenece a una Columna
    public function column()
    {
        return $this->belongsTo(Column::class);
    }
}