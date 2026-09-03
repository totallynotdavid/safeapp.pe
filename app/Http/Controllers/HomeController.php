<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Navigation\Sidebar;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Authenticated entry point.
 *
 * SAFEAPP has no cross-workflow dashboard data to show. Entry belongs at the
 * first work area the person is allowed to use, with Configuración as the
 * fallback for administration-only accounts.
 */
class HomeController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        /** @var Usuario $user */
        $user = $request->user();

        $url = Sidebar::startUrlForUser($user);

        if ($url !== null) {
            return redirect()->to($url);
        }

        abort(403, 'Tu cuenta no tiene áreas de trabajo habilitadas.');
    }
}
