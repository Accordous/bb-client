<?php

namespace Accordous\BbClient\ValueObject;

class Multa extends ValueObject
{
    public $tipo;
    public $data;
    public $porcentagem;
    public $valor;

    public function __construct(
        int $tipo,
        string $data = '',
        float $porcentagem = 0.0,
        float $valor = 0.0
    ) {
        $this->tipo = $tipo;
        $this->data = $data;
        $this->porcentagem = $porcentagem;
        $this->valor = $valor;
    }
}