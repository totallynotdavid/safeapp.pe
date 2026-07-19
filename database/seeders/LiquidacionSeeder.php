<?php

namespace Database\Seeders;

use App\Models\Cartera;
use App\Models\NcMovil;
use App\Models\Supervisor;
use App\Models\Usuario;
use Illuminate\Database\Seeder;

// Dev-only queue so the liquidación screen has validated cases to settle. The
// dev admin owns them as the registering ejecutivo. A spread of estados lets the
// filter be exercised, plus one case per exclusion so both gates are visible.
class LiquidacionSeeder extends Seeder
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

        $caso = ['id_usuario' => $admin->id_usuario, 'zonal' => $idTienda];
        $cartera = Cartera::query()->seleccionables($idTienda)->firstOrFail();
        $supervisor = Supervisor::query()->firstOrFail();

        foreach (NcMovil::ESTADOS_LIQUIDABLES as $estado) {
            NcMovil::factory()->estado($estado)->atribuida($cartera, $supervisor)->create($caso);

            // The unattributed twin of each estado. Most production cases have no
            // ejecutivo TDP, so the screen has to read well without one.
            NcMovil::factory()->estado($estado)->create($caso);
        }

        // The two ways a case stays out of the queue, one per gate.
        NcMovil::factory()->rechazada()->create($caso);
        NcMovil::factory()->caida()->create($caso);
    }
}
