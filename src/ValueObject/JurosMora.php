<?php

namespace Accordous\BbClient\ValueObject;

class JurosMora extends ValueObject
{
    public $tipo;
    public $porcentagem;
    public $valor;

    public function __construct(
        int $tipo,
        float $porcentagem = 0.0,
        float $valor = 0.0
    ) {
        $this->tipo = $tipo;
        $this->porcentagem = $porcentagem;
        $this->valor = $valor;
    }
}