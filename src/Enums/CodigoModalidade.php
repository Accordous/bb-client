<?php

namespace Accordous\BbClient\Enums;

enum CodigoModalidade: string
{
    case SIMPLES = '01';
    case VINCULADA = '04';

    public function getDescription(): string
    {
        return match($this) {
            self::SIMPLES => 'Modalidade Simples',
            self::VINCULADA => 'Modalidade Vinculada',
        };
    }

    public static function isValid(string $value): bool
    {
        return self::tryFrom($value) !== null;
    }
}