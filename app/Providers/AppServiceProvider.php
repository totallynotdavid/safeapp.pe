<?php

namespace App\Providers;

use App\Models\Usuario;
use App\View\Composers\NavegacionDeConfiguracion;
use App\View\Composers\NavegacionPrincipal;
use App\View\Composers\SelectorDeTienda;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\DevCommands;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // migrate:fresh, migrate:refresh, migrate:reset, migrate:rollback and
        // db:wipe. A production run of any of these ends the business.
        DB::prohibitDestructiveCommands(app()->isProduction());

        // Off in production, where the extra query beats a failed page. Outside
        // it, an unplanned lazy load is a bug in a list screen and should stop
        // the request that made it.
        Model::preventLazyLoading(! app()->isProduction());

        // Null lets the model policy decide when the user lacks the permission.
        Gate::before(static fn (Usuario $user, string $ability): ?bool => $user->hasPermiso($ability) ? true : null);

        // artisan dev starts Vite through package.json's dev script by default.
        // The commands live in mise tasks, so run Vite directly.
        DevCommands::nodeExec('vite', 'vite');

        // Each navigation partial is composed where it is included, not on the
        // layout around it. settings-nav renders both inside Configuración's
        // content and in the layout aside, and only the partial's own composer
        // reaches both.
        View::composer('partials.sidebar', NavegacionPrincipal::class);
        View::composer('partials.settings-nav', NavegacionDeConfiguracion::class);
        View::composer('partials.tienda-activa', SelectorDeTienda::class);
    }
}
