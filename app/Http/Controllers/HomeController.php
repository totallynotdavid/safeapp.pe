<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Navigation\Sidebar;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

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
