<?php

// FUNNEL: the screens an agent works a client through. Split out of web.php
// because they are one coherent cluster with its own vocabulary (queue, pool,
// contact, off-list intake), and because web.php was becoming a single closure
// holding every module in the app.

use App\Http\Controllers\ClienteController;
use App\Http\Controllers\DistribucionController;
use App\Http\Controllers\GestionController;
use App\Http\Controllers\HistoricoController;
use App\Http\Controllers\ImportacionController;
use App\Http\Controllers\ProduccionController;
use App\Http\Controllers\SeguimientoController;
use Illuminate\Support\Facades\Route;

// GESTION MOVIL and GESTION FIJA are one screen over two segments. The
// segment is a route default so each keeps its own permiso. The shared
// client detail and write routes carry no permiso here, so the controller
// re-checks both the segment permiso and agent ownership.
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
});

// The off-list door. Registering a client only creates it; the first contact
// is logged on the client's own screen, so gestión stays the single write.
Route::middleware('can:clientes.crear')->group(function () {
    Route::get('/clientes/nuevo', [ClienteController::class, 'create'])->name('clientes.create');
    Route::post('/clientes', [ClienteController::class, 'store'])->name('clientes.store');
    Route::post('/datos/{dato}/tomar', [ClienteController::class, 'tomar'])->name('clientes.tomar');
});

// The bulk door, next to the off-list one: both create clients, and keeping them
// side by side is what makes it obvious that only this one loads a whole
// campaign. Its own permiso, since loading a file for the whole operation is not
// something an agent registering one RUC should be able to do.
Route::middleware('can:clientes.importar')->group(function () {
    Route::get('/clientes/importar', [ImportacionController::class, 'index'])->name('clientes.importar');
    Route::post('/clientes/importar', [ImportacionController::class, 'store'])->name('clientes.importar.store');
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

Route::get('/gestion/dato/{dato}', [GestionController::class, 'show'])->name('gestion.show');
Route::post('/gestion/dato/{dato}', [GestionController::class, 'store'])->name('gestion.store');

// What each ejecutivo has on the board this period, per producto. Legacy's
// DASHBOARD EJE REAL; see ProduccionController for why three of its four tables
// could not have worked.
Route::middleware('can:produccion.ver')
    ->get('/produccion', [ProduccionController::class, 'index'])
    ->name('produccion.index');
