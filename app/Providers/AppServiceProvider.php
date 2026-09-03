<?php

namespace App\Providers;

use App\Http\Controllers\GestionController;
use App\Models\Dato;
use App\Models\Usuario;
use App\Navigation\Sidebar;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // A held permiso grants that ability. Return null, not false, when it
        // is absent so model policies still decide view/create/update/delete;
        // those verbs are never registered as permiso keys.
        Gate::before(static fn (Usuario $user, string $ability): ?bool => $user->hasPermiso($ability) ? true : null);

        View::composer('layouts.app', static function ($view): void {
            $user = Auth::user();
            $routeName = Route::currentRouteName();
            $dato = request()->route('dato');

            // The shared cockpit detail route belongs to the segment carried by
            // its bound record, so the matching queue remains selected.
            if ($routeName === 'gestion.show' && $dato instanceof Dato) {
                $routeName = GestionController::rutaIndex($dato->tipo_data);
            }

            $view->with([
                'sidebarSections' => $user ? Sidebar::forUser($user, $routeName) : collect(),
                'settingsUrl' => $user ? Sidebar::settingsUrlForUser($user) : null,
            ]);
        });

        View::composer('layouts.settings', static function ($view): void {
            $user = Auth::user();
            $routeName = Route::currentRouteName() === 'configuracion.index'
                ? 'configuracion.perfil'
                : Route::currentRouteName();
            $view->with(
                'settingsSections',
                $user
                    ? Sidebar::settingsForUser($user, $routeName)
                    : collect(),
            );
        });

        View::composer('partials.tienda-activa', static function ($view): void {
            $user = Auth::user();
            $view->with('tiendasAsignadas', $user ? $user->asignaciones()->with('tienda')->get() : collect());
        });
    }
}
