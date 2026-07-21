<?php

namespace Tests\Concerns;

use Illuminate\Testing\TestResponse;

/**
 * Reads the checkboxes a rendered form offers, so a test can assert on which
 * options a screen presents and which arrive already ticked.
 *
 * The alternative is asserting on the markup, and the assertion that reads
 * naturally there ("the page contains `checked`") is the one that cannot tell
 * the right box from the wrong one.
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
