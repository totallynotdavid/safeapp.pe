<?php

namespace App\Http\Requests\NotaCargoFija;

use App\Http\Requests\Solicitud;
use App\Models\NcFija;
use Illuminate\Validation\Rule;

class UpdateValidacionFijaRequest extends Solicitud
{
    public function rules(): array
    {
        return [
            'validacion' => [
                'required',
                Rule::in([
                    NcFija::VALIDACION_PROCEDE,
                    NcFija::VALIDACION_RECHAZADO,
                ]),
            ],
            'comentario_validador' => ['required', 'string'],
        ];
    }
}
