<?php

namespace App\Http\Controllers;

use App\Exports\ReporteContacto;
use App\Http\RangoFechas;
use App\Models\Llamada;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

// Agent contact log: the contacts the signed-in agent logged over a date range,
// read-only. Each row links to the client where the gestión form owns the write.
// SeguimientoController shows the supervisor view of the same log.
class HistoricoController extends Controller
{
    private const POR_PAGINA = 50;

    public function index(Request $request): View
    {
        $rango = RangoFechas::desde($request);
        $busqueda = $request->string('buscar')->trim()->limit(100)->toString();

        return view('historico.index', [
            'contactos' => $this
                ->mios($request->user()->id_usuario, $rango, $busqueda)
                ->orderByDesc('fecha')
                ->paginate(self::POR_PAGINA)
                ->withQueryString(),
            'rango' => $rango,
            'busqueda' => $busqueda,
        ]);
    }

    public function exportar(Request $request): StreamedResponse
    {
        return ReporteContacto::descargar(
            'mis-gestiones',
            $this->mios(
                $request->user()->id_usuario,
                RangoFechas::desde($request),
                $request->string('buscar')->trim()->limit(100)->toString(),
            ),
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
    private function mios(int $idUsuario, RangoFechas $rango, string $busqueda = ''): Builder
    {
        return Llamada::query()
            ->with('respuesta', 'cliente')
            ->where('id_usuario', $idUsuario)
            ->when($busqueda !== '', fn (Builder $query) => $this->buscar($query, $busqueda))
            ->whereBetween('fecha', $rango->limites());
    }

    /** @param Builder<Llamada> $query */
    private function buscar(Builder $query, string $busqueda): Builder
    {
        $escaped = addcslashes($busqueda, '%_\\');

        return $query->where(static function (Builder $contactos) use ($escaped): void {
            $contactos
                ->where('razon_social', 'like', "%{$escaped}%")
                ->orWhere('nombre_contacto', 'like', "%{$escaped}%")
                ->orWhere('telefono', 'like', "%{$escaped}%")
                ->orWhereHas('cliente', static fn (Builder $clientes) => $clientes->where(
                    'ruc',
                    'like',
                    "%{$escaped}%",
                ));
        });
    }
}
