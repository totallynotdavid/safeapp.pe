<?php

namespace App\Http\Controllers;

use App\Cockpit\AltaDeClientes;
use App\Cockpit\ClienteNoDisponible;
use App\Cockpit\DistribucionDeDatos;
use App\Cockpit\MezclaDeLineas;
use App\Http\Requests\Gestion\StoreGestionClienteRequest;
use App\Http\Requests\Gestion\StoreGestionRequest;
use App\Models\Cliente;
use App\Models\Dato;
use App\Models\Respuesta;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

// The agent cockpit: the campaign records assigned to the signed-in agent, and
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
        $busqueda = $request->string('buscar')->trim()->limit(100)->toString();

        $datos = Dato::query()
            ->enCola($agente->id_usuario, $agente->tiendaAsignada(), $tipo)
            ->with('cliente')
            ->join('cliente', 'cliente.id_cliente', '=', 'datos.id_cliente')
            ->when($busqueda !== '', static function ($query) use ($busqueda): void {
                $escaped = addcslashes($busqueda, '%_\\');

                $query->where(static function ($clientes) use ($escaped): void {
                    $clientes
                        ->where('cliente.ruc', 'like', "%{$escaped}%")
                        ->orWhere('cliente.razon_social', 'like', "%{$escaped}%");
                });
            })
            ->orderBy('cliente.razon_social')
            ->select('datos.*')
            ->paginate(self::POR_PAGINA)
            ->withQueryString();

        return view('gestion.index', [
            'datos' => $datos,
            'tipo' => $tipo,
            'origenes' => DistribucionDeDatos::origenesDisponibles($tipo),
            'lote' => DistribucionDeDatos::LOTE,
            'rutaBase' => self::rutaIndex($tipo),
            'busqueda' => $busqueda,
        ]);
    }

    public function create(Request $request, string $tipo): View
    {
        $this->autorizarTipo($request, $tipo);

        return view('gestion.create', [
            'tipo' => $tipo,
            'rutaBase' => self::rutaIndex($tipo),
            'rutaGuardar' => self::rutaAgregar($tipo).'.store',
        ]);
    }

    public function storeCliente(StoreGestionClienteRequest $request, string $tipo): RedirectResponse
    {
        $this->autorizarTipo($request, $tipo);
        $agente = $request->user();
        $idTienda = $agente->tiendaAsignada();

        if ($idTienda === null) {
            return back()->withInput()->with('error', 'No tiene una tienda activa asignada.');
        }

        try {
            $dato = AltaDeClientes::registrar($request->cliente(), $tipo, $agente, $idTienda);
        } catch (ClienteNoDisponible) {
            return back()->withInput()->with('error', 'El cliente no está disponible para esta gestión.');
        }

        return redirect()->route('gestion.show', $dato)->with('status', 'Cliente registrado. Registre la gestión.');
    }

    public function show(Request $request, Dato $dato): View
    {
        $this->autorizarGestion($request, $dato);

        $cliente = $dato->cliente;

        // The contact history is the customer's, not the campaign's: an agent
        // working a reloaded record needs to see what was already said to this
        // customer under any earlier campaign.
        $historial = $cliente
            ->llamadas()
            ->where('id_usuario', $request->user()->id_usuario)
            ->with(['agente', 'respuesta'])
            ->orderByDesc('fecha')
            ->get();

        return view('gestion.show', [
            'dato' => $dato,
            'cliente' => $cliente,
            'respuestas' => Respuesta::query()->orderBy('descripcion')->get()->groupBy('tipo'),
            'rutaBase' => self::rutaIndex($dato->tipo_data),
            'historial' => $historial,
            'telefonos' => $cliente->telefonosConocidosPara($request->user()),
            // The last contact is the freshest read on the line mix; the file's
            // counts stand in for a record nobody has called yet. Composed here
            // from rows already loaded, so it costs no extra query.
            'mezcla' => MezclaDeLineas::observada($historial->first() ?? $dato),
        ]);
    }

    public function store(StoreGestionRequest $request, Dato $dato): RedirectResponse
    {
        $this->autorizarGestion($request, $dato);

        $dato->registrarGestion($request->user()->id_usuario, $request->contacto());

        return redirect()
            ->route(self::rutaIndex($dato->tipo_data))
            ->with('status', 'Gestión registrada.');
    }

    /**
     * An agent may only open and work a record assigned to them, and
     * only through the screen that owns that data segment. Both controllers
     * paths go through here so neither rule can be forgotten at a call site.
     */
    private function autorizarGestion(Request $request, Dato $dato): void
    {
        $permiso = self::PERMISOS[$dato->tipo_data] ?? null;

        abort_if($permiso === null, 404);
        abort_unless($request->user()->can($permiso), 403);
        abort_unless(
            $dato->asignacionActiva?->id_usuario === $request->user()->id_usuario,
            403,
        );
        abort_unless(Cliente::visiblePara($request->user())->whereKey($dato->id_cliente)->exists(), 403);
    }

    private function autorizarTipo(Request $request, string $tipo): void
    {
        $permiso = self::PERMISOS[$tipo] ?? null;
        abort_if($permiso === null, 404);
        abort_unless($request->user()->can($permiso), 403);
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

    public static function rutaAgregar(string $tipoData): string
    {
        return self::rutaIndex($tipoData).'.agregar';
    }
}
