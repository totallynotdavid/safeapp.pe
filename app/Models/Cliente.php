<?php

namespace App\Models;

use Database\Factories\ClienteFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * @property int $id_cliente
 * @property string $ruc
 * @property string|null $razon_social
 * @property-read EloquentCollection<int, Dato> $datos
 * @property-read EloquentCollection<int, Llamada> $llamadas
 * @property-read EloquentCollection<int, AccesoCliente> $accesos
 */
class Cliente extends Model
{
    /** @use HasFactory<ClienteFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $table = 'cliente';

    protected $primaryKey = 'id_cliente';

    protected $fillable = ['ruc', 'razon_social'];

    public static function porRuc(string $ruc): ?self
    {
        return self::query()->where('ruc', $ruc)->first();
    }

    /** @return Builder<self> */
    public function scopeVisiblePara(Builder $query, Usuario $usuario): Builder
    {
        return $query->whereHas(
            'accesos',
            static fn (Builder $accesos): Builder => $accesos->where('id_usuario', $usuario->id_usuario),
        );
    }

    /**
     * Creates only the customer identity, without campaign or workflow state.
     */
    public static function resolver(string $ruc, ?string $razonSocial = null): self
    {
        $cliente = self::query()
            ->firstOrCreate(
                ['ruc' => $ruc],
                ['razon_social' => $razonSocial],
            );

        // Fill a missing name without overwriting an existing one.
        if ($razonSocial !== null && $cliente->razon_social === null) {
            $cliente->update(['razon_social' => $razonSocial]);
        }

        return $cliente;
    }

    /** @return Collection<int, object{telefono: string, veces: int, ultimo_contacto: string}> */
    public function telefonosConocidos(): Collection
    {
        return $this
            ->llamadas()
            ->selectRaw('telefono, COUNT(*) as veces, MAX(fecha) as ultimo_contacto')
            ->whereNotNull('telefono')
            ->where('telefono', '<>', '')
            ->groupBy('telefono')
            ->orderByDesc('ultimo_contacto')
            ->get();
    }

    /** @return Collection<int, object{telefono: string, veces: int, ultimo_contacto: string}> */
    public function telefonosConocidosPara(Usuario $usuario): Collection
    {
        return $this
            ->llamadas()
            ->where('id_usuario', $usuario->id_usuario)
            ->selectRaw('telefono, COUNT(*) as veces, MAX(fecha) as ultimo_contacto')
            ->whereNotNull('telefono')
            ->where('telefono', '<>', '')
            ->groupBy('telefono')
            ->orderByDesc('ultimo_contacto')
            ->get();
    }

    public function datos(): HasMany
    {
        return $this->hasMany(Dato::class, 'id_cliente', 'id_cliente');
    }

    public function llamadas(): HasMany
    {
        return $this->hasMany(Llamada::class, 'id_cliente', 'id_cliente');
    }

    public function accesos(): HasMany
    {
        return $this->hasMany(AccesoCliente::class, 'id_cliente', 'id_cliente');
    }
}
