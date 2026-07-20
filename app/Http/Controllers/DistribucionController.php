<?php

namespace App\Http\Controllers;

use App\Cockpit\DistribucionDeDatos;
use App\Http\Requests\Gestion\AsignarLoteRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

// How clients get into an agent's queue: they draw a batch from the unassigned
// pool, and hand back whatever they have not worked. Both actions are posted
// from the queue screen and are gated by that segment's permiso on the route.
class DistribucionController extends Controller
{
    public function asignar(AsignarLoteRequest $request, string $tipo): RedirectResponse
    {
        $agente = $request->user();
        $idTienda = $agente->tiendaAsignada();

        // Without an active tienda the assignment would write id_tienda NULL,
        // and the queue is scoped by tienda, so the agent would take clients out
        // of the pool and then never see them. Refuse instead of stranding rows.
        if ($idTienda === null) {
            return back()->with('error', 'No tiene una tienda activa asignada.');
        }

        $origen = $request->string('origen')->toString();
        $asignados = DistribucionDeDatos::asignarLote($agente->id_usuario, $idTienda, $origen, $tipo);

        if ($asignados === 0) {
            return back()->with('error', 'Ya no quedan clientes disponibles con ese origen.');
        }

        return back()->with('status', "Se le asignaron {$asignados} clientes.");
    }

    public function liberar(Request $request, string $tipo): RedirectResponse
    {
        $liberados = DistribucionDeDatos::liberarPendientes($request->user()->id_usuario, $tipo);

        return back()->with('status', "Devolvió {$liberados} clientes a la cola.");
    }
}
