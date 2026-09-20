<?php

namespace App\Providers;

use App\Http\Controllers\GestionController;
use App\Models\Dato;
use App\Models\Usuario;
use App\Navigation\Sidebar;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // migrate:fresh, migrate:refresh, migrate:reset, migrate:rollback and
        // db:wipe. A production run of any of these ends the business.
        DB::prohibitDestructiveCommands($this->app->isProduction());

        // Off in production, where the extra query beats a failed page. Outside
        // it, an unplanned lazy load is a bug in a list screen and should stop
        // the request that made it.
        Model::preventLazyLoading(! $this->app->isProduction());

        // Null lets the model policy decide when the user lacks the permission.
        Gate::before(static fn (Usuario $user, string $ability): ?bool => $user->hasPermiso($ability) ? true : null);

        View::composer('layouts.app', static function ($view): void {
            $user = Auth::user();
            $routeName = Route::currentRouteName();
            $dato = request()->route('dato');

            // Detail routes use the sidebar section for their record type.
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
            $routeName = Route::currentRouteName();

            if ($routeName === 'configuracion.index') {
                $routeName = 'configuracion.perfil';
            }

            $view->with(
                'settingsSections',
                $user
                    ? Sidebar::settingsForUser($user, $routeName)
                    : collect(),
            );
        });

        View::composer('partials.tienda-activa', static function ($view): void {
            $user = Auth::user();

            $view->with(
                'tiendasAsignadas',
                $user
                    ? $user->asignaciones()->with('tienda')->get()
                    : collect(),
            );
        });
    }
}
