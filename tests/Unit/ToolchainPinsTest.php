<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * mise.toml is the one place a tool version is written. CI installs from it, so
 * the version CI runs cannot differ from the one a developer runs, and anything
 * else that names a tool version has to agree with it.
 */
class ToolchainPinsTest extends TestCase
{
    private static function leer(string $ruta): string
    {
        $contenido = file_get_contents(dirname(__DIR__, 2).'/'.$ruta);
        self::assertIsString($contenido, $ruta);

        return $contenido;
    }

    public function test_every_ci_job_installs_its_tools_from_mise(): void
    {
        $ci = self::leer('.github/workflows/ci.yml');

        $trabajos = preg_match_all('/^\s+runs-on:/m', $ci);

        $this->assertGreaterThan(0, $trabajos);
        $this->assertSame(
            $trabajos,
            preg_match_all('/^\s+- uses: jdx\/mise-action@/m', $ci),
            'each job installs from mise.toml',
        );
    }

    public function test_ci_pins_no_tool_version_of_its_own(): void
    {
        $ci = self::leer('.github/workflows/ci.yml');

        $this->assertDoesNotMatchRegularExpression(
            '/^\s*[\w-]*version[\w-]*\s*:/mi',
            $ci,
            'a version key in ci.yml would shadow mise.toml',
        );
        $this->assertDoesNotMatchRegularExpression(
            '/uses:\s*(shivammathur\/setup-php|oven-sh\/setup-bun|nhedger\/setup-mago)/',
            $ci,
            'install tools through mise, not a per-tool action',
        );
    }

    public function test_mise_pins_exact_versions(): void
    {
        $versiones = [];

        // bun, mago, and the php and composer entries' `version` keys.
        $this->assertSame(4, preg_match_all(
            '/^(?:bun|mago|version)\s*=\s*"([^"]*)"/m',
            self::leer('mise.toml'),
            $versiones,
        ));
        foreach ($versiones[1] as $version) {
            $this->assertMatchesRegularExpression('/^\d+\.\d+\.\d+$/', $version);
        }
    }

    public function test_the_mago_schema_in_mago_toml_is_the_pinned_mago(): void
    {
        $pin = [];

        $this->assertSame(1, preg_match('/^mago\s*=\s*"([^"]+)"/m', self::leer('mise.toml'), $pin));
        $this->assertStringContainsString(
            "https://mago.carthage.software/{$pin[1]}/schema.json",
            self::leer('mago.toml'),
        );
    }
}
