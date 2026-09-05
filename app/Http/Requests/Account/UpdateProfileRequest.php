<?php

namespace App\Http\Requests\Account;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function rules(): array
    {
        $rules = [
            'campo' => ['required', 'in:personal,fecha_nacimiento,telefono,correo'],
        ];

        return $rules
        + match ($this->input('campo')) {
            'personal' => [
                'personal' => ['required', 'string', 'max:50'],
            ],
            'fecha_nacimiento' => [
                'fecha_nacimiento' => ['nullable', 'date'],
            ],
            'telefono' => [
                'telefono' => ['nullable', 'string', 'max:20'],
            ],
            'correo' => [
                'correo' => ['nullable', 'email:rfc', 'max:100'],
            ],
            default => [],
        };
    }
}
