<?php

use App\Http\Controllers\ClienteIdentidadController;
use App\Http\Controllers\LiquidacionController;
use App\Http\Controllers\QuiebreRegistroController;
use App\Http\Controllers\QuiebreValidacionController;
use Illuminate\Support\Facades\Route;

Route::middleware('can:quiebre.registrar')->group(function () {
    Route::get('/quiebres/registro', [QuiebreRegistroController::class, 'index'])->name('quiebres.registro.index');
    Route::get('/quiebres/registro/crear', [QuiebreRegistroController::class, 'create'])->name(
        'quiebres.registro.create',
    );
    Route::post('/quiebres/registro', [QuiebreRegistroController::class, 'store'])->name('quiebres.registro.store');
    Route::post('/quiebres/registro/cliente', [ClienteIdentidadController::class, 'store'])
        ->defaults('origen', 'quiebre')
        ->name('quiebres.registro.cliente.store');
    Route::get('/quiebres/registro/{quiebre}/editar', [QuiebreRegistroController::class, 'edit'])->name(
        'quiebres.registro.edit',
    );
    Route::put('/quiebres/registro/{quiebre}', [QuiebreRegistroController::class, 'update'])->name(
        'quiebres.registro.update',
    );
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

Route::middleware('can:liquidacion.gestionar')->group(function () {
    Route::get('/liquidaciones', [LiquidacionController::class, 'index'])->name('liquidaciones.index');
    Route::get('/liquidaciones/{ncMovil}', [LiquidacionController::class, 'edit'])->name('liquidaciones.edit');
    Route::put('/liquidaciones/{ncMovil}', [LiquidacionController::class, 'update'])->name('liquidaciones.update');
});
