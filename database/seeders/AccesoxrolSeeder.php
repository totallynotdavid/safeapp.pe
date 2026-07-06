<?php

namespace Database\Seeders;

use App\Models\Menu;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

// Role -> menu templates. The ADMINISTRADOR role (id 1) sees the whole tree;
// the other roles start empty and are granted per screen as modules land.
class AccesoxrolSeeder extends Seeder
{
    public function run(): void
    {
        $adminRol = 1;

        $rows = Menu::pluck('id_menu')
            ->map(fn (int $idMenu): array => ['id_rol' => $adminRol, 'id_menu' => $idMenu])
            ->all();

        DB::table('accesoxrol')->insert($rows);
    }
}
