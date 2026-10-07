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

    /** @return array{ruc: string, razon_social: string, zonal: string|null, ...<string, int|string|null>} */
    public function cliente(): array
    {
        return [
            ...Operadores::conteos($this->input(...)),
            'ruc' => $this->string('ruc')->toString(),
            'razon_social' => $this->string('razon_social')->toString(),
            'zonal' => $this->filled('zonal') ? $this->string('zonal')->toString() : null,
        ];
    }
}
