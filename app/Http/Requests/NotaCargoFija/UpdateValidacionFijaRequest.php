<?php

namespace App\Http\Requests\NotaCargoFija;

use App\Models\NcFija;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

// The validador passes or rejects a case. Their identity and the decision date
// come from the session and the clock, never the form.
//
// Unlike móvil, nothing on the case is theirs to correct: legacy's fija write
// touched only the decision (its avanzado twin corrected the charge, and that
// screen is gone). A figure the validador disagrees with is a rejection.
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
