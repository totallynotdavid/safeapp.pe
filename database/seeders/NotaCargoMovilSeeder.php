<?php

namespace Database\Seeders;

use App\Models\Cartera;
use App\Models\Cliente;
use App\Models\NcMovil;
use App\Models\Supervisor;
use App\Models\Usuario;
use Illuminate\Database\Seeder;

// Dev-only stock for the four nota-de-cargo-móvil screens. The dev admin owns
// every case as the registering ejecutivo, so the registro queue (which is
// per-user) is not empty. A spread of estados lets the filters be exercised,
// plus one case per liquidación exclusion so both gates are visible.
class NotaCargoMovilSeeder extends Seeder
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

        // Against clients ClienteSeeder already created, so every case on screen
        // names a client the dev can also open in gestión.
        $clientes = Cliente::query()
            ->limit(count(NcMovil::ESTADOS_LIQUIDABLES) + 3)
            ->pluck('id_cliente');
        $caso = ['id_usuario' => $admin->id_usuario, 'zonal' => $idTienda];
        $cartera = Cartera::seleccionables()->firstOrFail();
        $supervisor = Supervisor::query()->firstOrFail();

        foreach (NcMovil::ESTADOS_LIQUIDABLES as $indice => $estado) {
            $cliente = ['id_cliente' => $clientes[$indice] ?? Cliente::factory()];

            NcMovil::factory()
                ->estado($estado)
                ->atribuida($cartera, $supervisor)
                ->create($caso + $cliente);

            // The unattributed twin of each estado. Most production cases have no
            // ejecutivo TDP, so the screen has to read well without one.
            NcMovil::factory()
                ->estado($estado)
                ->create($caso + $cliente);
        }

        // The two ways a case stays out of the liquidación queue, one per gate.
        NcMovil::factory()->rechazada()->create($caso);
        NcMovil::factory()->caida()->create($caso);

        // One case at each earlier stage, so registro, validación and back all
        // have something to show on a fresh database.
        NcMovil::factory()->count(3)->registrada()->create($caso);
    }
}
