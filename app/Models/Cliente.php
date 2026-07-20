<?php

namespace App\Models;

use Database\Factories\ClienteFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * A customer, identified by RUC. Nothing about campaigns, ownership or progress
 * lives here: `Dato` is the customer on a campaign list, and `AsignacionDato` is
 * the agent holding that record.
 *
 * A row is never deleted. Every workflow that mentions a RUC resolves to one of
 * these, so a charge note raised years ago still names a customer even after the
 * campaign that introduced them is long gone.
 */
class Cliente extends Model
{
    /** @use HasFactory<ClienteFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $table = 'cliente';

    protected $primaryKey = 'id_cliente';

    protected $fillable = ['ruc', 'razon_social'];

    /**
     * The single way a workflow resolves a RUC to a customer.
     *
     * Legacy had four copies of this (the quiebre and three notacargo screens),
     * each its own AJAX endpoint, because each of those forms was a modal with
     * no customer in hand. `ruc` is unique, so this is one row or nothing.
     */
    public static function porRuc(string $ruc): ?self
    {
        return self::query()->where('ruc', $ruc)->first();
    }

    /**
     * Resolves a RUC, creating the customer when it is new.
     *
     * This is what a workflow calls when it must have a customer to file
     * against: raising a charge note for a RUC nobody has loaded is normal, and
     * used to leave the record pointing at nothing. In the production dump 83%
     * of nc_fija rows referenced a RUC absent from the work queue, which is the
     * defect this closes. Creating identity is safe precisely because identity
     * carries no campaign, no owner and no progress: it puts nobody in a queue.
     */
    public static function resolver(string $ruc, ?string $razonSocial = null): self
    {
        $cliente = self::query()->firstOrCreate(['ruc' => $ruc], ['razon_social' => $razonSocial]);

        // A later mention with a name fills in one we never had. It does not
        // overwrite a name already on file: a load never overwrites what a
        // person recorded, and that rule does not stop at the campaign table.
        if ($razonSocial !== null && $cliente->razon_social === null) {
            $cliente->update(['razon_social' => $razonSocial]);
        }

        return $cliente;
    }

    /**
     * The phone numbers known for this customer, newest contact first.
     *
     * Derived from the call log, which is the source of truth: a number is known
     * because someone was contacted on it. There is deliberately no phone table
     * to keep in step. Spanning campaigns is the point, since the customer is
     * the same person whichever list delivered them.
     *
     * @return Collection<int, object{telefono: string, veces: int, ultimo_contacto: string}>
     */
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

    public function datos(): HasMany
    {
        return $this->hasMany(Dato::class, 'id_cliente', 'id_cliente');
    }

    public function llamadas(): HasMany
    {
        return $this->hasMany(Llamada::class, 'id_cliente', 'id_cliente');
    }
}
