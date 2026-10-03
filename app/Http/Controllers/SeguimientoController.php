<?php

namespace App\Http\Controllers;

use App\Exports\ReporteContacto;
use App\Http\RangoFechas;
use App\Models\Llamada;
use App\Models\Supervisor;
use App\Models\Usuario;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

    private function supervisor(Request $request): ?int
    {
        $id = $request->integer('supervisor');

        return $id > 0 && Supervisor::query()->whereKey($id)->exists()
            ? $id
            : null;
    }

    /**
     * Uses each user's current supervisor, not their supervisor when the
     * contact was logged.
     *
     * @return Builder<Llamada>
     */
    private function delEquipo(
        int $supervisor,
        RangoFechas $rango,
        string $busqueda = '',
    ): Builder {
        return Llamada::query()
            ->with('respuesta', 'agente', 'cliente')
            ->whereIn(
                'id_usuario',
                Usuario::query()
                    ->select('id_usuario')
                    ->where('id_supervisor', $supervisor),
            )
            ->when(
                $busqueda !== '',
                fn (Builder $query) => $this->buscar($query, $busqueda),
            )
            ->whereBetween('fecha', $rango->limites());
    }

    /** @param Builder<Llamada> $query */
    private function buscar(Builder $query, string $busqueda): Builder
    {
        $escaped = addcslashes($busqueda, '%_\\');

        return $query->where(
            static function (Builder $contactos) use ($escaped): void {
                $contactos
                    ->where('razon_social', 'like', "%{$escaped}%")
                    ->orWhere('nombre_contacto', 'like', "%{$escaped}%")
                    ->orWhere('telefono', 'like', "%{$escaped}%")
                    ->orWhereHas(
                        'cliente',
                        static fn (Builder $clientes) => $clientes->where(
                            'ruc',
                            'like',
                            "%{$escaped}%",
                        ),
                    );
            },
        );
    }
}
