<?php

namespace App\Http\Requests\NotaCargoMovil;

use App\Http\Requests\Solicitud;
use App\Models\NcMovil;
use Illuminate\Validation\Rule;

// The back office moves a validated case through the lifecycle and records what
// fulfilment produced. `estado` belongs to this stage alone: registro sets it to
// PENDIENTE once and never touches it again, and liquidación leaves it alone.
class UpdateBackMovilRequest extends Solicitud
{
    public function rules(): array
    {
        return [
            'estado' => ['required', Rule::in(NcMovil::ESTADOS)],
            'fecha_actualizacion' => ['required', 'date'],
            'comentario_back' => ['required', 'string'],
            // Back may correct the región the ejecutivo chose.
            'zonal' => ['required', Rule::exists('tienda', 'id_tienda')],
            'id_cartera' => ['nullable', Rule::exists('cartera', 'id_cartera')],
            'etapa' => ['required', Rule::in(NcMovil::ETAPAS)],
            'nodo' => ['required', 'string', 'max:100'],
            'oportunidad' => ['nullable', 'string', 'max:100'],
            'casofwa' => ['nullable', 'string', 'max:100'],
            'ciclo' => ['nullable', 'string', 'max:50'],
            'cuenta_financiera' => ['nullable', 'string', 'max:100'],
        ];
    }
}
