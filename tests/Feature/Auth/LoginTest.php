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

    public function test_login_screen_renders(): void
    {
        $this->get('/login')->assertOk()->assertViewIs('auth.login');
    }

    public function test_authenticates_with_a_correct_bcrypt_password(): void
    {
        $usuario = Usuario::factory()
            ->for(Rol::factory()->state(['nombre_rol' => 'Supervisor']), 'rol')
            ->create(['usuario' => 'agente1']);

        $this->post('/login', [
            'usuario' => 'agente1',
            'clave' => UsuarioFactory::CLAVE,
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($usuario);
    }

    public function test_rejects_a_wrong_password(): void
    {
        Usuario::factory()->create(['usuario' => 'agente1']);

        $this
            ->from('/login')
            ->post('/login', [
                'usuario' => 'agente1',
                'clave' => 'contrasena-incorrecta',
            ])
            ->assertRedirect('/login')
            ->assertSessionHasErrors('usuario');

        $this->assertGuest();
    }

    public function test_rejects_an_inactive_account_even_with_the_right_password(): void
    {
        Usuario::factory()->inactivo()->create(['usuario' => 'baja']);

        $this
            ->from('/login')
            ->post('/login', [
                'usuario' => 'baja',
                'clave' => UsuarioFactory::CLAVE,
            ])
            ->assertRedirect('/login')
            ->assertSessionHasErrors('usuario');

        $this->assertGuest();
    }

    public function test_authenticates_a_legacy_md5_account_and_upgrades_the_hash(): void
    {
        $usuario = Usuario::factory()->claveMd5('secreto-legado')->create(['usuario' => 'viejo']);
        $this->assertSame(md5('secreto-legado'), $usuario->clave);

        $this->post('/login', [
            'usuario' => 'viejo',
            'clave' => 'secreto-legado',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($usuario);

        $rehashed = $usuario->fresh()->clave;
        $this->assertNotSame(md5('secreto-legado'), $rehashed);
        $this->assertTrue(password_verify('secreto-legado', $rehashed));
    }

    public function test_logout_ends_the_session(): void
    {
        $usuario = Usuario::factory()->create();

        $this
            ->actingAs($usuario)
            ->post('/logout')
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }
}
