<?php

namespace App\Http\Controllers;

use App\Http\Requests\NotaCargoMovil\StoreNotaCargoMovilRequest;
use App\Http\Requests\NotaCargoMovil\UpdateNotaCargoMovilRequest;
use App\Models\Cartera;
use App\Models\Cliente;
use App\Models\NcMovil;
use App\Models\Tienda;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

// Nota de cargo móvil, REGISTRO: an ejecutivo files a case against a client and
// tracks their own. Validación and back are separate screens with their own
// permisos; this one never writes their columns.
class NotaCargoMovilController extends Controller
{
    public function index(Request $request): View
    {
        $ano = (int) $request->integer('ano', now()->year);
        $mes = (int) $request->integer('mes', now()->month);
        $estado = $request->query('estado');
        $filtroActivo = in_array($estado, NcMovil::ESTADOS, true);

        return view('notas-cargo.movil.index', [
            'cargos' => $this->mias($request->user()->id_usuario, $ano, $mes, $filtroActivo ? $estado : null),
            'estados' => NcMovil::ESTADOS,
            'estado' => $filtroActivo ? $estado : null,
            'ano' => $ano,
            'mes' => $mes,
            'anos' => range(now()->year, now()->year - 5),
        ]);
    }

    public function create(Request $request): View
    {
        $ruc = $request->string('ruc')->trim()->toString();

        return view('notas-cargo.movil.create', [
            'ruc' => $ruc === '' ? null : $ruc,
            'cliente' => $ruc === '' ? null : Cliente::porRuc($ruc),
            'regiones' => Tienda::query()->orderBy('nombre')->get(),
            'carteras' => Cartera::query()->seleccionables()->get(),
            'modalidades' => NcMovil::MODALIDADES,
            'niveles' => NcMovil::NIVELES_NEGOCIACION,
            'tiposProducto' => NcMovil::TIPOS_PRODUCTO,
            'etapas' => NcMovil::ETAPAS,
        ]);
    }

    public function store(StoreNotaCargoMovilRequest $request): RedirectResponse
    {
        $cargo = new NcMovil($request->safe()->except('id_cartera'));

        // Owner, supervisor and date come from the session and the clock. Legacy
        // posted all three as hidden form fields.
        $cargo->id_usuario = $request->user()->id_usuario;
        $cargo->id_supervisor = $request->user()->id_supervisor;
        $cargo->fecha_ingreso = now()->toDateString();
        $cargo->id_cartera = (int) $request->input('id_cartera', 0);
        $cargo->save();

        return redirect()
            ->route('notas-cargo.movil.index')
            ->with('status', 'Nota de cargo registrada.');
    }

    public function edit(NcMovil $ncMovil): View
    {
        Gate::authorize('view', $ncMovil);

        return view('notas-cargo.movil.edit', [
            'cargo' => $ncMovil->load('cliente', 'ejecutivo', 'supervisor', 'validador', 'region', 'cartera'),
            'niveles' => NcMovil::NIVELES_NEGOCIACION,
            'etapas' => NcMovil::ETAPAS,
        ]);
    }

    public function update(UpdateNotaCargoMovilRequest $request, NcMovil $ncMovil): RedirectResponse
    {
        Gate::authorize('update', $ncMovil);

        $ncMovil->fill($request->validated())->save();

        return redirect()
            ->route('notas-cargo.movil.index')
            ->with('status', 'Nota de cargo actualizada.');
    }

    /**
     * One ejecutivo's own cases for a period. Rejected cases are left out: the
     * ejecutivo cannot act on them and legacy excluded them from every branch.
     *
     * @return Collection<int, NcMovil>
     */
    private function mias(int $idUsuario, int $ano, int $mes, ?string $estado): Collection
    {
        return NcMovil::query()
            ->with('cliente')
            ->where('id_usuario', $idUsuario)
            ->where('validacion', '<>', NcMovil::VALIDACION_RECHAZADO)
            ->enPeriodo($ano, $mes, $estado)
            ->orderByDesc('fecha_ingreso')
            ->get();
    }
}
