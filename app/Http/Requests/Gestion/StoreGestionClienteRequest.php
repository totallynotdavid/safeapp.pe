<?php

namespace App\Http\Requests\Gestion;

use App\Cockpit\Operadores;
use Illuminate\Foundation\Http\FormRequest;

class StoreGestionClienteRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'ruc' => ['required', 'digits:11'],
            'razon_social' => ['required', 'string', 'max:255'],
            'zonal' => ['nullable', 'string', 'max:100'],
            ...Operadores::reglas(),
        ];
    }

    /** @return array<string, mixed> */
    public function cliente(): array
    {
        return [
            ...$this->safe()->except(array_map(Operadores::columna(...), Operadores::TODOS)),
            ...Operadores::conteos($this->input(...)),
        ];
    }
}
