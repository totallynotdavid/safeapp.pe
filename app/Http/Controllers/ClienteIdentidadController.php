<?php

namespace App\Http\Controllers;

use App\Cockpit\AccesoAClientes;
use App\Cockpit\ClienteNoDisponible;
use App\Http\Requests\Cliente\StoreClienteIdentidadRequest;
use Illuminate\Http\RedirectResponse;

// There is intentionally no standalone customer-creation page.  This action is
// published only by the three case forms that can resume after identity intake.
class ClienteIdentidadController extends Controller
{
    public function store(StoreClienteIdentidadRequest $request, string $origen): RedirectResponse
    {
        $rutas = [
            'movil' => 'notas-cargo.movil.create',
            'fija' => 'notas-cargo.fija.create',
            'quiebre' => 'quiebres.registro.create',
        ];
        $ruta = $rutas[$origen] ?? abort(404);

        try {
            AccesoAClientes::resolverOcrear(
                $request->user(),
                $request->string('ruc')->toString(),
                $request->string('razon_social')->trim()->toString(),
            );
        } catch (ClienteNoDisponible) {
            return back()->withInput()->withErrors(['ruc' => 'El cliente no está disponible para esta gestión.']);
        }

        return redirect()->route($ruta, ['ruc' => $request->string('ruc')->toString()]);
    }
}
