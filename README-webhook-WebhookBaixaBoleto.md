# 🧾 Schema: `RequisicaoAlterarBoletos` (com descrições)

O Webhook da API Cobrança notifica o emissor do boleto bancário sobre o recebimento pelo Banco do Brasil de uma Baixa Operacional de um boleto, seja a liquidação (pagamento) ou solicitação de baixa.

Baixa Operacional é o meio pelo qual a Instituição Recebedora (onde o boleto foi pago) informa à Base Centralizada de Cobrança (PCR) que o boleto está sendo pago e esta, por sua vez, é responsável por repassar essa informação ao Banco emissor do boleto.

Para obter mais informações sobre o que é o serviço de Webhook e seu funcionamento, acesse nossa documentação negocial completa.

https://apoio.developers.bb.com.br/referency/post/6125045d8378f10012877468

## Webhook: `POST baixa-operacional`

> Representação dos campos enviados no Webhook de Baixa Operacional de um boleto bancário.

### 🔹 Identificação do Boleto

| Campo | Tipo | Descrição |
|-------|------|-------------------|
| `id` | `string` (20 caracteres) | Identificador único do boleto no sistema do Banco do Brasil. Deve conter apenas dígitos. |

---

### 📅 Datas

| Campo | Tipo | Descrição |
|-------|------|-------------------|
| `dataRegistro` | `string` (formato `dd.mm.aaaa`) | Data em que o boleto foi registrado no sistema. |
| `dataVencimento` | `string` (formato `dd.mm.aaaa`) | Data de vencimento do boleto. |
| `dataLiquidacao` | `string` (formato `dd/mm/aaaa HH:mm:ss`) | Data e hora em que o boleto foi liquidado (pago). |

---

### 💰 Valores

| Campo | Tipo | Descrição |
|-------|------|-------------------|
| `valorOriginal` | `number` (double) | Valor original do boleto. |
| `valorPagoSacado` | `number` (double) | Valor efetivamente pago pelo sacado (pagador). |

---

### 🏦 Dados Bancários

| Campo | Tipo | Descrição |
|-------|------|-------------------|
| `numeroConvenio` | `integer` | Identificador do convênio de cobrança vinculado ao boleto. |
| `numeroOperacao` | `integer` | Número da operação bancária associada ao boleto. |
| `carteiraConvenio` | `integer` | Número da carteira do convênio. |
| `variacaoCarteiraConvenio` | `integer` | Variação da carteira do convênio. |
| `codigoModalidadeBoleto` | `integer` | Código da modalidade de cobrança utilizada. |

---

### 📄 Situação da Baixa

| Campo | Tipo | Descrição |
|-------|------|-------------------|
| `codigoEstadoBaixaOperacional` | `integer` | Código que representa o tipo de baixa operacional. Ex: `06` - Liquidação, `09` - Baixa por solicitação. |

---

### 🏦 Instituição e Canal de Liquidação

| Campo | Tipo | Descrição |
|-------|------|-------------------|
| `instituicaoLiquidacao` | `integer` (3 dígitos) | Código da instituição recebedora que processou o pagamento. |
| `canalLiquidacao` | `integer` | Código do canal utilizado para liquidação. Ex: `1` - Internet Banking, `2` - ATM, etc. |

---

### 👤 Dados do Pagador (Portador)

| Campo | Tipo | Descrição |
|-------|------|-------------------|
| `tipoPessoaPortador` | `integer` | Tipo de pessoa do pagador. Domínios: `1` - Pessoa Física; `2` - Pessoa Jurídica. |
| `identidadePortador` | `integer` (int64) | Número do CPF ou CNPJ do pagador. |
| `nomePortador` | `string` | Nome completo do pagador. |

---

### 💳 Forma de Pagamento

| Campo | Tipo | Descrição |
|-------|------|-------------------|
| `formaPagamento` | `integer` | Código da forma de pagamento utilizada. Ex: `1` - Dinheiro, `2` - Débito em conta, `3` - Pix, etc. |

## Exemplo completo (JSON)
```json
{
  "id": "00024589070000000412",
  "dataRegistro": "01.09.2025",
  "dataVencimento": "10.09.2025",
  "valorOriginal": 1500.00,
  "valorPagoSacado": 1500.00,
  "numeroConvenio": 1234567,
  "numeroOperacao": 987654,
  "carteiraConvenio": 17,
  "variacaoCarteiraConvenio": 35,
  "codigoEstadoBaixaOperacional": 6,
  "dataLiquidacao": "10/09/2025 14:35:22",
  "instituicaoLiquidacao": 001,
  "canalLiquidacao": 1,
  "codigoModalidadeBoleto": 1,
  "tipoPessoaPortador": 1,
  "identidadePortador": 12345678901,
  "nomePortador": "João da Silva",
  "formaPagamento": 3
}
```
