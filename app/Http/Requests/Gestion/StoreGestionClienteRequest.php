<?php

namespace App\Http\Requests\Gestion;

use App\Cockpit\Operadores;
use Illuminate\Foundation\Http\FormRequest;

class StoreGestionClienteRequest extends FormRequest
{
    public function rules(): array
    {
        $rules = [
            'ruc' => ['required', 'digits:11'],
            'razon_social' => ['required', 'string', 'max:255'],
            'zonal' => ['nullable', 'string', 'max:100'],
        ];

        foreach (Operadores::TODOS as $operador) {
            $rules[Operadores::columna($operador)] = ['nullable', 'integer', 'min:0', 'max:100000'];
        }

        return $rules;
    }

    /** @return array<string, mixed> */
    public function cliente(): array
    {
        $datos = $this->safe()->except(array_map(Operadores::columna(...), Operadores::TODOS));

        foreach (Operadores::TODOS as $operador) {
            $columna = Operadores::columna($operador);
            $datos[$columna] = (int) $this->input($columna, 0);
        }

        return $datos;
    }
}
