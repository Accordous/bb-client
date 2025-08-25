<?php

namespace Accordous\BbClient\ValueObject;

class Desconto extends ValueObject
{
    public $tipo;
    public $dataExpiracao;
    public $porcentagem;
    public $valor;

    public function __construct(
        int $tipo,
        string $dataExpiracao = '',
        float $porcentagem = 0.0,
        float $valor = 0.0
    ) {
        $this->tipo = $tipo;
        $this->dataExpiracao = $dataExpiracao;
        $this->porcentagem = $porcentagem;
        $this->valor = $valor;
    }
}