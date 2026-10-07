<?php

namespace Tests\Feature\Asignacion;

use App\Models\Tienda;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TiendaActivaTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_operator_switches_to_another_tienda_they_are_assigned(): void
    {
        [$operador, $lima, $arequipa] = $this->conDosTiendas();

        $this
            ->actingAs($operador)
            ->put(route('tienda-activa.update'), ['id_tienda' => $arequipa->id_tienda])
            ->assertRedirect(route('home'));

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

    /**
     * @return array{Usuario, Tienda, Tienda}
     */
    private function conDosTiendas(): array
    {
        $operador = Usuario::factory()->createOne();
        $lima = Tienda::factory()->createOne();
        $arequipa = Tienda::factory()->createOne();

        $operador->sincronizarAsignaciones([$lima->id_tienda, $arequipa->id_tienda], $lima->id_tienda);

        return [$operador, $lima, $arequipa];
    }
}
