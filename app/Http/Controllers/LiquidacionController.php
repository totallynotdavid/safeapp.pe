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
// live in the still-legacy notacargo_movil screens; this screen reads the case
// and writes only the settlement fields. Legacy passed a tienda but never
// filtered on it, so this is a global queue with no per-row scoping.
class LiquidacionController extends Controller
{
    public function index(Request $request): View
    {
        $ano = (int) $request->integer('ano', now()->year);
        $estado = $request->query('estado');
        $filtroActivo = in_array($estado, NcMovil::ESTADOS_LIQUIDACION, true);

        return view('liquidaciones.index', [
            'cargos' => $this->porLiquidar($ano, $filtroActivo ? $estado : null),
            'estados' => NcMovil::ESTADOS_LIQUIDACION,
            'estado' => $filtroActivo ? $estado : null,
            'ano' => $ano,
            'anos' => range(now()->year, now()->year - 5),
        ]);
    }

    public function edit(NcMovil $ncMovil): View
    {
        return view('liquidaciones.edit', [
            'cargo' => $ncMovil->load('ejecutivo', 'validador', 'tienda'),
            'observaciones' => NcMovil::OBSERVACIONES,
        ]);
    }

    public function update(UpdateLiquidacionRequest $request, NcMovil $ncMovil): RedirectResponse
    {
        $ncMovil->fill($request->validated())->save();

        return redirect()->route('liquidaciones.index')->with('status', 'Liquidación registrada.');
    }

    /**
     * Validated cases updated in a year, most recent first. A specific estado
     * narrows the queue; without one, every validated case for the year shows.
     * Legacy keyed the year filter off fecha_actualizacion, which is what the
     * list displays, so this does the same.
     *
     * @return Collection<int, NcMovil>
     */
    private function porLiquidar(int $ano, ?string $estado): Collection
    {
        return NcMovil::query()
            ->with('ejecutivo')
            ->where('validacion', NcMovil::VALIDACION_PROCEDE)
            ->whereYear('fecha_actualizacion', $ano)
            ->when($estado !== null, static fn ($query) => $query->where('estado', $estado))
            ->orderByDesc('fecha_actualizacion')
            ->get();
    }
}
