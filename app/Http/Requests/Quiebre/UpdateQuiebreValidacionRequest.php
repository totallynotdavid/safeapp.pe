<?php

namespace App\Http\Requests\Quiebre;

use App\Http\Requests\Solicitud;
use App\Models\Quiebre;
use Illuminate\Validation\Rule;

class UpdateQuiebreValidacionRequest extends Solicitud
{
    // Route middleware (`can:quiebre.validar`) and the QuiebrePolicy authorize
    // access; this request only shapes the input.
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'estado' => ['required', Rule::in(Quiebre::ESTADOS)],
            'caso_sf' => ['required', 'string', 'max:50'],
            'comentario_validador' => ['nullable', 'string'],
        ];
    }
}
