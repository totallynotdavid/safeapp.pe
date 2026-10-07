<?php

namespace Tests\Feature\Gestion;

use App\Cockpit\AccesoAClientes;
use App\Models\Dato;
use App\Models\Menu;
use App\Models\Tienda;
use App\Models\Usuario;
use Database\Seeders\RespuestaSeeder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

// Every gestión path is scoped by the agent's active tienda, so agents are placed
// on one.
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
        $agente = Usuario::factory()->createOne();

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
        $dato = Dato::factory()
            ->tipoData($tipoData)
            ->asignadoA($agente->id_usuario, $this->tienda->id_tienda)
            ->createOne($atributos);

        AccesoAClientes::otorgar($dato->id_cliente, $agente);

        return $dato;
    }

    /**
     * The page as text, tags stripped and whitespace collapsed.
     *
     * Lets a test assert on the label and the value a user reads together
     * ("Líneas ENTEL 5") without pinning the markup that puts them side by side,
     * which a bare assertSee on the number alone cannot do.
     */
    protected function textoVisible(TestResponse $respuesta): string
    {
        return trim((string) preg_replace('/\s+/u', ' ', strip_tags((string) $respuesta->getContent())));
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
