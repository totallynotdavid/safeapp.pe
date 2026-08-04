<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Which of their assigned tiendas an operator is currently working.
 *
 * No permiso gates this: it acts only on the caller's own assignments, which an
 * admin already decided. The membership check is the authorization, and it lives
 * in Usuario::activarTienda so it holds for any future caller too.
 */
class TiendaActivaController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $idTienda = $request->integer('id_tienda');

        if (! $request->user()->activarTienda($idTienda)) {
            return back()->with('error', 'Esa tienda no está asignada a su usuario.');
        }

        // Every operational query is scoped by the active tienda. Return to
        // the person's task entry point rather than a stale detail view.
        return redirect()->route('home')->with('status', 'Tienda activa actualizada.');
    }
}
