<?php

namespace App\Http\Controllers;

use App\Exports\ReporteContacto;
use App\Http\RangoFechas;
use App\Models\Llamada;
use App\Models\Supervisor;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

// GESTION DIARIA: every contact one supervisor's team logged over a date range.
// The same call log histórico reads, widened from one agent to a whole team,
// which is why both screens share RangoFechas and ReporteContacto.
//
// Legacy called this "REPORTE GESTION SUPERVISORES" and it was export-only: the
// page rendered a #tablaDatatable div that no script ever filled, so a
// supervisor had to download a file to read a number. It lists on screen here
// and exports the same rows.
class SeguimientoController extends Controller
{
    public function index(Request $request): View
    {
        $rango = RangoFechas::desde($request);
        $supervisor = $this->supervisor($request);

        return view('seguimiento.index', [
            'contactos' => $supervisor === null
                ? new Collection
                : $this->delEquipo($supervisor, $rango)->orderByDesc('fecha')->get(),
            'supervisores' => Supervisor::query()->orderBy('nombre')->get(),
            'supervisor' => $supervisor,
            'rango' => $rango,
        ]);
    }

    public function exportar(Request $request): RedirectResponse|StreamedResponse
    {
        $supervisor = $this->supervisor($request);

        // Nothing chosen means nothing to export, and an empty workbook is a
        // worse answer than being sent back to pick a supervisor.
        if ($supervisor === null) {
            return redirect()
                ->route('seguimiento.index')
                ->with('status', 'Elija un supervisor para exportar.');
        }

        return ReporteContacto::descargar(
            'gestiones-equipo',
            $this->delEquipo($supervisor, RangoFechas::desde($request)),
        );
    }

    /**
     * The chosen supervisor, or null when the request names none or names one
     * that does not exist. This arrives from a query string, so an unknown id is
     * "no team selected" rather than an error page.
     */
    private function supervisor(Request $request): ?int
    {
        $id = $request->integer('supervisor');

        return $id > 0 && Supervisor::query()->whereKey($id)->exists() ? $id : null;
    }

    /**
     * Contacts logged in the range by everyone reporting to one supervisor.
     *
     * The team is resolved through usuario.id_supervisor, which is the user's
     * current supervisor rather than the one they reported to when the contact
     * was logged. That is what legacy did and what the screen means: this is a
     * supervisor asking what their team has done, not an audit of past reporting
     * lines.
     *
     * @return Builder<Llamada>
     */
    private function delEquipo(int $supervisor, RangoFechas $rango): Builder
    {
        return Llamada::query()
            ->with('respuesta', 'agente')
            ->whereIn(
                'id_usuario',
                Usuario::query()->select('id_usuario')->where('id_supervisor', $supervisor),
            )
            ->whereBetween('fecha', $rango->limites());
    }
}
