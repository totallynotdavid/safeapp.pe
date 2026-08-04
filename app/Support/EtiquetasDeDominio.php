<?php

namespace App\Support;

/**
 * Keeps persistence-oriented workflow values separate from the language shown
 * to people. Database and request values intentionally remain stable uppercase
 * codes; UI consumers use these sentence-case labels instead.
 */
final class EtiquetasDeDominio
{
    private const ETIQUETAS = [
        'estado' => [
            'XINGRESAR' => 'Por ingresar',
            'PENDIENTE' => 'Pendiente',
            'PROCESANDO' => 'Procesando',
            'INGRESADO' => 'Ingresado',
            'DELIVERY' => 'Delivery',
            'ACTIVADO' => 'Activado',
            'CAIDA' => 'Caída',
            'BOLSA' => 'Bolsa',
            'SALIDAANTICIPADA' => 'Salida anticipada',
            'LIQUIDADO' => 'Liquidado',
            'AGENDADO' => 'Agendado',
            'INICIADO' => 'Iniciado',
            'OBSERVADO' => 'Observado',
            'ARBITRAJE' => 'Arbitraje',
            'COMPLETADO' => 'Completado',
            'CANCELADO' => 'Cancelado',
            'ATENDIDO' => 'Atendido',
            'DEVUELTO' => 'Devuelto',
            'ADR' => 'ADR',
        ],
        'validacion' => [
            'PENDIENTE' => 'Pendiente',
            'PROCEDE' => 'Procede',
            'RECHAZADO' => 'Rechazado',
        ],
        'modalidad' => [
            'ALTA' => 'Alta',
            'PORTABILIDAD' => 'Portabilidad',
            'BAM' => 'BAM',
            'FUNNEL' => 'Funnel',
        ],
        'tipo_producto' => [
            'M4' => 'M4',
            'COMBO' => 'Combo',
            'M4/COMBO' => 'M4/Combo',
        ],
        'etapa' => [
            'F1-PERDIDA' => 'F1 · Perdida',
            'F1-GANADA' => 'F1 · Ganada',
            'F2-GESTION' => 'F2 · Gestión',
            'F3-NEGOCIACION' => 'F3 · Negociación',
            'F4-DISENO' => 'F4 · Diseño',
            'F5-DEFINICION' => 'F5 · Definición',
        ],
        'tecnologia' => ['FTTH' => 'FTTH', 'HFC' => 'HFC', 'GPON' => 'GPON'],
        'nodo' => [
            'SINNODO' => 'Sin nodo',
            'NAT' => 'NAT',
            'NCP' => 'NCP',
            'VACANTE' => 'Vacante',
            'RESERVADO' => 'Reservado',
        ],
        'observacion' => [
            'DEUDA' => 'Deuda',
            'BAJA' => 'Baja',
            'BAJA PARCIAL' => 'Baja parcial',
            'PORT OUT' => 'Port out',
            'SUSPENSION' => 'Suspensión',
            'ACUERDO COMERCIAL' => 'Acuerdo comercial',
        ],
        'servicio' => ['FIJA' => 'Fija', 'MOVIL' => 'Móvil'],
        'si_no' => ['SI' => 'Sí', 'NO' => 'No'],
        'detalle_equipo' => [
            'DECO' => 'Decodificador',
            'BAM' => 'BAM',
            'MODEM' => 'Módem',
            'CELULAR' => 'Celular',
            'TELEFONO' => 'Teléfono',
            'OTROS' => 'Otros',
        ],
    ];

    public static function texto(?string $valor, ?string $dominio = null): ?string
    {
        if ($valor === null || trim($valor) === '') {
            return $valor;
        }

        $valor = trim($valor);

        return self::ETIQUETAS[$dominio][$valor] ?? self::titulo($valor);
    }

    /**
     * @param iterable<array-key, string> $valores
     * @return list<array{value: string, label: string}>
     */
    public static function opciones(iterable $valores, ?string $dominio = null): array
    {
        $valores = is_array($valores) ? $valores : iterator_to_array($valores);
        $asociativo = ! array_is_list($valores);

        return collect($valores)->map(static function ($etiqueta, $valor) use ($asociativo, $dominio): array {
            $valor = (string) ($asociativo ? $valor : $etiqueta);
            $etiqueta = (string) $etiqueta;

            return ['value' => $valor, 'label' => self::texto($etiqueta, $dominio) ?? ''];
        })->values()->all();
    }

    private static function titulo(string $valor): string
    {
        $valor = mb_strtolower($valor, 'UTF-8');
        $valor = mb_strtoupper(mb_substr($valor, 0, 1, 'UTF-8'), 'UTF-8').mb_substr($valor, 1, null, 'UTF-8');

        return (
            preg_replace_callback(
                '/\b(adr|bam|caeq|ccaa|ftth|gpon|hfc|m4|nat|ncp|sf|tv)\b/ui',
                static fn (array $match): string => mb_strtoupper($match[0], 'UTF-8'),
                $valor,
            ) ?? $valor
        );
    }
}
