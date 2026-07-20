<?php

namespace App\Http\Controllers;

use App\Models\Llamada;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

// HISTORICO: the contacts the signed-in agent logged over a date range. Read
// only. Legacy let an agent log a fresh contact from this list, which was a
// fourth copy of the insert; here each row links to the client, where the one
// gestión form does that write.
class HistoricoController extends Controller
{
    private const DIAS_POR_DEFECTO = 7;

    public function index(Request $request): View
    {
        [$desde, $hasta] = $this->rango($request);

        $contactos = Llamada::query()
            ->with('respuesta')
            ->where('id_usuario', $request->user()->id_usuario)
            ->whereBetween('fecha', [$desde->startOfDay(), $hasta->endOfDay()])
            ->orderByDesc('fecha')
            ->get();

        return view('historico.index', [
            'contactos' => $contactos,
            'desde' => $desde->toDateString(),
            'hasta' => $hasta->toDateString(),
        ]);
    }

    /**
     * The requested range, defaulting to the last week. An unparseable or
     * inverted range falls back rather than erroring: this is a report, and the
     * dates arrive from a query string anyone can edit.
     *
     * @return array{Carbon, Carbon}
     */
    private function rango(Request $request): array
    {
        $hoy = now();
        $desde = $this->fecha($request->query('desde')) ?? $hoy->copy()->subDays(self::DIAS_POR_DEFECTO);
        $hasta = $this->fecha($request->query('hasta')) ?? $hoy;

        return $desde->greaterThan($hasta) ? [$hasta, $desde] : [$desde, $hasta];
    }

    private function fecha(mixed $valor): ?Carbon
    {
        if (! is_string($valor) || $valor === '') {
            return null;
        }

        return Carbon::hasFormat($valor, 'Y-m-d') ? Carbon::parse($valor) : null;
    }
}
