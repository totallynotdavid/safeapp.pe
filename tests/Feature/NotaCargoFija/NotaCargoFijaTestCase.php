<?php

namespace Tests\Feature\NotaCargoFija;

use App\Cockpit\AccesoAClientes;
use App\Models\Cliente;
use App\Models\Menu;
use App\Models\NcFija;
use App\Models\Producto;
use App\Models\Tienda;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// None of the three stages scopes by tienda, so the fixture never places the user
// in one.
abstract class NotaCargoFijaTestCase extends TestCase
{
    use RefreshDatabase;

    protected const RUC = '20123456789';

    protected ?Usuario $usuarioActual = null;

    protected function usuarioCon(string $permiso): Usuario
    {
        $user = Usuario::factory()->createOne();
        $user->menus()->attach(Menu::factory()->create(['permiso' => $permiso]));
        $this->usuarioActual = $user;

        return $user;
    }

    protected function cliente(string $ruc = self::RUC): Cliente
    {
        $cliente = Cliente::factory()->create(['ruc' => $ruc]);
        if ($this->usuarioActual !== null) {
            AccesoAClientes::otorgar($cliente, $this->usuarioActual);
        }

        return $cliente;
    }

    protected function producto(): Producto
    {
        return Producto::factory()->create([
            'subtipo' => 'DUO BA + TV',
            'descripcion' => '400MB',
            'cargo_fijo' => '169.90',
        ]);
    }

    /**
     * A case as the ejecutivo just filed it: awaiting validación, so it is what
     * the registro and validación screens work with.
     */
    protected function casoPendiente(Usuario $ejecutivo): NcFija
    {
        return NcFija::factory()
            ->registrada()
            ->deRuc(self::RUC)
            ->create(['id_usuario' => $ejecutivo->id_usuario]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    protected function payload(Cliente $cliente, Producto $producto, array $overrides = []): array
    {
        return array_merge([
            'id_cliente' => $cliente->id_cliente,
            'zonal' => Tienda::factory()->create()->id_tienda,
            'id_producto' => $producto->id,
            'modalidad' => 'ALTA',
            'contacto' => 'ANA VIGIL',
            'telefono1' => '987654321',
            'direccion' => 'AV. LA MARINA 1234',
            'tecnologia' => 'FTTH',
            'comentario' => 'CLIENTE INTERESADO',
        ], $overrides);
    }
}
