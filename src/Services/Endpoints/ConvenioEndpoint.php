<?php

namespace Accordous\BbClient\Services\Endpoints;

use Illuminate\Http\Client\Response;

class ConvenioEndpoint extends Endpoint
{
    /**
     * Ativar consulta de baixa operacional
     *
     * @param string $id
     * @return Response
     */
    public function ativarConsultaBaixaOperacional(string $id): Response
    {
        $path = "/cobrancas/v2/convenios/{$id}/ativar-consulta-baixa-operacional?" . http_build_query($this->addDevAppKey());
        $response = $this->client()->patch($path);
        
        // If response is not successful and body is empty, the API may have returned JSON
        // but Laravel HTTP client didn't capture it properly. Let's make a cURL request instead.
        if (!$response->successful() && empty($response->body())) {
            return $this->makeCurlRequest('PATCH', $path);
        }
        
        return $response;
    }

    /**
     * Desativar consulta de baixa operacional
     *
     * @param string $id
     * @return Response
     */
    public function desativarConsultaBaixaOperacional(string $id): Response
    {
        $path = "/cobrancas/v2/convenios/{$id}/desativar-consulta-baixa-operacional?" . http_build_query($this->addDevAppKey());
        $response = $this->client()->patch($path);
        
        // If response is not successful and body is empty, the API may have returned JSON
        // but Laravel HTTP client didn't capture it properly. Let's make a cURL request instead.
        if (!$response->successful() && empty($response->body())) {
            return $this->makeCurlRequest('PATCH', $path);
        }
        
        return $response;
    }

    /**
     * Listar retorno de movimento
     *
     * @param string $id
     * @param array $params
     * @return Response
     */
    public function listarRetornoMovimento(string $id, array $params = []): Response
    {
        $queryParams = $this->addDevAppKey(array_filter($params));
        
        return $this->client()->post("/cobrancas/v2/convenios/{$id}/listar-retorno-movimento?" . http_build_query($queryParams), []);
    }
}