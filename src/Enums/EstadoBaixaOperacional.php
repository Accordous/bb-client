<?php

namespace Accordous\BbClient\Enums;

enum EstadoBaixaOperacional: int
{
    case BB = 1;
    case OUTROS_BANCOS = 2;
    case CANCELAMENTO_BAIXA = 10;

    public function getDescription(): string
    {
        return match($this) {
            self::BB => 'Banco do Brasil',
            self::OUTROS_BANCOS => 'Outros Bancos',
            self::CANCELAMENTO_BAIXA => 'Cancelamento de Baixa',
        };
    }

    public static function isValid(int $value): bool
    {
        return self::tryFrom($value) !== null;
    }
}