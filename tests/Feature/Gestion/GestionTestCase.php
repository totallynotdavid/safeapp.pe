<?php

namespace Tests\Feature\Gestion;

use App\Models\Dato;
use App\Models\Menu;
use App\Models\Tienda;
use App\Models\Usuario;
use Database\Seeders\RespuestaSeeder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// Shared fixture for the cockpit tests: a seeded disposition catalog and agents
// placed on one tienda, since every gestión path is scoped by the agent's active
// tienda.
abstract class GestionTestCase extends TestCase
{
    use RefreshDatabase;

    protected Tienda $tienda;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RespuestaSeeder::class);
        $this->tienda = Tienda::factory()->create();
    }

    /**
     * An agent holding the given permisos, placed on the shared tienda through
     * the same write path the asignación screen uses.
     */
    protected function agente(string ...$permisos): Usuario
    {
        $agente = Usuario::factory()->create();

        foreach ($permisos as $permiso) {
            $agente->menus()->attach(Menu::factory()->create(['permiso' => $permiso]));
        }

        $agente->sincronizarAsignaciones([$this->tienda->id_tienda], $this->tienda->id_tienda);

        return $agente;
    }

    /**
     * A campaign record held by the given agent, which is the only state in
     * which one is workable.
     *
     * @param  array<string, mixed>  $atributos
     */
    protected function datoDe(Usuario $agente, string $tipoData = 'MOVIL', array $atributos = []): Dato
    {
        return Dato::factory()
            ->tipoData($tipoData)
            ->asignadoA($agente->id_usuario, $this->tienda->id_tienda)
            ->create($atributos);
    }

    /**
     * The records the agent currently holds. Ownership is an open hold, never a
     * column on the record, so every test that asks "what does this agent have"
     * asks it the one way.
     *
     * @return Collection<int, Dato>
     */
    protected function enPoderDe(Usuario $agente): Collection
    {
        return Dato::query()
            ->whereHas(
                'asignacionActiva',
                static fn (Builder $q): Builder => $q->where('id_usuario', $agente->id_usuario),
            )
            ->with('asignacionActiva')
            ->get();
    }
}
