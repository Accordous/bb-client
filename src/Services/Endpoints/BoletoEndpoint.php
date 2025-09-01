<?php

namespace Accordous\BbClient\Services\Endpoints;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Validator;

class BoletoEndpoint extends Endpoint
{
    /**
     * Lista boletos com filtros
     */
    public function list(array $filters = []): Response
    {
        // Validate required parameters for boleto listing
        $requiredParams = [
            'indicadorSituacao',
            'agenciaBeneficiario',
            'contaBeneficiario'
        ];
        foreach ($requiredParams as $param) {
            if (!isset($filters[$param]) || empty($filters[$param])) {
                throw new \InvalidArgumentException("Parameter '{$param}' is required for boleto listing");
            }
        }
        
        $queryParams = $this->addDevAppKey(array_filter($filters));
        
        return $this->client()->get('/cobrancas/v2/boletos', $queryParams);
    }

    /**
     * Registra um novo boleto
     */
    public function create(array $data): Response
    {
        $validatedData = $this->validate($data);

        return $this->client()->post('/cobrancas/v2/boletos?' . http_build_query($this->addDevAppKey()), $validatedData);
    }

    /**
     * Consulta um boleto específico
     */
    public function show(string $id, int $numeroConvenio): Response
    {
        $queryParams = $this->addDevAppKey([
            'numeroConvenio' => $numeroConvenio
        ]);
        
        return $this->client()->get("/cobrancas/v2/boletos/{$id}", $queryParams);
    }

    /**
     * Atualiza um boleto
     */
    public function update(string $id, array $data): Response
    {
        $validatedData = $this->validateUpdate($data);
        
        return $this->client()->patch("/cobrancas/v2/boletos/{$id}?" . http_build_query($this->addDevAppKey()), $validatedData);
    }

    /**
     * Baixa um boleto (cancelamento)
     */
    public function baixar(string $id, array $data = []): Response
    {
        return $this->client()->patch("/cobrancas/v2/boletos/{$id}/baixar?" . http_build_query($this->addDevAppKey()), $data);
    }

    /**
     * Consulta baixa operacional
     */
    public function consultarBaixaOperacional(array $params): Response
    {
        $queryParams = $this->addDevAppKey(array_filter($params));
        
        return $this->client()->get('/cobrancas/v2/boletos-baixa-operacional', $queryParams);
    }

    /**
     * Cancela PIX do boleto
     */
    public function cancelarPix(string $id): Response
    {
        $path = "/cobrancas/v2/boletos/{$id}/cancelar-pix?" . http_build_query($this->addDevAppKey());
        
        $response = $this->client()->post($path);
        
        // If response is not successful and body is empty, the API may have returned JSON
        // but Laravel HTTP client didn't capture it properly. Let's make a cURL request instead.
        if (!$response->successful() && empty($response->body())) {
            $baseUrl = config('banco-do-brasil.base_url', 'https://api.hm.bb.com.br');
            $fullUrl = $baseUrl . $path;
            return $this->makeCurlRequest('POST', $fullUrl);
        }
        
        return $response;
    }

    /**
     * Gera PIX para o boleto
     */
    public function gerarPix(string $id): Response
    {
        $path = "/cobrancas/v2/boletos/{$id}/gerar-pix?" . http_build_query($this->addDevAppKey());
        
        // Enviar número do convênio no corpo da requisição como exigido pela API
        $body = [
            'numeroConvenio' => (int) config('banco-do-brasil.convenio', 3128557)
        ];
        
        $response = $this->client()->post($path, $body);
        
        // If response is not successful and body is empty, the API may have returned JSON
        // but Laravel HTTP client didn't capture it properly. Let's make a cURL request instead.
        if (!$response->successful() && empty($response->body())) {
            return $this->makeCurlRequest('POST', $path, $body);
        }
        
        return $response;
    }

