<?php

namespace App\Http\Requests\NotaCargoFija;

use App\Http\Requests\Solicitud;
use App\Models\NcFija;
use Illuminate\Validation\Rule;

// The back office moves a validated case through the lifecycle and records what
// fulfilment produced. `estado` belongs to this stage alone: registro sets it to
// XINGRESAR once and never touches it again.
class UpdateBackFijaRequest extends Solicitud
{
    public function rules(): array
    {
        return [
            'estado' => ['required', Rule::in(NcFija::ESTADOS)],
            // The month a settled case counts in: a COMPLETADO case without one
            // falls out of every queue that windows by month.
            'fecha_liquidacion' => ['required_if:estado,'.NcFija::ESTADO_COMPLETADO, 'nullable', 'date'],
            'comentario_back' => ['required', 'string'],
            // Back may correct the región the ejecutivo chose.
            'zonal' => ['required', Rule::exists('tienda', 'id_tienda')],
            'nodo' => ['required', Rule::in(NcFija::NODOS)],
            'peticion' => ['nullable', 'string', 'max:100'],
            'contrata' => ['nullable', 'string', 'max:100'],
            'nasignado' => ['nullable', 'string', 'max:100'],
            'caso_sf' => ['nullable', 'string', 'max:100'],
            'ncfinanciera' => ['nullable', 'string', 'max:100'],
            'ncfe' => ['nullable', 'string', 'max:100'],
        ];
    }

    /**
     * The contractor is free text with no catalog behind it, which in four years
     * produced COMFICA and COMFICA2, JBA and JB, and a handful of stray letters.
     * Case and padding are at least not part of the problem.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('contrata')) {
            $this->merge(['contrata' => mb_strtoupper(trim((string) $this->input('contrata')))]);
        }
    }
}
