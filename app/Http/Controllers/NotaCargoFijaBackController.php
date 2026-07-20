<?php

namespace App\Http\Controllers;

use App\Http\Requests\NotaCargoFija\UpdateBackFijaRequest;
use App\Models\NcFija;
use App\Models\Tienda;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

// Nota de cargo fija, BACK: the back office moves validated cases through the
// lifecycle and records what fulfilment produced. `estado` is this stage's
// column and no other screen writes it. Fija has no liquidación stage, so
// closing a case as COMPLETADO with the month it settled happens here too.
//
// Global queue, for the same reason as validación: the per-tienda copy of this
// screen filtered the case's región against the reviewer's own store, and every
// user who holds it also holds the unfiltered one.
class NotaCargoFijaBackController extends Controller
{
    public function index(Request $request): View
    {
        $ano = (int) $request->integer('ano', now()->year);
        $mes = (int) $request->integer('mes', now()->month);
        $estado = $request->query('estado');
        $filtroActivo = in_array($estado, NcFija::ESTADOS, true);

        return view('notas-cargo.fija.back.index', [
            'cargos' => $this->enSeguimiento($ano, $mes, $filtroActivo ? $estado : null),
            'estados' => NcFija::ESTADOS,
            'estado' => $filtroActivo ? $estado : null,
            'ano' => $ano,
            'mes' => $mes,
            'anos' => range(now()->year, now()->year - 5),
        ]);
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

    public function update(UpdateBackFijaRequest $request, NcFija $ncFija): RedirectResponse
    {
        // Only a validated case has anything to fulfil, and the queue only lists
        // those. Legacy trusted the posted id, so a rejected case could be moved
        // through the lifecycle anyway.
        abort_unless($ncFija->esValidado(), 403);

        $data = $request->validated();
        $ncFija->fill($request->safe()->except(['estado', 'fecha_liquidacion']));
        $ncFija->estado = $data['estado'];
        $ncFija->fecha_liquidacion = $data['fecha_liquidacion'] ?? null;
        // Stamped rather than posted, which makes it exactly "when the back
        // office last acted" and is what every queue windows a cancelled case
        // by. Legacy stamped it server-side here too.
        $ncFija->fecha_actualizacion = now()->toDateString();
        $ncFija->id_back = $request->user()->id_usuario;
        $ncFija->save();

        return redirect()
            ->route('notas-cargo.fija.back.index')
            ->with('status', 'Seguimiento actualizado.');
    }

    /**
     * Validated cases in the period, windowed per estado by NcFijaBuilder.
     *
     * @return Collection<int, NcFija>
     */
    private function enSeguimiento(int $ano, int $mes, ?string $estado): Collection
    {
        return NcFija::query()
            ->with('cliente', 'ejecutivo', 'region')
            ->where('validacion', NcFija::VALIDACION_PROCEDE)
            ->enPeriodo($ano, $mes, $estado)
            ->orderByDesc('fecha_ingreso')
            ->get();
    }
}
