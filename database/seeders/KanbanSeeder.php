<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Board;
use App\Models\Column;
use App\Models\Task;

class KanbanSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Crear un tablero de prueba
        $board = Board::create([
            'name' => 'Mi Primer Tablero Kanban',
            'description' => 'Seguimiento de mi proyecto ágil'
        ]);

        // 2. Crear las columnas dinámicas para ese tablero
        $todo = Column::create(['board_id' => $board->id, 'name' => 'Por Hacer', 'position' => 1]);
        $progress = Column::create(['board_id' => $board->id, 'name' => 'En Progreso', 'position' => 2]);
        $done = Column::create(['board_id' => $board->id, 'name' => 'Terminado', 'position' => 3]);

        // 3. Crear un par de tareas dentro de las columnas
        Task::create([
            'column_id' => $todo->id,
            'title' => 'Diseñar Base de Datos',
            'description' => 'Crear migraciones y modelos en Laravel',
            'priority' => 'high',
            'tags' => 'Backend, SQL',
            'position' => 1
        ]);

        Task::create([
            'column_id' => $progress->id,
            'title' => 'Maquetar interfaz gráfica',
            'description' => 'Hacer el HTML/CSS del tablero con drag and drop',
            'priority' => 'medium',
            'tags' => 'Frontend',
            'position' => 1
        ]);
    }
}