<?php

namespace App\Http\Requests\Gestion;

use Illuminate\Foundation\Http\FormRequest;

class AsignarLoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        // Shape only. Whether the origen still has stock is decided by the
        // locked read at assignment time, since any earlier count can be stale.
        return [
            'origen' => ['required', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'origen.required' => 'Elija un origen.',
        ];
    }
}
