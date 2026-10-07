<?php

namespace App\Http\Requests\Quiebre;

use App\Http\Requests\Solicitud;

// The ejecutivo amends the contact block and comentario of a case they already
// registered. Ownership and the editable-state check are enforced by the policy
// in the controller; this only shapes the editable fields.
class UpdateQuiebreSeguimientoRequest extends Solicitud
{
    public function rules(): array
    {
        return [
            'numero_problema' => ['required', 'integer', 'min:1'],
            'contacto1' => ['required', 'string', 'max:100'],
            'celular1' => ['required', 'string', 'max:50'],
            'correo1' => ['required', 'email', 'max:100'],
            'contacto2' => ['nullable', 'string', 'max:100'],
            'celular2' => ['nullable', 'string', 'max:50'],
            'correo2' => ['nullable', 'email', 'max:100'],
            'comentario_ejecutivo' => ['nullable', 'string'],
        ];
    }
}
