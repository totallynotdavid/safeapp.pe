<?php

namespace App\Http\Requests\Account;

use Illuminate\Foundation\Http\FormRequest;

class UpdateNotificationPreferencesRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'correo_cuenta' => ['nullable', 'boolean'],
            'correo_seguridad' => ['nullable', 'boolean'],
        ];
    }
}
