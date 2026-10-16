<?php

namespace App\Http\Controllers;

use App\Exports\ReporteNotaCargoMovil;
use App\Http\FiltroPeriodo;
use App\Http\Requests\NotaCargoMovil\UpdateBackMovilRequest;
use App\Models\Builders\NcMovilBuilder;
use App\Models\Cartera;
use App\Models\NcMovil;
use App\Models\Tienda;
use App\Models\Usuario;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

// Nota de cargo móvil, BACK: the back office moves validated cases through the
// lifecycle. `estado` is this stage's column and no other screen writes it.
class NotaCargoMovilBackController extends Controller
{
    public function index(Request $request): View
    {
        $filtro = FiltroPeriodo::desde($request, NcMovil::ESTADOS);

        return view('notas-cargo.movil.back.index', [
            'cargos' => $this->enSeguimiento($filtro)->orderByDesc('fecha_ingreso')->get(),
            'estados' => NcMovil::ESTADOS,
            'filtro' => $filtro,
        ]);
    }

    public function exportar(Request $request): StreamedResponse
    {
        return ReporteNotaCargoMovil::descargar(
            'notas-cargo-movil-back',
            $this->enSeguimiento(FiltroPeriodo::desde($request, NcMovil::ESTADOS)),
        );
    }

    public function edit(NcMovil $ncMovil): View
    {
        abort_unless($ncMovil->esValidado(), 403);

        return view('notas-cargo.movil.back.edit', [
            'cargo' => $ncMovil->load('cliente', 'ejecutivo', 'supervisor', 'validador', 'region', 'cartera'),
            'estados' => NcMovil::ESTADOS,
            'etapas' => NcMovil::ETAPAS,
            'regiones' => Tienda::query()->orderBy('nombre')->get(),
            'carteras' => Cartera::seleccionables()->get(),
        ]);
    }

    public function update(
        UpdateBackMovilRequest $request,
        NcMovil $ncMovil,
        #[CurrentUser]
        Usuario $agente,
    ): RedirectResponse {
        // The queue only lists validated cases, but the posted id is not trusted.
        abort_unless($ncMovil->esValidado(), 403);

        $ncMovil->fill($request->atributos(['estado', 'fecha_actualizacion', 'id_cartera']));
        $ncMovil->estado = $request->string('estado')->toString();
        $ncMovil->fecha_actualizacion = $request->string('fecha_actualizacion')->toString();
        $ncMovil->id_cartera = $request->integer('id_cartera');
        $ncMovil->id_back = $agente->id_usuario;
        $ncMovil->save();

        return redirect()
            ->route('notas-cargo.movil.back.index')
            ->with('status', 'Seguimiento actualizado.');
    }

    /**
     * Validated cases in the period, windowed per estado by NcMovil::enPeriodo.
     *
     * Returned unresolved so the screen and the export run the same filter. The
     * screen adds its own ordering; the export streams by primary key, for the
     * reason recorded on Reporte.
     *
     * @return NcMovilBuilder<NcMovil>
     */
    private function enSeguimiento(FiltroPeriodo $filtro): NcMovilBuilder
    {
        return NcMovil::query()
            ->with('cliente', 'ejecutivo', 'region')
            ->where('validacion', NcMovil::VALIDACION_PROCEDE)
            ->enPeriodo($filtro->ano, $filtro->mes, $filtro->estado);
    }
}
