<?php

namespace App\Http\Requests\Asignacion;

use App\Http\Requests\Solicitud;
use Illuminate\Validation\Rule;

class UpdateAsignacionRequest extends Solicitud
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * A user may hold zero tiendas (unassigned, sees nothing). When they hold
     * any, exactly one must be marked active, and it has to be one of the chosen
     * tiendas so the active row can never point outside the assignment set.
     */
    public function rules(): array
    {
        return [
            'tiendas' => ['nullable', 'array'],
            'tiendas.*' => ['integer', Rule::exists('tienda', 'id_tienda')],
            'activa' => ['nullable', 'integer', 'required_with:tiendas', Rule::in($this->enteros('tiendas'))],
        ];
    }

    public function messages(): array
    {
        return [
            'activa.required_with' => 'Elija la tienda activa.',
            'activa.in' => 'La tienda activa debe ser una de las tiendas asignadas.',
        ];
    }
}
