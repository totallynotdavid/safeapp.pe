<?php

namespace App\Http\Controllers;

use App\Exports\ReporteContacto;
use App\Http\RangoFechas;
use App\Models\Llamada;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

// HISTORICO: the contacts the signed-in agent logged over a date range. Read
// only. Legacy let an agent log a fresh contact from this list, which was a
// fourth copy of the insert; here each row links to the client, where the one
// gestión form does that write.
//
// The team-wide view of the same log is SeguimientoController, kept a separate
// screen because it answers a supervisor's question rather than an agent's and
// carries its own permiso.
class HistoricoController extends Controller
{
    public function index(Request $request): View
    {
        $rango = RangoFechas::desde($request);

        return view('historico.index', [
            'contactos' => $this->mios($request->user()->id_usuario, $rango)->orderByDesc('fecha')->get(),
            'rango' => $rango,
        ]);
    }

    public function exportar(Request $request): StreamedResponse
    {
        return ReporteContacto::descargar(
            'mis-gestiones',
            $this->mios($request->user()->id_usuario, RangoFechas::desde($request)),
        );
    }

    /**
     * One agent's own contacts in the range.
     *
     * Returned unresolved so the screen and the export run the same filter,
     * including the scoping to the signed-in agent.
     *
     * @return Builder<Llamada>
     */
    private function mios(int $idUsuario, RangoFechas $rango): Builder
    {
        return Llamada::query()
            ->with('respuesta', 'cliente')
            ->where('id_usuario', $idUsuario)
            ->whereBetween('fecha', $rango->limites());
    }
}
