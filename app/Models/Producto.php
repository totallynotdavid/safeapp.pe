<?php

namespace App\Models;

use Database\Factories\ProductoFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property string $tipo
 * @property string $subtipo
 * @property string $descripcion
 * @property string $cargo_fijo
 * @property int $estado
 *
 * @method static Builder<Producto> ofrecibles(string $tipo)
 */
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
     * Grouped by subtipo. The registro form holds the whole catalog and fills
     * the charge from it.
     *
     * @return Collection<array-key, Collection<int, Producto>>
     */
    public static function catalogoFija(): Collection
    {
        return self::ofrecibles(self::FIJA)
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
