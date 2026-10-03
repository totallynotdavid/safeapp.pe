<?php

namespace App\Http\Controllers;

use App\Http\Requests\NotaCargoMovil\UpdateValidacionMovilRequest;
use App\Models\NcMovil;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

// Nota de cargo móvil, VALIDACIÓN: a validador passes or rejects the cases
// ejecutivos have filed, and may correct the line count and the monthly charge
// the decision turns on. Nothing else on the case is theirs to change.
//
// One global queue. The per-tienda variant filtered on the case's región rather
// than the reviewer's store, so a Lima validador saw none of the Piura cases.
class NotaCargoMovilValidacionController extends Controller
{
    public function index(Request $request): View
    {
        $ano = (int) $request->integer('ano', now()->year);

        return view('notas-cargo.movil.validacion.index', [
            'cargos' => $this->pendientes($ano),
            'ano' => $ano,
            'anos' => range(now()->year, now()->year - 5),
        ]);
    }

    public function edit(NcMovil $ncMovil): View
    {
        abort_unless($ncMovil->esEditable(), 403);

        return view('notas-cargo.movil.validacion.edit', [
            'cargo' => $ncMovil->load('cliente', 'ejecutivo', 'supervisor', 'validador', 'region', 'cartera'),
            'validaciones' => [NcMovil::VALIDACION_PROCEDE, NcMovil::VALIDACION_RECHAZADO],
        ]);
    }

    public function update(UpdateValidacionMovilRequest $request, NcMovil $ncMovil): RedirectResponse
    {
        // A case already ruled on is not re-ruled. Legacy loaded and overwrote
        // whatever id the form posted, so a second validador could silently
        // replace the first one's decision.
        abort_unless($ncMovil->esEditable(), 403);

        $data = $request->validated();
        $ncMovil->fill(['q_lineas' => $data['q_lineas'], 'cargo_fijo' => $data['cargo_fijo']]);
        $ncMovil->validacion = $data['validacion'];
        $ncMovil->comentario_validador = $data['comentario_validador'];
        $ncMovil->id_validador = $request->user()->id_usuario;
        $ncMovil->fecha_validacion = now()->toDateString();
        $ncMovil->save();

        return redirect()
            ->route('notas-cargo.movil.validacion.index')
            ->with('status', 'Validación registrada.');
    }

    /**
     * Every case still awaiting a decision, filed in the given year. There is no
     * estado filter: an undecided case has not reached the back office, so its
     * estado is still the PENDIENTE it was registered with.
     *
     * @return Collection<int, NcMovil>
     */
    private function pendientes(int $ano): Collection
    {
        return NcMovil::query()
            ->with('cliente', 'ejecutivo')
            ->where('validacion', NcMovil::VALIDACION_PENDIENTE)
            ->whereYear('fecha_ingreso', $ano)
            ->orderByDesc('fecha_ingreso')
            ->get();
    }
}
