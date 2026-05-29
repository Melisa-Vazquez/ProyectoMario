<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::create('tasks', function (Blueprint $table) {
        $table->id();
        // Enlaza la tarea a una columna. Si la columna se borra, la tarea también.
        $table->foreignId('column_id')->constrained()->onDelete('cascade');
        $table->string('title'); // Título de la tarjeta
        $table->text('description')->nullable(); // Detalle de la tarea
        $table->enum('priority', ['low', 'medium', 'high'])->default('medium'); // Prioridades fijas
        $table->string('tags')->nullable(); // Etiquetas separadas por comas (ej: "Frontend,Bug")
        $table->integer('position')->default(0); // Para el orden al arrastrar y soltar de arriba a abajo
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
