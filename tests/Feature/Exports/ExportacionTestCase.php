<?php

namespace Tests\Feature\Exports;

use App\Models\Menu;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\LeeLibro;
use Tests\TestCase;

/**
 * Every export answers to the permiso of the screen it is a view of and carries
 * no permiso of its own, so the exports of one screen are always reached through
 * the same grant that lists it.
 */
abstract class ExportacionTestCase extends TestCase
{
    use LeeLibro;
    use RefreshDatabase;

    protected const RUC = '20123456789';

    protected const RUC_AJENO = '20999999999';

    protected function usuarioCon(string $permiso): Usuario
    {
        $user = Usuario::factory()->createOne();
        $user->menus()->attach(Menu::factory()->create(['permiso' => $permiso]));

        return $user;
    }
}
