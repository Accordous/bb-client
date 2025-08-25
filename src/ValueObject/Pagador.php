<?php

namespace Accordous\BbClient\ValueObject;

use Accordous\BbClient\Enums\TipoInscricao;
use Exception;

class Pagador extends ValueObject
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
        if (!TipoInscricao::isValid($tipoInscricao)) {
            throw new Exception('Tipo de inscrição inválido.');
        }

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