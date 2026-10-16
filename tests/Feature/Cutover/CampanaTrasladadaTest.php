<?php

namespace Tests\Feature\Cutover;

use App\Models\AsignacionDato;
use App\Models\Dato;

class CampanaTrasladadaTest extends CutoverTestCase
{
    public function test_a_held_record_becomes_a_record_plus_an_open_hold(): void
    {
        $this->datoLegado([
            'id_usuario' => 42,
            'id_tienda' => 4,
            'fecha_asignacion' => '2026-06-01',
            'estado' => 1,
        ]);

        $this->transformar();

        $dato = Dato::query()->with('asignacionActiva')->sole();
        $this->assertSame(Dato::ESTADO_GESTIONADO, $dato->estado);
        $asignacion = $dato->asignacionActiva;
        $this->assertNotNull($asignacion);
        $this->assertSame(42, $asignacion->id_usuario);
        $this->assertSame(4, $asignacion->id_tienda);
        $this->assertSame('2026-06-01', $asignacion->fecha_asignacion->toDateString());
        $this->assertNull($asignacion->fecha_liberacion);
    }

    public function test_an_unheld_record_arrives_with_no_hold_at_all(): void
    {
        $this->datoLegado(['id_usuario' => 0]);

        $this->transformar();

        $this->assertSame(1, Dato::query()->count());
        $this->assertSame(0, AsignacionDato::query()->count());
    }

    /**
     * Legacy only began writing fecha_asignacion in May 2026, so 22,288 of the
     * open holds have none. The hold is real either way, and a hold without a
     * start date is not a state the new schema should be able to represent.
     */
    public function test_a_hold_legacy_never_dated_is_dated_at_the_cutover(): void
    {
        $this->datoLegado(['id_usuario' => 42, 'id_tienda' => 4, 'fecha_asignacion' => '0000-00-00']);

        $this->transformar();

        $this->assertSame(
            now()->toDateString(),
            AsignacionDato::query()->sole()->fecha_asignacion->toDateString(),
        );
    }

    /**
     * 28 groups of rows in the export collide on the key the new schema
     * enforces, and every one is an agregarcliente double submit: identical to
     * its twin apart from the id.
     */
    public function test_a_repeated_registration_collapses_to_one_record(): void
    {
        $this->datoLegado(['origen' => 'INHOUSE', 'tipo_data' => 'CONVERGENTE', 'id_usuario' => 7, 'id_tienda' => 1]);
        $this->datoLegado(['origen' => 'INHOUSE', 'tipo_data' => 'CONVERGENTE', 'id_usuario' => 7, 'id_tienda' => 1]);

        $this->transformar();

        $this->assertSame(1, Dato::query()->count());
        // And one hold, because two would break the index that makes a single
        // holder a constraint rather than a rule.
        $this->assertSame(1, AsignacionDato::query()->count());
    }

    public function test_a_hand_registered_client_is_filed_by_the_case_it_led_to(): void
    {
        // Provenance written into the queue column is what stranded 2,677
        // clients: the queue filters on tipo_data and no screen covers
        // CONVERGENTE. What the agent did next is the evidence for where they
        // belong.
        $this->datoLegado(['ruc' => '20111111111', 'origen' => 'INHOUSE', 'tipo_data' => 'CONVERGENTE']);
        $this->filaLegado('nc_fija', ['ruc' => '20111111111', 'razon_social' => 'X', 'id_usuario' => 1]);

        $this->datoLegado(['ruc' => '20222222222', 'origen' => 'INHOUSE', 'tipo_data' => 'CONVERGENTE']);
        $this->filaLegado('nc_movil', ['ruc' => '20222222222', 'razon_social' => 'Y', 'id_usuario' => 1]);

        $this->transformar();

        $this->assertSame('FIJA', $this->segmentoDe('20111111111'));
        $this->assertSame('MOVIL', $this->segmentoDe('20222222222'));
    }

    public function test_a_hand_registered_client_with_no_case_falls_to_the_common_segment(): void
    {
        $this->datoLegado(['origen' => 'INHOUSE', 'tipo_data' => 'CONVERGENTE']);

        $this->transformar();

        $dato = Dato::query()->sole();
        $this->assertSame('MOVIL', $dato->tipo_data);
        // The hand-registered provenance stays in origen.
        $this->assertSame('INHOUSE', $dato->origen);
    }

    private function segmentoDe(string $ruc): ?string
    {
        return Dato::query()
            ->whereHas('cliente', static fn ($q) => $q->where('ruc', $ruc))
            ->first()
            ?->tipo_data;
    }
}
