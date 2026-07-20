<?php

namespace App\Cockpit;

/**
 * What one import run did, or why it did nothing.
 *
 * An import is all or nothing (see ImportacionDeClientes), so a result carries
 * either counts or errores, never both.
 */
final readonly class ResultadoImportacion
{
    /**
     * @param  list<string>  $errores
     */
    private function __construct(
        public int $nuevos,
        public int $actualizados,
        public array $errores,
    ) {}

    public static function cargada(int $nuevos, int $actualizados): self
    {
        return new self($nuevos, $actualizados, []);
    }

    /**
     * @param  list<string>  $errores
     */
    public static function rechazada(array $errores): self
    {
        return new self(0, 0, $errores);
    }

    public function exitosa(): bool
    {
        return $this->errores === [];
    }

    public function total(): int
    {
        return $this->nuevos + $this->actualizados;
    }
}
