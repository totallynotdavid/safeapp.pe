<?php

namespace App\Http\Requests\NotaCargoFija;

use App\Models\NcFija;
use App\Models\Producto;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

// The ejecutivo registers a new case. The route already gates entry on the
// notacargo.fija.registrar permiso, so this only shapes the payload.
// Server-owned fields (the owner, the supervisor, fecha_ingreso, both estados
// and the SINNODO default) are set by the controller and are deliberately
// absent here.
class StoreNotaCargoFijaRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            // Resolved before the form renders, and still checked here so a
            // hand-posted id cannot attach a case to a client that does not
            // exist. Legacy took ruc and razon_social as free text.
            'id_cliente' => ['required', Rule::exists('cliente', 'id_cliente')],
            'zonal' => ['required', Rule::exists('tienda', 'id_tienda')],
            // What was sold, chosen from the catalog rather than typed. Legacy
            // asked for subtipo and descripción as two dependent free-ish
            // dropdowns fed by three AJAX round trips, and let the pair be
            // anything: 335 of the 5,970 rows name a product the catalog does
            // not have, including 'COMPLETA BA + TV' and 'FTTTHH'-grade typos.
            // The case still stores the strings, not this id (see the model),
            // so a catalog that later changes cannot rewrite a past sale.
            'id_producto' => [
                'required',
                Rule::exists('producto', 'id')
                    ->where('tipo', Producto::FIJA)
                    ->where('estado', Producto::OFRECIBLE),
            ],
            // Blank means "what the catalog says". The price is overridable
            // because it genuinely moves: 2,029 of the 5,635 on-catalog rows
            // were sold at something other than the current list price, and an
            // agent who has agreed a different figure needs somewhere to put it.
            'cargo_fijo' => ['nullable', 'numeric', 'min:0'],
            'modalidad' => ['required', Rule::in(NcFija::MODALIDADES)],
            'contacto' => ['required', 'string', 'max:100'],
            'telefono1' => ['required', 'string', 'max:50'],
            // Legacy marked this required on a form that had no second number to
            // give; it is set in 346 of the 5,970 rows. Optional here, which is
            // what the data says it always was.
            'telefono2' => ['nullable', 'string', 'max:50'],
            'direccion' => ['required', 'string'],
            'coordenadas' => ['nullable', 'string', 'max:100'],
            'tecnologia' => ['required', Rule::in(NcFija::TECNOLOGIAS)],
            'comentario' => ['nullable', 'string'],
        ];
    }
}
