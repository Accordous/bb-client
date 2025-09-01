<?php

namespace Accordous\BbClient\Http;

use Accordous\BbClient\Services\BancoDoBrasilService;
use Illuminate\Http\Client\RequestException;

class BancoDoBrasilClient
{
    /**
     * @var BancoDoBrasilService
     */
    protected $service;

    /**
     * BancoDoBrasilClient constructor.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->service = new BancoDoBrasilService($config);
    }

    /**
     * Get OAuth token for API authentication.
     *
     * @return string
     * @throws RequestException
     */
    public function getToken()
    {
        return $this->service->getToken();
    }

    /**
     * Get boletos service
     *
     * @return \Accordous\BbClient\Services\Endpoints\BoletoEndpoint
     */
    public function boletos()
    {
        return $this->service->boletos();
    }

    /**
     * Get convenios service
     *
     * @return \Accordous\BbClient\Services\Endpoints\ConvenioEndpoint
     */
    public function convenios()
    {
        return $this->service->convenios();
    }

    /**
     * Get webhooks service
     *
     * @return \Accordous\BbClient\Services\Endpoints\WebhookEndpoint
     */
    public function webhooks()
    {
        return $this->service->webhooks();
    }
}