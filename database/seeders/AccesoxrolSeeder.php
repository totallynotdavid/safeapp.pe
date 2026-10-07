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

        $rows = [];
        foreach (Menu::query()->get(['id_menu']) as $menu) {
            $rows[] = ['id_rol' => $adminRol, 'id_menu' => $menu->id_menu];
        }

        DB::table('accesoxrol')->insert($rows);
    }
}
