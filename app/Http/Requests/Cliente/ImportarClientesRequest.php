<?php

namespace App\Http\Requests\Cliente;

use Illuminate\Foundation\Http\FormRequest;

class ImportarClientesRequest extends FormRequest
{
    // Validation loads the entire file before writing, so uploads are capped.
    private const MAX_KB = 20_480;

    /**
     * @return array<string, string>
     */
    public function rules(): array
    {
        // Accept .txt because CSV files may arrive with that extension.
        return [
            'archivo' => 'required|file|mimes:csv,txt,xlsx|max:'.self::MAX_KB,
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return ['archivo' => 'archivo CSV o XLSX'];
    }
}
