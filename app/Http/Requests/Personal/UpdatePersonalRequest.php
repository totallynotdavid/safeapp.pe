<?php

namespace App\Http\Requests\Personal;

use App\Http\Requests\Solicitud;

class UpdatePersonalRequest extends Solicitud
{
    // Access is gated by `can:personal.gestionar` on the route.
    public function authorize(): bool
    {
        return true;
    }

    /**
     * The contact fields this screen owns. Account identity (usuario, clave,
     * id_rol) belongs to the usuarios module and is not editable here.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'personal' => ['required', 'string', 'max:50'],
            'fecha_nacimiento' => ['nullable', 'date'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'correo' => ['nullable', 'email', 'max:100'],
        ];
    }
}
