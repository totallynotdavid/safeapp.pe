<?php

namespace App\Http\Controllers;

use App\Cockpit\AccesoAClientes;
use App\Http\Requests\Quiebre\StoreQuiebreRegistroRequest;
use App\Http\Requests\Quiebre\UpdateQuiebreSeguimientoRequest;
use App\Models\Cliente;
use App\Models\Quiebre;
use App\Models\Tienda;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class QuiebreRegistroController extends Controller
{
    public function index(Request $request): View
    {
        $ano = (int) $request->integer('ano', now()->year);
        $estado = $request->query('estado');
        $filtroActivo = in_array($estado, Quiebre::ESTADOS, true);

        return view('quiebres.registro.index', [
            'quiebres' => $this->misQuiebres(
                $request->user()->id_usuario,
                $ano,
                $filtroActivo ? $estado : null,
            ),
            'estados' => Quiebre::ESTADOS,
            'estado' => $filtroActivo ? $estado : null,
            'ano' => $ano,
            'anos' => range(now()->year, now()->year - 5),
        ]);
    }

    public function create(Request $request): View
    {
        $ruc = $request->string('ruc')->trim()->toString();

        // The resolved client is the identity the quiebre is filed against.
        $cliente = $ruc === ''
            ? null
            : Cliente::visiblePara($request->user())
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

    public function store(StoreQuiebreRegistroRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $user = $request->user();

        $quiebre = new Quiebre($data);
        $quiebre->id_cliente = (int) $data['id_cliente'];
        $quiebre->fecha_inicio = $data['fecha_inicio_averia'] ?? null;

        // Workflow ownership and initial state are server-controlled.
        $quiebre->estado = 'PENDIENTE';
        $quiebre->id_usuario = $user->id_usuario;
        $quiebre->id_supervisor = $user->id_supervisor ?? 0;
        $quiebre->id_validador = 0;
        $quiebre->ticket_atencion = Quiebre::requiereTicket(
            $request->date('fecha_activacion'),
        )
            ? 'SI'
            : 'NO';
        $quiebre->fecha_registro = now()->toDateString();
        $quiebre->save();

        AccesoAClientes::otorgar((int) $quiebre->id_cliente, $user);

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

        $quiebre->fill($request->validated())->save();

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
