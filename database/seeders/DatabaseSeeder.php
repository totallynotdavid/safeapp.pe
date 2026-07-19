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
            // Contact-disposition catalog the GESTION dropdown reads; reference
            // data like the menus and roles above.
            RespuestaSeeder::class,
            UsuarioSeeder::class,
            // Tiendas, then the admin's assignment, before any screen that scopes
            // on it: QuiebreSeeder reads the admin's active tienda.
            TiendaSeeder::class,
            AsignacionSeeder::class,
            // Both read the admin's active tienda, so they follow the assignment.
            ClienteSeeder::class,
            QuiebreSeeder::class,
            LiquidacionSeeder::class,
            // After every user-creating seeder: it back-fills their supervisor.
            SupervisorSeeder::class,
        ]);
    }
}
