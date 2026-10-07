<?php

namespace Tests\Feature\Account;

use App\Mail\AccountEmail;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class AccountSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_update_changes_only_the_authenticated_users_requested_field(): void
    {
        Mail::fake();
        $usuario = Usuario::factory()->create([
            'personal' => 'NOMBRE ANTERIOR',
            'telefono' => '999111222',
        ]);
        $otro = Usuario::factory()->create(['personal' => 'PERSONA AJENA']);

        $this
            ->actingAs($usuario)
            ->from(route('configuracion.perfil'))
            ->put(route('configuracion.perfil.update'), [
                'campo' => 'personal',
                'personal' => 'Ada Lovelace',
                'id_usuario' => $otro->id_usuario,
            ])
            ->assertRedirect(route('configuracion.perfil'))
            ->assertSessionHas('status', 'Información personal actualizada.');

        $this->assertSame('ADA LOVELACE', $usuario->fresh()->personal);
        $this->assertSame('999111222', $usuario->fresh()->telefono);
        $this->assertSame('PERSONA AJENA', $otro->fresh()->personal);
        $this->assertDatabaseHas('notificacion_usuario', [
            'id_usuario' => $usuario->id_usuario,
            'categoria' => 'cuenta',
            'titulo' => 'Información personal actualizada',
        ]);
        Mail::assertNothingQueued();
    }

    public function test_changing_an_email_requires_reverification_and_queues_a_verification_message(): void
    {
        Mail::fake();
        $usuario = Usuario::factory()->create([
            'correo' => 'antes@example.test',
            'correo_verificado_en' => now(),
        ]);

        $this
            ->actingAs($usuario)
            ->from(route('configuracion.perfil'))
            ->put(route('configuracion.perfil.update'), [
                'campo' => 'correo',
                'correo' => 'despues@example.test',
            ])
            ->assertRedirect(route('configuracion.perfil'))
            ->assertSessionHas('status', 'Datos actualizados. Revisa tu correo para verificar la nueva dirección.');

        $this->assertSame('despues@example.test', $usuario->fresh()->correo);
        $this->assertNull($usuario->fresh()->correo_verificado_en);
        Mail::assertQueued(AccountEmail::class, 1);
    }

    public function test_password_change_requires_the_current_password_and_does_not_mutate_on_failure(): void
    {
        $usuario = Usuario::factory()->clave('correcta')->createOne();
        $original = $usuario->clave;

        $this
            ->actingAs($usuario)
            ->from(route('configuracion.seguridad'))
            ->put(route('configuracion.seguridad.update'), [
                'clave_actual' => 'incorrecta',
                'clave' => 'nueva-clave',
                'clave_confirmation' => 'nueva-clave',
            ])
            ->assertRedirect(route('configuracion.seguridad'))
            ->assertSessionHasErrors('clave_actual');

        $this->assertSame($original, $usuario->fresh()->clave);
    }

    public function test_password_change_replaces_the_credential_and_records_a_security_event(): void
    {
        $usuario = Usuario::factory()->clave('correcta')->create();

        $this
            ->actingAs($usuario)
            ->from(route('configuracion.seguridad'))
            ->put(route('configuracion.seguridad.update'), [
                'clave_actual' => 'correcta',
                'clave' => 'nueva-clave',
                'clave_confirmation' => 'nueva-clave',
            ])
            ->assertRedirect(route('configuracion.seguridad'))
            ->assertSessionHas('status', 'Contraseña actualizada.');

        $this->assertTrue(password_verify('nueva-clave', (string) $usuario->fresh()?->clave));
        $this->assertDatabaseHas('notificacion_usuario', [
            'id_usuario' => $usuario->id_usuario,
            'categoria' => 'seguridad',
            'titulo' => 'Contraseña actualizada',
        ]);
    }

    public function test_notification_preferences_are_owned_by_the_authenticated_user_and_returned_to_json_clients(): void
    {
        $usuario = Usuario::factory()->create();
        $otro = Usuario::factory()->create();

        $this
            ->actingAs($usuario)
            ->putJson(route('configuracion.notificaciones.update'), [
                'correo_cuenta' => false,
                'correo_seguridad' => true,
                'id_usuario' => $otro->id_usuario,
            ])
            ->assertOk()
            ->assertJsonPath('preferences.correo_cuenta', false)
            ->assertJsonPath('preferences.correo_seguridad', true);

        $this->assertDatabaseHas('preferencia_notificacion_usuario', [
            'id_usuario' => $usuario->id_usuario,
            'correo_cuenta' => false,
            'correo_seguridad' => true,
        ]);
        $this->assertDatabaseMissing('preferencia_notificacion_usuario', ['id_usuario' => $otro->id_usuario]);
    }

    public function test_email_verification_accepts_only_its_owner_and_a_valid_signed_link(): void
    {
        $usuario = Usuario::factory()->createOne(['correo' => 'persona@example.test']);
        $otro = Usuario::factory()->createOne();
        $url = URL::temporarySignedRoute('configuracion.correo.verificar', now()->addMinute(), [
            'usuario' => $usuario,
            'hash' => sha1((string) $usuario->correo),
        ]);

        $this->actingAs($otro)->get($url)->assertForbidden();
        $this->assertNull($usuario->fresh()->correo_verificado_en);

        $this
            ->actingAs($usuario)
            ->get($url)
            ->assertRedirect(route('configuracion.perfil'))
            ->assertSessionHas('status', 'Correo verificado. Ya puedes recibir notificaciones por email.');

        $this->assertNotNull($usuario->fresh()->correo_verificado_en);
    }
}
