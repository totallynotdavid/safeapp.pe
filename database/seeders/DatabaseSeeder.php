<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Menu, Rol, and Accesoxrol are reference data seeded in every
        // environment. The grants reference seeded menus and roles, so the order
        // matters. UsuarioSeeder skips outside local/testing, so this whole
        // chain is safe to run in production.
        $this->call([
            MenuSeeder::class,
            RolSeeder::class,
            AccesoxrolSeeder::class,
            // Reference data: the contact-disposition and product catalogs.
            RespuestaSeeder::class,
            ProductoSeeder::class,
            UsuarioSeeder::class,
            // Tiendas, then the admin's assignment, before any screen that scopes
            // on it: QuiebreSeeder reads the admin's active tienda.
            TiendaSeeder::class,
            AsignacionSeeder::class,
            // NotaCargoMovilSeeder reads both catalogs to attribute its cases.
            CarteraSeeder::class,
            SupervisorSeeder::class,
            // All read the admin's active tienda, so they follow the assignment.
            // The three case seeders also file against ClienteSeeder's clients.
            ClienteSeeder::class,
            QuiebreSeeder::class,
            NotaCargoMovilSeeder::class,
            NotaCargoFijaSeeder::class,
        ]);
    }
}
