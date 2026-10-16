<?php

namespace Tests\Feature\Personal;

use App\Models\Menu;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PersonalManagementTest extends TestCase
{
    use RefreshDatabase;

    private function gestor(): Usuario
    {
        $menu = Menu::factory()->create(['permiso' => 'personal.gestionar']);
        $user = Usuario::factory()->createOne();
        $user->menus()->attach($menu);

        return $user;
    }

    public function test_the_gestionar_permiso_gates_the_edit_screen(): void
    {
        $usuario = Usuario::factory()->createOne();

        $this->actingAs(Usuario::factory()->create())->get(route('personal.edit', $usuario))->assertForbidden();
        $this->actingAs($this->gestor())->get(route('personal.edit', $usuario))->assertOk();
    }

    public function test_updates_the_contact_data(): void
    {
        $usuario = Usuario::factory()->createOne();

        $this->actingAs($this->gestor())->put(route('personal.update', $usuario), [
            'personal' => 'maría ñoño',
            'fecha_nacimiento' => '1990-05-20',
            'telefono' => '987654321',
            'correo' => 'maria@example.com',
        ])->assertRedirect(route('equipo.show', $usuario));

        $fresh = $usuario->fresh();
        $this->assertSame('MARÍA ÑOÑO', $fresh->personal);
        $this->assertSame('1990-05-20', $fresh->fecha_nacimiento->format('Y-m-d'));
        $this->assertSame('987654321', $fresh->telefono);
        $this->assertSame('maria@example.com', $fresh->correo);
    }

    public function test_clears_optional_fields_when_left_blank(): void
    {
        $usuario = Usuario::factory()->create(['telefono' => '999999999', 'correo' => 'old@example.com']);

        $this->actingAs($this->gestor())->put(route('personal.update', $usuario), [
            'personal' => 'SOLO NOMBRE',
        ])->assertRedirect(route('equipo.show', $usuario));

        $fresh = $usuario->fresh();
        $this->assertNull($fresh->telefono);
        $this->assertNull($fresh->correo);
        $this->assertNull($fresh->fecha_nacimiento);
    }

    /** @return array<string, array{array<string, string>, string}> */
    public static function rejectedInput(): array
    {
        return [
            'an invalid correo' => [['personal' => 'NOMBRE', 'correo' => 'not-an-email'], 'correo'],
            'a missing nombre' => [[], 'personal'],
        ];
    }

    /** @param  array<string, string>  $input */
    #[DataProvider('rejectedInput')]
    public function test_rejects_invalid_input(array $input, string $campo): void
    {
        $usuario = Usuario::factory()->create();

        $this
            ->actingAs($this->gestor())
            ->put(route('personal.update', $usuario), $input)
            ->assertSessionHasErrors($campo);
    }
}
