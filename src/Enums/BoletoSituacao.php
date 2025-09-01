<?php

namespace Accordous\BbClient\Enums;

enum BoletoSituacao: string
{
    case ATIVO = 'A';
    case BAIXADO = 'B';
    case CANCELADO = 'C';
    case PAGO = 'P';

    public function getDescription(): string
    {
        return match($this) {
            self::ATIVO => 'Ativo',
            self::BAIXADO => 'Baixado',
            self::CANCELADO => 'Cancelado',
            self::PAGO => 'Pago',
        };
    }

    public static function isValid(string $value): bool
    {
        return self::tryFrom($value) !== null;
    }
}