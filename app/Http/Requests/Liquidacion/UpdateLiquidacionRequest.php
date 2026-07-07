<?php

namespace App\Http\Requests\Liquidacion;

use App\Models\NcMovil;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLiquidacionRequest extends FormRequest
{
    // Route middleware (`can:liquidacion.gestionar`) authorizes access; this
    // request only shapes the input.
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        // comision and descuento stay strings to match the free-form legacy
        // columns; observación is a closed set and the settlement date is required.
        return [
            'comision' => ['required', 'string', 'max:50'],
            'observacion' => ['required', Rule::in(NcMovil::OBSERVACIONES)],
            'descuento' => ['required', 'string', 'max:50'],
            'fecha_liquidacion' => ['required', 'date'],
            'comentario_liquidador' => ['nullable', 'string'],
        ];
    }
}
