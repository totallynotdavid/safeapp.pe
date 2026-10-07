<?php

namespace App\Models;

use Database\Factories\QuiebreFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id_quiebre
 * @property string $estado
 * @property int $id_validador
 * @property-read Carbon|null $fecha_validacion
 * @property-write Carbon|string|null $fecha_validacion
 * @property string|null $caso_sf
 * @property string|null $comentario_validador
 * @property int $id_usuario
 * @property int $id_supervisor
 * @property int|null $zonal_telefonica
 * @property int $id_cliente
 * @property string|null $servicio
 * @property string|null $numero_problema
 * @property string|null $tipo_averia
 * @property string|null $problema_equipo
 * @property string|null $detalle_equipo
 * @property Carbon|null $fecha_inicio_averia
 * @property Carbon|null $fecha_activacion
 * @property Carbon|null $fecha_inicio
 * @property string|null $ticket_atencion
 * @property string|null $numero_ticket
 * @property Carbon|null $fecha_ticket_atencion
 * @property string|null $contacto1
 * @property string|null $celular1
 * @property string|null $correo1
 * @property string|null $contacto2
 * @property string|null $celular2
 * @property string|null $correo2
 * @property string|null $comentario_ejecutivo
 * @property string|null $back
 * @property-read Carbon|null $fecha_registro
 * @property-write Carbon|string|null $fecha_registro
 * @property string|null $validacion
 * @property-read Cliente|null $cliente
 * @property-read Usuario|null $ejecutivo
 * @property-read Usuario|null $validador
 * @property-read Tienda|null $tienda
 */
class Quiebre extends Model
{
    /** @use HasFactory<QuiebreFactory> */
    use HasFactory;

    public const ESTADOS = ['PENDIENTE', 'ATENDIDO', 'PROCESANDO', 'DEVUELTO'];

    public const ESTADOS_EDITABLES = ['PENDIENTE', 'PROCESANDO'];

    public const SERVICIOS = ['FIJA', 'MOVIL'];

    public const DETALLES_EQUIPO = ['DECO', 'BAM', 'MODEM', 'CELULAR', 'TELEFONO', 'OTROS'];

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

    // Services active for at least this many days require a ticket.
    public const DIAS_PARA_TICKET = 90;

    public $timestamps = false;

    protected $table = 'quiebre';

    protected $primaryKey = 'id_quiebre';

    protected $fillable = [
        'estado',
        'id_validador',
        'fecha_validacion',
        'caso_sf',
        'comentario_validador',
        'zonal_telefonica',
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

    public function esEditable(): bool
    {
        return in_array($this->estado, self::ESTADOS_EDITABLES, true);
    }

    // Ticket requirement is based on service age at registration time.
    public static function requiereTicket(?Carbon $fechaActivacion): bool
    {
        return $fechaActivacion !== null && $fechaActivacion->diffInDays(now()) >= self::DIAS_PARA_TICKET;
    }

    /** @return BelongsTo<Cliente, $this> */
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'id_cliente', 'id_cliente');
    }

    /** @return BelongsTo<Usuario, $this> */
    public function ejecutivo(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

    /** @return BelongsTo<Usuario, $this> */
    public function validador(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_validador', 'id_usuario');
    }

    /** @return BelongsTo<Tienda, $this> */
    public function tienda(): BelongsTo
    {
        return $this->belongsTo(Tienda::class, 'zonal_telefonica', 'id_tienda');
    }
}
