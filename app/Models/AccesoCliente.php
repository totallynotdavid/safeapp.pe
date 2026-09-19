<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccesoCliente extends Model
{
    public const CREATED_AT = 'fecha_otorgado';

    public const UPDATED_AT = null;

    protected $table = 'cliente_acceso';

    protected $primaryKey = 'id_acceso';

    protected $fillable = ['id_cliente', 'id_usuario'];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'id_cliente', 'id_cliente');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }
}
