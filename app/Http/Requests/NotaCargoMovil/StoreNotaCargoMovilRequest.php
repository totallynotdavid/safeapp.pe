<?php

namespace App\Http\Requests\NotaCargoMovil;

use App\Models\NcMovil;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreNotaCargoMovilRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'id_cliente' => [
                'required',
                Rule::exists('cliente_acceso', 'id_cliente')
                    ->where('id_usuario', $this->user()?->id_usuario),
            ],
            'zonal' => ['required', Rule::exists('tienda', 'id_tienda')],
            'q_lineas' => ['required', 'integer', 'min:1'],
            'cargo_fijo' => ['required', 'numeric', 'min:0'],
            'modalidad' => ['required', Rule::in(NcMovil::MODALIDADES)],
            'contacto' => ['required', 'string', 'max:100'],
            'telefono1' => ['required', 'string', 'max:50'],
            'telefono2' => ['nullable', 'string', 'max:50'],
            'nivel_negociacion' => [
                'required',
                Rule::in(NcMovil::NIVELES_NEGOCIACION),
            ],
            'fecha_estimada' => ['required', 'date'],
            'tipo_producto' => [
                'required',
                Rule::in(NcMovil::TIPOS_PRODUCTO),
            ],
            'etapa' => ['required', Rule::in(NcMovil::ETAPAS)],
            'oportunidad' => ['nullable', 'string', 'max:100'],
            'id_cartera' => [
                'nullable',
                Rule::exists('cartera', 'id_cartera'),
            ],
            'comentario_ejecutivo' => ['nullable', 'string'],
        ];
    }
}
