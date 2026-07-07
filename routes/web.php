<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\RolController;
use App\Http\Controllers\UsuarioController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'));

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::view('/dashboard', 'dashboard')->name('dashboard');
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::middleware('can:usuarios.manage')->group(function () {
        Route::get('/usuarios', [UsuarioController::class, 'index'])->name('usuarios.index');
        Route::get('/usuarios/crear', [UsuarioController::class, 'create'])->name('usuarios.create');
        Route::post('/usuarios', [UsuarioController::class, 'store'])->name('usuarios.store');
        Route::get('/usuarios/{usuario}/editar', [UsuarioController::class, 'edit'])->name('usuarios.edit');
        Route::put('/usuarios/{usuario}', [UsuarioController::class, 'update'])->name('usuarios.update');
        Route::patch('/usuarios/{usuario}/estado', [UsuarioController::class, 'toggleEstado'])->name('usuarios.estado');
    });

    Route::middleware('can:roles.manage')->group(function () {
        Route::get('/roles', [RolController::class, 'index'])->name('roles.index');
        Route::get('/roles/crear', [RolController::class, 'create'])->name('roles.create');
        Route::post('/roles', [RolController::class, 'store'])->name('roles.store');
        Route::get('/roles/{rol}/editar', [RolController::class, 'edit'])->name('roles.edit');
        Route::put('/roles/{rol}', [RolController::class, 'update'])->name('roles.update');
        Route::delete('/roles/{rol}', [RolController::class, 'destroy'])->name('roles.destroy');
    });
});
