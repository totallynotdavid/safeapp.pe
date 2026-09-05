<?php

namespace App\Http\Requests\NotaCargoFija;

use App\Models\NcFija;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateValidacionFijaRequest extends FormRequest
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
