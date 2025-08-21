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

## REST API

### 🧾 Endpoints de Boletos

| Método | Endpoint | Descrição |
|--------|----------|-----------|
| `GET`  | `/boletos` | Lista boletos por filtros e situação |
| `POST` | `/boletos` | Registra um novo boleto de cobrança |
| `GET`  | `/boletos/{id}` | Consulta detalhada de um boleto específico |
| `PATCH`| `/boletos/{id}` | Altera dados de um boleto existente |
| `POST` | `/boletos/{id}/baixar` | Realiza a baixa (cancelamento) de um boleto |
| `POST` | `/boletos/{id}/cancelar-pix` | Cancela o Pix vinculado ao boleto |
| `POST` | `/boletos/{id}/gerar-pix` | Gera um Pix vinculado ao boleto |
| `GET`  | `/boletos/{id}/pix` | Consulta os dados do Pix vinculado ao boleto |
| `GET`  | `/boletos-baixa-operacional` | Consulta informações de baixa operacional de boletos |

---

### 🔄 Endpoints de Movimento

| Método | Endpoint | Descrição |
|--------|----------|-----------|
| `POST` | `/convenios/{id}/listar-retorno-movimento` | Lista dados de retorno de movimentações vinculadas aos boletos registrados |

---

### 📡 Webhook

| Método | Endpoint | Descrição |
|--------|----------|-----------|
| `POST` | `/baixa-operacional` | Notificação automática de liquidação/baixa de boleto bancário |

---

### 🔐 Autenticação

A autenticação é feita via OAuth2. É necessário:

- Obter um token de acesso com os escopos:
  - `cobrancas.boletos-info`
  - `cobrancas.boletos-requisicao`
  - `cobrancas.convenio-requisicao`
- Incluir o header `gw-dev-app-key` com a chave da aplicação registrada no [Portal Developers BB](https://apoio.developers.bb.com.br/sandbox/spec/5f4e6f6cb71fb5001268c96a)

---

### 📚 Referência Oficial

Para mais detalhes sobre os parâmetros, schemas e exemplos de payloads, consulte a [documentação oficial da Cobranças API - BB](https://apoio.developers.bb.com.br/sandbox/spec/5f4e6f6cb71fb5001268c96a).


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
