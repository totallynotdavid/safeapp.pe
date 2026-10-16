<?php

namespace App\Http\Controllers;

use App\Exports\ReporteNotaCargoFija;
use App\Http\FiltroPeriodo;
use App\Http\Requests\NotaCargoFija\UpdateBackFijaRequest;
use App\Models\Builders\NcFijaBuilder;
use App\Models\NcFija;
use App\Models\Tienda;
use App\Models\Usuario;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

// Nota de cargo fija, BACK: the back office moves validated cases through the
// lifecycle. `estado` is this stage's column and no other screen writes it. Fija
// has no liquidación stage, so closing a case as COMPLETADO happens here too.
class NotaCargoFijaBackController extends Controller
{
    public function index(Request $request): View
    {
        $filtro = FiltroPeriodo::desde($request, NcFija::ESTADOS);

        return view('notas-cargo.fija.back.index', [
            'cargos' => $this->enSeguimiento($filtro)->orderByDesc('fecha_ingreso')->get(),
            'estados' => NcFija::ESTADOS,
            'filtro' => $filtro,
        ]);
    }

    public function exportar(Request $request): StreamedResponse
    {
        return ReporteNotaCargoFija::descargar(
            'notas-cargo-fija-back',
            $this->enSeguimiento(FiltroPeriodo::desde($request, NcFija::ESTADOS)),
        );
    }

    public function edit(NcFija $ncFija): View
    {
        abort_unless($ncFija->esValidado(), 403);

        return view('notas-cargo.fija.back.edit', [
            'cargo' => $ncFija->load('cliente', 'ejecutivo', 'supervisor', 'validador', 'region'),
            'estados' => NcFija::ESTADOS,
            'nodos' => NcFija::NODOS,
            'regiones' => Tienda::query()->orderBy('nombre')->get(),
        ]);
    }

    public function update(
        UpdateBackFijaRequest $request,
        NcFija $ncFija,
        #[CurrentUser]
        Usuario $agente,
    ): RedirectResponse {
        // The queue only lists validated cases, but the posted id is not trusted.
        abort_unless($ncFija->esValidado(), 403);

        $ncFija->fill($request->atributos(['estado', 'fecha_liquidacion']));
        $ncFija->estado = $request->string('estado')->toString();
        $ncFija->fecha_liquidacion = $request->date('fecha_liquidacion');
        // Stamped rather than posted: it is when the back office last acted,
        // which every queue windows a cancelled case by.
        $ncFija->fecha_actualizacion = now()->toDateString();
        $ncFija->id_back = $agente->id_usuario;
        $ncFija->save();

        return redirect()
            ->route('notas-cargo.fija.back.index')
            ->with('status', 'Seguimiento actualizado.');
    }

    /**
     * Validated cases in the period, windowed per estado by NcFijaBuilder.
     *
     * Returned unresolved so the screen and the export run the same filter. The
     * screen adds its own ordering; the export streams by primary key, for the
     * reason recorded on Reporte.
     *
     * @return NcFijaBuilder<NcFija>
     */
    private function enSeguimiento(FiltroPeriodo $filtro): NcFijaBuilder
    {
        return NcFija::query()
            ->with('cliente', 'ejecutivo', 'region')
            ->where('validacion', NcFija::VALIDACION_PROCEDE)
            ->enPeriodo($filtro->ano, $filtro->mes, $filtro->estado);
    }
}
