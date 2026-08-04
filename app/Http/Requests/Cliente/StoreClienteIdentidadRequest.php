<?php

namespace App\Http\Requests\Cliente;

use Illuminate\Foundation\Http\FormRequest;

class StoreClienteIdentidadRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'ruc' => ['required', 'digits:11'],
            'razon_social' => ['required', 'string', 'max:255'],
        ];
    }
}
