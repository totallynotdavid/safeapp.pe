<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        health: '/up',
        // One file per module cluster as they port, so no single file collects
        // every screen in the app. Each carries the same web + auth stack.
        then: static function (): void {
            Route::middleware(['web', 'auth'])->group(base_path('routes/cockpit.php'));
            Route::middleware(['web', 'auth'])->group(base_path('routes/notas-cargo.php'));
            Route::middleware(['web', 'auth'])->group(base_path('routes/control-operativo.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
