<?php

namespace Database\Seeders;

use App\Models\Cartera;
use App\Models\Tienda;
use Illuminate\Database\Seeder;

// Dev-only: no screen creates carteras. Seeds a few executives to attribute
// notas de cargo to, plus retired ones so the seleccionables filter has
// something to exclude.
class CarteraSeeder extends Seeder
{
    private const POR_TIENDA = 3;

    public function run(): void
    {
        if (! app()->environment('local', 'testing')) {
            return;
        }

        foreach (Tienda::query()->get(['id_tienda']) as $tienda) {
            Cartera::factory()->count(self::POR_TIENDA)->create(['id_tienda' => $tienda->id_tienda]);
            Cartera::factory()->retirada()->create(['id_tienda' => $tienda->id_tienda]);
        }
    }
}
