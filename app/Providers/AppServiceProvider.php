<?php

namespace App\Providers;

use App\Models\Usuario;
use App\Navigation\Sidebar;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void {}

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // A held permiso grants that ability. Return null, not false, when it
        // is absent so model policies still decide view/create/update/delete;
        // those verbs are never registered as permiso keys.
        Gate::before(static function (Usuario $user, string $ability): ?bool {
            return $user->hasPermiso($ability) ? true : null;
        });

        View::composer('layouts.app', static function ($view): void {
            $user = Auth::user();
            $view->with('sidebarSections', $user ? Sidebar::forUser($user) : collect());
        });
    }
}
