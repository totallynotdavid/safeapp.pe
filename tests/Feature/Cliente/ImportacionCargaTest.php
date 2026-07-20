<?php

namespace Tests\Feature\Cliente;

use App\Models\Cliente;
use App\Models\Menu;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

// CARGAR DATOS: what a load does to the clients already in the table.
class ImportacionCargaTest extends TestCase
{
    use RefreshDatabase;

    private const PERMISO = 'clientes.importar';

    private const ENCABEZADO = 'ruc;razon_social;tipo_data;origen;zonal;nodo;q_movistar;q_entel;q_claro;q_bitel';

    public function test_reloading_a_client_updates_it_instead_of_duplicating_it(): void
    {
        $fila = '20123456789;ACME SAC;MOVIL;CAMPAÑA JULIO;LIMA;;0;0;0;0';

        $this->actingAs($this->cargador())->post(route('clientes.importar.store'), [
            'archivo' => $this->csv(self::ENCABEZADO."\n".$fila),
        ]);

        $this->actingAs($this->cargador())->post(route('clientes.importar.store'), [
            'archivo' => $this->csv(self::ENCABEZADO."\n20123456789;ACME SAC EIRL;MOVIL;CAMPAÑA AGOSTO;LIMA;;0;0;0;0"),
        ]);

        $this->assertSame(1, Cliente::query()->where('ruc', '20123456789')->count());
        $this->assertSame('ACME SAC EIRL', Cliente::porRuc('20123456789')?->razon_social);
    }

    public function test_a_load_never_takes_a_client_away_from_the_agent_working_it(): void
    {
        // The defect the legacy screen had: it copied id_usuario and estado
        // straight out of the staging table, so a load could unassign a client
        // an agent was mid-way through, or mark a worked client as pending.
        $agente = Usuario::factory()->create();
        $cliente = Cliente::factory()->create([
            'ruc' => '20123456789',
            'id_usuario' => $agente->id_usuario,
            'estado' => Cliente::ESTADO_GESTIONADO,
        ]);

        $this->actingAs($this->cargador())->post(route('clientes.importar.store'), [
            'archivo' => $this->csv(self::ENCABEZADO."\n20123456789;OTRA RAZON;MOVIL;CAMPAÑA JULIO;LIMA;;0;0;0;0"),
        ]);

        $cliente->refresh();

        $this->assertSame($agente->id_usuario, $cliente->id_usuario);
        $this->assertSame(Cliente::ESTADO_GESTIONADO, $cliente->estado);
        $this->assertSame('OTRA RAZON', $cliente->razon_social);
    }

    public function test_a_load_keeps_the_line_counts_an_agent_corrected(): void
    {
        // Same rule as ownership: the file creates the counts, a person owns
        // them afterwards, because the gestión form lets an agent fix them.
        Cliente::factory()->create(['ruc' => '20123456789', 'q_movistar' => 7]);

        $this->actingAs($this->cargador())->post(route('clientes.importar.store'), [
            'archivo' => $this->csv(self::ENCABEZADO."\n20123456789;ACME SAC;MOVIL;CAMPAÑA JULIO;LIMA;;0;0;0;0"),
        ]);

        $this->assertSame(7, Cliente::porRuc('20123456789')?->q_movistar);
    }

    private function csv(string $contenido): UploadedFile
    {
        return UploadedFile::fake()->createWithContent('clientes.csv', $contenido);
    }

    private function cargador(): Usuario
    {
        $user = Usuario::factory()->create();
        $user->menus()->attach(Menu::factory()->create(['permiso' => self::PERMISO]));

        return $user;
    }
}
