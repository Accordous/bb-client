<?php

namespace Accordous\BbClient\ValueObject;

class Desconto extends ValueObject
{
    public int $tipo;
    public string $dataExpiracao;
    public float|null $porcentagem;
    public float|null $valor;

    public function __construct(
        int $tipo,
        string $dataExpiracao = '',
        float|null $porcentagem = null,
        float|null $valor = null
    ) {
        $this->tipo = $tipo;
        $this->dataExpiracao = $dataExpiracao;
        $this->porcentagem = $porcentagem;
        $this->valor = $valor;
    }
}