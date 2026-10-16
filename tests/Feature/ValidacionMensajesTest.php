<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ValidacionMensajesTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_rejected_field_names_itself_in_spanish(): void
    {
        $this
            ->post('/login', [])
            ->assertSessionHasErrors([
                'usuario' => 'Debe completar el usuario.',
                'clave' => 'Debe completar la contraseña.',
            ]);
    }
}
