# 🧾 Schema: `Objeto de Webook` (com descrições)

O Webhook da API Cobrança notifica o emissor do boleto bancário sobre o recebimento pelo Banco do Brasil de uma Baixa Operacional de um boleto, seja a liquidação (pagamento) ou solicitação de baixa.

Baixa Operacional é o meio pelo qual a Instituição Recebedora (onde o boleto foi pago) informa à Base Centralizada de Cobrança (PCR) que o boleto está sendo pago e esta, por sua vez, é responsável por repassar essa informação ao Banco emissor do boleto.

Para obter mais informações sobre o que é o serviço de Webhook e seu funcionamento, acesse nossa documentação negocial completa.

https://apoio.developers.bb.com.br/referency/post/6125045d8378f10012877468

## Webhook: `POST baixa-operacional`

> Representação dos campos enviados no Webhook de Baixa Operacional de um boleto bancário.

### 🔹 Identificação do Boleto

| Campo | Tipo | Descrição |
|-------|------|-------------------|
| `id` | `string` (20 caracteres) (matches \d) | Identificador único do boleto no sistema do Banco do Brasil. Deve conter apenas dígitos. |

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
| `valorOriginal` | `number` (double) | Valor original do boleto registrado. |
| `valorPagoSacado` | `number` (double) | Valor pago pelo boleto (considerando evetuais acréscimos ou descontos). |

Para `valorOriginal` e `valorPagoSacado`: Utiliza o padrão americano para números (decimais separados por pontos), as casas dos milhares não são separadas por nenhum caracter e zeros à direita na casas decimais são ignorados.
---

### 🏦 Dados Bancários

| Campo | Tipo | Descrição |
|-------|------|-------------------|
| `numeroConvenio` | `integer` | Número da carteira do convênio de cobrança firmado entre o beneficiário e o Banco do Brasil. Um beneficiário pode ter mais de um convênio. |
| `numeroOperacao` | `integer` | Número da Operação de Cobrança |
| `carteiraConvenio` | `integer` | Determina as características do serviço de Cobrança e define como os boletos serão tratados pelo BB. |
| `variacaoCarteiraConvenio` | `integer` | Parâmetro de agrupamento de boletos dentro de uma mesma carteira. |
| `codigoModalidadeBoleto` | `integer` | É a categoria do serviço de cobrança que indica as particularidades, forma e modelo do serviço de cobrança contratado. |

Possíveis valores para `codigoModalidadeBoleto`:
1 - Simples
4 - Vinculada

---

### 📄 Situação da Baixa

| Campo | Tipo | Descrição |
|-------|------|-------------------|
| `codigoEstadoBaixaOperacional` | `integer` | Código para identificação da situação da Baixa Operacional. |

Possíveis valores para `codigoEstadoBaixaOperacional`:
1 - Baixa Operacional emitida pelo BB
2 - Baixa Operacional emitida por outro Banco
10 - Cancelamento da Baixa Operacional

---

### 🏦 Instituição e Canal de Liquidação

| Campo | Tipo | Descrição |
|-------|------|-------------------|
| `instituicaoLiquidacao` | `integer` (3 dígitos) | Código Compe da Instituição Financeira, com três dígitos numéricos |
| `canalLiquidacao` | `integer` | Código do canal onde a transação foi realizada. |

Possíveis valores para `canalLiquidacao`:
1 - Agências - Postos tradicionais
2 - Terminal de Auto-atendimento
3 - Internet (home/office banking)
4 - Pix
5 - Correspondente bancário
6 - Central de atendimento (Call Center)
7 - Arquivo eletrônico
8 - DDA
9 - Correspondente bancário digital

---

### 👤 Dados do Pagador (Portador)

| Campo | Tipo | Descrição |
|-------|------|-------------------|
| `tipoPessoaPortador` | `integer` | Tipo de pessoa do pagador. Valores: `1` - Pessoa Física; `2` - Pessoa Jurídica. |
| `identidadePortador` | `integer` (int64) | Número do documento fiscal da pessoa que efetuou o pagamento. Retorna CPF para pessoas físicas e CNPJ para pessoas jurídicas. O campo "tipoPessoaPortador" indica qual o tipo de número do documento. |
| `nomePortador` | `string` | Nome da pessoa que efetuou o pagamento. Pode ser o nome de uma pessoa física ou jurídica. |

---

### 💳 Forma de Pagamento

| Campo | Tipo | Descrição |
|-------|------|-------------------|
| `formaPagamento` | `integer` | Código da forma de pagamento utilizada. |

Possíveis valores para `formaPagamento`:
1 - Em espécie
2 - Débito em conta
3 - Cartão de Crédito
4 - Cheque

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
  "codigoEstadoBaixaOperacional": 1,
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
