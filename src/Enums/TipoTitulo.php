<?php

namespace Accordous\BbClient\Enums;

enum TipoTitulo: int
{
    case CHEQUE = 1;
    case DUPLICATA_MERCANTIL = 2;
    case DUPLICATA_SERVICO = 3;
    case NOTA_PROMISSORIA = 4;
    case NOTA_SEGURO = 5;
    case RECIBO = 6;
    case LETRA_CAMBIO = 7;
    case NOTA_DEBITO = 8;
    case NOTA_SERVICO = 9;
    case OUTROS = 99;

    public function getDescription(): string
    {
        return match($this) {
            self::CHEQUE => 'Cheque',
            self::DUPLICATA_MERCANTIL => 'Duplicata Mercantil',
            self::DUPLICATA_SERVICO => 'Duplicata de Serviço',
            self::NOTA_PROMISSORIA => 'Nota Promissória',
            self::NOTA_SEGURO => 'Nota de Seguro',
            self::RECIBO => 'Recibo',
            self::LETRA_CAMBIO => 'Letra de Câmbio',
            self::NOTA_DEBITO => 'Nota de Débito',
            self::NOTA_SERVICO => 'Nota de Serviço',
            self::OUTROS => 'Outros',
        };
    }

    public function isCommercial(): bool
    {
        return match($this) {
            self::DUPLICATA_MERCANTIL,
            self::DUPLICATA_SERVICO,
            self::NOTA_SERVICO => true,
            default => false,
        };
    }

    public static function isValid(int $value): bool
    {
        return self::tryFrom($value) !== null;
    }
}