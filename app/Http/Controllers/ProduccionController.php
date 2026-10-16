<?php

namespace App\Http\Controllers;

use App\Http\FiltroPeriodo;
use App\Models\Builders\NotaCargoBuilder;
use App\Models\NcFija;
use App\Models\NcMovil;
use App\Models\Usuario;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/** Each table is driven by its producto's own estado list and windowed by the rule the queues use. */
class ProduccionController extends Controller
{
    public function index(Request $request): View
    {
        // No estado filter: the point of this screen is the split across all of
        // them, so the period always resolves to the full union.
        $filtro = FiltroPeriodo::desde($request, []);

        return view('produccion.index', [
            'filtro' => $filtro,
            'tablas' => [
                [
                    'titulo' => 'Fija',
                    'estados' => NcFija::ESTADOS,
                    'filas' => $this->porEjecutivo(
                        NcFija::query()->where('validacion', NcFija::VALIDACION_PROCEDE),
                        $filtro,
                    ),
                ],
                [
                    'titulo' => 'Móvil',
                    'estados' => NcMovil::ESTADOS,
                    'filas' => $this->porEjecutivo(
                        NcMovil::query()->where('validacion', NcMovil::VALIDACION_PROCEDE),
                        $filtro,
                    ),
                ],
            ],
        ]);
    }

    /**
     * An ejecutivo with nothing in the period is absent rather than a row of
     * zeros. Rows are a list rather than a map keyed by name because two
     * ejecutivos can share a `personal`, and keying on it would silently merge
     * their numbers.
     *
     * @return Collection<int, array{personal: string, totales: Collection<string, float>}>
     */
    private function porEjecutivo(NotaCargoBuilder $consulta, FiltroPeriodo $filtro): Collection
    {
        /** @var array<int, array<string, float>> $totales */
        $totales = [];
        foreach ($consulta->enPeriodo($filtro->ano, $filtro->mes, null)->totalPorEstado() as $fila) {
            $totales[$fila->id_usuario][$fila->estado] = $fila->total;
        }

        $nombres = [];
        foreach (Usuario::query()->whereIn('id_usuario', array_keys($totales))->get([
            'id_usuario',
            'personal',
        ]) as $ejecutivo) {
            $nombres[$ejecutivo->id_usuario] = $ejecutivo->personal;
        }

        $filas = [];
        foreach ($totales as $id => $porEstado) {
            $filas[] = ['personal' => $nombres[$id] ?? '', 'totales' => new Collection($porEstado)];
        }

        return new Collection($filas)
            ->sortBy('personal')
            ->values();
    }
}
