<?php

namespace App\Http\Requests\NotaCargoMovil;

use App\Models\NcMovil;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

// The ejecutivo amends their own case while it is still awaiting validación.
// This is a narrower set than registro: the client, the región and the modalidad
// are what the case is, and changing them would make it a different case, so
// they are fixed at registration. Whether the case may be amended at all is a
// policy question, not a validation one, and lives in NcMovilPolicy.
class UpdateNotaCargoMovilRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'q_lineas' => ['required', 'integer', 'min:1'],
            'cargo_fijo' => ['required', 'numeric', 'min:0'],
            'telefono1' => ['required', 'string', 'max:50'],
            'telefono2' => ['nullable', 'string', 'max:50'],
            'nivel_negociacion' => ['required', Rule::in(NcMovil::NIVELES_NEGOCIACION)],
            'fecha_estimada' => ['required', 'date'],
            'etapa' => ['required', Rule::in(NcMovil::ETAPAS)],
            'oportunidad' => ['nullable', 'string', 'max:100'],
            'comentario_ejecutivo' => ['nullable', 'string'],
        ];
    }
}
