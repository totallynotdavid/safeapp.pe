<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Testing\PendingCommand;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Views under test pull assets through @vite; tests don't build them.
        $this->withoutVite();
    }

    /**
     * artisan() is typed to also return an int, which only happens when console
     * output mocking is off.
     *
     * @param  array<string, mixed>  $parametros
     */
    protected function comando(string $nombre, array $parametros = []): PendingCommand
    {
        $comando = $this->artisan($nombre, $parametros);
        assert($comando instanceof PendingCommand, 'console output mocking must be on');

        return $comando;
    }
}
