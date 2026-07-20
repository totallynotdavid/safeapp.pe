<?php

namespace Tests\Feature\Asignacion;

use App\Models\Tienda;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// The self-service half of assignment: an operator working two stores moves
// between them without an admin.
class TiendaActivaTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_operator_switches_to_another_tienda_they_are_assigned(): void
    {
        [$operador, $lima, $arequipa] = $this->conDosTiendas();

        $this
            ->actingAs($operador)
            ->put(route('tienda-activa.update'), ['id_tienda' => $arequipa->id_tienda])
            ->assertRedirect(route('dashboard'));

        $this->assertSame($arequipa->id_tienda, $operador->fresh()->tiendaAsignada());
    }

    public function test_switching_leaves_exactly_one_active_assignment(): void
    {
        [$operador, $lima, $arequipa] = $this->conDosTiendas();

        $this->actingAs($operador)->put(route('tienda-activa.update'), ['id_tienda' => $arequipa->id_tienda]);

        $this->assertSame(1, $operador->asignaciones()->where('estado', 0)->count());
    }

    public function test_a_tienda_they_were_never_assigned_is_refused(): void
    {
        // Legacy ran two blind UPDATEs with no membership check, so posting any
        // id_tienda deactivated the real assignment and activated nothing,
        // leaving the operator with an empty queue and no way back.
        [$operador, $lima] = $this->conDosTiendas();
        $ajena = Tienda::factory()->create();

        $this
            ->actingAs($operador)
            ->put(route('tienda-activa.update'), ['id_tienda' => $ajena->id_tienda])
            ->assertSessionHas('error');

        $this->assertSame($lima->id_tienda, $operador->fresh()->tiendaAsignada());
    }

    public function test_the_switch_is_hidden_from_an_operator_with_one_tienda(): void
    {
        $operador = Usuario::factory()->create();
        $operador->sincronizarAsignaciones([Tienda::factory()->create()->id_tienda], null);

        $this->actingAs($operador)->get(route('dashboard'))->assertDontSee('tienda-activa');
    }

    /**
     * @return array{Usuario, Tienda, Tienda}
     */
    private function conDosTiendas(): array
    {
        $operador = Usuario::factory()->create();
        $lima = Tienda::factory()->create();
        $arequipa = Tienda::factory()->create();

        $operador->sincronizarAsignaciones([$lima->id_tienda, $arequipa->id_tienda], $lima->id_tienda);

        return [$operador, $lima, $arequipa];
    }
}
