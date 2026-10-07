<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Lang;
use Tests\TestCase;

// Every form in the app rejected input by showing the raw translation key
// ("validation.required") because APP_LOCALE and APP_FALLBACK_LOCALE were both
// `es` and the framework ships only `en`. These pin the two halves of the fix.
class ValidacionMensajesTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_rejected_field_names_itself_in_spanish(): void
    {
        $mensaje = Lang::get('validation.required', ['attribute' => Lang::get('validation.attributes.ruc')]);

        $this->assertSame('Debe completar el RUC.', $mensaje);
    }

    public function test_the_wording_agrees_with_attributes_of_either_gender(): void
    {
        // ":Attribute seleccionado no existe" reads as "La región seleccionado
        // no existe". The messages agree with a noun they supply themselves.
        foreach (['zonal', 'id_cliente'] as $campo) {
            $atributo = $this->mensaje('validation.attributes.'.$campo);

            $this->assertStringNotContainsString('seleccionado', $this->mensaje('validation.exists', [
                'attribute' => $atributo,
            ]));
            $this->assertStringContainsString('opción válida', $this->mensaje('validation.in', [
                'attribute' => $atributo,
            ]));
        }
    }

    public function test_an_untranslated_rule_falls_back_to_a_sentence_not_a_key(): void
    {
        // The fallback locale is `en`, so a rule this app has not translated
        // degrades to an English sentence. A raw key reaching a user is the bug.
        foreach (['required', 'exists', 'in', 'date', 'numeric', 'url', 'ipv4'] as $regla) {
            $this->assertStringNotContainsString(
                'validation.',
                $this->mensaje('validation.'.$regla, ['attribute' => 'x']),
            );
        }
    }

    /** @param  array<string, string>  $reemplazos */
    private function mensaje(string $clave, array $reemplazos = []): string
    {
        $mensaje = Lang::get($clave, $reemplazos);
        $this->assertIsString($mensaje);

        return $mensaje;
    }
}
