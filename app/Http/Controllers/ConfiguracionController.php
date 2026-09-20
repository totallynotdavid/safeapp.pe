<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ConfiguracionController extends Controller
{
    public function index(Request $request): View
    {
        return view('configuracion.index', [
            'usuario' => $request->user(),
        ]);
    }
}
