<?php

namespace App\Cockpit;

use App\Models\AccesoCliente;
use App\Models\Cliente;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;

/** Owns the durable privacy boundary around customer identity. */
final class AccesoAClientes
{
    public static function otorgar(Cliente|int $cliente, Usuario|int $usuario): void
    {
        AccesoCliente::query()->firstOrCreate([
            'id_cliente' => $cliente instanceof Cliente ? $cliente->id_cliente : $cliente,
            'id_usuario' => $usuario instanceof Usuario ? $usuario->id_usuario : $usuario,
        ]);
    }

    /**
     * A dialog may create a genuinely new identity for the acting executive.
     * An existing but inaccessible RUC is indistinguishable from unavailable to
     * the caller; it must never become a way to acquire another person's work.
     *
     * @throws ClienteNoDisponible
     */
    public static function resolverOcrear(Usuario $usuario, string $ruc, string $razonSocial): Cliente
    {
        return DB::transaction(static function () use ($usuario, $ruc, $razonSocial): Cliente {
            $cliente = Cliente::query()->where('ruc', $ruc)->lockForUpdate()->first();

            if ($cliente !== null) {
                if (! Cliente::visiblePara($usuario)->whereKey($cliente->id_cliente)->exists()) {
                    throw new ClienteNoDisponible;
                }

                return $cliente;
            }

            $cliente = Cliente::resolver($ruc, $razonSocial);
            self::otorgar($cliente, $usuario);

            return $cliente;
        });
    }
}
