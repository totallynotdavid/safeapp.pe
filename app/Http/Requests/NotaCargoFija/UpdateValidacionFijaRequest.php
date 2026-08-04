<?php

namespace App\Http\Requests\NotaCargoFija;

use App\Models\NcFija;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

// The validador passes or rejects a case. Their identity and the decision date
// come from the session and the clock, never the form.
//
// Unlike móvil, the validador only decides, not corrects.
// A disagreed figure is a rejection.
class UpdateValidacionFijaRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            // Only a decision. Leaving a case PENDIENTE is not one: the screen
            // exists to move cases out of that state.
            'validacion' => ['required', Rule::in([NcFija::VALIDACION_PROCEDE, NcFija::VALIDACION_RECHAZADO])],
            'comentario_validador' => ['required', 'string'],
        ];
    }
}
