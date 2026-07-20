<?php

namespace Tests\Feature\Cutover;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * A legacy-shaped source database on its own in-memory connection, plus the
 * handful of rows every cutover test starts from.
 *
 * The figures the transform's comments cite come from measuring the real export
 * against MariaDB. These tests pin the behaviour those measurements led to, one
 * row per rule, so a later change has to disagree with the reasoning out loud.
 * The schema itself is EsquemaLegado.
 */
abstract class CutoverTestCase extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Overridden after boot, not before: the config repository does not
        // exist until the application is up. RefreshDatabase only touches the
        // default connection, so redefining this one afterwards is safe.
        config(['database.connections.legacy' => [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => false,
        ]]);

        EsquemaLegado::construir();
    }

    /**
     * @param  array<string, mixed>  $atributos
     */
    protected function filaLegado(string $tabla, array $atributos): void
    {
        DB::connection('legacy')->table($tabla)->insert($atributos);
    }

    /**
     * A campaign row with the columns every test would otherwise have to repeat.
     *
     * @param  array<string, mixed>  $atributos
     */
    protected function datoLegado(array $atributos = []): void
    {
        $this->filaLegado('datos', [
            'ruc' => '20123456789',
            'razon_social' => 'CLIENTE UNO SAC',
            'origen' => 'CAMPAÑA JULIO',
            'tipo_data' => 'MOVIL',
            'estado' => 0,
            'id_usuario' => 0,
            'id_tienda' => 0,
            'fecha_asignacion' => '0000-00-00',
            'zonal' => 'LIMA',
            'nodo' => null,
            'q_movistar' => 0,
            'q_entel' => 0,
            'q_claro' => 0,
            'q_bitel' => 0,
            ...$atributos,
        ]);
    }

    protected function transformar(): void
    {
        $this->artisan('cutover:transform')->assertSuccessful();
    }
}
