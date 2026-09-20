<?php

namespace App\View\Composers;

use App\Models\AsignacionTienda;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class SelectorDeTienda
{
    public function __construct(
        private readonly Request $request,
    ) {}

    public function compose(View $view): void
    {
        $asignaciones = $this->asignaciones();

        $view->with([
            'tiendasDisponibles' => $asignaciones
                ->map(static fn (AsignacionTienda $asignacion): array => [
                    'value' => (string) $asignacion->id_tienda,
                    // A tienda row can go missing without taking the assignment
                    // with it, and an operator still has to be able to leave it.
                    'label' => $asignacion->tienda->nombre ?? 'Tienda '.$asignacion->id_tienda,
                ])
                ->all(),
            'tiendaActiva' => (string) ($asignaciones->firstWhere('estado', 0)->id_tienda ?? ''),
        ]);
    }

    /** @return Collection<int, AsignacionTienda> */
    private function asignaciones(): Collection
    {
        $usuario = $this->request->user();

        return $usuario
            ? $usuario->asignaciones()->with('tienda')->get()
            : collect();
    }
}
