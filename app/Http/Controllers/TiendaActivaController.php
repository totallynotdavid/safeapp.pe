<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TiendaActivaController extends Controller
{
    public function update(Request $request, #[CurrentUser] Usuario $agente): RedirectResponse
    {
        if (! $agente->activarTienda($request->integer('id_tienda'))) {
            return back()->with('error', 'Esa tienda no está asignada a su usuario.');
        }

        return redirect()
            ->route('home')
            ->with('status', 'Tienda activa actualizada.');
    }
}
