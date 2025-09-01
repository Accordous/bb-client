<?php

namespace Accordous\BbClient\ValueObject;

use Accordous\BbClient\Enums\CodigoModalidade;
use Accordous\BbClient\Enums\TipoTitulo;
use Accordous\BbClient\Enums\TipoInscricao;
use Exception;

class BoletoBuilder extends ValueObject
{
    private array $data = [];

    public function __construct()
    {
        $this->data = [
            'indicadorPix' => 'S', // Default to enable PIX
        ];
    }

    public function numeroConvenio(int $numeroConvenio): self
    {
        $this->data['numeroConvenio'] = $numeroConvenio;
        return $this;
    }

    public function numeroCarteira(int $numeroCarteira): self
    {
        $this->data['numeroCarteira'] = $numeroCarteira;
        return $this;
    }

    public function numeroVariacaoCarteira(int $numeroVariacaoCarteira): self
    {
        $this->data['numeroVariacaoCarteira'] = $numeroVariacaoCarteira;
        return $this;
    }

    public function codigoModalidade(CodigoModalidade|string $codigoModalidade): self
    {
        // Converte enum para valor se necessário
        $codigoModalidadeValue = $codigoModalidade instanceof CodigoModalidade ? $codigoModalidade->value : $codigoModalidade;

        if (!CodigoModalidade::isValid($codigoModalidadeValue)) {
            throw new Exception('Código de modalidade inválido. Use 01 (Simples) ou 04 (Vinculada).');
        }
        $this->data['codigoModalidade'] = $codigoModalidadeValue;
        return $this;
    }

    public function dataEmissao(string $dataEmissao): self
    {
        $this->data['dataEmissao'] = $dataEmissao;
        return $this;
    }

    public function dataVencimento(string $dataVencimento): self
    {
        $this->data['dataVencimento'] = $dataVencimento;
        return $this;
    }

    public function valorOriginal(float $valorOriginal): self
    {
        $this->data['valorOriginal'] = $valorOriginal;
        return $this;
    }

    public function valorAbatimento(float $valorAbatimento): self
    {
        $this->data['valorAbatimento'] = $valorAbatimento;
        return $this;
    }

    public function quantidadeDiasProtesto(int $quantidadeDiasProtesto): self
    {
        $this->data['quantidadeDiasProtesto'] = $quantidadeDiasProtesto;
        return $this;
    }

    public function quantidadeDiasNegativacao(int $quantidadeDiasNegativacao): self
    {
        $this->data['quantidadeDiasNegativacao'] = $quantidadeDiasNegativacao;
        return $this;
    }

    public function codigoTipoTitulo(TipoTitulo|int $codigoTipoTitulo): self
    {
        // Converte enum para valor se necessário
        $codigoTipoTituloValue = $codigoTipoTitulo instanceof TipoTitulo ? $codigoTipoTitulo->value : $codigoTipoTitulo;

        if (!TipoTitulo::isValid($codigoTipoTituloValue)) {
            throw new Exception('Código de tipo de título inválido.');
        }
        $this->data['codigoTipoTitulo'] = $codigoTipoTituloValue;
        return $this;
    }

    public function descricaoTipoTitulo(string $descricaoTipoTitulo): self
    {
        $this->data['descricaoTipoTitulo'] = $descricaoTipoTitulo;
        return $this;
    }

    public function numeroTituloBeneficiario(string $numeroTituloBeneficiario): self
    {
        $this->data['numeroTituloBeneficiario'] = $numeroTituloBeneficiario;
        return $this;
    }

    public function numeroTituloCliente(string $numeroTituloCliente): self
    {
        $this->data['numeroTituloCliente'] = $numeroTituloCliente;
        return $this;
    }

    public function mensagemBloquetoOcorrencia(string $mensagem): self
    {
        $this->data['mensagemBloquetoOcorrencia'] = $mensagem;
        return $this;
    }

    public function pagador(Pagador $pagador): self
    {
        $this->data['pagador'] = $pagador->toArray();
        return $this;
    }

    public function beneficiarioFinal(Beneficiario $beneficiario): self
    {
        $this->data['beneficiarioFinal'] = $beneficiario->toArray();
        return $this;
    }

    public function desconto(Desconto $desconto): self
    {
        $this->data['desconto'] = $desconto->toArray();
        return $this;
    }

    public function segundoDesconto(Desconto $desconto): self
    {
        $this->data['segundoDesconto'] = $desconto->toArray();
        return $this;
    }

    public function terceiroDesconto(Desconto $desconto): self
    {
        $this->data['terceiroDesconto'] = $desconto->toArray();
        return $this;
    }

    public function jurosMora(JurosMora $jurosMora): self
    {
        $this->data['jurosMora'] = $jurosMora->toArray();
        return $this;
    }

    public function multa(Multa $multa): self
    {
        $this->data['multa'] = $multa->toArray();
        return $this;
    }

    public function indicadorPix(string $indicadorPix): self
    {
        if (!in_array($indicadorPix, ['S', 'N'])) {
            throw new Exception('Indicador PIX deve ser S ou N.');
        }
        $this->data['indicadorPix'] = $indicadorPix;
        return $this;
    }

    public function indicadorAceiteTituloVencido(string $indicador): self
    {
        if (!in_array($indicador, ['S', 'N'])) {
            throw new Exception('Indicador deve ser S ou N.');
        }
        $this->data['indicadorAceiteTituloVencido'] = $indicador;
        return $this;
    }

    public function indicadorPermissaoRecebimentoParcial(string $indicador): self
    {
        if (!in_array($indicador, ['S', 'N'])) {
            throw new Exception('Indicador deve ser S ou N.');
        }
        $this->data['indicadorPermissaoRecebimentoParcial'] = $indicador;
        return $this;
    }

    public function numeroDiasLimiteRecebimento(int $numeroDias): self
    {
        $this->data['numeroDiasLimiteRecebimento'] = $numeroDias;
        return $this;
    }

    public function codigoAceite(string $codigoAceite): self
    {
        if (!in_array($codigoAceite, ['A', 'N'])) {
            throw new Exception('Código de aceite deve ser A ou N.');
        }
        $this->data['codigoAceite'] = $codigoAceite;
        return $this;
    }

    public function build(): array
    {
        // Validate required fields
        $requiredFields = [
            'numeroConvenio',
            'numeroCarteira',
            'numeroVariacaoCarteira',
            'codigoModalidade',
            'dataEmissao',
            'dataVencimento',
            'valorOriginal',
            'codigoTipoTitulo',
            'pagador'
        ];

        foreach ($requiredFields as $field) {
            if (!isset($this->data[$field])) {
                throw new Exception("Campo obrigatório não informado: {$field}");
            }
        }

        return $this->data;
    }
}