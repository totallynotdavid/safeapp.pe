<?php

namespace App\Http\Requests\Rol;

use App\Http\Requests\Solicitud;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreRolRequest extends Solicitud
{
    // Access is gated by `can:roles.manage` on the route.
    public function authorize(): bool
    {
        return true;
    }

    // Role names are upper-case by convention across the seeded data. Normalize
    // before validating so the uniqueness check sees the stored form and can't
    // be bypassed by casing.
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
            'nombre_rol' => ['required', 'string', 'max:50', Rule::unique('rol', 'nombre_rol')],
            'menus' => ['array'],
            'menus.*' => [Rule::exists('menu', 'id_menu')],
        ];
    }
}
