<?php

namespace App\Http\Controllers;

use App\Cockpit\ImportacionDeClientes;
use App\Cockpit\PlantillaDeImportacion;
use App\Http\Requests\Cliente\ImportarClientesRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * CARGAR DATOS: loading a campaign file of clients.
 *
 * Legacy's version of this screen had no upload. It was a single button that
 * promoted a `datos_temporal` staging table someone filled out of band, so the
 * front door of the import lived outside the application entirely and the screen
 * held only the last step. The file is the front door now, and the staging table
 * is gone with it.
 */
class ImportacionController extends Controller
{
    public function index(): View
    {
        return view('clientes.importar');
    }

    public function store(ImportarClientesRequest $request): RedirectResponse
    {
        $archivo = $request->file('archivo');
        $resultado = ImportacionDeClientes::desdeArchivo(
            $archivo->getRealPath(),
            $archivo->getClientOriginalExtension(),
        );

        if (! $resultado->exitosa()) {
            return back()->with('errores', $resultado->errores);
        }

        return back()->with(
            'status',
            "Se cargaron {$resultado->total()} clientes: {$resultado->nuevos} nuevos, {$resultado->actualizados} actualizados.",
        );
    }

    public function templateCsv(): StreamedResponse
    {
        return PlantillaDeImportacion::csv();
    }

    public function templateXlsx(): StreamedResponse
    {
        return PlantillaDeImportacion::xlsx();
    }
}
