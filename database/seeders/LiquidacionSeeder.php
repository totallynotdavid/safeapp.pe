<?php

namespace Database\Seeders;

use App\Models\NcMovil;
use App\Models\Usuario;
use Illuminate\Database\Seeder;

// Dev-only queue so the liquidación screen has validated cases to settle. The
// dev admin owns them as the registering ejecutivo. A spread of estados lets the
// filter be exercised, plus one rejected case that must stay out of the queue.
class LiquidacionSeeder extends Seeder
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

        foreach (NcMovil::ESTADOS_LIQUIDACION as $estado) {
            NcMovil::factory()
                ->count(2)
                ->estado($estado)
                ->create(['id_usuario' => $admin->id_usuario]);
        }

        NcMovil::factory()->improcede()->create(['id_usuario' => $admin->id_usuario]);
    }
}
