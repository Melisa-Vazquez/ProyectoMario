<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BoardController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\ColumnController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CompletionController;

// Tablero completo
Route::get('/boards/{id}', [BoardController::class, 'show']);

// Columnas — apuntan al ColumnController correcto
Route::post('/columns',        [ColumnController::class, 'store']);
Route::put('/columns/{id}',    [ColumnController::class, 'update']);
Route::delete('/columns/{id}', [ColumnController::class, 'destroy']);

// Tareas
Route::post('/tasks',         [TaskController::class, 'store']);
Route::put('/tasks/{id}',     [TaskController::class, 'update']);
Route::delete('/tasks/{id}',  [TaskController::class, 'destroy']);

// Colaboradores
Route::get('/users', [UserController::class, 'index']);

// Completados del equipo (gráfica de velocidad)
Route::get('/completions',  [CompletionController::class, 'index']);
Route::post('/completions', [CompletionController::class, 'store']);