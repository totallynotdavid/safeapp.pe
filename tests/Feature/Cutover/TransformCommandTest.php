<?php

namespace Tests\Feature\Cutover;

use App\Models\Cliente;
use App\Models\Dato;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TransformCommandTest extends CutoverTestCase
{
    /**
     * migrate:fresh vacuums SQLite, which it cannot do inside the transaction
     * RefreshDatabase wraps each test in.
     */
    private function salirDeLaTransaccion(): void
    {
        DB::rollBack();
    }

    /**
     * Rerunnable by refusing, not by merging. A second run over a populated
     * database would double every table, and an upsert path that only ever runs
     * once in earnest is a path nobody has exercised. Resetting first is one
     * behaviour instead of two.
     */
    public function test_it_refuses_a_database_that_already_holds_rows(): void
    {
        $this->datoLegado();
        $this->transformar();

        $this
            ->comando('cutover:transform')
            ->expectsOutputToContain('--fresh')
            ->assertFailed();

        $this->assertSame(1, Cliente::query()->count());
        $this->assertSame(1, Dato::query()->count());
    }

    public function test_fresh_rebuilds_the_destination_so_a_rerun_needs_no_other_step(): void
    {
        $this->datoLegado();
        $this->transformar();
        $this->salirDeLaTransaccion();
        DB::connection('legacy')->table('datos')->delete();
        $this->datoLegado(['ruc' => '20999999999', 'razon_social' => 'CLIENTE DOS SAC']);

        $this->comando('cutover:transform', ['--fresh' => true])->assertSuccessful();

        $this->assertSame(['20999999999'], Cliente::query()->pluck('ruc')->all());
    }

    /**
     * The destination on the night is the legacy database itself, and
     * migrate:fresh drops tables while leaving views standing. A legacy view
     * outlives the columns it selects, and an invalid view makes mysqldump fail
     * for the whole database, so the backup is what breaks first.
     */
    public function test_it_refuses_a_database_still_holding_legacy_views(): void
    {
        DB::statement('CREATE VIEW reporteusu_excel AS SELECT id_usuario FROM usuario');

        $this
            ->comando('cutover:transform')
            ->expectsOutputToContain('--fresh')
            ->assertFailed();
    }

    public function test_fresh_drops_the_legacy_views_the_destination_still_holds(): void
    {
        DB::statement('CREATE VIEW reporteusu_excel AS SELECT id_usuario FROM usuario');
        $this->salirDeLaTransaccion();

        $this->comando('cutover:transform', ['--fresh' => true])->assertSuccessful();

        $this->assertSame([], Schema::getViews());
    }

    public function test_an_empty_export_still_leaves_the_catalogs_the_app_owns(): void
    {
        $this->transformar();

        // Nothing to carry across, but the menu and the disposition catalog are
        // the app's own reference data and a database without them is not one
        // it can run on.
        $this->assertDatabaseCount('menu', 27);
        $this->assertDatabaseCount('respuesta', 29);
        $this->assertDatabaseCount('cliente', 0);
    }

    public function test_cutover_grants_a_held_customers_identity_to_the_holding_executive(): void
    {
        $this->usuarioLegado(7);
        $this->datoLegado(['id_usuario' => 7, 'id_tienda' => 1]);

        $this->transformar();

        $this->assertDatabaseHas('cliente_acceso', ['id_usuario' => 7, 'id_cliente' => 1]);
    }
}
