<?php

// NOTA DE CARGO: the three stages a case passes through, each its own screen
// with its own permiso and its own columns. Liquidación is the fourth stage and
// still lives in web.php with the rest of the COBRANZA section.
//
// Legacy shipped five sidebar leaves per producto, because validación and back
// each existed twice: once filtered to the reviewer's tienda and once not. The
// filtered copies are gone. See the controllers for the evidence.

use App\Http\Controllers\NotaCargoMovilBackController;
use App\Http\Controllers\NotaCargoMovilController;
use App\Http\Controllers\NotaCargoMovilValidacionController;
use Illuminate\Support\Facades\Route;

Route::middleware('can:notacargo.movil.registrar')->group(function () {
    Route::get('/notas-cargo/movil', [NotaCargoMovilController::class, 'index'])
        ->name('notas-cargo.movil.index');
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
    Route::get('/notas-cargo/movil/back/{ncMovil}', [NotaCargoMovilBackController::class, 'edit'])
        ->name('notas-cargo.movil.back.edit');
    Route::put('/notas-cargo/movil/back/{ncMovil}', [NotaCargoMovilBackController::class, 'update'])
        ->name('notas-cargo.movil.back.update');
});
