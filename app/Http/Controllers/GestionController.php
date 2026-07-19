<?php

namespace App\Http\Controllers;

use App\Cockpit\DistribucionDeClientes;
use App\Http\Requests\Gestion\StoreGestionRequest;
use App\Models\Cliente;
use App\Models\Respuesta;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

// The agent cockpit: the queue of clients assigned to the signed-in agent, and
// the form that logs a contact against one. GESTION MOVIL and GESTION FIJA are
// one screen over two segments, so the route supplies the segment and its
// permiso.
class GestionController extends Controller
{
    private const POR_PAGINA = 50;

    // Segments an agent can work, each behind its own permiso. A segment absent
    // here has no screen and cannot be opened.
    private const PERMISOS = [
        'MOVIL' => 'gestion.movil',
        'FIJA' => 'gestion.fija',
    ];

    public function index(Request $request, string $tipo): View
    {
        $agente = $request->user();

        $clientes = Cliente::query()
            ->enCola($agente->id_usuario, $agente->tiendaAsignada(), $tipo)
            ->orderBy('razon_social')
            ->paginate(self::POR_PAGINA);

        return view('gestion.index', [
            'clientes' => $clientes,
            'tipo' => $tipo,
            'origenes' => DistribucionDeClientes::origenesDisponibles($tipo),
            'lote' => DistribucionDeClientes::LOTE,
            'rutaBase' => self::rutaIndex($tipo),
        ]);
    }

    public function show(Request $request, Cliente $cliente): View
    {
        $this->autorizarGestion($request, $cliente);

        return view('gestion.show', [
            'cliente' => $cliente,
            'respuestas' => Respuesta::query()->orderBy('descripcion')->get()->groupBy('tipo'),
            'rutaBase' => self::rutaIndex($cliente->tipo_data),
            'historial' => $cliente->llamadas()->with(['agente', 'respuesta'])->orderByDesc('fecha')->get(),
            'telefonos' => $cliente->telefonosConocidos(),
        ]);
    }

    public function store(StoreGestionRequest $request, Cliente $cliente): RedirectResponse
    {
        $this->autorizarGestion($request, $cliente);

        $cliente->registrarGestion($request->user()->id_usuario, $request->contacto());

        return redirect()
            ->route(self::rutaIndex($cliente->tipo_data))
            ->with('status', 'Gestión registrada.');
    }

    /**
     * An agent may only open and work a client that is assigned to them, and
     * only through the screen that owns that data segment. Both controllers
     * paths go through here so neither rule can be forgotten at a call site.
     */
    private function autorizarGestion(Request $request, Cliente $cliente): void
    {
        $permiso = self::PERMISOS[$cliente->tipo_data] ?? null;

        abort_if($permiso === null, 404);
        abort_unless($request->user()->can($permiso), 403);
        abort_unless($cliente->id_usuario === $request->user()->id_usuario, 403);
    }

    /**
     * The route name a segment's screen is published under. Its distribution
     * actions hang off it as .asignar and .liberar, so this is the only place
     * that turns a data segment into a route name.
     */
    public static function rutaIndex(string $tipoData): string
    {
        return 'gestion.'.mb_strtolower($tipoData);
    }
}
