<?php

namespace Accordous\BbClient\Data;

use Accordous\BbClient\Enums\WebhookEstadoBaixaOperacional;
use Carbon\Carbon;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

class WebhookBaixaBoletoData extends Data
{
    public function __construct(
        /** Identificador único do boleto no sistema do Banco do Brasil */
        public string $id,
        
        /** Data em que o boleto foi registrado no sistema (formato dd.mm.aaaa) */
        #[MapInputName('dataRegistro')]
        public string $data_registro,
        
        /** Data de vencimento do boleto (formato dd.mm.aaaa) */
        #[MapInputName('dataVencimento')]
        public string $data_vencimento,
        
        /** Valor original do boleto */
        #[MapInputName('valorOriginal')]
        public float $valor_original,
        
        /** Valor efetivamente pago pelo sacado */
        #[MapInputName('valorPagoSacado')]
        public ?float $valor_pago_sacado = null,
        
        /** Identificador do convênio de cobrança */
        #[MapInputName('numeroConvenio')]
        public int $numero_convenio,
        
        /** Número da operação bancária */
        #[MapInputName('numeroOperacao')]
        public ?int $numero_operacao = null,
        
        /** Número da carteira do convênio */
        #[MapInputName('carteiraConvenio')]
        public ?int $carteira_convenio = null,
        
        /** Variação da carteira do convênio */
        #[MapInputName('variacaoCarteiraConvenio')]
        public ?int $variacao_carteira_convenio = null,
        
        /** Código que representa o tipo de baixa operacional */
        #[MapInputName('codigoEstadoBaixaOperacional')]
        public int $codigo_estado_baixa_operacional,
        
        /** Data e hora em que o boleto foi liquidado (formato dd/mm/aaaa HH:mm:ss) */
        #[MapInputName('dataLiquidacao')]
        public ?string $data_liquidacao = null,
        
        /** Código da instituição recebedora que processou o pagamento */
        #[MapInputName('instituicaoLiquidacao')]
        public ?int $instituicao_liquidacao = null,
        
        /** Código do canal utilizado para liquidação */
        #[MapInputName('canalLiquidacao')]
        public ?int $canal_liquidacao = null,
        
        /** Código da modalidade de cobrança utilizada */
        #[MapInputName('codigoModalidadeBoleto')]
        public ?int $codigo_modalidade_boleto = null,
        
        /** Tipo de pessoa do pagador (1 - PF, 2 - PJ) */
        #[MapInputName('tipoPessoaPortador')]
        public ?int $tipo_pessoa_portador = null,
        
        /** Número do CPF ou CNPJ do pagador */
        #[MapInputName('identidadePortador')]
        public ?int $identidade_portador = null,
        
        /** Nome completo do pagador */
        #[MapInputName('nomePortador')]
        public ?string $nome_portador = null,
        
        /** Código da forma de pagamento utilizada */
        #[MapInputName('formaPagamento')]
        public ?int $forma_pagamento = null,
    ) {}

    /**
     * Converte o estado da baixa operacional para enum
     */
    public function getEstadoBaixaOperacional(): ?WebhookEstadoBaixaOperacional
    {
        return WebhookEstadoBaixaOperacional::tryFrom($this->codigo_estado_baixa_operacional);
    }

    /**
     * Converte a data de liquidação para Carbon
     */
    public function getDataLiquidacaoAsCarbon(): ?Carbon
    {
        if (!$this->data_liquidacao) {
            return null;
        }

        try {
            // Formato esperado: "dd/mm/aaaa HH:mm:ss"
            return Carbon::createFromFormat('d/m/Y H:i:s', $this->data_liquidacao);
        } catch (\Exception) {
            return null;
        }
    }

    /**
     * Converte a data de registro para Carbon
     */
    public function getDataRegistroAsCarbon(): Carbon
    {
        return Carbon::createFromFormat('d.m.Y', $this->data_registro);
    }

    /**
     * Converte a data de vencimento para Carbon
     */
    public function getDataVencimentoAsCarbon(): Carbon
    {
        return Carbon::createFromFormat('d.m.Y', $this->data_vencimento);
    }

    /**
     * Valor efetivamente pago (fallback para valor original se não informado)
     */
    public function getValorPago(): float
    {
        return $this->valor_pago_sacado ?? $this->valor_original;
    }

    /**
     * Cria uma collection de webhooks a partir de um array
     */
    #[DataCollectionOf(WebhookBaixaBoletoData::class)]
    public static function collection(array $items): DataCollection
    {
        return static::collect($items, DataCollection::class);
    }
}