<?php

namespace App\Http\Controllers;

use App\Exports\ReporteContacto;
use App\Http\RangoFechas;
use App\Models\Llamada;
use App\Models\Usuario;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class HistoricoController extends Controller
{
    private const POR_PAGINA = 50;

    public function index(Request $request, #[CurrentUser] Usuario $agente): View
    {
        $rango = RangoFechas::desde($request);
        $busqueda = $request->string('buscar')->trim()->limit(100)->toString();

        return view('historico.index', [
            'contactos' => $this
                ->mios($agente->id_usuario, $rango, $busqueda)
                ->orderByDesc('fecha')
                ->paginate(self::POR_PAGINA)
                ->withQueryString(),
            'rango' => $rango,
            'busqueda' => $busqueda,
        ]);
    }

    public function exportar(Request $request, #[CurrentUser] Usuario $agente): StreamedResponse
    {
        return ReporteContacto::descargar(
            'mis-gestiones',
            $this->mios(
                $agente->id_usuario,
                RangoFechas::desde($request),
                $request->string('buscar')->trim()->limit(100)->toString(),
            ),
        );
    }

    /** @return Builder<Llamada> */
    private function mios(
        int $idUsuario,
        RangoFechas $rango,
        string $busqueda = '',
    ): Builder {
        $contactos = Llamada::query()
            ->with('respuesta', 'cliente')
            ->where('id_usuario', $idUsuario)
            ->whereBetween('fecha', $rango->limites());

        return $busqueda === '' ? $contactos : $this->buscar($contactos, $busqueda);
    }

    /**
     * @param  Builder<Llamada>  $query
     * @return Builder<Llamada>
     */
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
