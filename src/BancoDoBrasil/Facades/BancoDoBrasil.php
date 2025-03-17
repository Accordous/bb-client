<?php

namespace BancoDoBrasil\Facades;

use Illuminate\Support\Facades\Facade;
use BancoDoBrasil\Http\BancoDoBrasilClient;

/**
 * @method static string getToken()
 * @method static array registrarBoletoCobranca(array $data)
 * 
 * @see \BancoDoBrasil\Http\BancoDoBrasilClient
 */
class BancoDoBrasil extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return BancoDoBrasilClient::class;
    }
} 