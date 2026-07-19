<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

// The fixed catalog of contact dispositions, seeded as reference data in every
// environment because the gestión form cannot be used without it.
class Respuesta extends Model
{
    // Every channel a disposition can belong to. A disposition carries its
    // channel, which is why logging a contact never asks for one.
    public const CANALES = ['LLAMADA', 'VISITA'];

    public $timestamps = false;

    protected $table = 'respuesta';

    protected $primaryKey = 'id_respuesta';

    protected function casts(): array
    {
        return [
            'contactabilidad' => 'boolean',
            'negociacion' => 'boolean',
            'volverllamar' => 'boolean',
        ];
    }

    public function llamadas(): HasMany
    {
        return $this->hasMany(Llamada::class, 'id_respuesta', 'id_respuesta');
    }
}
