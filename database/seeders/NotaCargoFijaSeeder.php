<?php

namespace Database\Seeders;

use App\Models\Cliente;
use App\Models\NcFija;
use App\Models\Usuario;
use Illuminate\Database\Seeder;

// Dev-only stock for the three nota-de-cargo-fija screens. The dev admin owns
// every case as the registering ejecutivo, so the registro queue (which is
// per-user) is not empty. A spread of estados lets the filters be exercised.
class NotaCargoFijaSeeder extends Seeder
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
        $clientes = Cliente::query()->limit(count(NcFija::ESTADOS))->pluck('id_cliente');
        $caso = ['id_usuario' => $admin->id_usuario, 'zonal' => $idTienda];

        foreach (NcFija::ESTADOS as $indice => $estado) {
            NcFija::factory()
                ->estado($estado)
                ->create($caso + ['id_cliente' => $clientes[$indice] ?? Cliente::factory()]);
        }

        // A rejected case, which the back office must never see.
        NcFija::factory()->rechazada()->create($caso);

        // Cases still awaiting a decision, so registro and validación both have
        // something to show on a fresh database.
        NcFija::factory()->count(3)->registrada()->create($caso);
    }
}
