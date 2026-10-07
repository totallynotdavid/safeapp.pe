<?php

namespace App\Http\Controllers;

use App\Cockpit\AccesoAClientes;
use App\Http\Requests\Quiebre\StoreQuiebreRegistroRequest;
use App\Http\Requests\Quiebre\UpdateQuiebreSeguimientoRequest;
use App\Models\Cliente;
use App\Models\Quiebre;
use App\Models\Tienda;
use App\Models\Usuario;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;

class QuiebreRegistroController extends Controller
{
    public function index(Request $request, #[CurrentUser] Usuario $agente): View
    {
        $ano = $request->integer('ano', now()->year);
        $estado = $request->string('estado')->toString();
        $estado = in_array($estado, Quiebre::ESTADOS, true) ? $estado : null;

        return view('quiebres.registro.index', [
            'quiebres' => $this->misQuiebres($agente->id_usuario, $ano, $estado),
            'estados' => Quiebre::ESTADOS,
            'estado' => $estado,
            'ano' => $ano,
            'anos' => range(now()->year, now()->year - 5),
        ]);
    }

    public function create(Request $request, #[CurrentUser] Usuario $agente): View
    {
        $ruc = $request->string('ruc')->trim()->toString();

        // The resolved client is the identity the quiebre is filed against.
        $cliente = $ruc === ''
            ? null
            : Cliente::visiblePara($agente)
                ->where('ruc', $ruc)
                ->first();

        return view('quiebres.registro.create', [
            'ruc' => $ruc === '' ? null : $ruc,
            'cliente' => $cliente,
            'tiendas' => Tienda::query()->orderBy('nombre')->get(),
            'servicios' => Quiebre::SERVICIOS,
            'tiposAveria' => Quiebre::TIPOS_AVERIA,
            'detalles' => Quiebre::DETALLES_EQUIPO,
        ]);
    }

    public function store(StoreQuiebreRegistroRequest $request, #[CurrentUser] Usuario $agente): RedirectResponse
    {
        $quiebre = new Quiebre($request->atributos());
        $quiebre->id_cliente = $request->integer('id_cliente');
        $quiebre->fecha_inicio = $request->date('fecha_inicio_averia');

        // Workflow ownership and initial state are server-controlled.
        $quiebre->estado = 'PENDIENTE';
        $quiebre->id_usuario = $agente->id_usuario;
        $quiebre->id_supervisor = $agente->id_supervisor;
        $quiebre->id_validador = 0;
        $quiebre->ticket_atencion = Quiebre::requiereTicket(
            $request->date('fecha_activacion'),
        )
            ? 'SI'
            : 'NO';
        $quiebre->fecha_registro = now()->toDateString();
        $quiebre->save();

        AccesoAClientes::otorgar((int) $quiebre->id_cliente, $agente);

        return redirect()
            ->route('quiebres.registro.index')
            ->with('status', 'Quiebre registrado.');
    }

    public function edit(Quiebre $quiebre): View
    {
        Gate::authorize('view', $quiebre);

        return view('quiebres.registro.edit', [
            'quiebre' => $quiebre->load('cliente', 'tienda', 'validador'),
            'tiposAveria' => Quiebre::TIPOS_AVERIA,
        ]);
    }

    public function update(
        UpdateQuiebreSeguimientoRequest $request,
        Quiebre $quiebre,
    ): RedirectResponse {
        Gate::authorize('update', $quiebre);

        $quiebre->fill($request->atributos())->save();

        return redirect()
            ->route('quiebres.registro.index')
            ->with('status', 'Quiebre actualizado.');
    }

    /**
     * @return Collection<int, Quiebre>
     */
    private function misQuiebres(
        int $idUsuario,
        int $ano,
        ?string $estado,
    ): Collection {
        return Quiebre::query()
            ->with('cliente')
            ->where('id_usuario', $idUsuario)
            ->whereYear('fecha_registro', $ano)
            ->when(
                $estado !== null,
                static fn ($query) => $query->where('estado', $estado),
            )
            ->orderByDesc('fecha_registro')
            ->get();
    }
}
