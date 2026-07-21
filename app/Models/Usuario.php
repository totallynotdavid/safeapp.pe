<?php

namespace App\Models;

use Database\Factories\UsuarioFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\DB;

class Usuario extends Authenticatable
{
    /** @use HasFactory<UsuarioFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $table = 'usuario';

    protected $primaryKey = 'id_usuario';

    protected $authPasswordName = 'clave';

    // No remember_token column on the legacy table; disable "remember me"
    // rather than adding a column nothing else needs yet.
    protected $rememberTokenName = '';

    protected $fillable = [
        'usuario',
        'clave',
        'id_rol',
        'personal',
        'sexo',
        'id_supervisor',
        'fecha_nacimiento',
        'telefono',
        'correo',
    ];

    protected $hidden = ['clave'];

    protected function casts(): array
    {
        return [
            'estado' => 'boolean',
            'fecha_nacimiento' => 'date',
        ];
    }

    public function rol(): BelongsTo
    {
        return $this->belongsTo(Rol::class, 'id_rol', 'id_rol');
    }

    // Optional: id_supervisor defaults to 0 (no row) until a user is assigned,
    // so this resolves to null rather than hiding the user behind an inner join.
    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(Supervisor::class, 'id_supervisor', 'id_supervisor');
    }

    public function menus(): BelongsToMany
    {
        return $this->belongsToMany(Menu::class, 'acceso', 'id_usuario', 'id_menu');
    }

    public function asignaciones(): HasMany
    {
        return $this->hasMany(AsignacionTienda::class, 'id_usuario', 'id_usuario');
    }

    public function asignacionActiva(): HasOne
    {
        return $this->hasOne(AsignacionTienda::class, 'id_usuario', 'id_usuario')->where('estado', 0);
    }

    // The one place that resolves "which tienda is this operator working".
    // Operational screens scope their rows to it; null means the user has no
    // active assignment and sees nothing.
    public function tiendaAsignada(): ?int
    {
        return $this->asignacionActiva?->id_tienda;
    }

    /**
     * Write owner of the assignment invariant tiendaAsignada() reads: this user
     * ends up assigned to exactly $idTiendas, with $idActiva the only active row
     * (estado 0) and every other assignment inactive. Legacy switched the active
     * tienda with two blind UPDATEs and had no working way to create the first
     * assignment; replacing the whole set in one transaction keeps "at most one
     * active tienda" true by construction. An $idActiva outside $idTiendas leaves
     * the user with no active tienda, which the form request forbids.
     *
     * @param  list<int>  $idTiendas
     */
    public function sincronizarAsignaciones(array $idTiendas, ?int $idActiva): void
    {
        DB::transaction(function () use ($idTiendas, $idActiva): void {
            $this->asignaciones()->delete();

            foreach (array_unique($idTiendas) as $idTienda) {
                $this->asignaciones()->create([
                    'id_tienda' => $idTienda,
                    'estado' => $idTienda === $idActiva ? 0 : 1,
                ]);
            }
        });
    }

    /**
     * Switches which of this user's existing assignments is the active one.
     *
     * The self-service half of sincronizarAsignaciones: an operator working two
     * stores moves between them without an admin, but cannot assign themselves
     * a tienda they were never given. Returns false when the tienda is not one
     * of theirs, so the caller says so rather than silently doing nothing.
     *
     * Legacy did this with two blind UPDATEs and no membership check, so posting
     * any id_tienda activated an assignment row that did not exist, leaving the
     * operator with no active tienda and an empty queue.
     */
    public function activarTienda(int $idTienda): bool
    {
        return DB::transaction(function () use ($idTienda): bool {
            if (! $this->asignaciones()->where('id_tienda', $idTienda)->exists()) {
                return false;
            }

            $this->asignaciones()->update(['estado' => 1]);
            $this->asignaciones()->where('id_tienda', $idTienda)->update(['estado' => 0]);

            return true;
        });
    }

    public function hasPermiso(string $permiso): bool
    {
        // Strict compare: Collection's two-arg contains() uses loose ==, and
        // PHP's null == '' is true, so a null-permiso header would match ''.
        return $this->menus->contains(static fn (Menu $menu): bool => $menu->permiso === $permiso);
    }
}
