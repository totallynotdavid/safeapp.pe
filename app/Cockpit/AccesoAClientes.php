<?php

namespace App\Cockpit;

use App\Models\AccesoCliente;
use App\Models\Cliente;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;

final class AccesoAClientes
{
    public static function otorgar(Cliente|int $cliente, Usuario|int $usuario): void
    {
        $clienteId = $cliente instanceof Cliente
            ? $cliente->id_cliente
            : $cliente;

        $usuarioId = $usuario instanceof Usuario
            ? $usuario->id_usuario
            : $usuario;

        AccesoCliente::query()->firstOrCreate([
            'id_cliente' => $clienteId,
            'id_usuario' => $usuarioId,
        ]);
    }

    /** @throws ClienteNoDisponible */
    public static function resolverOCrear(
        Usuario $usuario,
        string $ruc,
        string $razonSocial,
    ): Cliente {
        return DB::transaction(
            static function () use ($usuario, $ruc, $razonSocial): Cliente {
                $cliente = Cliente::query()
                    ->where('ruc', $ruc)
                    ->lockForUpdate()
                    ->first();

                if ($cliente !== null) {
                    $esVisible = Cliente::visiblePara($usuario)
                        ->whereKey($cliente->id_cliente)
                        ->exists();

                    // A RUC that exists but is not visible must not grant access to
                    // another user's client.
                    if (! $esVisible) {
                        throw new ClienteNoDisponible;
                    }

                    return $cliente;
                }

                $cliente = Cliente::resolver($ruc, $razonSocial);

                self::otorgar($cliente, $usuario);

                return $cliente;
            },
        );
    }
}
