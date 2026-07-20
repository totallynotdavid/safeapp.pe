<?php

namespace Database\Seeders;

use App\Models\Cartera;
use App\Models\Tienda;
use Illuminate\Database\Seeder;

// Dev-only. No ported screen creates carteras (production maintains its 22 rows
// by hand), so this gives the catalog a few executives for the notas de cargo to
// be attributed to, plus retired ones so the seleccionables filter has something
// to exclude. The id_tienda spread is deliberate and no longer a selection gate:
// all 22 production carteras sit in one región, which is why filtering on it
// left six of the seven regiones with an empty dropdown.
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
