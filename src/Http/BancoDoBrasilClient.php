<?php

namespace Accordous\BbClient\Http;

use Accordous\BbClient\Services\BancoDoBrasilService;
use Accordous\BbClient\Services\Endpoints\BoletoEndpoint;
use Accordous\BbClient\Services\Endpoints\ConvenioEndpoint;
use Accordous\BbClient\Services\Endpoints\WebhookEndpoint;
use Illuminate\Http\Client\RequestException;

class BancoDoBrasilClient
{
    /**
     * @var BancoDoBrasilService
     */
    protected BancoDoBrasilService $service;

    /**
     * BancoDoBrasilClient constructor.
     */
    public function __construct(string $clientId, string $clientSecret, string $developerApplicationKey, string $convenio = '')
    {
        $this->service = new BancoDoBrasilService($clientId, $clientSecret, $developerApplicationKey, $convenio);
    }

    /**
     * Get OAuth token for API authentication.
     *
     * @throws RequestException
     */
    public function getToken(): string
    {
        return $this->service->getToken();
    }

    /**
     * Get boletos service
     */
    public function boletos(): BoletoEndpoint
    {
        return $this->service->boletos();
    }

    /**
     * Get convenios service
     */
    public function convenios(): ConvenioEndpoint
    {
        return $this->service->convenios();
    }

    /**
     * Get webhooks service
     */
    public function webhooks(): WebhookEndpoint
    {
        return $this->service->webhooks();
    }
}