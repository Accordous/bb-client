<?php

namespace Accordous\BbClient\Enums;

/**
 * Enum para códigos de estado de baixa operacional recebidos via webhook
 * Baseado na documentação: README-webhook-WebhookBaixaBoleto.md
 */
enum WebhookEstadoBaixaOperacional: int
{
    case BAIXA_OPERACIONAL_BB = 1;
    case BAIXA_OPERACIONAL_OUTRO_BANCO = 2;
    case CANCELAMENTO_BAIXA_OPERACIONAL = 10;

    public function getDescription(): string
    {
        return match($this) {
            self::BAIXA_OPERACIONAL_BB => 'Baixa Operacional emitida pelo BB',
            self::BAIXA_OPERACIONAL_OUTRO_BANCO => 'Baixa Operacional emitida por outro Banco',
            self::CANCELAMENTO_BAIXA_OPERACIONAL => 'Cancelamento da Baixa Operacional',
        };
    }

    public static function isValid(int $value): bool
    {
        return self::tryFrom($value) !== null;
    }

    public function isPayment(): bool
    {
        return $this === self::BAIXA_OPERACIONAL_BB || $this === self::BAIXA_OPERACIONAL_OUTRO_BANCO;
    }

    public function isCancellation(): bool
    {
        return $this === self::CANCELAMENTO_BAIXA_OPERACIONAL;
    }
}