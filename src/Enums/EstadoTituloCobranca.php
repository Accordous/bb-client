<?php

namespace Accordous\BbClient\Enums;

enum EstadoTituloCobranca: string
{
    case REGISTRADO = '01';
    case LIQUIDADO = '02';
    case PROTESTADO = '03';
    case VENCIDO = '04';
    case CANCELADO = '05';

    public function getDescription(): string
    {
        return match($this) {
            self::REGISTRADO => 'Registrado',
            self::LIQUIDADO => 'Liquidado',
            self::PROTESTADO => 'Protestado',
            self::VENCIDO => 'Vencido',
            self::CANCELADO => 'Cancelado',
        };
    }

    public static function isValid(string $value): bool
    {
        return self::tryFrom($value) !== null;
    }
}