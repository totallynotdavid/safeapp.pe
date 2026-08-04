<?php

namespace App\Http\Requests\Cliente;

use Illuminate\Foundation\Http\FormRequest;

class ImportarClientesRequest extends FormRequest
{
    // 20 MB is roughly 200k client rows, well past any single campaign file.
    // The cap exists because validation reads the whole file before writing
    // anything, so an unbounded upload is an unbounded request.
    private const MAX_KB = 20_480;

    /**
     * @return array<string, string>
     */
    public function rules(): array
    {
        // `txt` is allowed alongside CSV because a CSV renamed by a mail
        // client or exported from a terminal commonly arrives with that
        // extension. XLSX is supported through the same parser contract.
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
