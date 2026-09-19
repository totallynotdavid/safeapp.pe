<?php

namespace Tests\Concerns;

use Illuminate\Testing\TestResponse;

/**
 * Reads back the checkboxes a rendered form offers. Asserting on the markup
 * instead reads naturally ("the page contains `checked`") and cannot tell the
 * right box from the wrong one.
 */
trait LeeFormulario
{
    /**
     * The checkboxes for one field: submitted value => whether it is ticked.
     *
     * Values come back as the strings the form would post, which is what the
     * browser sends whatever the column type behind them is.
     *
     * @param  string  $campo  the name attribute, brackets and all
     * @return array<string, bool>
     */
    protected function casillas(TestResponse $respuesta, string $campo): array
    {
        preg_match_all(
            '/<input\b[^>]*name="'.preg_quote($campo, '/').'"[^>]*>/',
            $respuesta->getContent(),
            $inputs,
        );

        $casillas = [];

        foreach ($inputs[0] as $input) {
            preg_match('/\bvalue="([^"]*)"/', $input, $valor);
            $casillas[$valor[1]] = str_contains($input, 'checked');
        }

        return $casillas;
    }
}
