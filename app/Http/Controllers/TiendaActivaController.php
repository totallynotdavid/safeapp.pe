<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TiendaActivaController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $idTienda = $request->integer('id_tienda');

        if (! $request->user()->activarTienda($idTienda)) {
            return back()->with('error', 'Esa tienda no está asignada a su usuario.');
        }

        return redirect()
            ->route('home')
            ->with('status', 'Tienda activa actualizada.');
    }
}
