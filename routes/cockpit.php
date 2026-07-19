<?php

// FUNNEL: the screens an agent works a client through. Split out of web.php
// because they are one coherent cluster with its own vocabulary (queue, pool,
// contact, off-list intake), and because web.php was becoming a single closure
// holding every module in the app.

use App\Http\Controllers\ClienteController;
use App\Http\Controllers\DistribucionController;
use App\Http\Controllers\GestionController;
use App\Http\Controllers\HistoricoController;
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
    Route::post('/clientes/{cliente}/tomar', [ClienteController::class, 'tomar'])->name('clientes.tomar');
});

Route::middleware('can:historico.ver')
    ->get('/historico', [HistoricoController::class, 'index'])
    ->name('historico.index');

Route::get('/gestion/cliente/{cliente}', [GestionController::class, 'show'])->name('gestion.show');
Route::post('/gestion/cliente/{cliente}', [GestionController::class, 'store'])->name('gestion.store');
