<?php

namespace Accordous\BbClient\ValueObject;

class Beneficiario extends ValueObject
{
    public int $tipoInscricao;
    public string $numeroInscricao;
    public string $nome;
    public string $endereco;
    public string $cep;
    public string $cidade;
    public string $bairro;
    public string $uf;
    public string $telefone;

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