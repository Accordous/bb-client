<?php

namespace Accordous\BbClient\Facades;

use Illuminate\Support\Facades\Facade;
use Accordous\BbClient\Http\BancoDoBrasilClient;

/**
 * @method static string getToken()
 * @method static \Accordous\BbClient\Services\Endpoints\BoletoEndpoint boletos()
 * @method static \Accordous\BbClient\Services\Endpoints\ConvenioEndpoint convenios()
 * @method static \Accordous\BbClient\Services\Endpoints\WebhookEndpoint webhooks()
 * 
 * @see \Accordous\BbClient\Http\BancoDoBrasilClient
 */
class BancoDoBrasil extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return BancoDoBrasilClient::class;
    }
}