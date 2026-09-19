<?php

namespace Tests\Feature\Cutover;

use App\Models\Usuario;
use Illuminate\Support\Facades\DB;

class CuentasTrasladadasTest extends CutoverTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->filaLegado('rol', ['id_rol' => 1, 'nombre_rol' => 'ADMINISTRADOR']);
        $this->filaLegado('rol', ['id_rol' => 6, 'nombre_rol' => 'EJECUTIVO COMERCIAL']);
    }

    public function test_the_roles_are_productions_and_not_the_seeder_three(): void
    {
        $this->usuarioLegado(1, idRol: 6);

        $this->transformar();

        $this->assertSame('EJECUTIVO COMERCIAL', Usuario::query()->sole()->rol?->nombre_rol);
    }

    public function test_the_role_for_a_retired_module_does_not_arrive(): void
    {
        // BACK OFFICE AVANZADO holds no users and no grants in production. It
        // exists for a module that was never usable, so carrying it would put an
        // empty role in front of whoever opens the ROLES screen.
        $this->filaLegado('rol', ['id_rol' => 10, 'nombre_rol' => 'BACK OFFICE AVANZADO']);
        $this->usuarioLegado(1, idRol: 6);

        $this->transformar();

        $this->assertDatabaseMissing('rol', ['id_rol' => 10]);
        $this->assertDatabaseHas('rol', ['id_rol' => 6]);
    }

    public function test_a_store_assignment_naming_a_deleted_account_is_dropped(): void
    {
        // asigna has no key of its own, so 23 rows naming deleted users are
        // invisible junk rather than a broken link.
        $this->usuarioLegado(1);
        $this->filaLegado('tienda', ['id_tienda' => 4, 'nombre' => 'REGION LIMA']);
        $this->filaLegado('asigna', ['id_tienda' => 4, 'id_usuario' => 1, 'estado' => 0]);
        $this->filaLegado('asigna', ['id_tienda' => 4, 'id_usuario' => 999, 'estado' => 0]);

        $this->transformar();

        $this->assertSame(1, DB::table('asignacion_tienda')->count());
        $this->assertSame(4, Usuario::query()->sole()->tiendaAsignada());
    }
}
