<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificacionUsuario extends Model
{
    protected $table = 'notificacion_usuario';
    protected $primaryKey = 'id_notificacion';
    protected $fillable = ['id_usuario', 'categoria', 'titulo', 'descripcion'];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }
}
