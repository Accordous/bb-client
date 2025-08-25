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
        return $this->client()->patch("/cobrancas/v2/convenios/{$id}/ativar-consulta-baixa-operacional");
    }

    /**
     * Desativar consulta de baixa operacional
     *
     * @param string $id
     * @return Response
     */
    public function desativarConsultaBaixaOperacional(string $id): Response
    {
        return $this->client()->patch("/cobrancas/v2/convenios/{$id}/desativar-consulta-baixa-operacional");
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
        $queryParams = array_filter($params);
        
        return $this->client()->post("/cobrancas/v2/convenios/{$id}/listar-retorno-movimento", $queryParams);
    }
}