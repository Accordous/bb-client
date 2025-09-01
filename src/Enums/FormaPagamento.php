<?php

namespace Accordous\BbClient\Enums;

enum FormaPagamento: int
{
    case DINHEIRO = 1;
    case CHEQUE = 2;
    case DOC_TED = 3;
    case CREDITO_CONTA = 4;
    case PIX = 5;

    public function getDescription(): string
    {
        return match($this) {
            self::DINHEIRO => 'Dinheiro',
            self::CHEQUE => 'Cheque',
            self::DOC_TED => 'DOC/TED',
            self::CREDITO_CONTA => 'Crédito em Conta',
            self::PIX => 'PIX',
        };
    }

    public static function isValid(int $value): bool
    {
        return self::tryFrom($value) !== null;
    }
}