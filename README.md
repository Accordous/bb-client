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

## Architecture

This package follows a clean architecture pattern with:

- **Enums**: Constants for API values (BoletoSituacao, TipoInscricao, etc.)
- **ValueObjects**: Immutable data objects (Pagador, Beneficiario, Desconto, etc.)
- **Services**: Business logic layer with endpoint-specific services
- **Builders**: Fluent interface for creating complex objects

### Enums Available

- `BoletoSituacao`: ATIVO, BAIXADO, CANCELADO, PAGO
- `TipoInscricao`: CPF, CNPJ
- `CodigoModalidade`: SIMPLES, VINCULADA
- `TipoTitulo`: CHEQUE, DUPLICATA_MERCANTIL, etc.
- `EstadoBaixaOperacional`: BB, OUTROS_BANCOS, CANCELAMENTO_BAIXA
- `CanalLiquidacao`: AGENCIA, CORRESPONDENTE, INTERNET_BANKING, etc.
- `FormaPagamento`: DINHEIRO, CHEQUE, DOC_TED, CREDITO_CONTA, PIX

### ValueObjects Available

- `Pagador`: Represents the payer information
- `Beneficiario`: Represents the beneficiary information
- `Desconto`: Represents discount information
- `JurosMora`: Represents interest information
- `Multa`: Represents fine information
- `BoletoBuilder`: Fluent builder for creating boleto data

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

Recursos relacionados a movimentação.

| Método | Endpoint | Descrição |
|--------|----------|-----------|
| `POST` | `/convenios/{id}/listar-retorno-movimento` | Lista dados de retorno de movimentações vinculadas aos boletos registrados |

---

### Endpoints de Convênio

Recursos relacionados ao convênio de cobrança.

| Método | Endpoint | Descrição |
|--------|----------|-----------|
| `PATCH` | `/convenios/{id}/ativar-consulta-baixa-operacional` | Habilitar Consulta de Baixa Operacional |
| `PATCH` | `/convenios/{id}/desativar-consulta-baixa-operacional` | Desativar Consulta Baixa Operacional |

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

### Basic Usage with Facade

#### Authentication

For OAuth token authentication:

```php
use Accordous\BbClient\Facades\BancoDoBrasil;

$token = BancoDoBrasil::getToken();
```

#### Creating a Boleto (Simple)

```php
use Accordous\BbClient\Facades\BancoDoBrasil;

$data = [
    'numeroConvenio' => 1234567,
    'numeroCarteira' => 17,
    'numeroVariacaoCarteira' => 35,
    'codigoModalidade' => '01',
    'dataEmissao' => '22.08.2025',
    'dataVencimento' => '22.09.2025',
    'valorOriginal' => 100.00,
    'codigoTipoTitulo' => 2,
    'pagador' => [
        'tipoInscricao' => 1,
        'numeroInscricao' => '12345678901',
        'nome' => 'João da Silva'
    ]
];

$response = BancoDoBrasil::registrarBoletoCobranca($data);
```

### Advanced Usage with Builder Pattern

#### Creating a Complete Boleto

```php
use Accordous\BbClient\Facades\BancoDoBrasil;
use Accordous\BbClient\ValueObject\BoletoBuilder;
use Accordous\BbClient\ValueObject\Pagador;
use Accordous\BbClient\ValueObject\Desconto;
use Accordous\BbClient\ValueObject\JurosMora;
use Accordous\BbClient\ValueObject\Multa;
use Accordous\BbClient\Enums\TipoInscricao;
use Accordous\BbClient\Enums\CodigoModalidade;
use Accordous\BbClient\Enums\TipoTitulo;

// Create payer
$pagador = new Pagador(
    TipoInscricao::CPF,
    '12345678901',
    'João da Silva',
    'Rua das Flores, 123',
    '01234-567',
    'São Paulo',
    'Centro',
    'SP',
    '11999999999'
);

// Create discount
$desconto = new Desconto(1, '25.08.2025', 5.0, 0.0); // 5% discount

// Create interest
$jurosMora = new JurosMora(2, 0.0, 2.0); // R$ 2.00 per day

// Create fine
$multa = new Multa(1, '23.08.2025', 2.0, 0.0); // 2% fine

// Build boleto
$boletoData = (new BoletoBuilder())
    ->numeroConvenio(1234567)
    ->numeroCarteira(17)
    ->numeroVariacaoCarteira(35)
    ->codigoModalidade(CodigoModalidade::SIMPLES)
    ->dataEmissao('22.08.2025')
    ->dataVencimento('22.09.2025')
    ->valorOriginal(100.00)
    ->codigoTipoTitulo(TipoTitulo::DUPLICATA_MERCANTIL)
    ->descricaoTipoTitulo('Duplicata Mercantil')
    ->numeroTituloBeneficiario('12345')
    ->numeroTituloCliente('67890')
    ->mensagemBloquetoOcorrencia('Pagamento via PIX disponível')
    ->pagador($pagador)
    ->desconto($desconto)
    ->jurosMora($jurosMora)
    ->multa($multa)
    ->indicadorPix('S')
    ->build();

$response = BancoDoBrasil::registrarBoletoCobranca($boletoData);
```

