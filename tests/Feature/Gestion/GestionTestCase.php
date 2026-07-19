<?php

namespace Tests\Feature\Gestion;

use App\Models\Cliente;
use App\Models\Menu;
use App\Models\Tienda;
use App\Models\Usuario;
use Database\Seeders\RespuestaSeeder;
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
     * @param  array<string, mixed>  $atributos
     */
    protected function clienteDe(Usuario $agente, string $tipoData = 'MOVIL', array $atributos = []): Cliente
    {
        return Cliente::factory()
            ->tipoData($tipoData)
            ->asignadoA($agente->id_usuario, $this->tienda->id_tienda)
            ->create($atributos);
    }
}
