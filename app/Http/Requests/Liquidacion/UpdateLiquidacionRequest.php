<?php

namespace App\Http\Requests\Liquidacion;

use App\Http\Requests\Solicitud;
use App\Models\NcMovil;
use Illuminate\Validation\Rule;

class UpdateLiquidacionRequest extends Solicitud
{
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
            'comision' => ['required', 'string', 'max:50'],
            'observacion' => ['required', Rule::in(NcMovil::OBSERVACIONES)],
            'descuento' => ['required', 'string', 'max:50'],
            'fecha_liquidacion' => ['required', 'date'],
            'comentario_liquidador' => ['nullable', 'string'],
        ];
    }
}
