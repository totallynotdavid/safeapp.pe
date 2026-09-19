<?php

namespace App\Http\Requests\Account;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePasswordRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'clave_actual' => ['required', 'string'],
            'clave' => [
                'required',
                'string',
                'min:8',
                'max:255',
                'confirmed',
                'different:clave_actual',
            ],
        ];
    }
}
