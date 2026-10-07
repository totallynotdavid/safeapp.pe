<?php

namespace Tests\Feature\Llamada;

use App\Models\Cliente;
use App\Models\Llamada;
use App\Models\Respuesta;
use App\Models\Usuario;
use Database\Seeders\RespuestaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LlamadaFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_respuesta_seeder_loads_the_full_disposition_catalog(): void
    {
        $this->seed(RespuestaSeeder::class);

        $this->assertSame(29, Respuesta::query()->count());
        $this->assertSame(14, Respuesta::query()->where('tipo', 'LLAMADA')->where('id_respuesta', '<=', 14)->count());
        $this->assertSame(14, Respuesta::query()->where('tipo', 'VISITA')->count());
    }

    public function test_it_normalizes_the_legacy_si_no_null_flags_to_booleans(): void
    {
        $this->seed(RespuestaSeeder::class);

        $sinContacto = Respuesta::query()->find(7);
        $this->assertFalse($sinContacto->contactabilidad);
        $this->assertFalse($sinContacto->negociacion);

        // "Volver a llamar" is the row legacy left without a negociacion flag.
        $volverALlamar = Respuesta::query()->find(10);
        $this->assertTrue($volverALlamar->contactabilidad);
        $this->assertNull($volverALlamar->negociacion);
        $this->assertTrue($volverALlamar->volverllamar);
    }

    public function test_a_llamada_reads_back_its_agent_and_disposition(): void
    {
        $this->seed(RespuestaSeeder::class);
        $agente = Usuario::factory()->createOne();

        $llamada = Llamada::factory()->createOne([
            'id_usuario' => $agente->id_usuario,
            'id_respuesta' => 2,
        ]);

        $this->assertSame($agente->id_usuario, $llamada->agente->id_usuario);
        $this->assertSame('Desea Movil', $llamada->respuesta->descripcion);
    }

    public function test_the_log_accumulates_every_contact_for_one_customer(): void
    {
        $this->seed(RespuestaSeeder::class);
        $agente = Usuario::factory()->create();

        Llamada::factory()
            ->count(3)
            ->create([
                'id_usuario' => $agente->id_usuario,
                'ruc' => '20123456789',
            ]);

        // The log hangs off identity, so contacts accumulate across whatever
        // campaigns delivered the customer in between.
        $this->assertSame(3, Cliente::porRuc('20123456789')?->llamadas()->count());
    }
}
