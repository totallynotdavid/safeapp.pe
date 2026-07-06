<?php

namespace Database\Seeders;

use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UsuarioSeeder extends Seeder
{
    public const USUARIO = 'admin';

    public const CLAVE = 'admin';

    public function run(): void
    {
        // Synthetic dev login, never a production credential. Guarding to
        // local/testing keeps `db:seed --force` on a real deploy from minting a
        // known-password admin. Production admins are provisioned out of band.
        if (! app()->environment('local', 'testing')) {
            return;
        }

        $admin = Usuario::factory()->clave(self::CLAVE)->create([
            'usuario' => self::USUARIO,
            'id_rol' => 1,
            'personal' => 'ADMINISTRADOR DEV',
        ]);

        // acceso holds the per-user grants the permission gate reads; roles are
        // only templates. Copy the admin role's rows into the user's grants.
        $menuIds = DB::table('accesoxrol')->where('id_rol', 1)->pluck('id_menu');
        $admin->menus()->attach($menuIds);
    }
}
