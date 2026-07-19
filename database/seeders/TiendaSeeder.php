<?php

namespace Database\Seeders;

use App\Models\Tienda;
use Illuminate\Database\Seeder;

// Tiendas are import-fed reference data in production (no management screen).
// This seeds a small dev spread so the assignment screen has stores to pick.
class TiendaSeeder extends Seeder
{
    public const NOMBRES = ['LIMA CENTRO', 'LIMA NORTE', 'AREQUIPA'];

    public function run(): void
    {
        if (! app()->environment('local', 'testing')) {
            return;
        }

        foreach (self::NOMBRES as $nombre) {
            Tienda::factory()->create(['nombre' => $nombre]);
        }
    }
}
