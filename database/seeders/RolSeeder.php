<?php

namespace Database\Seeders;

use App\Models\Rol;
use Illuminate\Database\Seeder;

class RolSeeder extends Seeder
{
    public function run(): void
    {
        Rol::query()->insert([
            ['id_rol' => 1, 'nombre_rol' => 'ADMINISTRADOR'],
            ['id_rol' => 2, 'nombre_rol' => 'SUPERVISOR'],
            ['id_rol' => 3, 'nombre_rol' => 'AGENTE'],
        ]);
    }
}
