<?php

namespace App\Http\Requests\Rol;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdateRolRequest extends FormRequest
{
    // Access is gated by `can:roles.manage` on the route.
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['nombre_rol' => Str::upper((string) $this->input('nombre_rol'))]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'nombre_rol' => [
                'required',
                'string',
                'max:50',
                Rule::unique('rol', 'nombre_rol')->ignore($this->route('rol')),
            ],
            'menus' => ['array'],
            'menus.*' => [Rule::exists('menu', 'id_menu')],
        ];
    }
}
