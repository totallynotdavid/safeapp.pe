<?php

namespace Tests\Feature\Auth;

use App\Models\Rol;
use App\Models\Usuario;
use Database\Factories\UsuarioFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticates_with_a_correct_bcrypt_password(): void
    {
        $usuario = Usuario::factory()
            ->for(Rol::factory()->state(['nombre_rol' => 'Supervisor']), 'rol')
            ->create(['usuario' => 'agente1']);

        $response = $this->postJson('/login', [
            'usuario' => 'agente1',
            'clave' => UsuarioFactory::CLAVE,
        ]);

        $response->assertOk()
            ->assertJsonPath('usuario', 'agente1')
            ->assertJsonPath('rol', 'Supervisor');
        $this->assertAuthenticatedAs($usuario);
    }

    public function test_rejects_a_wrong_password(): void
    {
        Usuario::factory()->create(['usuario' => 'agente1']);

        $this->postJson('/login', [
            'usuario' => 'agente1',
            'clave' => 'contrasena-incorrecta',
        ])->assertStatus(422)->assertJsonValidationErrors('usuario');

        $this->assertGuest();
    }

    public function test_rejects_an_inactive_account_even_with_the_right_password(): void
    {
        Usuario::factory()->inactivo()->create(['usuario' => 'baja']);

        $this->postJson('/login', [
            'usuario' => 'baja',
            'clave' => UsuarioFactory::CLAVE,
        ])->assertStatus(422)->assertJsonValidationErrors('usuario');

        $this->assertGuest();
    }

    public function test_authenticates_a_legacy_md5_account_and_upgrades_the_hash(): void
    {
        $usuario = Usuario::factory()->claveMd5('secreto-legado')->create(['usuario' => 'viejo']);
        $this->assertSame(md5('secreto-legado'), $usuario->clave);

        $this->postJson('/login', [
            'usuario' => 'viejo',
            'clave' => 'secreto-legado',
        ])->assertOk();

        $this->assertAuthenticatedAs($usuario);

        $rehashed = $usuario->fresh()->clave;
        $this->assertNotSame(md5('secreto-legado'), $rehashed);
        $this->assertTrue(password_verify('secreto-legado', $rehashed));
    }

    public function test_logout_ends_the_session(): void
    {
        $usuario = Usuario::factory()->create();

        $this->actingAs($usuario)
            ->postJson('/logout')
            ->assertOk()
            ->assertJsonPath('ok', true);

        $this->assertGuest();
    }
}
