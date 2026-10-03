<?php

namespace App\Models;

use Database\Factories\CarteraFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id_cartera
 * @property string $nombre
 * @property string $propietario
 * @property int $id_tienda
 * @property int $estado
 * @property string|null $DNI
 * @property-read Tienda|null $tienda
 */
class Cartera extends Model
{
    /** @use HasFactory<CarteraFactory> */
    use HasFactory;

    public const ACTIVA = 1;

    public $timestamps = false;

    protected $table = 'cartera';

    protected $primaryKey = 'id_cartera';

    protected $fillable = ['nombre', 'propietario', 'id_tienda', 'estado', 'DNI'];

    /**
     * @param  Builder<Cartera>  $query
     * @return Builder<Cartera>
     */
    public function scopeSeleccionables(Builder $query): Builder
    {
        return $query
            ->where('estado', self::ACTIVA)
            ->orderBy('propietario');
    }

    public function tienda(): BelongsTo
    {
        return $this->belongsTo(Tienda::class, 'id_tienda', 'id_tienda');
    }

    protected function casts(): array
    {
        return ['estado' => 'integer'];
    }
}
