<?php

namespace Database\Seeders;

use App\Models\Tienda;
use App\Models\Usuario;
use Illuminate\Database\Seeder;

// Dev-only: put the dev admin on a tienda so the operational screens that scope
// on tiendaAsignada() (quiebre, liquidación) have data to show. Goes through the
// same write owner the assignment screen uses, so the seed can't drift from it.
class AsignacionSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local', 'testing')) {
            return;
        }

        $admin = Usuario::query()->where('usuario', UsuarioSeeder::USUARIO)->first();
        $tienda = Tienda::query()->where('nombre', TiendaSeeder::NOMBRES[0])->first();
        if ($admin === null || $tienda === null) {
            return;
        }

        $admin->sincronizarAsignaciones([$tienda->id_tienda], $tienda->id_tienda);
    }
}
