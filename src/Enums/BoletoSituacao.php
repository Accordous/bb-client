<?php

namespace Accordous\BbClient\Enums;

class BoletoSituacao extends Enums
{
    public const ATIVO = 'A';
    public const BAIXADO = 'B';
    public const CANCELADO = 'C';
    public const PAGO = 'P';
}