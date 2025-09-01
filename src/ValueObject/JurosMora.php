<?php

namespace Accordous\BbClient\ValueObject;

class JurosMora extends ValueObject
{
    public int $tipo;
    public float|null $porcentagem;
    public float|null $valor;

    public function __construct(
        int $tipo,
        float|null $porcentagem = null,
        float|null $valor = null
    ) {
        $this->tipo = $tipo;
        $this->porcentagem = $porcentagem;
        $this->valor = $valor;
    }
}