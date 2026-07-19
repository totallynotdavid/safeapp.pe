<?php

namespace App\Http\Requests\Cliente;

use App\Cockpit\Operadores;
use App\Models\Cliente;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreClienteRequest extends FormRequest
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
        $rules = [
            // Peruvian RUC: 11 digits. Unique because every join in the app goes
            // through it, so a duplicate would silently merge two clients' call
            // histories.
            'ruc' => ['required', 'digits:11', Rule::unique('datos', 'ruc')],
            'razon_social' => ['required', 'string', 'max:255'],
            'zonal' => ['nullable', 'string', 'max:100'],
            // Which queue will work this client. Required and limited to the
            // segments that have a screen: a client filed under anything else
            // can never be worked again.
            'tipo_data' => ['required', Rule::in(Cliente::TIPOS_DATA)],
        ];

        foreach (Operadores::TODOS as $operador) {
            $rules[Operadores::columna($operador)] = ['nullable', 'integer', 'min:0', 'max:100000'];
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'ruc.digits' => 'El RUC debe tener 11 dígitos.',
            'ruc.unique' => 'Ese RUC ya está registrado.',
            'tipo_data.required' => 'Elija el segmento que trabajará al cliente.',
        ];
    }

    /**
     * The client as AltaDeClientes::registrar() expects it: line counts always
     * present so an omitted input reads as zero rather than null.
     *
     * @return array<string, mixed>
     */
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
