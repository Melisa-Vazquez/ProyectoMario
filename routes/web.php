<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\AdminController;

// Rutas de autenticación (solo para invitados)
Route::middleware('guest')->group(function () {
    Route::get('/login',    [LoginController::class,   'showForm'])->name('login');
    Route::post('/login',   [LoginController::class,   'login']);
    Route::get('/register', [RegisterController::class, 'showForm'])->name('register');
    Route::post('/register',[RegisterController::class, 'register']);
});

// Ruta principal protegida
Route::middleware('auth')->group(function () {
    Route::get('/', fn() => view('welcome'));
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Panel de administración
    Route::get('/admin/users',             [AdminController::class, 'users']);
    Route::post('/admin/users',            [AdminController::class, 'createUser']);
    Route::post('/admin/users/{id}/role',  [AdminController::class, 'updateRole']);
});
