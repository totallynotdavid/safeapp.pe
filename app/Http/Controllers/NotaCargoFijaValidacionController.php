<?php

namespace App\Http\Controllers;

use App\Http\Requests\NotaCargoFija\UpdateValidacionFijaRequest;
use App\Models\NcFija;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

// Nota de cargo fija, VALIDACIÓN: a validador passes or rejects the cases
// ejecutivos have filed. Nothing else on the case is theirs to change.
//
// The queue is global. Legacy shipped this screen twice, once filtered to the
// reviewer's own tienda and once not, and the filtered one is unused: everyone
// who holds it also holds the global one, which shows a superset of the same
// rows. Worse, the column it filtered on is the case's región rather than the
// reviewer's store, so a Lima validador saw none of the Piura cases. There is
// one screen now.
class NotaCargoFijaValidacionController extends Controller
{
    public function index(Request $request): View
    {
        $ano = (int) $request->integer('ano', now()->year);

        return view('notas-cargo.fija.validacion.index', [
            'cargos' => $this->pendientes($ano),
            'ano' => $ano,
            'anos' => range(now()->year, now()->year - 5),
        ]);
    }

    public function edit(NcFija $ncFija): View
    {
        abort_unless($ncFija->esEditable(), 403);

        return view('notas-cargo.fija.validacion.edit', [
            'cargo' => $ncFija->load('cliente', 'ejecutivo', 'supervisor', 'validador', 'region'),
            'validaciones' => [NcFija::VALIDACION_PROCEDE, NcFija::VALIDACION_RECHAZADO],
        ]);
    }

    public function update(UpdateValidacionFijaRequest $request, NcFija $ncFija): RedirectResponse
    {
        // A case already ruled on is not re-ruled. Legacy loaded and overwrote
        // whatever id the form posted, so a second validador could silently
        // replace the first one's decision.
        abort_unless($ncFija->esEditable(), 403);

        $data = $request->validated();
        $ncFija->validacion = $data['validacion'];
        $ncFija->comentario_validador = $data['comentario_validador'];
        $ncFija->id_validador = $request->user()->id_usuario;
        $ncFija->fecha_validacion = now()->toDateString();
        $ncFija->save();

        return redirect()
            ->route('notas-cargo.fija.validacion.index')
            ->with('status', 'Validación registrada.');
    }

    /**
     * Every case still awaiting a decision, filed in the given year. There is no
     * estado filter: an undecided case has not reached the back office, so its
     * estado is still the XINGRESAR it was registered with.
     *
     * @return Collection<int, NcFija>
     */
    private function pendientes(int $ano): Collection
    {
        return NcFija::query()
            ->with('cliente', 'ejecutivo')
            ->where('validacion', NcFija::VALIDACION_PENDIENTE)
            ->whereYear('fecha_ingreso', $ano)
            ->orderByDesc('fecha_ingreso')
            ->get();
    }
}
