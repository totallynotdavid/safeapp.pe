<?php

namespace Database\Seeders;

use App\Models\Quiebre;
use App\Models\Usuario;
use Illuminate\Database\Seeder;

// Dev-only queue so the validation screen has something to show: a spread of
// quiebres across every state for the dev admin, scoped to the tienda the
// AsignacionSeeder already put them on.
class QuiebreSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local', 'testing')) {
            return;
        }

        $admin = Usuario::query()->where('usuario', UsuarioSeeder::USUARIO)->first();
        $idTienda = $admin?->tiendaAsignada();
        if ($idTienda === null) {
            return;
        }

        foreach (Quiebre::ESTADOS as $estado) {
            Quiebre::factory()
                ->count(2)
                ->estado($estado)
                ->create([
                    'id_usuario' => $admin->id_usuario,
                    'zonal_telefonica' => $idTienda,
                ]);
        }
    }
}
