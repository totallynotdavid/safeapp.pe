<?php

namespace App\Models;

use Database\Factories\QuiebreFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class Quiebre extends Model
{
    /** @use HasFactory<QuiebreFactory> */
    use HasFactory;

    // The validation workflow states. estado carries the validation decision;
    // the list filters and the update form both draw their options from here so
    // the allowed set has a single owner.
    public const ESTADOS = ['PENDIENTE', 'ATENDIDO', 'PROCESANDO', 'DEVUELTO'];

    // While a case sits in one of these states the registering ejecutivo may
    // still amend its contact/comentario fields; once validated it is frozen.
    public const ESTADOS_EDITABLES = ['PENDIENTE', 'PROCESANDO'];

    public const SERVICIOS = ['FIJA', 'MOVIL', 'AVANZADO'];

    public const DETALLES_EQUIPO = ['DECO', 'BAM', 'MODEM', 'CELULAR', 'TELEFONO', 'OTROS'];

    // Averia classifications, stored value => display label. A few legacy values
    // differ from their label (e.g. "AJUSTE FACTURACION" shows "AJUSTE DE
    // FACTURACIÓN"); the stored value is preserved so cutover data still maps.
    public const TIPOS_AVERIA = [
        'SIN TV' => 'SIN TV',
        'SIN DATOS' => 'SIN DATOS',
        'NO PUEDE REALIZAR / RECIBIR LLAMADAS' => 'NO PUEDE REALIZAR / RECIBIR LLAMADAS',
        'SIN SERVICIO' => 'SIN SERVICIO',
        'FALLA EQUIPO' => 'FALLA EQUIPO',
        'DUPLICIDAD DE RECIBOS' => 'DUPLICIDAD DE RECIBOS',
        'FACTURA DE EQUIPOS' => 'FACTURA DE EQUIPOS',
        'DESCUENTO NO APLICADO' => 'DESCUENTO NO APLICADO',
        'CASI' => 'CASI',
        'CAEQ' => 'CAEQ',
        'REGISTRO CCAA' => 'REGISTRO CCAA',
        'INCIDENCIA PAGO APP' => 'INCIDENCIA PAGO APP',
        'AJUSTE FACTURACION' => 'AJUSTE DE FACTURACIÓN',
        'IMPACTO PAGOS' => 'IMPACTO DE PAGOS',
        'SOLICITUD BAJA' => 'SOLICITUD DE BAJA',
        'CAMBIO TITULARIDAD' => 'CAMBIO DE TITULARIDAD',
        'AGRUPACION LINEAS' => 'AGRUPACIÓN DE LÍNEAS',
        'RECLAMO FACTURACION' => 'RECLAMO FACTURACIÓN',
        'OTROS' => 'OTROS',
    ];

    // A ticket must be raised once the service has been active for at least this
    // long; below the threshold registration records no ticket.
    public const DIAS_PARA_TICKET = 90;

    public $timestamps = false;

    protected $table = 'quiebre';

    protected $primaryKey = 'id_quiebre';

    // Registration writes the case columns; validation writes the decision
    // columns. Server-owned fields (id_usuario, estado on create, id_validador,
    // fecha_registro, ticket_atencion) are set explicitly in the controllers,
    // never mass-assigned, so they are left out here.
    protected $fillable = [
        'estado',
        'id_validador',
        'fecha_validacion',
        'casosf',
        'comentario_validador',
        'zonal_telefonica',
        'ruc',
        'razon_social',
        'servicio',
        'tipo_averia',
        'numero_problema',
        'problema_equipo',
        'detalle_equipo',
        'fecha_inicio_averia',
        'fecha_activacion',
        'fecha_inicio',
        'numero_ticket',
        'fecha_ticket_atencion',
        'contacto1',
        'celular1',
        'correo1',
        'contacto2',
        'celular2',
        'correo2',
        'comentario_ejecutivo',
        'back',
    ];

    protected function casts(): array
    {
        return [
            'fecha_registro' => 'date',
            'fecha_inicio_averia' => 'date',
            'fecha_activacion' => 'date',
            'fecha_inicio' => 'date',
            'fecha_ticket_atencion' => 'date',
            'fecha_validacion' => 'date',
        ];
    }

    // Owns the "still amendable by its ejecutivo" rule so the policy and the
    // edit view agree on when the followup form is writable.
    public function esEditable(): bool
    {
        return in_array($this->estado, self::ESTADOS_EDITABLES, true);
    }

    // A service active for DIAS_PARA_TICKET or more at registration time needs a
    // ticket, which makes numero_ticket and its date required. Both the store
    // request and the controller consult this so the rule has one owner.
    public static function requiereTicket(?Carbon $fechaActivacion): bool
    {
        return (
            $fechaActivacion !== null
            && $fechaActivacion->diffInDays(Carbon::now('America/Lima')) >= self::DIAS_PARA_TICKET
        );
    }

    public function ejecutivo(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

    public function validador(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_validador', 'id_usuario');
    }

    public function tienda(): BelongsTo
    {
        return $this->belongsTo(Tienda::class, 'zonal_telefonica', 'id_tienda');
    }
}
