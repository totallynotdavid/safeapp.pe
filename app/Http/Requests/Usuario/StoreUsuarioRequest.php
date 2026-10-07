<?php

namespace App\Http\Requests\Usuario;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUsuarioRequest extends FormRequest
{
    // Access is gated by `can:usuarios.manage` on the route.
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'usuario' => ['required', 'string', 'max:50', Rule::unique('usuario', 'usuario')],
            'clave' => ['required', 'string', 'min:6', 'max:255'],
            'personal' => ['required', 'string', 'max:50'],
            'id_rol' => ['required', Rule::exists('rol', 'id_rol')],
            'sexo' => ['required', Rule::in(['M', 'F'])],
        ];
    }

    /** @return array{usuario: string, clave: string, personal: string, id_rol: int, sexo: string} */
    public function datos(): array
    {
        return [
            'usuario' => $this->string('usuario')->toString(),
            'clave' => $this->string('clave')->toString(),
            'personal' => $this->string('personal')->toString(),
            'id_rol' => $this->integer('id_rol'),
            'sexo' => $this->string('sexo')->toString(),
        ];
    }
}
