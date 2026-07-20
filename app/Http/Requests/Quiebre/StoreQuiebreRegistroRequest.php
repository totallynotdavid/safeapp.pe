<?php

namespace App\Http\Requests\Quiebre;

use App\Models\Quiebre;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

// The ejecutivo registers a new case. The route already gates entry on the
// quiebre.registrar permiso, so this only shapes the payload. Server-owned
// fields (owner, estado, fecha_registro, computed ticket_atencion) are added by
// the controller and deliberately absent here.
class StoreQuiebreRegistroRequest extends FormRequest
{
    public function rules(): array
    {
        $ticket = Quiebre::requiereTicket($this->date('fecha_activacion'))
            ? ['required']
            : ['nullable'];

        return [
            // The client is resolved before the form renders, so this arrives as
            // an id and must still name a real row: a hand-posted id_cliente
            // would otherwise attach the case to nothing. Legacy took ruc and
            // razon_social as free text here, which let the same RUC carry
            // different names across quiebres.
            'id_cliente' => ['required', Rule::exists('cliente', 'id_cliente')],
            'zonal_telefonica' => ['required', Rule::exists('tienda', 'id_tienda')],
            'servicio' => ['required', Rule::in(Quiebre::SERVICIOS)],
            'tipo_averia' => ['required', Rule::in(array_keys(Quiebre::TIPOS_AVERIA))],
            'numero_problema' => ['required', 'integer', 'min:1'],
            'problema_equipo' => ['required', Rule::in(['SI', 'NO'])],
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
