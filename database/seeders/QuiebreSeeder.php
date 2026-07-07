<?php

namespace Database\Seeders;

use App\Models\Asigna;
use App\Models\Quiebre;
use App\Models\Tienda;
use App\Models\Usuario;
use Illuminate\Database\Seeder;

// Dev-only queue so the validation screen has something to show. The dev admin
// is assigned a tienda and given a spread of quiebres across every state.
class QuiebreSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local', 'testing')) {
            return;
        }

        $admin = Usuario::query()->where('usuario', UsuarioSeeder::USUARIO)->first();
        if ($admin === null) {
            return;
        }

        $tienda = Tienda::factory()->create(['nombre' => 'LIMA CENTRO']);
        Asigna::factory()->create([
            'id_usuario' => $admin->id_usuario,
            'id_tienda' => $tienda->id_tienda,
            'estado' => 0,
        ]);

        foreach (Quiebre::ESTADOS as $estado) {
            Quiebre::factory()
                ->count(2)
                ->estado($estado)
                ->create([
                    'id_usuario' => $admin->id_usuario,
                    'zonal_telefonica' => $tienda->id_tienda,
                ]);
        }
    }
}
