<?php

namespace App\Http\Controllers;

use App\Http\Requests\Liquidacion\UpdateLiquidacionRequest;
use App\Models\NcMovil;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

// Liquidación: a liquidador settles validated notas de cargo móvil, recording
// the commission, discount, and settlement outcome. Registration and validation
// live in the notas de cargo móvil screens; this screen reads the case and
// writes only the settlement fields. Global queue with no per-row scoping.
class LiquidacionController extends Controller
{
    public function index(Request $request): View
    {
        $ano = (int) $request->integer('ano', now()->year);
        $estado = $request->query('estado');
        $filtroActivo = in_array($estado, NcMovil::ESTADOS_LIQUIDABLES, true);

        return view('liquidaciones.index', [
            'cargos' => $this->porLiquidar($ano, $filtroActivo ? $estado : null),
            'estados' => NcMovil::ESTADOS_LIQUIDABLES,
            'estado' => $filtroActivo ? $estado : null,
            'ano' => $ano,
            'anos' => range(now()->year, now()->year - 5),
        ]);
    }

    public function edit(NcMovil $ncMovil): View
    {
        return view('liquidaciones.edit', [
            'cargo' => $ncMovil->load('cliente', 'ejecutivo', 'supervisor', 'validador', 'region', 'cartera'),
            'observaciones' => NcMovil::OBSERVACIONES,
        ]);
    }

    public function update(UpdateLiquidacionRequest $request, NcMovil $ncMovil): RedirectResponse
    {
        $ncMovil->fill($request->validated())->save();

        return redirect()->route('liquidaciones.index')->with('status', 'Liquidación registrada.');
    }

    /**
     * Settleable cases updated in a year, most recent first. A specific estado
     * narrows the queue; without one, every settleable case for the year shows.
     * Legacy keyed the year filter off fecha_actualizacion, which is what the
     * list displays, so this does the same.
     *
     * Both gates are needed. Passing validación is what admits a case, but a
     * PROCEDE case that later fell (CAIDA) is still not settleable, and those
     * are a quarter of production. Filtering on validación alone would put them
     * in front of the liquidador.
     *
     * @return Collection<int, NcMovil>
     */
    private function porLiquidar(int $ano, ?string $estado): Collection
    {
        return NcMovil::query()
            ->with('cliente', 'ejecutivo')
            ->where('validacion', NcMovil::VALIDACION_PROCEDE)
            ->whereIn('estado', NcMovil::ESTADOS_LIQUIDABLES)
            ->whereYear('fecha_actualizacion', $ano)
            ->when($estado !== null, static fn ($query) => $query->where('estado', $estado))
            ->orderByDesc('fecha_actualizacion')
            ->get();
    }
}
