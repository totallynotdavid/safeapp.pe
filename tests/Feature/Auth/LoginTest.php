<?php

namespace Tests\Feature\Auth;

use App\Models\Menu;
use App\Models\Rol;
use App\Models\Usuario;
use Database\Factories\UsuarioFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_wrong_password_is_refused_and_the_error_is_attached_to_the_username_field(): void
    {
        Usuario::factory()->create(['usuario' => 'agente1']);

        $this
            ->from('/login')
            ->post('/login', [
                'usuario' => 'agente1',
                'clave' => 'contrasena-incorrecta',
            ])
            ->assertRedirect('/login');

        $this->assertGuest();

        $this
            ->get('/login')
            ->assertSee('aria-invalid="true"', false)
            ->assertSee('aria-describedby="usuario-status"', false)
            ->assertSee('id="usuario-status"', false)
            ->assertSee('role="alert"', false)
            ->assertSee('Credenciales inválidas.');
    }

    public function test_authenticates_with_a_correct_bcrypt_password(): void
    {
        $usuario = Usuario::factory()
            ->for(Rol::factory()->state(['nombre_rol' => 'Supervisor']), 'rol')
            ->create(['usuario' => 'agente1']);

        $this->post('/login', [
            'usuario' => 'agente1',
            'clave' => UsuarioFactory::CLAVE,
        ])->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($usuario);
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

    public function test_an_account_without_a_password_cannot_sign_in(): void
    {
        Usuario::factory()->sinClave()->create(['usuario' => 'migrado']);

        foreach (['cualquiera', UsuarioFactory::CLAVE, 'null'] as $intento) {
            $this
                ->from('/login')
                ->post('/login', ['usuario' => 'migrado', 'clave' => $intento])
                ->assertSessionHasErrors('usuario');

            $this->assertGuest();
        }
    }

    public function test_an_unsalted_md5_in_the_password_column_does_not_sign_in(): void
    {
        $usuario = Usuario::factory()->createOne(['usuario' => 'viejo']);

        // The query builder skips the model's `hashed` cast, which would
        // otherwise bcrypt the md5 and leave no raw md5 in the column.
        DB::table('usuario')
            ->where('id_usuario', $usuario->id_usuario)
            ->update(['clave' => md5('secreto-legado')]);

        $this->assertSame(
            md5('secreto-legado'),
            DB::table('usuario')->where('id_usuario', $usuario->id_usuario)->value('clave'),
        );

        $this
            ->from('/login')
            ->post('/login', ['usuario' => 'viejo', 'clave' => 'secreto-legado'])
            ->assertSessionHasErrors('usuario');

        $this->assertGuest();
    }

    public function test_an_administrator_sets_the_first_password_and_the_account_signs_in(): void
    {
        $migrado = Usuario::factory()->sinClave()->create(['usuario' => 'migrado']);
        $menu = Menu::factory()->create(['permiso' => 'usuarios.manage']);
        $admin = Usuario::factory()->createOne();
        $admin->menus()->attach($menu);

        $this
            ->actingAs($admin)
            ->put(route('usuarios.update', $migrado), ['personal' => 'MIGRADO', 'clave' => 'nueva-clave'])
            ->assertRedirect();
        Auth::logout();

        $this
            ->post('/login', ['usuario' => 'migrado', 'clave' => 'nueva-clave'])
            ->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($migrado);
    }

    public function test_sign_in_attempts_are_limited(): void
    {
        Usuario::factory()->create(['usuario' => 'agente1']);

        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['usuario' => 'agente1', 'clave' => 'mala'])->assertSessionHasErrors('usuario');
        }

        $this
            ->post('/login', ['usuario' => 'agente1', 'clave' => UsuarioFactory::CLAVE])
            ->assertTooManyRequests();

        $this->assertGuest();
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