### Service-Level Usage

#### Working with Boletos

```php
use Accordous\BbClient\Facades\BancoDoBrasil;

// List boletos
$response = BancoDoBrasil::boletos()->list([
    'numeroConvenio' => 1234567,
    'indicadorSituacao' => 'A', // Active boletos
    'pagina' => 1,
    'quantidadePorPagina' => 50
]);

// Get specific boleto
$response = BancoDoBrasil::boletos()->show('12345678901234567890', 1234567);

// Update boleto
$response = BancoDoBrasil::boletos()->update('12345678901234567890', [
    'numeroConvenio' => 1234567,
    'valorOriginal' => 150.00
]);

// Generate PIX for boleto
$response = BancoDoBrasil::boletos()->gerarPix('12345678901234567890');

// Cancel PIX
$response = BancoDoBrasil::boletos()->cancelarPix('12345678901234567890');

// Check PIX details
$response = BancoDoBrasil::boletos()->consultarPix('12345678901234567890');

// Cancel boleto
$response = BancoDoBrasil::boletos()->baixar('12345678901234567890');

// Check operational settlement
$response = BancoDoBrasil::boletos()->consultarBaixaOperacional([
    'agencia' => 1234,
    'conta' => 123456,
    'carteira' => 17,
    'variacao' => 35,
    'dataInicioAgendamentoTitulo' => '01/08/2025',
    'dataFimAgendamentoTitulo' => '31/08/2025'
]);
```

#### Working with Convenios

```php
use Accordous\BbClient\Facades\BancoDoBrasil;

// Enable operational settlement consultation
$response = BancoDoBrasil::convenios()->ativarConsultaBaixaOperacional('1234567');

// Disable operational settlement consultation
$response = BancoDoBrasil::convenios()->desativarConsultaBaixaOperacional('1234567');

// List movement returns
$response = BancoDoBrasil::convenios()->listarRetornoMovimento('1234567', [
    'dataInicio' => '01/08/2025',
    'dataFim' => '31/08/2025'
]);
```

#### Handling Webhooks

```php
use Accordous\BbClient\Facades\BancoDoBrasil;

// Process webhook data (usually in a controller)
$webhookData = [
    'id' => '00031285570000104055',
    'dataRegistro' => '11.06.2025',
    'dataVencimento' => '11.06.2025',
    'valorOriginal' => 1000,
    'valorPagoSacado' => 1000,
    'numeroConvenio' => 3128557,
    'numeroOperacao' => 10055680,
    'carteiraConvenio' => 17,
    'variacaoCarteiraConvenio' => 35,
    'codigoEstadoBaixaOperacional' => 1,
    'dataLiquidacao' => '12/06/2025 16:29:30',
    'instituicaoLiquidacao' => '001',
    'canalLiquidacao' => 4,
    'codigoModalidadeBoleto' => 1,
    'tipoPessoaPortador' => 2,
    'identidadePortador' => 98959112000179,
    'nomePortador' => 'CINE VENTURA DE PADUA',
    'formaPagamento' => 2
];

$response = BancoDoBrasil::webhooks()->processarBaixaOperacional($webhookData);
```

## Testing

The package includes comprehensive tests demonstrating all functionality:

```bash
./vendor/bin/phpunit tests/Feature/BancoDoBrasilIntegrationTest.php
```

## Error Handling

All methods return Laravel HTTP Client Response objects, so you can handle errors as usual:

```php
$response = BancoDoBrasil::boletos()->create($data);

if ($response->successful()) {
    $data = $response->json();
    // Handle success
} else {
    $error = $response->json();
    // Handle error
}
```

## Contributing

Please follow the established patterns:
- Use Enums for constants
- Create ValueObjects for complex data structures
- Use the Builder pattern for complex object creation
- Add tests for new functionality

## License

This package is licensed under the MIT License.
