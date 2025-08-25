<?php

namespace Accordous\BbClient\Enums;

class EstadoTituloCobranca extends Enums
{
    public const REGISTRADO = '01';
    public const LIQUIDADO = '02';
    public const PROTESTADO = '03';
    public const VENCIDO = '04';
    public const CANCELADO = '05';
}