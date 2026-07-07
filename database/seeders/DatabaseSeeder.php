<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Menu, Rol, and Accesoxrol are reference data seeded in every
        // environment; order matters because the grants reference seeded menus
        // and roles. UsuarioSeeder adds the dev admin and skips outside
        // local/testing, so this whole chain is safe to run in production.
        $this->call([
            MenuSeeder::class,
            RolSeeder::class,
            AccesoxrolSeeder::class,
            UsuarioSeeder::class,
            QuiebreSeeder::class,
        ]);
    }
}
