# Access control

Access control has five parts.

**Permisos.** A user's grants are the `menu` rows linked to them through the
`acceso` table. `Usuario::hasPermiso` checks a `menu.permiso` string. The
`Gate::before` hook in `AppServiceProvider` grants an ability when the user
holds that permiso and otherwise returns `null`, so a model policy can still
decide. Creating a user copies the role's grants from `accesoxrol` into
`acceso`. The user's grants can diverge from the role afterward.

**Sidebar.** [`Navigation\Sidebar`](../app/Navigation/Sidebar.php) owns the
structure and presentation of the navigation. It shows an item only when the
user holds its permiso. The menu tables store grants and nothing else. After
login, `Sidebar::startUrlForUser` sends the user to the first screen they may
see, or to Configuración when they have no operational access.

**Policies.** A route permiso admits a user to a screen. The classes in
[`app/Policies/`](../app/Policies/) decide whether that user may touch a
particular record, because a detail or update endpoint must not trust the posted
id.

- `NcMovilPolicy` and `NcFijaPolicy` let an ejecutivo view only the cases they
  registered. An update also requires the case to be editable, which means the
  validador has not ruled on it yet.
- `QuiebrePolicy` follows the same view and update rules. Its `validar` ability
  allows a user to validate only a quiebre whose `zonal_telefonica` is their
  assigned tienda.
- `DatoPolicy` lets an agent view or register a gestión only when they hold the
  permiso for the dato's segment, the dato is actively assigned to them, and its
  cliente is visible to them.
- `UsuarioPolicy` stops an operator from disabling their own account.

**Customer visibility.** `cliente_acceso` records which users may see which
customers. `Cliente::visiblePara` filters on it. `AccesoAClientes` grants access
and resolves a RUC for a user. A RUC that exists but is not visible to the user
raises `ClienteNoDisponible`, whose message does not say why. That keeps one
executive's history from leaking to another.

**Passwords.** The `Usuario` model hashes `usuario.clave` through the `hashed`
cast, and the login uses the framework's `Auth::attempt`. A `null` `clave` means
the account has no password and never matches. The cutover leaves every migrated
account that way. An operator with the `usuarios.manage` permiso sets a password
on the Usuarios screen, or anyone with shell access runs
`php artisan usuario:restablecer-clave <usuario>`, which prints a new random
password once.
