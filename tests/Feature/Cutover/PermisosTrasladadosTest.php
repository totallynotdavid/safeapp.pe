<?php

namespace Tests\Feature\Cutover;

use App\Models\Menu;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;

/**
 * The menu is seeded rather than copied: it keeps legacy's ids so grants still
 * point at the right screen, but it drops the leaves the rewrite removed, adds
 * two it introduced, and replaces the `url` legacy filtered on with the
 * `permiso` the app checks. Every one of those is a way a grant can stop
 * meaning what it meant.
 */
class PermisosTrasladadosTest extends CutoverTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->filaLegado('rol', ['id_rol' => 1, 'nombre_rol' => 'ADMINISTRADOR']);
        $this->filaLegado('rol', ['id_rol' => 6, 'nombre_rol' => 'EJECUTIVO COMERCIAL']);
    }

    public function test_a_grant_survives_as_the_permiso_the_app_checks(): void
    {
        $this->usuarioLegado(1);
        // Legacy menu 3 was datos.php, which the app reaches as gestion.movil.
        $this->filaLegado('acceso', ['id_usuario' => 1, 'id_menu' => 3]);

        $this->transformar();

        $this->assertTrue(Usuario::query()->sole()->hasPermiso('gestion.movil'));
    }

    public function test_a_grant_naming_a_deleted_account_is_dropped(): void
    {
        // 237 of the 8,475 grants name a user that no longer exists.
        $this->usuarioLegado(1);
        $this->filaLegado('acceso', ['id_usuario' => 1, 'id_menu' => 3]);
        $this->filaLegado('acceso', ['id_usuario' => 999, 'id_menu' => 3]);

        $this->transformar();

        $this->assertSame(1, DB::table('acceso')->where('id_menu', 3)->count());
    }

    public function test_a_grant_naming_a_menu_that_never_existed_is_dropped(): void
    {
        // 32 grants point at menu -1 or 0.
        $this->usuarioLegado(1);
        $this->filaLegado('acceso', ['id_usuario' => 1, 'id_menu' => 0]);
        $this->filaLegado('acceso', ['id_usuario' => 1, 'id_menu' => -1]);

        $this->transformar();

        $this->assertSame(0, DB::table('acceso')->whereIn('id_menu', [0, -1])->count());
    }

    /**
     * Legacy menu 15 was BACK FIJA filtered to the reviewer's own store; the
     * rewrite kept only its global counterpart, menu 21. Dropping the grant is
     * safe because every holder of the filtered leaf also holds the global one,
     * which was verified across the export before the leaf was retired.
     */
    public function test_a_grant_to_a_retired_leaf_is_dropped_and_its_replacement_kept(): void
    {
        $this->usuarioLegado(1);
        $this->filaLegado('acceso', ['id_usuario' => 1, 'id_menu' => 15]);
        $this->filaLegado('acceso', ['id_usuario' => 1, 'id_menu' => 21]);

        $this->transformar();

        $usuario = Usuario::query()->sole();
        $this->assertSame(1, $usuario->menus()->count());
        $this->assertTrue($usuario->hasPermiso('notacargo.fija.back'));
    }

    /**
     * ROLES Y PERMISOS and ASIGNACION DE TIENDA are screens legacy never had,
     * so no grant carries over and nobody could reach them.
     */
    public function test_the_screens_the_rewrite_added_go_to_whoever_administers_users(): void
    {
        $this->usuarioLegado(1);
        $this->usuarioLegado(2);
        // Legacy menu 6 is mantUsuario.php, the app's usuarios.manage.
        $this->filaLegado('acceso', ['id_usuario' => 1, 'id_menu' => 6]);
        $this->filaLegado('acceso', ['id_usuario' => 2, 'id_menu' => 3]);

        $this->transformar();

        $administrador = Usuario::query()->find(1);
        $this->assertTrue($administrador->hasPermiso('roles.manage'));
        $this->assertTrue($administrador->hasPermiso('asignacion.gestionar'));

        $agente = Usuario::query()->find(2);
        $this->assertFalse($agente->hasPermiso('roles.manage'));
        $this->assertFalse($agente->hasPermiso('asignacion.gestionar'));
    }

    public function test_the_seeded_menu_keeps_the_ids_the_grants_point_at(): void
    {
        $this->transformar();

        // Not a tautology: the transform relies on this, and a renumbered menu
        // would silently repoint every grant at a different screen.
        $this->assertSame('gestion.movil', Menu::query()->find(3)?->permiso);
        $this->assertSame('notacargo.fija.back', Menu::query()->find(21)?->permiso);
    }
}
