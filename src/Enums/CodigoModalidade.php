<?php

namespace Accordous\BbClient\Enums;

enum CodigoModalidade: int
{
    case SIMPLES = 1;
    case VINCULADA = 4;

    public function getDescription(): string
    {
        return match($this) {
            self::SIMPLES => 'Modalidade Simples',
            self::VINCULADA => 'Modalidade Vinculada',
        };
    }

    public static function isValid(int $value): bool
    {
        return self::tryFrom($value) !== null;
    }

    public function getFormattedValue(): string
    {
        return str_pad((string)$this->value, 2, '0', STR_PAD_LEFT);
    }
}