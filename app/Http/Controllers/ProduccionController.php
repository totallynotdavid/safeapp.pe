<?php

namespace App\Http\Controllers;

use App\Http\FiltroPeriodo;
use App\Models\Builders\NotaCargoBuilder;
use App\Models\NcFija;
use App\Models\NcMovil;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

/**
 * What each ejecutivo has on the board this period, in money, per producto.
 *
 * Legacy called this DASHBOARD EJE REAL and drew four tables, three of which
 * could not work:
 *
 * - The fija table counted estados the fija lifecycle does not have. Its eight
 *   columns are the *avanzado* vocabulary (INSERTADO, EMITIDO, INSTALADO,
 *   LIQUIDADO, CAIDO...), of which only PENDIENTE and OBSERVADO exist in
 *   nc_fija. The 3,481 COMPLETADO and 1,299 CANCELADO cases had no column at
 *   all, so 5,588 of 5,970 rows, 93.6%, were invisible on their own dashboard.
 * - The avanzado table read nc_avanzado, which has never held a row.
 * - The summary table totalled fija and avanzado on estado='LIQUIDADO', which
 *   neither table has ever contained, so two of its three money columns were
 *   structurally zero and TOTAL only ever showed móvil.
 *
 * Only the móvil table worked. This is that table, given the fija counterpart
 * legacy never actually had, both driven by each producto's real estado list and
 * windowed by the same rule the queues use.
 */
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
     * One row per ejecutivo, each carrying their totals keyed by estado.
     *
     * Only validated cases count. An ejecutivo with nothing in the period is
     * absent rather than shown as a row of zeros, which is what legacy did too
     * and what keeps the table readable across 600 users.
     *
     * Rows are a list rather than a map keyed by name because two ejecutivos can
     * share a `personal`, and keying on it would silently merge their numbers.
     *
     * @return Collection<int, array{personal: string, totales: Collection<string, float>}>
     */
    private function porEjecutivo(NotaCargoBuilder $consulta, FiltroPeriodo $filtro): Collection
    {
        $totales = $consulta
            ->enPeriodo($filtro->ano, $filtro->mes, null)
            ->totalPorEstado()
            ->groupBy('id_usuario')
            ->map(static fn (Collection $delEjecutivo): Collection => $delEjecutivo
                ->mapWithKeys(static fn (object $fila): array => [$fila->estado => (float) $fila->total]));

        $nombres = Usuario::query()
            ->whereIn('id_usuario', $totales->keys())
            ->pluck('personal', 'id_usuario');

        return $totales
            ->map(static fn (Collection $porEstado, int $id): array => [
                'personal' => $nombres[$id] ?? '',
                'totales' => $porEstado,
            ])
            ->sortBy('personal')
            ->values();
    }
}
