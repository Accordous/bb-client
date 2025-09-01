<?php

namespace Accordous\BbClient\Services\Endpoints;

use Illuminate\Http\Client\Response;

class WebhookEndpoint extends Endpoint
{
    /**
     * Processa webhook de baixa operacional
     */
    public function processarBaixaOperacional(array $data): Response
    {
        $validatedData = $this->validate($data);
        
        return $this->client()->post('/cobrancas/v2/baixa-operacional', $validatedData);
    }

    protected function rules(): array
    {
        return [
            'id' => 'required|string',
            'dataRegistro' => 'required|string',
            'dataVencimento' => 'required|string',
            'valorOriginal' => 'required|numeric',
            'valorPagoSacado' => 'required|numeric',
            'numeroConvenio' => 'required|integer',
            'numeroOperacao' => 'required|integer',
            'carteiraConvenio' => 'required|integer',
            'variacaoCarteiraConvenio' => 'required|integer',
            'codigoEstadoBaixaOperacional' => 'required|integer',
            'dataLiquidacao' => 'nullable|string',
            'instituicaoLiquidacao' => 'nullable|string',
            'canalLiquidacao' => 'nullable|integer',
            'codigoModalidadeBoleto' => 'required|integer',
            'tipoPessoaPortador' => 'nullable|integer',
            'identidadePortador' => 'nullable|string',
            'nomePortador' => 'nullable|string',
            'formaPagamento' => 'nullable|integer',
        ];
    }

    protected function messages(): array
    {
        return [
            'id.required' => 'O ID do boleto é obrigatório.',
            'dataRegistro.required' => 'A data de registro é obrigatória.',
            'dataVencimento.required' => 'A data de vencimento é obrigatória.',
            'valorOriginal.required' => 'O valor original é obrigatório.',
            'valorPagoSacado.required' => 'O valor pago pelo sacado é obrigatório.',
            'numeroConvenio.required' => 'O número do convênio é obrigatório.',
            'numeroOperacao.required' => 'O número da operação é obrigatório.',
            'carteiraConvenio.required' => 'A carteira do convênio é obrigatória.',
            'variacaoCarteiraConvenio.required' => 'A variação da carteira do convênio é obrigatória.',
            'codigoEstadoBaixaOperacional.required' => 'O código do estado da baixa operacional é obrigatório.',
            'codigoModalidadeBoleto.required' => 'O código da modalidade do boleto é obrigatório.',
        ];
    }
}