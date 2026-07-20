<?php

namespace App\Http\Requests\NotaCargoMovil;

use App\Models\NcMovil;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

// The validador passes or rejects a case, and may correct the two figures the
// decision turns on before doing so. Their identity and the decision date come
// from the session and the clock, never the form.
class UpdateValidacionMovilRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            // Only a decision. Leaving a case PENDIENTE is not one: the screen
            // exists to move cases out of that state.
            'validacion' => ['required', Rule::in([NcMovil::VALIDACION_PROCEDE, NcMovil::VALIDACION_RECHAZADO])],
            'comentario_validador' => ['required', 'string'],
            'q_lineas' => ['required', 'integer', 'min:1'],
            'cargo_fijo' => ['required', 'numeric', 'min:0'],
        ];
    }
}
