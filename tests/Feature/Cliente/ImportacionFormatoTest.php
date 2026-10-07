<?php

namespace Tests\Feature\Cliente;

use App\Models\Cliente;
use App\Models\Menu;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ImportacionFormatoTest extends TestCase
{
    use RefreshDatabase;

    private const PERMISO = 'clientes.importar';

    private const ENCABEZADO = 'ruc;razon_social;tipo_data;origen;zonal;nodo;q_movistar;q_entel;q_claro;q_bitel';

    public function test_the_permiso_gates_the_screen(): void
    {
        $this
            ->actingAs(Usuario::factory()->create())
            ->get(route('clientes.importar'))
            ->assertForbidden();

        $this
            ->actingAs($this->cargador())
            ->get(route('clientes.importar'))
            ->assertOk();
    }

    public function test_it_loads_a_semicolon_file_that_excel_wrote(): void
    {
        // Excel on a Spanish locale writes semicolons and prefixes a BOM. Both
        // were present in the files the legacy upload left behind.
        $this
            ->actingAs($this->cargador())
            ->post(route('clientes.importar.store'), [
                'archivo' => $this->csv(
                    "\u{FEFF}".self::ENCABEZADO."\n20123456789;ACME SAC;MOVIL;CAMPAÑA JULIO;LIMA;;3;1;0;0",
                ),
            ])
            ->assertRedirect();

        $cliente = Cliente::porRuc('20123456789');

        $this->assertNotNull($cliente);
        $this->assertSame('ACME SAC', $cliente->razon_social);

        $dato = $cliente->datos()->sole();
        $this->assertSame('CAMPAÑA JULIO', $dato->origen);
        $this->assertSame(3, $dato->q_movistar);
    }

    public function test_it_loads_a_comma_file_too(): void
    {
        $this
            ->actingAs($this->cargador())
            ->post(route('clientes.importar.store'), [
                'archivo' => $this->csv("ruc,tipo_data,origen\n20123456789,FIJA,CAMPAÑA JULIO"),
            ])
            ->assertRedirect();

        $this->assertSame('FIJA', Cliente::porRuc('20123456789')?->datos()->value('tipo_data'));
    }

    public function test_one_bad_row_rejects_the_whole_file(): void
    {
        // Partial loads are the thing that leaves an operator unable to say what
        // is missing. Rejecting is safe because the load is idempotent.
        $this
            ->actingAs($this->cargador())
            ->post(route('clientes.importar.store'), [
                'archivo' => $this->csv(
                    self::ENCABEZADO
                    ."\n20123456789;ACME SAC;MOVIL;CAMPAÑA JULIO;LIMA;;0;0;0;0"
                    ."\n123;OTRA SAC;MOVIL;CAMPAÑA JULIO;LIMA;;0;0;0;0",
                ),
            ])
            ->assertRedirect()
            ->assertSessionHas('errores');

        $this->assertSame(0, Cliente::query()->count());
    }

    public function test_a_segment_with_no_queue_is_refused(): void
    {
        // tipo_data decides which queue works the client. Legacy let the import
        // write anything, which is how 2,677 CONVERGENTE clients became
        // unreachable: no screen has ever covered that segment.
        $this
            ->actingAs($this->cargador())
            ->post(route('clientes.importar.store'), [
                'archivo' => $this->csv("ruc;tipo_data;origen\n20123456789;CONVERGENTE;CAMPAÑA JULIO"),
            ])
            ->assertSessionHas('errores');

        $this->assertSame(0, Cliente::query()->count());
    }

    public function test_a_client_without_an_origen_is_refused(): void
    {
        // The pool picker groups by origen and skips nulls, so a client loaded
        // without one could never be drawn by any agent.
        $this
            ->actingAs($this->cargador())
            ->post(route('clientes.importar.store'), [
                'archivo' => $this->csv("ruc;tipo_data;origen\n20123456789;MOVIL;"),
            ])
            ->assertSessionHas('errores');

        $this->assertSame(0, Cliente::query()->count());
    }

    public function test_a_ruc_repeated_inside_one_file_is_refused(): void
    {
        // The unique index does not catch this: the upsert would keep whichever
        // copy came last and report success.
        $this
            ->actingAs($this->cargador())
            ->post(route('clientes.importar.store'), [
                'archivo' => $this->csv(
                    "ruc;tipo_data;origen\n20123456789;MOVIL;CAMPAÑA JULIO\n20123456789;FIJA;CAMPAÑA JULIO",
                ),
            ])
            ->assertSessionHas('errores');

        $this->assertSame(0, Cliente::query()->count());
    }

    public function test_a_file_missing_a_required_column_is_refused_once_not_per_row(): void
    {
        $respuesta = $this
            ->actingAs($this->cargador())
            ->post(route('clientes.importar.store'), [
                'archivo' => $this->csv("ruc;razon_social\n20123456789;ACME SAC\n20123456780;OTRA SAC"),
            ]);

        $respuesta->assertSessionHas(
            'errores',
            static fn (mixed $errores): bool => is_countable($errores) && count($errores) === 1,
        );
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
