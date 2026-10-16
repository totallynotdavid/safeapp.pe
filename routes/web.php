<?php

use App\Http\Controllers\AccountSettingsController;
use App\Http\Controllers\AsignacionController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\ConfiguracionController;
use App\Http\Controllers\EquipoController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ImportacionController;
use App\Http\Controllers\PersonalController;
use App\Http\Controllers\RolController;
use App\Http\Controllers\TiendaActivaController;
use App\Http\Controllers\UsuarioController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:5,1');
});

Route::middleware('auth')->group(function () {
    Route::get('/', HomeController::class)->name('home');
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    Route::get('/configuracion', [ConfiguracionController::class, 'index'])->name('configuracion.index');
    Route::get('/configuracion/informacion-personal', [AccountSettingsController::class, 'profile'])->name(
        'configuracion.perfil',
    );
    Route::put('/configuracion/informacion-personal', [AccountSettingsController::class, 'updateProfile'])->name(
        'configuracion.perfil.update',
    );
    Route::get('/configuracion/seguridad', [AccountSettingsController::class, 'security'])->name(
        'configuracion.seguridad',
    );
    Route::put('/configuracion/seguridad', [AccountSettingsController::class, 'updatePassword'])->name(
        'configuracion.seguridad.update',
    );
    Route::get('/configuracion/notificaciones', [AccountSettingsController::class, 'notifications'])->name(
        'configuracion.notificaciones',
    );
    Route::put('/configuracion/notificaciones', [AccountSettingsController::class, 'updateNotifications'])->name(
        'configuracion.notificaciones.update',
    );
    Route::get('/configuracion/correo/{usuario}/{hash}', [AccountSettingsController::class, 'verifyEmail'])->middleware(
        'signed',
    )->name('configuracion.correo.verificar');
    Route::post('/configuracion/correo/verificacion', [AccountSettingsController::class, 'resendVerification'])->name(
        'configuracion.correo.reenviar',
    );
    Route::get('/configuracion/equipo', [EquipoController::class, 'index'])->name('equipo.index');

    // Part of the shell rather than a module: the control lives in the header and
    // acts only on the caller's own assignments, so it carries no permiso.
    Route::put('/tienda-activa', [TiendaActivaController::class, 'update'])->name('tienda-activa.update');

    Route::middleware('can:usuarios.manage')->group(function () {
        Route::get('/configuracion/equipo/nuevo', [UsuarioController::class, 'create'])->name('usuarios.create');
        Route::post('/configuracion/equipo', [UsuarioController::class, 'store'])->name('usuarios.store');
        Route::get('/configuracion/equipo/{usuario}/cuenta', [UsuarioController::class, 'edit'])->name('usuarios.edit');
        Route::put('/configuracion/equipo/{usuario}/cuenta', [UsuarioController::class, 'update'])->name(
            'usuarios.update',
        );
        Route::patch('/configuracion/equipo/{usuario}/estado', [UsuarioController::class, 'toggleEstado'])->name(
            'usuarios.estado',
        );
    });

    Route::middleware('can:roles.manage')->group(function () {
        Route::get('/configuracion/roles', [RolController::class, 'index'])->name('roles.index');
        Route::get('/configuracion/roles/crear', [RolController::class, 'create'])->name('roles.create');
        Route::post('/configuracion/roles', [RolController::class, 'store'])->name('roles.store');
        Route::get('/configuracion/roles/{rol}/editar', [RolController::class, 'edit'])->name('roles.edit');
        Route::put('/configuracion/roles/{rol}', [RolController::class, 'update'])->name('roles.update');
        Route::delete('/configuracion/roles/{rol}', [RolController::class, 'destroy'])->name('roles.destroy');
    });

    Route::middleware('can:personal.gestionar')->group(function () {
        Route::get('/configuracion/equipo/{usuario}/personal', [PersonalController::class, 'edit'])->name(
            'personal.edit',
        );
        Route::put('/configuracion/equipo/{usuario}/personal', [PersonalController::class, 'update'])->name(
            'personal.update',
        );
    });

    Route::middleware('can:asignacion.gestionar')->group(function () {
        Route::get('/configuracion/equipo/{usuario}/tiendas', [AsignacionController::class, 'edit'])->name(
            'asignacion.edit',
        );
        Route::put('/configuracion/equipo/{usuario}/tiendas', [AsignacionController::class, 'update'])->name(
            'asignacion.update',
        );
    });

    Route::middleware('can:clientes.importar')->group(function () {
        Route::get('/configuracion/importar-clientes/plantilla.xlsx', [
            ImportacionController::class,
            'templateXlsx',
        ])->name(
            'clientes.importar.plantilla.xlsx',
        );
        Route::get('/configuracion/importar-clientes/plantilla.csv', [
            ImportacionController::class,
            'templateCsv',
        ])->name(
            'clientes.importar.plantilla.csv',
        );
        Route::get('/configuracion/importar-clientes', [ImportacionController::class, 'index'])->name(
            'clientes.importar',
        );
        Route::post('/configuracion/importar-clientes', [ImportacionController::class, 'store'])->name(
            'clientes.importar.store',
        );
    });

    // Keep the person hub after its named subflows so a static path such as
    // /configuracion/equipo/nuevo is never mistaken for a Usuario binding.
    Route::get('/configuracion/equipo/{usuario}', [EquipoController::class, 'show'])->name('equipo.show');
});
