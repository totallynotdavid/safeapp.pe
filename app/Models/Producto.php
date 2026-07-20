<?php

namespace App\Models;

use Database\Factories\ProductoFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

// A sellable product in the catalog behind the nota de cargo fija form. See the
// create_producto_table migration for why `tipo` survives.
class Producto extends Model
{
    /** @use HasFactory<ProductoFactory> */
    use HasFactory;

    public const FIJA = 'FIJA';

    public const OFRECIBLE = 1;

    public $timestamps = false;

    protected $table = 'producto';

    protected $fillable = ['tipo', 'subtipo', 'descripcion', 'cargo_fijo', 'estado'];

    /**
     * The fija catalog as the registro form needs it: subtipo, then the
     * descripciones under it, then the charge for the pair.
     *
     * Legacy asked the server for each of those three separately, as three AJAX
     * round trips per case (crudProducto has a query for each), which is also
     * why the form let the price be typed over afterwards. The form is small
     * enough to hold the whole catalog, so it is one query and the price is
     * filled in from it.
     *
     * @return Collection<string, Collection<int, Producto>>
     */
    public static function catalogoFija(): Collection
    {
        return self::query()
            ->ofrecibles(self::FIJA)
            ->get()
            ->groupBy('subtipo');
    }

    /**
     * @param  Builder<Producto>  $query
     * @return Builder<Producto>
     */
    public function scopeOfrecibles(Builder $query, string $tipo): Builder
    {
        return $query
            ->where('tipo', $tipo)
            ->where('estado', self::OFRECIBLE)
            ->orderBy('subtipo')
            ->orderBy('cargo_fijo');
    }

    protected function casts(): array
    {
        return [
            'cargo_fijo' => 'decimal:2',
            'estado' => 'integer',
        ];
    }
}
