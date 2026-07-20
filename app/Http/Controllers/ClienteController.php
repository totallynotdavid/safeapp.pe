<?php

namespace App\Http\Controllers;

use App\Cockpit\AltaDeClientes;
use App\Cockpit\DistribucionDeDatos;
use App\Http\Requests\Cliente\StoreClienteRequest;
use App\Models\Cliente;
use App\Models\Dato;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

// The off-list door: an agent has a RUC that is not in their queue. Every path
// through this screen ends at the record's own workspace, so contacts are always
// logged by the one form that owns that write.
class ClienteController extends Controller
{
    /**
     * Resolves a RUC to whatever state it is in, and offers the registration
     * form when nothing is workable.
     *
     * Legacy answered this with a JSON round trip because the form was a modal
     * on the same page. Server-rendered, it is one request: the RUC is part of
     * the URL and the response is whichever state that RUC is in.
     *
     * A known customer with no campaign record still offers registration. That
     * is the common case for a customer first met through a charge note or an
     * avería: they exist, but nobody has ever put them in a queue.
     */
    public function create(Request $request): View|RedirectResponse
    {
        $ruc = $request->string('ruc')->trim()->toString();
        if ($ruc === '') {
            return view('clientes.create', ['ruc' => null, 'cliente' => null, 'dato' => null]);
        }

        $cliente = Cliente::porRuc($ruc);
        if ($cliente === null) {
            return view('clientes.create', ['ruc' => $ruc, 'cliente' => null, 'dato' => null]);
        }

        $dato = Dato::query()
            ->where('id_cliente', $cliente->id_cliente)
            ->with('asignacionActiva.agente')
            ->latest('id_dato')
            ->first();

        if ($dato?->asignacionActiva?->id_usuario === $request->user()->id_usuario) {
            return redirect()->route('gestion.show', $dato);
        }

        // Held by someone else, free to take, or known but never loaded.
        return view('clientes.create', ['ruc' => $ruc, 'cliente' => $cliente, 'dato' => $dato]);
    }

    public function store(StoreClienteRequest $request): RedirectResponse
    {
        $agente = $request->user();
        $idTienda = $agente->tiendaAsignada();

        // The queue is scoped by tienda, so a record registered without one
        // would be invisible the moment the agent left this screen.
        if ($idTienda === null) {
            return back()->withInput()->with('error', 'No tiene una tienda activa asignada.');
        }

        $dato = AltaDeClientes::registrar($request->cliente(), $agente->id_usuario, $idTienda);

        return redirect()
            ->route('gestion.show', $dato)
            ->with('status', 'Cliente registrado. Registre la gestión.');
    }

    /**
     * Takes a record that is in the pool but was found by RUC rather than drawn
     * as part of a batch.
     */
    public function tomar(Request $request, Dato $dato): RedirectResponse
    {
        $agente = $request->user();
        $idTienda = $agente->tiendaAsignada();

        if ($idTienda === null) {
            return back()->with('error', 'No tiene una tienda activa asignada.');
        }

        if (! DistribucionDeDatos::tomarDato($dato->id_dato, $agente->id_usuario, $idTienda)) {
            return back()->with('error', 'Otro agente tomó ese cliente primero.');
        }

        return redirect()->route('gestion.show', $dato);
    }
}
