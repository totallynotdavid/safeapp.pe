<?php

namespace Tests\Feature\Auth;

use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class RestablecerClaveTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_account_without_a_password_signs_in_with_the_one_the_command_prints(): void
    {
        $usuario = Usuario::factory()->sinClave()->create(['usuario' => 'migrado']);

        $this->assertSame(0, Artisan::call('usuario:restablecer-clave', ['usuario' => 'migrado']));
        $impresa = [];
        preg_match('/^[A-Za-z0-9]{16}$/m', Artisan::output(), $impresa);
        $this->assertNotEmpty($impresa, 'the command prints the new password on its own line');

        $this
            ->post('/login', ['usuario' => 'migrado', 'clave' => $impresa[0]])
            ->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($usuario);
    }

    public function test_it_replaces_an_existing_password(): void
    {
        $usuario = Usuario::factory()->clave('anterior')->create(['usuario' => 'agente1']);

        $this->comando('usuario:restablecer-clave', ['usuario' => 'agente1'])->assertSuccessful();

        $this->assertFalse(password_verify('anterior', (string) $usuario->fresh()?->clave));
    }

    public function test_it_fails_for_an_unknown_account(): void
    {
        $this
            ->comando('usuario:restablecer-clave', ['usuario' => 'nadie'])
            ->expectsOutputToContain('No account named')
            ->assertFailed();
    }
}
