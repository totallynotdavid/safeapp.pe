<?php

// NOTA DE CARGO: the three stages a case passes through, each its own screen
// with its own permiso and its own columns. Liquidación is the fourth stage and
// still lives in web.php with the rest of the COBRANZA section.
//
// Legacy shipped five sidebar leaves per producto, because validación and back
// each existed twice: once filtered to the reviewer's tienda and once not. The
// filtered copies are gone. See the controllers for the evidence.
//
// Each queue also exports. The export sits inside the screen's own permiso group
// and runs the screen's own query, so it needs no leaf of its own: legacy's four
// REPORTE BACK leaves were the back screen a third and fourth time, with an
// Excel button bolted on.

use App\Http\Controllers\NotaCargoFijaBackController;
use App\Http\Controllers\NotaCargoFijaController;
use App\Http\Controllers\NotaCargoFijaValidacionController;
use App\Http\Controllers\NotaCargoMovilBackController;
use App\Http\Controllers\NotaCargoMovilController;
use App\Http\Controllers\NotaCargoMovilValidacionController;
use Illuminate\Support\Facades\Route;

Route::middleware('can:notacargo.movil.registrar')->group(function () {
    Route::get('/notas-cargo/movil', [NotaCargoMovilController::class, 'index'])
        ->name('notas-cargo.movil.index');
    Route::get('/notas-cargo/movil/exportar', [NotaCargoMovilController::class, 'exportar'])
        ->name('notas-cargo.movil.exportar');
    Route::get('/notas-cargo/movil/crear', [NotaCargoMovilController::class, 'create'])
        ->name('notas-cargo.movil.create');
    Route::post('/notas-cargo/movil', [NotaCargoMovilController::class, 'store'])
        ->name('notas-cargo.movil.store');
    Route::get('/notas-cargo/movil/{ncMovil}/editar', [NotaCargoMovilController::class, 'edit'])
        ->name('notas-cargo.movil.edit');
    Route::put('/notas-cargo/movil/{ncMovil}', [NotaCargoMovilController::class, 'update'])
        ->name('notas-cargo.movil.update');
});

Route::middleware('can:notacargo.movil.validar')->group(function () {
    Route::get('/notas-cargo/movil/validacion', [NotaCargoMovilValidacionController::class, 'index'])
        ->name('notas-cargo.movil.validacion.index');
    Route::get('/notas-cargo/movil/validacion/{ncMovil}', [NotaCargoMovilValidacionController::class, 'edit'])
        ->name('notas-cargo.movil.validacion.edit');
    Route::put('/notas-cargo/movil/validacion/{ncMovil}', [NotaCargoMovilValidacionController::class, 'update'])
        ->name('notas-cargo.movil.validacion.update');
});

Route::middleware('can:notacargo.movil.back')->group(function () {
    Route::get('/notas-cargo/movil/back', [NotaCargoMovilBackController::class, 'index'])
        ->name('notas-cargo.movil.back.index');
    Route::get('/notas-cargo/movil/back/exportar', [NotaCargoMovilBackController::class, 'exportar'])
        ->name('notas-cargo.movil.back.exportar');
    Route::get('/notas-cargo/movil/back/{ncMovil}', [NotaCargoMovilBackController::class, 'edit'])
        ->name('notas-cargo.movil.back.edit');
    Route::put('/notas-cargo/movil/back/{ncMovil}', [NotaCargoMovilBackController::class, 'update'])
        ->name('notas-cargo.movil.back.update');
});

Route::middleware('can:notacargo.fija.registrar')->group(function () {
    Route::get('/notas-cargo/fija', [NotaCargoFijaController::class, 'index'])
        ->name('notas-cargo.fija.index');
    Route::get('/notas-cargo/fija/exportar', [NotaCargoFijaController::class, 'exportar'])
        ->name('notas-cargo.fija.exportar');
    Route::get('/notas-cargo/fija/crear', [NotaCargoFijaController::class, 'create'])
        ->name('notas-cargo.fija.create');
    Route::post('/notas-cargo/fija', [NotaCargoFijaController::class, 'store'])
        ->name('notas-cargo.fija.store');
    Route::get('/notas-cargo/fija/{ncFija}/editar', [NotaCargoFijaController::class, 'edit'])
        ->name('notas-cargo.fija.edit');
    Route::put('/notas-cargo/fija/{ncFija}', [NotaCargoFijaController::class, 'update'])
        ->name('notas-cargo.fija.update');
});

Route::middleware('can:notacargo.fija.validar')->group(function () {
    Route::get('/notas-cargo/fija/validacion', [NotaCargoFijaValidacionController::class, 'index'])
        ->name('notas-cargo.fija.validacion.index');
    Route::get('/notas-cargo/fija/validacion/{ncFija}', [NotaCargoFijaValidacionController::class, 'edit'])
        ->name('notas-cargo.fija.validacion.edit');
    Route::put('/notas-cargo/fija/validacion/{ncFija}', [NotaCargoFijaValidacionController::class, 'update'])
        ->name('notas-cargo.fija.validacion.update');
});

Route::middleware('can:notacargo.fija.back')->group(function () {
    Route::get('/notas-cargo/fija/back', [NotaCargoFijaBackController::class, 'index'])
        ->name('notas-cargo.fija.back.index');
    Route::get('/notas-cargo/fija/back/exportar', [NotaCargoFijaBackController::class, 'exportar'])
        ->name('notas-cargo.fija.back.exportar');
    Route::get('/notas-cargo/fija/back/{ncFija}', [NotaCargoFijaBackController::class, 'edit'])
        ->name('notas-cargo.fija.back.edit');
    Route::put('/notas-cargo/fija/back/{ncFija}', [NotaCargoFijaBackController::class, 'update'])
        ->name('notas-cargo.fija.back.update');
});
