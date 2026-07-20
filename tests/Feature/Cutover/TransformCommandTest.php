<?php

namespace Tests\Feature\Cutover;

use App\Models\Cliente;
use App\Models\Dato;

/**
 * The command's contract with whoever runs it on the night.
 */
class TransformCommandTest extends CutoverTestCase
{
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
            ->artisan('cutover:transform')
            ->expectsOutputToContain('migrate:fresh')
            ->assertFailed();

        $this->assertSame(1, Cliente::query()->count());
        $this->assertSame(1, Dato::query()->count());
    }

    public function test_an_empty_export_still_leaves_the_catalogs_the_app_owns(): void
    {
        $this->transformar();

        // Nothing to carry across, but the menu and the disposition catalog are
        // the app's own reference data and a database without them is not one
        // it can run on.
        $this->assertDatabaseCount('menu', 28);
        $this->assertDatabaseCount('respuesta', 29);
        $this->assertDatabaseCount('cliente', 0);
    }
}
