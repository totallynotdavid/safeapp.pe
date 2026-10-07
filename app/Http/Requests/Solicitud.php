<?php

namespace App\Http\Requests;

use App\Models\Usuario;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Laravel types the signed-in user and the validated input as `mixed`. These
 * two accessors give the requests that read them a real type.
 */
abstract class Solicitud extends FormRequest
{
    /**
     * The validated input, minus the keys the caller sets itself.
     *
     * @param  list<string>  $excepto
     * @return array<string, mixed>
     */
    public function atributos(array $excepto = []): array
    {
        /** @var array<string, mixed> */
        return $this->safe()->except($excepto);
    }

    /**
     * A posted list of ids as integers. A missing key is an empty list.
     *
     * @return list<int>
     */
    public function enteros(string $clave): array
    {
        return array_values(array_map(
            static fn (mixed $id): int => is_scalar($id) ? (int) $id : 0,
            $this->array($clave),
        ));
    }

    /** The signed-in user. Only routes behind `auth` build these requests. */
    public function agente(): Usuario
    {
        /** @var Usuario|null $usuario */
        $usuario = $this->user();

        if (! $usuario instanceof Usuario) {
            abort(401);
        }

        return $usuario;
    }
}
