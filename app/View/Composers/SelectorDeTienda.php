<?php

namespace App\View\Composers;

use App\Models\AsignacionTienda;
use App\Models\Usuario;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class SelectorDeTienda
{
    public function __construct(
        #[CurrentUser]
        private readonly ?Usuario $usuario,
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
        return $this->usuario
            ? $this->usuario->asignaciones()->with('tienda')->get()
            : new Collection;
    }
}
