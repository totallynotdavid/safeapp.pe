<?php

namespace Tests\Feature\Cliente;

use App\Models\AsignacionDato;
use App\Models\Cliente;
use App\Models\Dato;
use App\Models\Llamada;
use App\Models\Menu;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ImportacionCargaTest extends TestCase
{
    use RefreshDatabase;

    private const PERMISO = 'clientes.importar';

    private const ENCABEZADO = 'ruc;razon_social;tipo_data;origen;zonal;nodo;q_movistar;q_entel;q_claro;q_bitel';

    public function test_reloading_the_same_campaign_updates_it_instead_of_duplicating_it(): void
    {
        $fila = '20123456789;ACME SAC;MOVIL;CAMPAÑA JULIO;LIMA;;0;0;0;0';

        $this->actingAs($this->cargador())->post(route('clientes.importar.store'), [
            'archivo' => $this->csv(self::ENCABEZADO."\n".$fila),
        ]);

        $this->actingAs($this->cargador())->post(route('clientes.importar.store'), [
            'archivo' => $this->csv(self::ENCABEZADO
            ."\n20123456789;ACME SAC EIRL;MOVIL;CAMPAÑA JULIO;AREQUIPA;;0;0;0;0"),
        ]);

        $this->assertSame(1, Cliente::query()->where('ruc', '20123456789')->count());
        $this->assertSame(1, Cliente::porRuc('20123456789')?->datos()->count());
        // The feed is authoritative for both, so the second load wins.
        $this->assertSame('ACME SAC EIRL', Cliente::porRuc('20123456789')?->razon_social);
        $this->assertSame('AREQUIPA', Cliente::porRuc('20123456789')?->datos()->value('zonal'));
    }

    /**
     * A load cannot unassign a record an agent is working or mark a worked one
     * as pending: neither column is reachable from `datos` by the importer.
     */
    public function test_a_load_cannot_disturb_the_agent_working_a_record(): void
    {
        $agente = Usuario::factory()->createOne();
        $dato = Dato::factory()
            ->gestionado()
            ->asignadoA($agente->id_usuario, 1)
            ->createOne(['ruc' => '20123456789', 'origen' => 'CAMPAÑA JULIO']);

        $this->actingAs($this->cargador())->post(route('clientes.importar.store'), [
            'archivo' => $this->csv(self::ENCABEZADO."\n20123456789;OTRA RAZON;MOVIL;CAMPAÑA JULIO;LIMA;;0;0;0;0"),
        ]);

        $dato->refresh();

        $this->assertSame($agente->id_usuario, $dato->asignacionActiva?->id_usuario);
        $this->assertSame(Dato::ESTADO_GESTIONADO, $dato->estado);
        $this->assertSame(1, AsignacionDato::query()->count());
    }

    /**
     * The file is authoritative for campaign facts, and a reload refreshes
     * them. What an agent confirmed is not overwritten because it was never
     * stored here: it lives on the contact they logged.
     */
    public function test_a_load_refreshes_the_campaign_counts_and_leaves_the_contacts_alone(): void
    {
        $dato = Dato::factory()->createOne([
            'ruc' => '20123456789',
            'origen' => 'CAMPAÑA JULIO',
            'q_movistar' => 2,
        ]);
        Llamada::factory()->create(['id_cliente' => $dato->id_cliente, 'q_movistar' => 7]);

        $this->actingAs($this->cargador())->post(route('clientes.importar.store'), [
            'archivo' => $this->csv(self::ENCABEZADO."\n20123456789;ACME SAC;MOVIL;CAMPAÑA JULIO;LIMA;;5;0;0;0"),
        ]);

        $this->assertSame(5, $dato->fresh()->q_movistar);
        $this->assertSame(7, $dato->cliente->llamadas()->sole()->q_movistar);
    }

    public function test_a_second_campaign_adds_a_record_rather_than_replacing_one(): void
    {
        Dato::factory()->create(['ruc' => '20123456789', 'origen' => 'CAMPAÑA JULIO']);

        $this->actingAs($this->cargador())->post(route('clientes.importar.store'), [
            'archivo' => $this->csv(self::ENCABEZADO."\n20123456789;ACME SAC;MOVIL;CAMPAÑA AGOSTO;LIMA;;0;0;0;0"),
        ]);

        $this->assertSame(1, Cliente::query()->where('ruc', '20123456789')->count());
        $this->assertSame(2, Cliente::porRuc('20123456789')?->datos()->count());
    }

    private function csv(string $contenido): UploadedFile
    {
        return UploadedFile::fake()->createWithContent('clientes.csv', $contenido);
    }

    private function cargador(): Usuario
    {
        $user = Usuario::factory()->createOne();
        $user->menus()->attach(Menu::factory()->create(['permiso' => self::PERMISO]));

        return $user;
    }
}
