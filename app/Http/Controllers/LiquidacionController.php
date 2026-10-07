<?php

namespace App\Http\Controllers;

use App\Http\Requests\Liquidacion\UpdateLiquidacionRequest;
use App\Models\NcMovil;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class LiquidacionController extends Controller
{
    public function index(Request $request): View
    {
        $ano = $request->integer('ano', now()->year);
        $estado = $request->string('estado')->toString();
        $estado = in_array($estado, NcMovil::ESTADOS_LIQUIDABLES, true) ? $estado : null;

        return view('liquidaciones.index', [
            'cargos' => $this->porLiquidar($ano, $estado),
            'estados' => NcMovil::ESTADOS_LIQUIDABLES,
            'estado' => $estado,
            'ano' => $ano,
            'anos' => range(now()->year, now()->year - 5),
        ]);
    }

    public function edit(NcMovil $ncMovil): View
    {
        return view('liquidaciones.edit', [
            'cargo' => $ncMovil->load(
                'cliente',
                'ejecutivo',
                'supervisor',
                'validador',
                'region',
                'cartera',
            ),
            'observaciones' => NcMovil::OBSERVACIONES,
        ]);
    }

    public function update(
        UpdateLiquidacionRequest $request,
        NcMovil $ncMovil,
    ): RedirectResponse {
        $ncMovil->fill($request->atributos())->save();

        return redirect()
            ->route('liquidaciones.index')
            ->with('status', 'Liquidación registrada.');
    }

    /**
     * Legacy filters by fecha_actualizacion because that is the date shown in
     * the liquidation list.
     *
     * A case must still be in a settleable state after passing validation;
     * PROCEDE cases can later become CAIDA.
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
            ->when(
                $estado !== null,
                static fn ($query) => $query->where('estado', $estado),
            )
            ->orderByDesc('fecha_actualizacion')
            ->get();
    }
}
