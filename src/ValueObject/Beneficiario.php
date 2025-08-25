<?php

namespace Accordous\BbClient\ValueObject;

class Beneficiario extends ValueObject
{
    public $tipoInscricao;
    public $numeroInscricao;
    public $nome;
    public $endereco;
    public $cep;
    public $cidade;
    public $bairro;
    public $uf;
    public $telefone;

    public function __construct(
        int $tipoInscricao,
        string $numeroInscricao,
        string $nome,
        string $endereco = '',
        string $cep = '',
        string $cidade = '',
        string $bairro = '',
        string $uf = '',
        string $telefone = ''
    ) {
        $this->tipoInscricao = $tipoInscricao;
        $this->numeroInscricao = $numeroInscricao;
        $this->nome = $nome;
        $this->endereco = $endereco;
        $this->cep = $cep;
        $this->cidade = $cidade;
        $this->bairro = $bairro;
        $this->uf = $uf;
        $this->telefone = $telefone;
    }
}