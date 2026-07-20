<?php

namespace App\Http\Requests\NotaCargoMovil;

use App\Models\NcMovil;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

// The ejecutivo registers a new case. The route already gates entry on the
// notacargo.movil.registrar permiso, so this only shapes the payload.
// Server-owned fields (the owner, the supervisor, fecha_ingreso, and both
// estados) are set by the controller and are deliberately absent here.
class StoreNotaCargoMovilRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            // Resolved before the form renders, and still checked here so a
            // hand-posted id cannot attach a case to a client that does not
            // exist. Legacy took ruc and razon_social as free text.
            'id_cliente' => ['required', Rule::exists('cliente', 'id_cliente')],
            'zonal' => ['required', Rule::exists('tienda', 'id_tienda')],
            'q_lineas' => ['required', 'integer', 'min:1'],
            'cargo_fijo' => ['required', 'numeric', 'min:0'],
            'modalidad' => ['required', Rule::in(NcMovil::MODALIDADES)],
            'contacto' => ['required', 'string', 'max:100'],
            'telefono1' => ['required', 'string', 'max:50'],
            'telefono2' => ['nullable', 'string', 'max:50'],
            'nivel_negociacion' => ['required', Rule::in(NcMovil::NIVELES_NEGOCIACION)],
            'fecha_estimada' => ['required', 'date'],
            'tipo_producto' => ['required', Rule::in(NcMovil::TIPOS_PRODUCTO)],
            'etapa' => ['required', Rule::in(NcMovil::ETAPAS)],
            // Optional, unlike legacy. It is a free-text note with 1,412
            // distinct values across 1,898 rows, many of them "." or "-":
            // the shape of a required field with nothing to put in it.
            'oportunidad' => ['nullable', 'string', 'max:100'],
            'id_cartera' => ['nullable', Rule::exists('cartera', 'id_cartera')],
            'comentario_ejecutivo' => ['nullable', 'string'],
        ];
    }
}
