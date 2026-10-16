<?php

namespace App\Http\Controllers;

use App\Http\Requests\NotaCargoFija\UpdateValidacionFijaRequest;
use App\Models\NcFija;
use App\Models\Usuario;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

// Nota de cargo fija, VALIDACIÓN: a validador passes or rejects the cases
// ejecutivos have filed. Nothing else on the case is theirs to change.
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

    public function update(
        UpdateValidacionFijaRequest $request,
        NcFija $ncFija,
        #[CurrentUser]
        Usuario $agente,
    ): RedirectResponse {
        // A case already ruled on is not re-ruled, so a second validador cannot
        // silently replace the first one's decision.
        abort_unless($ncFija->esEditable(), 403);

        $ncFija->validacion = $request->string('validacion')->toString();
        $ncFija->comentario_validador = $request->string('comentario_validador')->toString();
        $ncFija->id_validador = $agente->id_usuario;
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
