<?php

use App\Http\Controllers\DistribucionController;
use App\Http\Controllers\GestionController;
use App\Http\Controllers\HistoricoController;
use App\Http\Controllers\ProduccionController;
use App\Http\Controllers\SeguimientoController;
use Illuminate\Support\Facades\Route;

// Movil and fija are one screen over two segments. The segment is a route
// default so each keeps its own permiso.
Route::middleware('can:gestion.movil')->group(function () {
    Route::get('/gestion/movil', [GestionController::class, 'index'])
        ->defaults('tipo', 'MOVIL')
        ->name('gestion.movil');
    Route::post('/gestion/movil/asignar', [DistribucionController::class, 'asignar'])
        ->defaults('tipo', 'MOVIL')
        ->name('gestion.movil.asignar');
    Route::post('/gestion/movil/liberar', [DistribucionController::class, 'liberar'])
        ->defaults('tipo', 'MOVIL')
        ->name('gestion.movil.liberar');
    Route::get('/gestion/movil/agregar', [GestionController::class, 'create'])
        ->defaults('tipo', 'MOVIL')
        ->name('gestion.movil.agregar');
    Route::post('/gestion/movil/agregar', [GestionController::class, 'storeCliente'])
        ->defaults('tipo', 'MOVIL')
        ->name('gestion.movil.agregar.store');
});

Route::middleware('can:gestion.fija')->group(function () {
    Route::get('/gestion/fija', [GestionController::class, 'index'])
        ->defaults('tipo', 'FIJA')
        ->name('gestion.fija');
    Route::post('/gestion/fija/asignar', [DistribucionController::class, 'asignar'])
        ->defaults('tipo', 'FIJA')
        ->name('gestion.fija.asignar');
    Route::post('/gestion/fija/liberar', [DistribucionController::class, 'liberar'])
        ->defaults('tipo', 'FIJA')
        ->name('gestion.fija.liberar');
    Route::get('/gestion/fija/agregar', [GestionController::class, 'create'])
        ->defaults('tipo', 'FIJA')
        ->name('gestion.fija.agregar');
    Route::post('/gestion/fija/agregar', [GestionController::class, 'storeCliente'])
        ->defaults('tipo', 'FIJA')
        ->name('gestion.fija.agregar.store');
});

Route::middleware('can:historico.ver')->group(function () {
    Route::get('/historico', [HistoricoController::class, 'index'])->name('historico.index');
    Route::get('/historico/exportar', [HistoricoController::class, 'exportar'])->name('historico.exportar');
});

// The same call log widened from one agent to a supervisor's whole team. Its own
// permiso rather than a mode on histórico: the 599 users legacy granted it are a
// strict subset of histórico's 602, so folding the two would hand three people a
// view of other people's work that they do not have today.
Route::middleware('can:seguimiento.equipo')->group(function () {
    Route::get('/seguimiento', [SeguimientoController::class, 'index'])->name('seguimiento.index');
    Route::get('/seguimiento/exportar', [SeguimientoController::class, 'exportar'])->name('seguimiento.exportar');
});

// No permiso here: the controller re-checks both the segment permiso and agent
// ownership.
Route::get('/gestion/dato/{dato}', [GestionController::class, 'show'])->name('gestion.show');
Route::post('/gestion/dato/{dato}', [GestionController::class, 'store'])->name('gestion.store');

Route::middleware('can:produccion.ver')
    ->get('/produccion', [ProduccionController::class, 'index'])
    ->name('produccion.index');
