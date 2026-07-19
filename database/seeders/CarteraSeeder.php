<?php

namespace Database\Seeders;

use App\Models\Cartera;
use App\Models\Tienda;
use Illuminate\Database\Seeder;

// Dev-only. No ported screen creates carteras (production maintains its 22 rows
// by hand), so this gives every tienda a few executives for the notas de cargo
// to be attributed to, plus one retired executive per tienda so the
// seleccionables filter has something to exclude.
class CarteraSeeder extends Seeder
{
    private const POR_TIENDA = 3;

    public function run(): void
    {
        if (! app()->environment('local', 'testing')) {
            return;
        }

        foreach (Tienda::query()->pluck('id_tienda') as $idTienda) {
            Cartera::factory()->count(self::POR_TIENDA)->create(['id_tienda' => $idTienda]);
            Cartera::factory()->retirada()->create(['id_tienda' => $idTienda]);
        }
    }
}
