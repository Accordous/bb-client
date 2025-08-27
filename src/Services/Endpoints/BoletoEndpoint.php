<?php

namespace Accordous\BbClient\Services\Endpoints;

use Illuminate\Http\Client\Response;

class BoletoEndpoint extends Endpoint
{
    /**
     * Lista boletos com filtros
     *
     * @param array $filters
     * @return Response
     */
    public function list(array $filters = []): Response
    {
        $queryParams = array_filter($filters);
        
        return $this->client()->get('/cobrancas/v2/boletos', $queryParams);
    }

    /**
     * Registra um novo boleto
     *
     * @param array $data
     * @return Response
     */
    public function create(array $data): Response
    {
        $validatedData = $this->validate($data);
        
        return $this->client()->post('/cobrancas/v2/boletos', $validatedData);
    }

    /**
     * Consulta um boleto específico
     *
     * @param string $id
     * @param int $numeroConvenio
     * @return Response
     */
    public function show(string $id, int $numeroConvenio): Response
    {
        return $this->client()->get("/cobrancas/v2/boletos/{$id}", [
            'numeroConvenio' => $numeroConvenio
        ]);
    }

    /**
     * Atualiza um boleto
     *
     * @param string $id
     * @param array $data
     * @return Response
     */
    public function update(string $id, array $data): Response
    {
        $validatedData = $this->validate($data);
        
        return $this->client()->patch("/cobrancas/v2/boletos/{$id}", $validatedData);
    }

    /**
     * Baixa um boleto (cancelamento)
     *
     * @param string $id
     * @param array $data
     * @return Response
     */
    public function baixar(string $id, array $data = []): Response
    {
        return $this->client()->patch("/cobrancas/v2/boletos/{$id}/baixar", $data);
    }

    /**
     * Cancela PIX do boleto
     *
     * @param string $id
     * @return Response
     */
    public function cancelarPix(string $id): Response
    {
        return $this->client()->post("/cobrancas/v2/boletos/{$id}/cancelar-pix");
    }

    /**
     * Gera PIX para o boleto
     *
     * @param string $id
     * @return Response
     */
    public function gerarPix(string $id): Response
    {
        return $this->client()->post("/cobrancas/v2/boletos/{$id}/gerar-pix");
    }

    /**
     * Consulta PIX do boleto
     *
     * @param string $id
     * @return Response
     */
    public function consultarPix(string $id): Response
    {
        return $this->client()->get("/cobrancas/v2/boletos/{$id}/pix");
    }

    /**
     * Consulta baixa operacional
     *
     * @param array $params
     * @return Response
     */
    public function consultarBaixaOperacional(array $params): Response
    {
        $queryParams = array_filter($params);
        
        return $this->client()->get('/cobrancas/v2/boletos-baixa-operacional', $queryParams);
    }

    protected function rules(): array
    {
        return [
            'numeroConvenio' => 'required|integer',
            'numeroCarteira' => 'required|integer',
            'numeroVariacaoCarteira' => 'required|integer',
            'codigoModalidade' => 'required|in:1,4,"01","04"',
            'dataEmissao' => 'required|string',
            'dataVencimento' => 'required|string',
            'valorOriginal' => 'required|numeric|min:0',
            'valorAbatimento' => 'nullable|numeric|min:0',
            'quantidadeDiasProtesto' => 'nullable|integer|min:0',
            'quantidadeDiasNegativacao' => 'nullable|integer|min:0',
            'orgaoNegativador' => 'nullable|integer',
            'indicadorAceiteTituloVencido' => 'nullable|string|in:S,N',
            'numeroDiasLimiteRecebimento' => 'nullable|integer|min:0',
            'codigoAceite' => 'nullable|string|in:A,N',
            'codigoTipoTitulo' => 'required',
            'descricaoTipoTitulo' => 'nullable|string|max:30',
            'indicadorPermissaoRecebimentoParcial' => 'nullable|string|in:S,N',
            'numeroTituloBeneficiario' => 'nullable|string|max:20',
            'campoUtilizacaoBeneficiario' => 'nullable|string|max:25',
            'numeroTituloCliente' => 'nullable|string|max:20',
            'mensagemBloquetoOcorrencia' => 'nullable|string|max:220',
            'desconto' => 'nullable|array',
            'segundoDesconto' => 'nullable|array',
            'terceiroDesconto' => 'nullable|array',
            'jurosMora' => 'nullable|array',
            'multa' => 'nullable|array',
            'pagador' => 'required|array',
            'beneficiarioFinal' => 'nullable|array',
            'indicadorPix' => 'nullable|string|in:S,N',
        ];
    }

    protected function messages(): array
    {
        return [
            'numeroConvenio.required' => 'O número do convênio é obrigatório.',
            'numeroCarteira.required' => 'O número da carteira é obrigatório.',
            'numeroVariacaoCarteira.required' => 'O número da variação da carteira é obrigatório.',
            'codigoModalidade.required' => 'O código da modalidade é obrigatório.',
            'codigoModalidade.in' => 'O código da modalidade deve ser 01 (Simples) ou 04 (Vinculada).',
            'dataEmissao.required' => 'A data de emissão é obrigatória.',
            'dataVencimento.required' => 'A data de vencimento é obrigatória.',
            'valorOriginal.required' => 'O valor original é obrigatório.',
            'valorOriginal.min' => 'O valor original deve ser maior ou igual a zero.',
            'codigoTipoTitulo.required' => 'O código do tipo de título é obrigatório.',
            'pagador.required' => 'Os dados do pagador são obrigatórios.',
        ];
    }
}