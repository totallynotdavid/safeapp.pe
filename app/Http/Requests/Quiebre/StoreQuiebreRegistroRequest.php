<?php

namespace App\Http\Requests\Quiebre;

use App\Models\Quiebre;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreQuiebreRegistroRequest extends FormRequest
{
    public function rules(): array
    {
        $ticket = Quiebre::requiereTicket($this->date('fecha_activacion'))
            ? ['required']
            : ['nullable'];

        return [
            // The client must belong to the current user.
            'id_cliente' => [
                'required',
                Rule::exists('cliente_acceso', 'id_cliente')
                    ->where('id_usuario', $this->user()?->id_usuario),
            ],
            'zonal_telefonica' => [
                'required',
                Rule::exists('tienda', 'id_tienda'),
            ],
            'servicio' => [
                'required',
                Rule::in(Quiebre::SERVICIOS),
            ],
            'tipo_averia' => [
                'required',
                Rule::in(array_keys(Quiebre::TIPOS_AVERIA)),
            ],
            'numero_problema' => [
                'required',
                'integer',
                'min:1',
            ],
            'problema_equipo' => [
                'required',
                Rule::in(['SI', 'NO']),
            ],
            'detalle_equipo' => [
                Rule::requiredIf($this->input('problema_equipo') === 'SI'),
                'nullable',
                Rule::in(Quiebre::DETALLES_EQUIPO),
            ],
            'fecha_activacion' => ['required', 'date'],
            'fecha_inicio_averia' => ['nullable', 'date'],
            'numero_ticket' => [...$ticket, 'string', 'max:50'],
            'fecha_ticket_atencion' => [...$ticket, 'date'],
            'contacto1' => ['required', 'string', 'max:100'],
            'celular1' => ['required', 'string', 'max:50'],
            'correo1' => ['required', 'email', 'max:100'],
            'contacto2' => ['nullable', 'string', 'max:100'],
            'celular2' => ['nullable', 'string', 'max:50'],
            'correo2' => ['nullable', 'email', 'max:100'],
            'comentario_ejecutivo' => ['nullable', 'string'],
            'back' => ['nullable', 'string', 'max:50'],
        ];
    }
}
