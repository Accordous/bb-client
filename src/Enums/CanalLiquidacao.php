<?php

namespace Accordous\BbClient\Enums;

enum CanalLiquidacao: int
{
    case AGENCIA = 1;
    case CORRESPONDENTE = 2;
    case INTERNET_BANKING = 3;
    case MOBILE_BANKING = 4;
    case ATM = 5;

    public function getDescription(): string
    {
        return match($this) {
            self::AGENCIA => 'Agência',
            self::CORRESPONDENTE => 'Correspondente',
            self::INTERNET_BANKING => 'Internet Banking',
            self::MOBILE_BANKING => 'Mobile Banking',
            self::ATM => 'ATM',
        };
    }

    public static function isValid(int $value): bool
    {
        return self::tryFrom($value) !== null;
    }
}