    /**
     * Consulta PIX do boleto
     */
    public function consultarPix(string $id): Response
    {
        $path = "/cobrancas/v2/boletos/{$id}/pix";
        $queryParams = $this->addDevAppKey();
        
        $response = $this->client()->get($path, $queryParams);
        
        // If response is not successful and body is empty, the API may have returned JSON
        // but Laravel HTTP client didn't capture it properly. Let's make a cURL request instead.
        if (!$response->successful() && empty($response->body())) {
            $baseUrl = config('banco-do-brasil.base_url', 'https://api.hm.bb.com.br');
            $fullUrl = $baseUrl . $path . '?' . http_build_query($queryParams);
            return $this->makeCurlRequest('GET', $fullUrl);
        }
        
        return $response;
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

    /**
     * Valida dados para atualização de boleto
     */
    protected function validateUpdate(array $attributes): array
    {
        return Validator::validate($attributes, $this->updateRules(), $this->updateMessages());
    }

    /**
     * Regras de validação para atualização de boleto
     */
    protected function updateRules(): array
    {
        return [
            'numeroConvenio' => 'required|integer',
            
            // Alteração de data de vencimento
            'indicadorNovaDataVencimento' => 'nullable|string|in:S,N',
            'alteracaoData' => 'nullable|array',
            'alteracaoData.novaDataVencimento' => 'nullable|string',
            
            // Alteração de valor nominal
            'indicadorNovoValorNominal' => 'nullable|string|in:S,N',
            'alteracaoValor' => 'nullable|array',
            'alteracaoValor.novoValorNominal' => 'nullable|numeric|min:0',
            
            // Inclusão de desconto
            'indicadorAtribuirDesconto' => 'nullable|string|in:S,N',
            'desconto' => 'nullable|array',
            'desconto.tipoPrimeiroDesconto' => 'nullable|integer|in:0,1,2,3',
            'desconto.valorPrimeiroDesconto' => 'nullable|numeric|min:0',
            'desconto.percentualPrimeiroDesconto' => 'nullable|numeric|min:0',
            'desconto.dataPrimeiroDesconto' => 'nullable|string',
            'desconto.tipoSegundoDesconto' => 'nullable|integer|in:0,1,2,3',
            'desconto.valorSegundoDesconto' => 'nullable|numeric|min:0',
            'desconto.percentualSegundoDesconto' => 'nullable|numeric|min:0',
            'desconto.dataSegundoDesconto' => 'nullable|string',
            'desconto.tipoTerceiroDesconto' => 'nullable|integer|in:0,1,2,3',
            'desconto.valorTerceiroDesconto' => 'nullable|numeric|min:0',
            'desconto.percentualTerceiroDesconto' => 'nullable|numeric|min:0',
            'desconto.dataTerceiroDesconto' => 'nullable|string',
            
            // Alteração de desconto existente
            'indicadorAlterarDesconto' => 'nullable|string|in:S,N',
            'alteracaoDesconto' => 'nullable|array',
            'alteracaoDesconto.tipoPrimeiroDesconto' => 'nullable|integer|in:0,1,2,3',
            'alteracaoDesconto.novoValorPrimeiroDesconto' => 'nullable|numeric|min:0',
            'alteracaoDesconto.novoPercentualPrimeiroDesconto' => 'nullable|numeric|min:0',
            'alteracaoDesconto.novaDataLimitePrimeiroDesconto' => 'nullable|string',
            'alteracaoDesconto.tipoSegundoDesconto' => 'nullable|integer|in:0,1,2,3',
            'alteracaoDesconto.novoValorSegundoDesconto' => 'nullable|numeric|min:0',
            'alteracaoDesconto.novoPercentualSegundoDesconto' => 'nullable|numeric|min:0',
            'alteracaoDesconto.novaDataLimiteSegundoDesconto' => 'nullable|string',
            'alteracaoDesconto.tipoTerceiroDesconto' => 'nullable|integer|in:0,1,2,3',
            'alteracaoDesconto.novoValorTerceiroDesconto' => 'nullable|numeric|min:0',
            'alteracaoDesconto.novoPercentualTerceiroDesconto' => 'nullable|numeric|min:0',
            'alteracaoDesconto.novaDataLimiteTerceiroDesconto' => 'nullable|string',
            
            // Alteração de data de desconto
            'indicadorAlterarDataDesconto' => 'nullable|string|in:S,N',
            'alteracaoDataDesconto' => 'nullable|array',
            'alteracaoDataDesconto.novaDataLimitePrimeiroDesconto' => 'nullable|string',
            'alteracaoDataDesconto.novaDataLimiteSegundoDesconto' => 'nullable|string',
            'alteracaoDataDesconto.novaDataLimiteTerceiroDesconto' => 'nullable|string',
            
            // Protesto
            'indicadorProtestar' => 'nullable|string|in:S,N',
            'protesto' => 'nullable|array',
            'protesto.quantidadeDiasProtesto' => 'nullable|numeric|min:0',
            'indicadorSustacaoProtesto' => 'nullable|string|in:S,N',
            'indicadorCancelarProtesto' => 'nullable|string|in:S,N',
            
            // Abatimento
            'indicadorIncluirAbatimento' => 'nullable|string|in:S,N',
            'abatimento' => 'nullable|array',
            'abatimento.valorAbatimento' => 'nullable|numeric|min:0',
            'indicadorAlterarAbatimento' => 'nullable|string|in:S,N',
            'alteracaoAbatimento' => 'nullable|array',
            'alteracaoAbatimento.novoValorAbatimento' => 'nullable|numeric|min:0',
            
            // Juros de mora
            'indicadorCobrarJuros' => 'nullable|string|in:S,N',
            'juros' => 'nullable|array',
            'juros.tipoJuros' => 'nullable|integer|in:0,1,2,3',
            'juros.valorJuros' => 'nullable|numeric|min:0',
            'juros.taxaJuros' => 'nullable|numeric|min:0',
            'indicadorDispensarJuros' => 'nullable|string|in:S,N',
            
            // Multa
            'indicadorCobrarMulta' => 'nullable|string|in:S,N',
            'multa' => 'nullable|array',
            'multa.tipoMulta' => 'nullable|integer|in:0,1,2',
            'multa.valorMulta' => 'nullable|numeric|min:0',
            'multa.taxaMulta' => 'nullable|numeric|min:0',
            'multa.dataInicioMulta' => 'nullable|string',
            'indicadorDispensarMulta' => 'nullable|string|in:S,N',
            
            // Negativação
            'indicadorNegativar' => 'nullable|string|in:S,N',
            'negativacao' => 'nullable|array',
            'negativacao.quantidadeDiasNegativacao' => 'nullable|integer|min:0',
            'negativacao.tipoNegativacao' => 'nullable|integer|in:1,2,3',
            'negativacao.orgaoNegativador' => 'nullable|integer',
            
            // Alteração de seu número
            'indicadorAlterarSeuNumero' => 'nullable|string|in:S,N',
            'alteracaoSeuNumero' => 'nullable|array',
            'alteracaoSeuNumero.codigoSeuNumero' => 'nullable|string|max:20',
            
            // Alteração de endereço do pagador
            'indicadorAlterarEnderecoPagador' => 'nullable|string|in:S,N',
            'alteracaoEndereco' => 'nullable|array',
            'alteracaoEndereco.enderecoPagador' => 'nullable|string',
            'alteracaoEndereco.bairroPagador' => 'nullable|string',
            'alteracaoEndereco.cidadePagador' => 'nullable|string',
            'alteracaoEndereco.UFPagador' => 'nullable|string|size:2',
            'alteracaoEndereco.CEPPagador' => 'nullable|integer',
            
            // Alteração de prazo do boleto vencido
            'indicadorAlterarPrazoBoletoVencido' => 'nullable|string|in:S,N',
            'alteracaoPrazo' => 'nullable|array',
            'alteracaoPrazo.quantidadeDiasAceite' => 'nullable|integer|min:0',
        ];
    }

    /**
     * Mensagens de validação para atualização de boleto
     */
    protected function updateMessages(): array
    {
        return [
            'numeroConvenio.required' => 'O número do convênio é obrigatório.',
            'numeroConvenio.integer' => 'O número do convênio deve ser um número inteiro.',
            
            'indicadorNovaDataVencimento.in' => 'O indicador de nova data de vencimento deve ser S ou N.',
            'alteracaoValor.novoValorNominal.numeric' => 'O novo valor nominal deve ser um número.',
            'alteracaoValor.novoValorNominal.min' => 'O novo valor nominal deve ser maior ou igual a zero.',
            
            'desconto.tipoPrimeiroDesconto.in' => 'O tipo do primeiro desconto deve ser 0, 1, 2 ou 3.',
            'desconto.valorPrimeiroDesconto.numeric' => 'O valor do primeiro desconto deve ser um número.',
            'desconto.valorPrimeiroDesconto.min' => 'O valor do primeiro desconto deve ser maior ou igual a zero.',
            
            'protesto.quantidadeDiasProtesto.numeric' => 'A quantidade de dias para protesto deve ser um número.',
            'protesto.quantidadeDiasProtesto.min' => 'A quantidade de dias para protesto deve ser maior ou igual a zero.',
            
            'abatimento.valorAbatimento.numeric' => 'O valor do abatimento deve ser um número.',
            'abatimento.valorAbatimento.min' => 'O valor do abatimento deve ser maior ou igual a zero.',
            
            'juros.tipoJuros.in' => 'O tipo de juros deve ser 0, 1, 2 ou 3.',
            'multa.tipoMulta.in' => 'O tipo de multa deve ser 0, 1 ou 2.',
            
            'negativacao.tipoNegativacao.in' => 'O tipo de negativação deve ser 1, 2 ou 3.',
            'alteracaoSeuNumero.codigoSeuNumero.max' => 'O código do seu número deve ter no máximo 20 caracteres.',
            'alteracaoEndereco.UFPagador.size' => 'A UF deve ter exatamente 2 caracteres.',
        ];
    }
}