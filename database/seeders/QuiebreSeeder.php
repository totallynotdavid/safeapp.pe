<?php

namespace Database\Seeders;

use App\Models\Cliente;
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

        // Against clients ClienteSeeder already created, so the joined name on
        // every quiebre screen belongs to a client the dev can also open in
        // gestión. Falling back to a fresh client keeps the seeder working if
        // the client seed ever shrinks below the number of estados.
        $clientes = Cliente::query()->limit(count(Quiebre::ESTADOS))->pluck('id_cliente');

        foreach (Quiebre::ESTADOS as $indice => $estado) {
            Quiebre::factory()
                ->count(2)
                ->estado($estado)
                ->create([
                    'id_cliente' => $clientes[$indice] ?? Cliente::factory(),
                    'id_usuario' => $admin->id_usuario,
                    'zonal_telefonica' => $idTienda,
                ]);
        }
    }
}
