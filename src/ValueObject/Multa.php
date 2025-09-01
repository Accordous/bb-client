<?php

namespace Accordous\BbClient\ValueObject;

class Multa extends ValueObject
{
    public int $tipo;
    public string $data;
    public float|null $porcentagem;
    public float|null $valor;

    public function __construct(
        int $tipo,
        string $data = '',
        float|null $porcentagem = null,
        float|null $valor = null
    ) {
        $this->tipo = $tipo;
        $this->data = $data;
        $this->porcentagem = $porcentagem;
        $this->valor = $valor;
    }
}