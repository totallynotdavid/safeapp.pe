<?php

namespace App\Http\Requests\NotaCargoFija;

use App\Models\NcFija;
use App\Models\Producto;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreNotaCargoFijaRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'id_cliente' => [
                'required',
                Rule::exists('cliente_acceso', 'id_cliente')
                    ->where('id_usuario', $this->user()?->id_usuario),
            ],

            'zonal' => [
                'required',
                Rule::exists('tienda', 'id_tienda'),
            ],

            // Cases keep the product strings, so later catalog changes do not
            // rewrite the product originally sold.
            'id_producto' => [
                'required',
                Rule::exists('producto', 'id')
                    ->where('tipo', Producto::FIJA)
                    ->where('estado', Producto::OFRECIBLE),
            ],

            // Blank uses the catalog price; agents may override it when needed.
            'cargo_fijo' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'modalidad' => [
                'required',
                Rule::in(NcFija::MODALIDADES),
            ],

            'contacto' => [
                'required',
                'string',
                'max:100',
            ],

            'telefono1' => [
                'required',
                'string',
                'max:50',
            ],

            // Most historical cases have no second phone number.
            'telefono2' => [
                'nullable',
                'string',
                'max:50',
            ],

            'direccion' => [
                'required',
                'string',
            ],

            'coordenadas' => [
                'nullable',
                'string',
                'max:100',
            ],

            'tecnologia' => [
                'required',
                Rule::in(NcFija::TECNOLOGIAS),
            ],

            'comentario' => [
                'nullable',
                'string',
            ],
        ];
    }
}
