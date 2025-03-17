# Banco do Brasil API Integration for Laravel

A Laravel package for integrating with Banco do Brasil's API services, focusing on "Boleto de Cobrança" registration.

## Installation

You can install the package via composer:

```bash
composer require accordous/bb-client
```

## Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --provider="BancoDoBrasil\BancoDoBrasilServiceProvider" --tag="config"
```

Then, add the following environment variables to your `.env` file:

```
BB_API_BASE_URL=https://api.hm.bb.com.br
BB_OAUTH_URL=https://oauth.hm.bb.com.br
BB_CLIENT_ID=seu-client-id-aqui
BB_CLIENT_SECRET=seu-client-secret-aqui
BB_DEVELOPER_APPLICATION_KEY=sua-developer-key-aqui
BB_GW_APP_KEY=sua-gw-app-key-aqui
BB_API_TIMEOUT=30
BB_API_CONNECT_TIMEOUT=10
```

## Usage

### OAuth Authentication

To get an authentication token:

```php
use BancoDoBrasil\Facades\BancoDoBrasil;

$token = BancoDoBrasil::getToken();
```

### Boleto de Cobrança

```php
use BancoDoBrasil\Facades\BancoDoBrasil;

$data = [
    // Your data
];

$response = BancoDoBrasil::registrarBoletoCobranca($data);
```
