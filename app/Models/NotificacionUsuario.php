<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id_notificacion
 * @property int $id_usuario
 * @property string $categoria
 * @property string $titulo
 * @property string $descripcion
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Usuario|null $usuario
 */
class NotificacionUsuario extends Model
{
    protected $table = 'notificacion_usuario';
    protected $primaryKey = 'id_notificacion';

    protected $fillable = [
        'id_usuario',
        'categoria',
        'titulo',
        'descripcion',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }
}
