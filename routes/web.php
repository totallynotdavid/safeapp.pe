<?php

use App\Http\Controllers\AsignacionController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\LiquidacionController;
use App\Http\Controllers\PersonalController;
use App\Http\Controllers\QuiebreRegistroController;
use App\Http\Controllers\QuiebreValidacionController;
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

    Route::middleware('can:quiebre.registrar')->group(function () {
        Route::get('/quiebres/registro', [QuiebreRegistroController::class, 'index'])->name('quiebres.registro.index');
        Route::get('/quiebres/registro/crear', [QuiebreRegistroController::class, 'create'])->name(
            'quiebres.registro.create',
        );
        Route::post('/quiebres/registro', [QuiebreRegistroController::class, 'store'])->name('quiebres.registro.store');
        Route::get('/quiebres/registro/{quiebre}/editar', [QuiebreRegistroController::class, 'edit'])->name(
            'quiebres.registro.edit',
        );
        Route::put('/quiebres/registro/{quiebre}', [QuiebreRegistroController::class, 'update'])->name(
            'quiebres.registro.update',
        );
    });

    Route::middleware('can:liquidacion.gestionar')->group(function () {
        Route::get('/liquidaciones', [LiquidacionController::class, 'index'])->name('liquidaciones.index');
        Route::get('/liquidaciones/{ncMovil}', [LiquidacionController::class, 'edit'])->name('liquidaciones.edit');
        Route::put('/liquidaciones/{ncMovil}', [LiquidacionController::class, 'update'])->name('liquidaciones.update');
    });

    Route::middleware('can:personal.gestionar')->group(function () {
        Route::get('/personal', [PersonalController::class, 'index'])->name('personal.index');
        Route::get('/personal/{usuario}/editar', [PersonalController::class, 'edit'])->name('personal.edit');
        Route::put('/personal/{usuario}', [PersonalController::class, 'update'])->name('personal.update');
    });

    Route::middleware('can:asignacion.gestionar')->group(function () {
        Route::get('/asignacion', [AsignacionController::class, 'index'])->name('asignacion.index');
        Route::get('/asignacion/{usuario}/editar', [AsignacionController::class, 'edit'])->name('asignacion.edit');
        Route::put('/asignacion/{usuario}', [AsignacionController::class, 'update'])->name('asignacion.update');
    });

    Route::middleware('can:quiebre.validar')->group(function () {
        Route::get('/quiebres/validacion', [QuiebreValidacionController::class, 'index'])->name(
            'quiebres.validacion.index',
        );
        Route::get('/quiebres/validacion/{quiebre}', [QuiebreValidacionController::class, 'edit'])->name(
            'quiebres.validacion.edit',
        );
        Route::put('/quiebres/validacion/{quiebre}', [QuiebreValidacionController::class, 'update'])->name(
            'quiebres.validacion.update',
        );
    });
});
