<?php

namespace Tests\Feature\Cliente;

use App\Models\Cliente;
use App\Models\Menu;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PHPUnit\Framework\Attributes\DataProvider;
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
        // Excel on a Spanish locale writes semicolons and prefixes a BOM.
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

    /**
     * One bad row rejects the whole file, because a partial load leaves an
     * operator unable to say what is missing.
     *
     * @return array<string, array{string}>
     */
    public static function refusedFiles(): array
    {
        return [
            'one bad row among good ones' => [
                self::ENCABEZADO
                    ."\n20123456789;ACME SAC;MOVIL;CAMPAÑA JULIO;LIMA;;0;0;0;0"
                    ."\n123;OTRA SAC;MOVIL;CAMPAÑA JULIO;LIMA;;0;0;0;0",
            ],
            // tipo_data decides which queue works the client, and no screen
            // covers CONVERGENTE.
            'a segment with no queue' => ["ruc;tipo_data;origen\n20123456789;CONVERGENTE;CAMPAÑA JULIO"],
            // The pool picker groups by origen and skips nulls.
            'a client without an origen' => ["ruc;tipo_data;origen\n20123456789;MOVIL;"],
            // The unique index does not catch this: the upsert would keep the
            // last copy and report success.
            'a ruc repeated inside the file' => [
                "ruc;tipo_data;origen\n20123456789;MOVIL;CAMPAÑA JULIO\n20123456789;FIJA;CAMPAÑA JULIO",
            ],
        ];
    }

    #[DataProvider('refusedFiles')]
    public function test_a_file_with_an_invalid_row_loads_nothing(string $contenido): void
    {
        $this
            ->actingAs($this->cargador())
            ->post(route('clientes.importar.store'), ['archivo' => $this->csv($contenido)])
            ->assertRedirect()
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
