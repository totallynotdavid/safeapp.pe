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
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

// Supervisor team contact log: every contact the team logged over a date range,
// on screen and as export. Shares filters and export shape with histórico.
// Both screens use RangoFechas and ReporteContacto for consistency.
class SeguimientoController extends Controller
{
    private const POR_PAGINA = 50;

    public function index(Request $request): View
    {
        $rango = RangoFechas::desde($request);
        $supervisor = $this->supervisor($request);
        $busqueda = $request->string('buscar')->trim()->limit(100)->toString();

        return view('seguimiento.index', [
            'contactos' => $supervisor === null
                ? new LengthAwarePaginator([], 0, self::POR_PAGINA, 1, [
                    'path' => $request->url(),
                    'query' => $request->query(),
                ])
                : $this
                    ->delEquipo($supervisor, $rango, $busqueda)
                    ->orderByDesc('fecha')
                    ->paginate(self::POR_PAGINA)
                    ->withQueryString(),
            'supervisores' => Supervisor::query()->orderBy('nombre')->get(),
            'supervisor' => $supervisor,
            'rango' => $rango,
            'busqueda' => $busqueda,
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
            $this->delEquipo(
                $supervisor,
                RangoFechas::desde($request),
                $request->string('buscar')->trim()->limit(100)->toString(),
            ),
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
    private function delEquipo(int $supervisor, RangoFechas $rango, string $busqueda = ''): Builder
    {
        return Llamada::query()
            ->with('respuesta', 'agente', 'cliente')
            ->whereIn(
                'id_usuario',
                Usuario::query()->select('id_usuario')->where('id_supervisor', $supervisor),
            )
            ->when($busqueda !== '', fn (Builder $query) => $this->buscar($query, $busqueda))
            ->whereBetween('fecha', $rango->limites());
    }

    /** @param Builder<Llamada> $query */
    private function buscar(Builder $query, string $busqueda): Builder
    {
        $escaped = addcslashes($busqueda, '%_\\');

        return $query->where(function (Builder $contactos) use ($escaped): void {
            $contactos
                ->where('razon_social', 'like', "%{$escaped}%")
                ->orWhere('nombre_contacto', 'like', "%{$escaped}%")
                ->orWhere('telefono', 'like', "%{$escaped}%")
                ->orWhereHas('cliente', fn (Builder $clientes) => $clientes->where('ruc', 'like', "%{$escaped}%"));
        });
    }
}
