<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BoardController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\ColumnController;

// Tablero completo
Route::get('/boards/{id}', [BoardController::class, 'show']);

// Columnas — apuntan al ColumnController correcto
Route::post('/columns',       [ColumnController::class, 'store']);    // ✅ antes apuntaba a BoardController@storeColumn (inexistente)
Route::delete('/columns/{id}', [ColumnController::class, 'destroy']); // ✅ antes no existía esta ruta

// Tareas
Route::post('/tasks',         [TaskController::class, 'store']);
Route::put('/tasks/{id}',     [TaskController::class, 'update']);
Route::delete('/tasks/{id}',  [TaskController::class, 'destroy']);