<?php

namespace App\Http\Controllers;

use App\Http\Requests\NotaCargoMovil\UpdateBackMovilRequest;
use App\Models\Cartera;
use App\Models\NcMovil;
use App\Models\Tienda;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

// Nota de cargo móvil, BACK: the back office moves validated cases through the
// lifecycle and records what fulfilment produced. `estado` is this stage's
// column and no other screen writes it.
//
// Global queue, for the same reason as validación: the per-tienda copy of this
// screen filtered the case's región against the reviewer's own store, and every
// user who holds it also holds the unfiltered one.
class NotaCargoMovilBackController extends Controller
{
    public function index(Request $request): View
    {
        $ano = (int) $request->integer('ano', now()->year);
        $mes = (int) $request->integer('mes', now()->month);
        $estado = $request->query('estado');
        $filtroActivo = in_array($estado, NcMovil::ESTADOS, true);

        return view('notas-cargo.movil.back.index', [
            'cargos' => $this->enSeguimiento($ano, $mes, $filtroActivo ? $estado : null),
            'estados' => NcMovil::ESTADOS,
            'estado' => $filtroActivo ? $estado : null,
            'ano' => $ano,
            'mes' => $mes,
            'anos' => range(now()->year, now()->year - 5),
        ]);
    }

    public function edit(NcMovil $ncMovil): View
    {
        abort_unless($ncMovil->esValidado(), 403);

        return view('notas-cargo.movil.back.edit', [
            'cargo' => $ncMovil->load('cliente', 'ejecutivo', 'supervisor', 'validador', 'region', 'cartera'),
            'estados' => NcMovil::ESTADOS,
            'etapas' => NcMovil::ETAPAS,
            'regiones' => Tienda::query()->orderBy('nombre')->get(),
            'carteras' => Cartera::query()->seleccionables()->get(),
        ]);
    }

    public function update(UpdateBackMovilRequest $request, NcMovil $ncMovil): RedirectResponse
    {
        // Only a validated case has anything to fulfil, and the queue only lists
        // those. Legacy trusted the posted id, so a rejected case could be moved
        // through the lifecycle anyway.
        abort_unless($ncMovil->esValidado(), 403);

        $data = $request->validated();
        $ncMovil->fill($request->safe()->except(['estado', 'fecha_actualizacion', 'id_cartera']));
        $ncMovil->estado = $data['estado'];
        $ncMovil->fecha_actualizacion = $data['fecha_actualizacion'];
        $ncMovil->id_cartera = (int) ($data['id_cartera'] ?? 0);
        $ncMovil->id_back = $request->user()->id_usuario;
        $ncMovil->save();

        return redirect()
            ->route('notas-cargo.movil.back.index')
            ->with('status', 'Seguimiento actualizado.');
    }

    /**
     * Validated cases in the period, windowed per estado by NcMovil::enPeriodo.
     *
     * @return Collection<int, NcMovil>
     */
    private function enSeguimiento(int $ano, int $mes, ?string $estado): Collection
    {
        return NcMovil::query()
            ->with('cliente', 'ejecutivo', 'region')
            ->where('validacion', NcMovil::VALIDACION_PROCEDE)
            ->enPeriodo($ano, $mes, $estado)
            ->orderByDesc('fecha_ingreso')
            ->get();
    }
}
