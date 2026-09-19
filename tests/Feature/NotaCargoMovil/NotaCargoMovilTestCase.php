<?php

namespace Tests\Feature\NotaCargoMovil;

use App\Cockpit\AccesoAClientes;
use App\Models\Cliente;
use App\Models\Menu;
use App\Models\NcMovil;
use App\Models\Tienda;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// None of the three stages scopes by tienda, so the fixture never places the user
// in one.
abstract class NotaCargoMovilTestCase extends TestCase
{
    use RefreshDatabase;

    protected const RUC = '20123456789';

    protected ?Usuario $usuarioActual = null;

    protected function usuarioCon(string $permiso): Usuario
    {
        $user = Usuario::factory()->create();
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

    /**
     * A case as the ejecutivo just filed it: awaiting validación, so it is what
     * the registro and validación screens work with.
     */
    protected function casoPendiente(Usuario $ejecutivo): NcMovil
    {
        return NcMovil::factory()
            ->registrada()
            ->deRuc(self::RUC)
            ->create(['id_usuario' => $ejecutivo->id_usuario]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    protected function payload(Cliente $cliente, array $overrides = []): array
    {
        return array_merge([
            'id_cliente' => $cliente->id_cliente,
            'zonal' => Tienda::factory()->create()->id_tienda,
            'q_lineas' => 5,
            'cargo_fijo' => '120.50',
            'modalidad' => 'PORTABILIDAD',
            'contacto' => 'ANA VIGIL',
            'telefono1' => '987654321',
            'nivel_negociacion' => '75',
            'fecha_estimada' => now()->addMonth()->toDateString(),
            'tipo_producto' => 'M4',
            'etapa' => 'F2-GESTION',
            'comentario_ejecutivo' => 'CLIENTE INTERESADO',
        ], $overrides);
    }
}
