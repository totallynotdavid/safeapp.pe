<?php

namespace App\Http\Requests\NotaCargoFija;

use App\Http\Requests\Solicitud;
use App\Models\NcFija;
use Illuminate\Validation\Rule;

// The ejecutivo amends their own case while it is still awaiting validación.
// This is a narrower set than registro: the client, the región and the product
// are what the case is, and changing them would make it a different case, so
// they are fixed at registration. Whether the case may be amended at all is a
// policy question, not a validation one, and lives in NcFijaPolicy.
class UpdateNotaCargoFijaRequest extends Solicitud
{
    public function rules(): array
    {
        return [
            'contacto' => ['required', 'string', 'max:100'],
            'telefono1' => ['required', 'string', 'max:50'],
            'telefono2' => ['nullable', 'string', 'max:50'],
            'direccion' => ['required', 'string'],
            'coordenadas' => ['nullable', 'string', 'max:100'],
            'tecnologia' => ['required', Rule::in(NcFija::TECNOLOGIAS)],
            'comentario' => ['nullable', 'string'],
        ];
    }
}
