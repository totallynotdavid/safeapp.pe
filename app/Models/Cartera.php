<?php

namespace App\Models;

use Database\Factories\CarteraFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// A carrier account executive a nota de cargo is attributed to, offered in the
// screens as "ejecutivo TDP". See the create_cartera_table migration for what
// each column holds and why the name does not mean a client portfolio.
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
     * The executives a form may offer. Retired executives stay in the table
     * because notas de cargo they own still display their name, so every form
     * that builds a choice list has to go through here.
     *
     * The catalog is deliberately not filtered by región, so it works across
     * all regions. The id_tienda column records where an executive sits but
     * is not a selection gate.
     *
     * @param  Builder<Cartera>  $query
     * @return Builder<Cartera>
     */
    public function scopeSeleccionables(Builder $query): Builder
    {
        return $query->where('estado', self::ACTIVA)->orderBy('propietario');
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
