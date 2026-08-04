<?php

namespace App\Http\Requests\Account;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function rules(): array
    {
        return match ($this->input('campo')) {
            'personal' => [
                'campo' => ['required', 'in:personal'],
                'personal' => ['required', 'string', 'max:50'],
            ],
            'fecha_nacimiento' => [
                'campo' => ['required', 'in:fecha_nacimiento'],
                'fecha_nacimiento' => ['nullable', 'date'],
            ],
            'telefono' => [
                'campo' => ['required', 'in:telefono'],
                'telefono' => ['nullable', 'string', 'max:20'],
            ],
            'correo' => [
                'campo' => ['required', 'in:correo'],
                'correo' => ['nullable', 'email:rfc', 'max:100'],
            ],
            default => ['campo' => ['required', 'in:personal,fecha_nacimiento,telefono,correo']],
        };
    }
}
