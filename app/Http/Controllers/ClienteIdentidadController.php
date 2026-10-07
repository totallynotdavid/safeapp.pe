<?php

namespace App\Http\Controllers;

use App\Cockpit\AccesoAClientes;
use App\Cockpit\ClienteNoDisponible;
use App\Http\Requests\Cliente\StoreClienteIdentidadRequest;
use App\Models\Usuario;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\RedirectResponse;

// Customer identity is created only as part of a case flow.
class ClienteIdentidadController extends Controller
{
    public function store(
        StoreClienteIdentidadRequest $request,
        string $origen,
        #[CurrentUser]
        Usuario $agente,
    ): RedirectResponse {
        $rutas = [
            'movil' => 'notas-cargo.movil.create',
            'fija' => 'notas-cargo.fija.create',
            'quiebre' => 'quiebres.registro.create',
        ];

        $ruta = $rutas[$origen] ?? abort(404);
        $ruc = $request->string('ruc')->toString();

        try {
            AccesoAClientes::resolverOcrear(
                $agente,
                $ruc,
                $request->string('razon_social')->trim()->toString(),
            );
        } catch (ClienteNoDisponible) {
            return back()
                ->withInput()
                ->withErrors([
                    'ruc' => 'El cliente no está disponible para esta gestión.',
                ]);
        }

        return redirect()->route($ruta, ['ruc' => $ruc]);
    }
}
