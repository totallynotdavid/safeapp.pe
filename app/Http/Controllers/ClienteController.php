<?php

namespace App\Http\Controllers;

use App\Cockpit\AltaDeClientes;
use App\Cockpit\DistribucionDeClientes;
use App\Http\Requests\Cliente\StoreClienteRequest;
use App\Models\Cliente;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

// The off-list door: an agent has a RUC that is not in their queue. Every path
// through this screen ends at the client's own workspace, so contacts are always
// logged by the one form that owns that write.
class ClienteController extends Controller
{
    /**
     * Resolves a RUC, or offers the registration form when nothing owns it.
     *
     * Legacy answered this with a JSON round trip because the form was a modal
     * on the same page. Server-rendered, it is one request: the RUC is part of
     * the URL and the response is whichever state that RUC is in.
     */
    public function create(Request $request): View|RedirectResponse
    {
        $ruc = $request->string('ruc')->trim()->toString();
        if ($ruc === '') {
            return view('clientes.create', ['ruc' => null, 'cliente' => null]);
        }

        $cliente = Cliente::porRuc($ruc);
        if ($cliente === null) {
            return view('clientes.create', ['ruc' => $ruc, 'cliente' => null]);
        }

        if ($cliente->id_usuario === $request->user()->id_usuario) {
            return redirect()->route('gestion.show', $cliente);
        }

        // Held by someone else, or free to take. Either way the client already
        // exists, so the answer is never "register it again".
        return view('clientes.create', ['ruc' => $ruc, 'cliente' => $cliente->load('agente')]);
    }

    public function store(StoreClienteRequest $request): RedirectResponse
    {
        $agente = $request->user();
        $idTienda = $agente->tiendaAsignada();

        // The queue is scoped by tienda, so a client registered without one
        // would be invisible the moment the agent left this screen.
        if ($idTienda === null) {
            return back()->withInput()->with('error', 'No tiene una tienda activa asignada.');
        }

        $cliente = AltaDeClientes::registrar($request->cliente(), $agente->id_usuario, $idTienda);

        return redirect()
            ->route('gestion.show', $cliente)
            ->with('status', 'Cliente registrado. Registre la gestión.');
    }

    /**
     * Takes a client that is in the pool but was found by RUC rather than drawn
     * as part of a batch.
     */
    public function tomar(Request $request, Cliente $cliente): RedirectResponse
    {
        $agente = $request->user();
        $idTienda = $agente->tiendaAsignada();

        if ($idTienda === null) {
            return back()->with('error', 'No tiene una tienda activa asignada.');
        }

        if (! DistribucionDeClientes::tomarCliente($cliente->id_cliente, $agente->id_usuario, $idTienda)) {
            return back()->with('error', 'Otro agente tomó ese cliente primero.');
        }

        return redirect()->route('gestion.show', $cliente);
    }
}
