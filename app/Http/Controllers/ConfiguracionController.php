<?php

namespace App\Http\Controllers;

use App\Navigation\Sidebar;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ConfiguracionController extends Controller
{
    public function index(Request $request): View
    {
        return view('configuracion.index', [
            'settingsSections' => Sidebar::settingsForUser(
                $request->user(),
                'configuracion.perfil',
            ),
            'usuario' => $request->user(),
        ]);
    }
}
