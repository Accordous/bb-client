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
        TipoInscricao|int $tipoInscricao,
        string $numeroInscricao,
        string $nome,
        string $endereco = '',
        string $cep = '',
        string $cidade = '',
        string $bairro = '',
        string $uf = '',
        string $telefone = ''
    ) {
        // Converte enum para valor se necessário
        $tipoInscricaoValue = $tipoInscricao instanceof TipoInscricao ? $tipoInscricao->value : $tipoInscricao;

        if (!TipoInscricao::isValid($tipoInscricaoValue)) {
            throw new Exception('Tipo de inscrição inválido.');
        }

        $this->tipoInscricao = $tipoInscricaoValue;
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