<?php

namespace App\Http\Controllers;

use App\Http\Requests\Quiebre\UpdateQuiebreValidacionRequest;
use App\Models\Quiebre;
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
    public function index(Request $request): View
    {
        $tienda = $request->user()->tiendaAsignada();
        $ano = (int) $request->integer('ano', now()->year);
        $estado = $request->query('estado');
        $filtroActivo = in_array($estado, Quiebre::ESTADOS, true);

        return view('quiebres.validacion.index', [
            'quiebres' => $tienda === null
                ? new Collection
                : $this->pendientes($tienda, $ano, $filtroActivo ? $estado : null),
            'sinAsignacion' => $tienda === null,
            'estados' => Quiebre::ESTADOS,
            'estado' => $filtroActivo ? $estado : null,
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

    public function update(UpdateQuiebreValidacionRequest $request, Quiebre $quiebre): RedirectResponse
    {
        Gate::authorize('validar', $quiebre);

        $data = $request->validated();
        $quiebre->estado = $data['estado'];
        $quiebre->caso_sf = $data['caso_sf'];
        $quiebre->comentario_validador = $data['comentario_validador'];
        // Validator identity and date are server-assigned from session and clock.
        $quiebre->id_validador = $request->user()->id_usuario;
        $quiebre->fecha_validacion = now()->toDateString();
        $quiebre->save();

        return redirect()->route('quiebres.validacion.index')->with('status', 'Validación registrada.');
    }

    /**
     * Open quiebres in a tienda for a year. A specific estado narrows the queue;
     * without one, every open state shows. Legacy filtered the default view on
     * fecha_registro but the per-estado view on fecha_inicio; this uses
     * fecha_registro throughout, matching the date the list actually displays.
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
