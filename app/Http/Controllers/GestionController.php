<?php

namespace App\Http\Controllers;

use App\Cockpit\AltaDeClientes;
use App\Cockpit\ClienteNoDisponible;
use App\Cockpit\DistribucionDeDatos;
use App\Cockpit\MezclaDeLineas;
use App\Http\Requests\Gestion\StoreGestionClienteRequest;
use App\Http\Requests\Gestion\StoreGestionRequest;
use App\Models\Dato;
use App\Models\Respuesta;
use App\Models\Usuario;
use App\Navigation\RutaDeGestion;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class GestionController extends Controller
{
    private const POR_PAGINA = 50;

    public const PERMISOS = [
        'MOVIL' => 'gestion.movil',
        'FIJA' => 'gestion.fija',
    ];

    public function index(Request $request, #[CurrentUser] Usuario $agente, string $tipo): View
    {
        $busqueda = $request->string('buscar')->trim()->limit(100)->toString();

        $datos = Dato::enCola($agente->id_usuario, $agente->tiendaAsignada(), $tipo)
            ->with('cliente')
            ->join('cliente', 'cliente.id_cliente', '=', 'datos.id_cliente')
            ->when($busqueda !== '', static function (Builder $query) use ($busqueda): Builder {
                $escaped = addcslashes($busqueda, '%_\\');

                return $query->where(static fn (Builder $clientes): Builder => $clientes
                    ->where('cliente.ruc', 'like', "%{$escaped}%")
                    ->orWhere('cliente.razon_social', 'like', "%{$escaped}%"));
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
            'rutaBase' => RutaDeGestion::index($tipo),
            'busqueda' => $busqueda,
        ]);
    }

    public function create(#[CurrentUser] Usuario $agente, string $tipo): View
    {
        $this->autorizarTipo($agente, $tipo);

        return view('gestion.create', [
            'tipo' => $tipo,
            'rutaBase' => RutaDeGestion::index($tipo),
            'rutaGuardar' => RutaDeGestion::agregar($tipo).'.store',
        ]);
    }

    public function storeCliente(
        StoreGestionClienteRequest $request,
        #[CurrentUser]
        Usuario $agente,
        string $tipo,
    ): RedirectResponse {
        $this->autorizarTipo($agente, $tipo);

        $idTienda = $agente->tiendaAsignada();

        if ($idTienda === null) {
            return back()->withInput()->with('error', 'No tiene una tienda activa asignada.');
        }

        try {
            $dato = AltaDeClientes::registrar($request->cliente(), $tipo, $agente, $idTienda);
        } catch (ClienteNoDisponible) {
            return back()->withInput()->with('error', 'El cliente no está disponible para esta gestión.');
        }

        return redirect()
            ->route('gestion.show', $dato)
            ->with('status', 'Cliente registrado. Registre la gestión.');
    }

    public function show(#[CurrentUser] Usuario $agente, Dato $dato): View
    {
        Gate::authorize('view', $dato);

        $cliente = $dato->cliente;

        // History belongs to the customer, not the campaign record.
        $historial = $cliente
            ->llamadas()
            ->where('id_usuario', $agente->id_usuario)
            ->with(['agente', 'respuesta'])
            ->orderByDesc('fecha')
            ->get();

        return view('gestion.show', [
            'dato' => $dato,
            'cliente' => $cliente,
            'respuestas' => Respuesta::query()->orderBy('descripcion')->get()->groupBy('tipo'),
            'rutaBase' => RutaDeGestion::index($dato->tipo_data),
            'historial' => $historial,
            'telefonos' => $cliente->telefonosConocidosPara($agente),
            // Fall back to the imported record when there is no prior contact.
            'mezcla' => MezclaDeLineas::observada($historial->first() ?? $dato),
        ]);
    }

    public function store(StoreGestionRequest $request, #[CurrentUser] Usuario $agente, Dato $dato): RedirectResponse
    {
        Gate::authorize('view', $dato);

        $dato->registrarGestion($agente->id_usuario, $request->contacto());

        return redirect()
            ->route(RutaDeGestion::index($dato->tipo_data))
            ->with('status', 'Gestión registrada.');
    }

    private function autorizarTipo(Usuario $agente, string $tipo): void
    {
        $permiso = self::PERMISOS[$tipo] ?? null;

        if ($permiso === null) {
            abort(404);
        }

        abort_unless($agente->can($permiso), 403);
    }
}
