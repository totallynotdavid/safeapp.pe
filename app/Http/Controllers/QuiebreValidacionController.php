<?php

namespace App\Http\Controllers;

use App\Http\Requests\Quiebre\UpdateQuiebreValidacionRequest;
use App\Models\Quiebre;
use App\Models\Usuario;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;

// Averías → Seguimiento: a validator reviews quiebres registered against their
// assigned tienda and records a decision. Registration lives in
// QuiebreRegistroController; this screen only reads and validates.
class QuiebreValidacionController extends Controller
{
    public function index(Request $request, #[CurrentUser] Usuario $agente): View
    {
        $tienda = $agente->tiendaAsignada();
        $ano = $request->integer('ano', now()->year);
        $estado = $request->string('estado')->toString();
        $estado = in_array($estado, Quiebre::ESTADOS, true) ? $estado : null;

        return view('quiebres.validacion.index', [
            'quiebres' => $tienda === null
                ? new Collection
                : $this->pendientes($tienda, $ano, $estado),
            'sinAsignacion' => $tienda === null,
            'estados' => Quiebre::ESTADOS,
            'estado' => $estado,
            'ano' => $ano,
            'anos' => range(now()->year, now()->year - 5),
        ]);
    }

    public function edit(Quiebre $quiebre): View
    {
        Gate::authorize('validar', $quiebre);

        return view('quiebres.validacion.edit', [
            'quiebre' => $quiebre->load('cliente', 'ejecutivo', 'tienda'),
            'estados' => Quiebre::ESTADOS,
        ]);
    }

    public function update(
        UpdateQuiebreValidacionRequest $request,
        Quiebre $quiebre,
        #[CurrentUser]
        Usuario $agente,
    ): RedirectResponse {
        Gate::authorize('validar', $quiebre);

        $quiebre->estado = $request->string('estado')->toString();
        $quiebre->caso_sf = $request->string('caso_sf')->toString();
        $quiebre->comentario_validador = $request->filled('comentario_validador')
            ? $request->string('comentario_validador')->toString()
            : null;
        // Validator identity and date are server-assigned from session and clock.
        $quiebre->id_validador = $agente->id_usuario;
        $quiebre->fecha_validacion = now()->toDateString();
        $quiebre->save();

        return redirect()->route('quiebres.validacion.index')->with('status', 'Validación registrada.');
    }

    /**
     * Open quiebres in a tienda for a year. A specific estado narrows the queue;
     * without one, every open state shows. The window is fecha_registro, the
     * date the list displays.
     *
     * @return Collection<int, Quiebre>
     */
    private function pendientes(int $tienda, int $ano, ?string $estado): Collection
    {
        return Quiebre::query()
            ->with('cliente')
            ->where('zonal_telefonica', $tienda)
            ->whereYear('fecha_registro', $ano)
            ->when(
                $estado !== null,
                static fn ($query) => $query->where('estado', $estado),
                static fn ($query) => $query->whereIn('estado', Quiebre::ESTADOS),
            )
            ->orderByDesc('fecha_registro')
            ->get();
    }
}
