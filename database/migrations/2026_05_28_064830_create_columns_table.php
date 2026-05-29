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
    Schema::create('columns', function (Blueprint $table) {
        $table->id();
        // Esto enlaza la columna a un tablero. Si el tablero se borra, sus columnas también.
        $table->foreignId('board_id')->constrained()->onDelete('cascade'); 
        $table->string('name'); // Ej: "Por hacer", "En progreso", "Listo"
        $table->integer('position')->default(0); // Para ordenar las columnas de izquierda a derecha
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('columns');
    }
};
