<?php

namespace App\Http\Requests\Usuario;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUsuarioRequest extends FormRequest
{
    // Access is gated by `can:usuarios.manage` on the route.
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Username and role stay fixed after creation: the username is the login
     * identity, and role is only a template the user's acceso rows were copied
     * from at creation and may since have drifted from on purpose.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'personal' => ['required', 'string', 'max:50'],
            // Blank keeps the current password; a present value replaces it.
            'clave' => ['nullable', 'string', 'min:6', 'max:255'],
        ];
    }
}
