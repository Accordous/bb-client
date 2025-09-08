<?php

namespace Accordous\BbClient\Enums;

/**
 * Enum para códigos de estado de baixa operacional recebidos via webhook
 * Baseado na documentação: README-webhook-WebhookBaixaBoleto.md
 */
enum WebhookEstadoBaixaOperacional: int
{
    case LIQUIDACAO = 6;
    case BAIXA_POR_SOLICITACAO = 9;

    public function getDescription(): string
    {
        return match($this) {
            self::LIQUIDACAO => 'Liquidação (pagamento)',
            self::BAIXA_POR_SOLICITACAO => 'Baixa por solicitação',
        };
    }

    public static function isValid(int $value): bool
    {
        return self::tryFrom($value) !== null;
    }

    public function isPayment(): bool
    {
        return $this === self::LIQUIDACAO;
    }

    public function isCancellation(): bool
    {
        return $this === self::BAIXA_POR_SOLICITACAO;
    }
}