<x-ui.stack direction="vertical" gap="6">
    <x-ui.heading level="2" class="settings-page__heading">Información personal</x-ui.heading>

    <x-ui.stack direction="vertical" gap="0">
        <x-ui.settings-expandable-row
            field="personal"
            label="Nombre completo"
            :value="$usuario->personal"
            :action="route('configuracion.perfil.update')"
            :expanded="$errors->has('personal')">
            <x-ui.text-input
                name="personal"
                label="Nombre completo"
                :value="old('personal', $usuario->personal)"
                :error="$errors->first('personal')"
                required
                maxlength="50"
                autocomplete="name" />
        </x-ui.settings-expandable-row>

        <x-ui.settings-expandable-row
            field="fecha_nacimiento"
            label="Fecha de nacimiento"
            :value="$usuario->fecha_nacimiento?->format('d/m/Y') ?? 'No registrada'"
            :action="route('configuracion.perfil.update')"
            :expanded="$errors->has('fecha_nacimiento')">
            <x-ui.date-input
                name="fecha_nacimiento"
                label="Fecha de nacimiento"
                :value="old('fecha_nacimiento', $usuario->fecha_nacimiento?->format('Y-m-d'))"
                :error="$errors->first('fecha_nacimiento')"
                optional
                has-clear />
        </x-ui.settings-expandable-row>

        <x-ui.settings-expandable-row
            field="telefono"
            label="Teléfono"
            :value="$usuario->telefono ?: 'No registrado'"
            :action="route('configuracion.perfil.update')"
            :expanded="$errors->has('telefono')">
            <x-ui.text-input
                name="telefono"
                label="Teléfono"
                type="tel"
                :value="old('telefono', $usuario->telefono)"
                :error="$errors->first('telefono')"
                optional
                maxlength="20"
                autocomplete="tel" />
        </x-ui.settings-expandable-row>

        <x-ui.settings-expandable-row
            field="correo"
            label="Correo"
            :value="$usuario->correo ?: 'No registrado'"
            :action="route('configuracion.perfil.update')"
            :expanded="$errors->has('correo')">
            <x-ui.text-input
                name="correo"
                label="Correo"
                type="email"
                :value="old('correo', $usuario->correo)"
                :error="$errors->first('correo')"
                optional
                maxlength="100"
                autocomplete="email" />
        </x-ui.settings-expandable-row>

        <x-ui.settings-info-row
            label="Verificación de correo"
            :value="$usuario->correo ? ($usuario->correo_verificado_en ? 'Verificado' : 'Pendiente de verificación') : 'Requiere un correo registrado'">
            @if ($usuario->correo && ! $usuario->correo_verificado_en)
                <x-slot:action>
                    <form method="POST" action="{{ route('configuracion.correo.reenviar') }}">
                        @csrf
                        <button type="submit" class="link">Reenviar</button>
                    </form>
                </x-slot:action>
            @endif
        </x-ui.settings-info-row>

        <x-ui.settings-info-row
            label="Cuenta"
            :value="$usuario->usuario.' · '.($usuario->rol?->nombre_rol ?? 'Sin rol')" />
    </x-ui.stack>

    <x-ui.card padding="4">
        <x-ui.stack direction="vertical" gap="4">
            <x-ui.stack direction="horizontal" gap="3" v-align="start">
                <x-ui.center class="settings-info-card__icon">
                    <x-ui.icon name="lock-closed" />
                </x-ui.center>
                <x-ui.stack direction="vertical" gap="0">
                    <x-ui.text type="body" weight="semibold">¿Por qué no se muestra toda mi información?</x-ui.text>
                    <x-ui.text type="supporting" color="secondary">Protegemos los datos de acceso y solo mostramos la información necesaria para administrar tu cuenta.</x-ui.text>
                </x-ui.stack>
            </x-ui.stack>
            <x-ui.divider />
            <x-ui.stack direction="horizontal" gap="3" v-align="start">
                <x-ui.center class="settings-info-card__icon">
                    <x-ui.icon name="pencil-square" />
                </x-ui.center>
                <x-ui.stack direction="vertical" gap="0">
                    <x-ui.text type="body" weight="semibold">¿Qué datos puedo editar?</x-ui.text>
                    <x-ui.text type="supporting" color="secondary">Puedes actualizar tus datos de contacto. El usuario y el rol los administra el equipo responsable.</x-ui.text>
                </x-ui.stack>
            </x-ui.stack>
            <x-ui.divider />
            <x-ui.stack direction="horizontal" gap="3" v-align="start">
                <x-ui.center class="settings-info-card__icon">
                    <x-ui.icon name="share" />
                </x-ui.center>
                <x-ui.stack direction="vertical" gap="0">
                    <x-ui.text type="body" weight="semibold">¿Con quién se comparte esta información?</x-ui.text>
                    <x-ui.text type="supporting" color="secondary">Tu información de contacto solo está disponible para las personas autorizadas a administrar tu equipo.</x-ui.text>
                </x-ui.stack>
            </x-ui.stack>
        </x-ui.stack>
    </x-ui.card>
</x-ui.stack>
