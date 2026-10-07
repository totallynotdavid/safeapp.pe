<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id_acceso
 * @property int $id_cliente
 * @property int $id_usuario
 * @property Carbon $fecha_otorgado
 * @property-read Cliente $cliente
 * @property-read Usuario $usuario
 */
class AccesoCliente extends Model
{
    public const CREATED_AT = 'fecha_otorgado';

    public const UPDATED_AT = null;

    protected $table = 'cliente_acceso';

    protected $primaryKey = 'id_acceso';

    protected $fillable = ['id_cliente', 'id_usuario'];

    /** @return BelongsTo<Cliente, $this> */
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'id_cliente', 'id_cliente');
    }

    /** @return BelongsTo<Usuario, $this> */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }
}
