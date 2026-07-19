<?php

namespace App\Http\Requests\Gestion;

use App\Cockpit\Operadores;
use App\Models\Cliente;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreGestionRequest extends FormRequest
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
            'telefono' => ['required', 'string', 'max:30'],
            // The channel is deliberately not accepted from the request. It is
            // read from the chosen respuesta, so it cannot contradict it.
            'id_respuesta' => ['required', 'integer', Rule::exists('respuesta', 'id_respuesta')],
            'nombre_contacto' => ['nullable', 'string', 'max:150'],
            'correo' => ['nullable', 'email', 'max:150'],
            'comentario' => ['nullable', 'string', 'max:1000'],
        ];

        // Built from the carrier list so adding a carrier needs no edit here.
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
            'id_respuesta.exists' => 'Elija una respuesta válida.',
        ];
    }

    /**
     * The contact payload as Cliente::registrarGestion() expects it: line counts
     * always present so an omitted input reads as zero rather than null.
     *
     * @return array<string, mixed>
     */
    public function contacto(): array
    {
        $contacto = $this->safe()->except(array_map(Operadores::columna(...), Operadores::TODOS));

        foreach (Operadores::TODOS as $operador) {
            $columna = Operadores::columna($operador);
            $contacto[$columna] = (int) $this->input($columna, 0);
        }

        return $contacto;
    }
}
