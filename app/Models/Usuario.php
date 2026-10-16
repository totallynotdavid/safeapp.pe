<?php

namespace App\Models;

use Database\Factories\UsuarioFactory;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * @property int $id_usuario
 * @property string $usuario
 * @property string|null $clave
 * @property int $id_rol
 * @property string $personal
 * @property bool $estado
 * @property string $sexo
 * @property int $id_supervisor
 * @property Carbon|null $fecha_nacimiento
 * @property string|null $telefono
 * @property string|null $correo
 * @property Carbon|null $correo_verificado_en
 * @property-read Rol|null $rol
 * @property-read Supervisor|null $supervisor
 * @property-read EloquentCollection<int, Menu> $menus
 * @property-read EloquentCollection<int, AsignacionTienda> $asignaciones
 * @property-read PreferenciaNotificacionUsuario|null $preferenciasNotificacion
 * @property-read EloquentCollection<int, NotificacionUsuario> $notificaciones
 * @property-read EloquentCollection<int, AccesoCliente> $accesosClientes
 * @property-read AsignacionTienda|null $asignacionActiva
 */
class Usuario extends Authenticatable
{
    /** @use HasFactory<UsuarioFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $table = 'usuario';

    protected $primaryKey = 'id_usuario';

    protected $authPasswordName = 'clave';

    // The table has no remember_token column.
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
            'clave' => 'hashed',
            'estado' => 'boolean',
            'fecha_nacimiento' => 'date',
            'correo_verificado_en' => 'datetime',
        ];
    }

    /** @return BelongsTo<Rol, $this> */
    public function rol(): BelongsTo
    {
        return $this->belongsTo(Rol::class, 'id_rol', 'id_rol');
    }

    /**
     * id_supervisor uses 0 to mean no supervisor.
     *
     * @return BelongsTo<Supervisor, $this>
     */
    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(Supervisor::class, 'id_supervisor', 'id_supervisor');
    }

    /** @return BelongsToMany<Menu, $this> */
    public function menus(): BelongsToMany
    {
        return $this->belongsToMany(Menu::class, 'acceso', 'id_usuario', 'id_menu');
    }

    /** @return HasMany<AsignacionTienda, $this> */
    public function asignaciones(): HasMany
    {
        return $this->hasMany(AsignacionTienda::class, 'id_usuario', 'id_usuario');
    }

    /** @return HasOne<PreferenciaNotificacionUsuario, $this> */
    public function preferenciasNotificacion(): HasOne
    {
        return $this->hasOne(PreferenciaNotificacionUsuario::class, 'id_usuario', 'id_usuario');
    }

    /** @return HasMany<NotificacionUsuario, $this> */
    public function notificaciones(): HasMany
    {
        return $this->hasMany(NotificacionUsuario::class, 'id_usuario', 'id_usuario');
    }

    /** @return HasMany<AccesoCliente, $this> */
    public function accesosClientes(): HasMany
    {
        return $this->hasMany(AccesoCliente::class, 'id_usuario', 'id_usuario');
    }

    /** @return HasOne<AsignacionTienda, $this> */
    public function asignacionActiva(): HasOne
    {
        return $this->hasOne(AsignacionTienda::class, 'id_usuario', 'id_usuario')
            ->where('estado', 0);
    }

    public function tiendaAsignada(): ?int
    {
        return $this->asignacionActiva?->id_tienda;
    }

    /**
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

    /** Returns false if the tienda is not assigned to the user. */
    public function activarTienda(int $idTienda): bool
    {
        return $this->getConnection()->transaction(function () use ($idTienda): bool {
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
        // A closure, because Collection::contains($key, $value) compares loosely.
        return $this->menus->contains(
            static fn (Menu $menu): bool => $menu->permiso === $permiso,
        );
    }
}
