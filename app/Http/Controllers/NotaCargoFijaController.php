<?php

namespace App\Http\Controllers;

use App\Http\Requests\NotaCargoFija\StoreNotaCargoFijaRequest;
use App\Http\Requests\NotaCargoFija\UpdateNotaCargoFijaRequest;
use App\Models\Cliente;
use App\Models\NcFija;
use App\Models\Producto;
use App\Models\Tienda;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

// Nota de cargo fija, REGISTRO: an ejecutivo files a case against a client and
// tracks their own. Validación and back are separate screens with their own
// permisos; this one never writes their columns.
class NotaCargoFijaController extends Controller
{
    public function index(Request $request): View
    {
        $ano = (int) $request->integer('ano', now()->year);
        $mes = (int) $request->integer('mes', now()->month);
        $estado = $request->query('estado');
        $filtroActivo = in_array($estado, NcFija::ESTADOS, true);

        return view('notas-cargo.fija.index', [
            'cargos' => $this->mias($request->user()->id_usuario, $ano, $mes, $filtroActivo ? $estado : null),
            'estados' => NcFija::ESTADOS,
            'estado' => $filtroActivo ? $estado : null,
            'ano' => $ano,
            'mes' => $mes,
            'anos' => range(now()->year, now()->year - 5),
        ]);
    }

    public function create(Request $request): View
    {
        $ruc = $request->string('ruc')->trim()->toString();

        return view('notas-cargo.fija.create', [
            'ruc' => $ruc === '' ? null : $ruc,
            'cliente' => $ruc === '' ? null : Cliente::porRuc($ruc),
            'regiones' => Tienda::query()->orderBy('nombre')->get(),
            'catalogo' => Producto::catalogoFija(),
            'modalidades' => NcFija::MODALIDADES,
            'tecnologias' => NcFija::TECNOLOGIAS,
        ]);
    }

    public function store(StoreNotaCargoFijaRequest $request): RedirectResponse
    {
        $producto = Producto::findOrFail($request->integer('id_producto'));

        $cargo = new NcFija($request->safe()->except(['id_producto', 'cargo_fijo']));

        // What was sold is copied off the catalog rather than posted, so the
        // pair on the case is always one the catalog actually offered. The price
        // is the catalog's unless the agent gave one.
        $cargo->subtipo = $producto->subtipo;
        $cargo->descripcion = $producto->descripcion;
        $cargo->cargo_fijo = $request->input('cargo_fijo') ?? $producto->cargo_fijo;

        // Owner, supervisor and date come from the session and the clock. Legacy
        // posted all three as hidden form fields.
        $cargo->id_usuario = $request->user()->id_usuario;
        $cargo->id_supervisor = $request->user()->id_supervisor;
        $cargo->fecha_ingreso = now()->toDateString();
        $cargo->save();

        return redirect()
            ->route('notas-cargo.fija.index')
            ->with('status', 'Nota de cargo registrada.');
    }

    public function edit(NcFija $ncFija): View
    {
        Gate::authorize('view', $ncFija);

        return view('notas-cargo.fija.edit', [
            'cargo' => $ncFija->load('cliente', 'ejecutivo', 'supervisor', 'validador', 'region'),
            'tecnologias' => NcFija::TECNOLOGIAS,
        ]);
    }

    public function update(UpdateNotaCargoFijaRequest $request, NcFija $ncFija): RedirectResponse
    {
        Gate::authorize('update', $ncFija);

        $ncFija->fill($request->validated())->save();

        return redirect()
            ->route('notas-cargo.fija.index')
            ->with('status', 'Nota de cargo actualizada.');
    }

    /**
     * One ejecutivo's own cases for a period. Rejected cases are left out: the
     * ejecutivo cannot act on them and legacy excluded them from every branch.
     *
     * @return Collection<int, NcFija>
     */
    private function mias(int $idUsuario, int $ano, int $mes, ?string $estado): Collection
    {
        return NcFija::query()
            ->with('cliente')
            ->where('id_usuario', $idUsuario)
            ->where('validacion', '<>', NcFija::VALIDACION_RECHAZADO)
            ->enPeriodo($ano, $mes, $estado)
            ->orderByDesc('fecha_ingreso')
            ->get();
    }
}
