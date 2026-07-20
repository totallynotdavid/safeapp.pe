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

        // Back to the dashboard rather than the current page: every operational
        // queue is scoped by tienda, so the page they were on is showing rows
        // that are no longer theirs to work.
        return redirect()->route('dashboard')->with('status', 'Tienda activa actualizada.');
    }
}
