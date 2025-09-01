# 🧾 Schema: `RequisicaoRegistroBoletos` (com descrições completas)

Schema completo para o endpoint que "Registra um novo boleto de cobrança vinculado ao convênio do cliente no Banco do Brasil".

## Endpoint: `POST /boletos`

### 📤 Corpo da Requisição (Schema)

| Campo | Tipo | Descrição Completa |
|-------|------|--------------------|
| `numeroConvenio` | `integer (int64)` | Número do convênio de Cobrança do Cliente. Identificador determinado pelo sistema Cobrança para controlar a emissão de boletos, liquidação, crédito de valores ao Beneficiário e intercâmbio de dados com o cliente. |
| `numeroCarteira` | `integer (int32)` | Características do serviço de boleto bancário e como ele deve ser tratado pelo banco. |
| `numeroVariacaoCarteira` | `integer (int32)` | Número da variação da carteira do convênio de cobrança. |
| `codigoModalidade` | `integer (int32)` | Identifica a característica dos boletos dentro das modalidades de cobrança existentes no banco. Domínio: `01` - Simples; `04` - Vinculada. |
| `dataEmissao` | `string` | Data de emissão do boleto no formato `dd.mm.aaaa`. |
| `dataVencimento` | `string` | Data de vencimento do boleto no formato `dd.mm.aaaa`. |
| `valorOriginal` | `number (float)` | Valor de cobrança > 0.00, emitido em Real (formato decimal separado por "."). Deve ser maior que a soma dos campos “VALOR DO DESCONTO DO TÍTULO” e “VALOR DO ABATIMENTO DO TÍTULO”, se informados. Informação não alterável após criação. No caso de emissão com valor equivocado, sugerimos cancelar e emitir novo boleto. |
| `valorAbatimento` | `number (float)` | Valor de dedução do boleto ≥ 0.00. |
| `quantidadeDiasProtesto` | `number (float)` | Dias após vencimento para iniciar protesto. Valor inteiro ≥ 0. |
| `quantidadeDiasNegativacao` | `integer (int32)` | Dias após vencimento para iniciar negativação via órgão definido no campo orgaoNegativador. |
| `orgaoNegativador` | `integer (int32)` | Código do órgão negativador. Domínio: `10` - SERASA. |
| `indicadorAceiteTituloVencido` | `string` | Indica se o boleto pode ser recebido após vencimento. 'S' ou 'N'. Se omitido, usa configuração do convênio. Quando informado "S" em conjunto com o campo "numeroDiasLimiteRecebimento", será definido a quantidade de dias (corridos) que este boleto ficará disponível para pagamento após seu vencimento. Obs.: Se definido "S" e o campo "numeroDiasLimiteRecebimento" ficar com valor zero também será assumido a informação de limite de recebimento que está definida no convênio.
Quando informado "N", fica definindo que o boleto NÃO permite pagamento em atraso, portanto só aceitará pagamento até a data do vencimento ou o próximo dia útil, quando o vencimento ocorrer em dia não útil.
Quando informado qualquer valor diferente de "S" ou "N" será assumido a informação de limite de recebimento que está definida no convênio. |
| `numeroDiasLimiteRecebimento` | `integer (int32)` | Número de dias limite para recebimento após vencimento. Valor inteiro > 0. |
| `codigoAceite` | `string` | Código que identifica se o boleto foi aceito pelo pagador. Domínios: `A` - Aceite; `N` - Não aceite. |
| `codigoTipoTitulo` | `integer (int32)` | Código do tipo de boleto. Domínios: 1- CHEQUE 2- DUPLICATA MERCANTIL 3- DUPLICATA MTIL POR INDICACAO 4- DUPLICATA DE SERVICO 5- DUPLICATA DE SRVC P/INDICACAO 6- DUPLICATA RURAL 7- LETRA DE CAMBIO 8- NOTA DE CREDITO COMERCIAL 9- NOTA DE CREDITO A EXPORTACAO 10- NOTA DE CREDITO INDULTRIAL 11- NOTA DE CREDITO RURAL 12- NOTA PROMISSORIA 13- NOTA PROMISSORIA RURAL 14- TRIPLICATA MERCANTIL 15- TRIPLICATA DE SERVICO 16- NOTA DE SEGURO 17- RECIBO 18- FATURA 19- NOTA DE DEBITO 20- APOLICE DE SEGURO 21- MENSALIDADE ESCOLAR 22- PARCELA DE CONSORCIO 23- DIVIDA ATIVA DA UNIAO 24- DIVIDA ATIVA DE ESTADO 25- DIVIDA ATIVA DE MUNICIPIO 31- CARTAO DE CREDITO 32- BOLETO PROPOSTA 33- BOLETO APORTE 99- OUTROS. |
| `descricaoTipoTitulo` | `string` | Descrição do tipo de boleto. |
| `indicadorPermissaoRecebimentoParcial` | `string` | Indica se é permitido pagamento parcial. Domínios: `S` - Sim; `N` - Não. |
| `numeroTituloBeneficiario` | `string` | Identificação do título pelo beneficiário. Aceita A-Z, 0-9, hífen, apóstrofo, espaço. |
| `campoUtilizacaoBeneficiario` | `string` | Informações adicionais sobre o beneficiário. |
| `numeroTituloCliente` | `string` | Número de identificação do boleto (correspondente ao NOSSO NÚMERO), no formato STRING, com 20 dígitos, que deverá ser formatado da seguinte forma: “000” + (número do convênio com 7 dígitos) + (10 algarismos - se necessário, completar com zeros à esquerda). |
| `mensagemBloquetoOcorrencia` | `string` | Mensagem para impressão no boleto. Máx. 165 caracteres, quebrado em 3 linhas de 55. Não aceita `\n`, `\r`, `\lf`. |

---

### 💸 Descontos

Define a ausência ou a forma como será concedido o desconto para o Título de Cobrança.

Campos: `desconto`, `segundoDesconto`, `terceiroDesconto`

| Campo | Tipo | Descrição |
|-------|------|-----------|
| `tipo` | `integer (int32)` | Forma de concessão: `0` - Sem desconto; `1` - Valor fixo; `2` - Percentual; `3` - Por antecipação. |
| `dataExpiracao` | `string` | Data de expiração do desconto (`dd.mm.aaaa`). Obrigatória se tipo > 0. |
| `porcentagem` | `number (float)` | Percentual de desconto (se tipo = 2). |
| `valor` | `number (float)` | Valor fixo de desconto (se tipo = 1). |

---

### 📈 Juros de Mora

Código utilizado pela FEBRABAN para identificar o tipo de taxa de juros, sendo: 0 - DISPENSAR, 1 - VALOR DIA ATRASO, 2 - TAXA MENSAL, 3 - ISENTO. Se informado ‘0’ (zero) ou ‘3’ (três), os campos “PERCENTUAL DE JUROS DO TÍTULO” e “VALOR DO JUROS DO TÍTULO” não devem ser informados ou ser informados igual a ‘0’ (zero).

O valor de juros e multa incidem sobre o valor atual do boleto (valor do boleto - valor de abatimento).

Campos: `jurosMora`

| Campo | Tipo | Descrição |
|-------|------|-----------|
| `tipo` | `integer (int32)` | Tipo de juros: `0` - Dispensar; `1` - Valor por dia; `2` - Taxa mensal; `3` - Isento. |
| `porcentagem` | `number (float)` | Percentual de juros (se tipo = 2). |
| `valor` | `number (float)` | Valor fixo de juros (se tipo = 1). |

---

### 🔥 Multa

Código para identificação do tipo de multa para o Título de Cobrança, inteiro >= 0, sendo: 0 - Sem multa, 1 - Valor da Multa, 2 - Percentual da Multa. Se informado ‘0’ (zero) os campos “DATA DE MULTA”, “PERCENTUAL DE MULTA” e “VALOR DA MULTA” não devem ser informados ou ser informados iguais a ‘0’ (zero).

O valor de juros e multa incidem sobre o valor atual do boleto (valor do boleto - valor de abatimento).

Campos: `multa`

| Campo | Tipo | Descrição |
|-------|------|-----------|
| `tipo` | `integer (int32)` | Tipo de multa: `0` - Sem multa; `1` - Valor fixo; `2` - Percentual. |
| `data` | `string` | Data da multa (`dd.mm.aaaa`). Obrigatória se tipo > 0. |
| `porcentagem` | `number (float)` | Percentual da multa (se tipo = 2). |
| `valor` | `number (float)` | Valor fixo da multa (se tipo = 1). |

---

### 👤 Pagador

| Campo | Tipo | Descrição |
|-------|------|-----------|
| `tipoInscricao` | `integer (int32)` | Tipo de inscrição: `1` - CPF; `2` - CNPJ. |
| `numeroInscricao` | `integer (int64)` | Número do CPF ou CNPJ. |
| `nome` | `string` | Nome completo do pagador. |
| `endereco` | `string` | Endereço completo. |
| `cep` | `integer (int32)` | CEP do pagador. |
| `cidade` | `string` | Cidade do pagador. |
| `bairro` | `string` | Bairro do pagador. |
| `uf` | `string` | Unidade federativa (UF). |
| `telefone` | `string` | Telefone do pagador. |
| `email` | `string` | E-mail do pagador. |

---

### 🧑‍💼 Beneficiário Final

| Campo | Tipo | Descrição |
|-------|------|-----------|
| `tipoInscricao` | `integer (int32)` | Tipo de inscrição: `1` - CPF; `2` - CNPJ. |
| `numeroInscricao` | `integer (int64)` | Número do CPF ou CNPJ. |
| `nome` | `string` | Nome completo do beneficiário final. |

---

### 📲 Pix

| Campo | Tipo | Descrição |
|-------|------|-----------|
| `indicadorPix` | `string` | Indica se o boleto terá QRCode Pix. Domínios: `S` - Com Pix; `N` - Sem Pix; outros valores serão tratados como `N`. |

## Exemplo completo (JSON)
```json
{
  "numeroConvenio": 1234567,
  "numeroCarteira": 17,
  "numeroVariacaoCarteira": 35,
  "codigoModalidade": 1,
  "dataEmissao": "01.09.2025",
  "dataVencimento": "10.09.2025",
  "valorOriginal": 1500.00,
  "valorAbatimento": 50.00,
  "quantidadeDiasProtesto": 5,
  "quantidadeDiasNegativacao": 10,
  "orgaoNegativador": 10,
  "indicadorAceiteTituloVencido": "S",
  "numeroDiasLimiteRecebimento": 15,
  "codigoAceite": "A",
  "codigoTipoTitulo": 2,
  "descricaoTipoTitulo": "DUPLICATA MERCANTIL",
  "indicadorPermissaoRecebimentoParcial": "S",
  "numeroTituloBeneficiario": "ABC123456789",
  "campoUtilizacaoBeneficiario": "Referente à prestação de serviço",
  "numeroTituloCliente": "00012345670000000001",
  "mensagemBloquetoOcorrencia": "Pagamento após vencimento sujeito a encargos. Em caso de dúvidas, contate nosso atendimento.",
  
  "desconto": {
    "tipo": 1,
    "dataExpiracao": "05.09.2025",
    "porcentagem": 0.0,
    "valor": 100.00
  },
  "segundoDesconto": {
    "dataExpiracao": "07.09.2025",
    "porcentagem": 5.0,
    "valor": 0.0
  },
  "terceiroDesconto": {
    "dataExpiracao": "08.09.2025",
    "porcentagem": 0.0,
    "valor": 50.00
  },

  "jurosMora": {
    "tipo": 2,
    "porcentagem": 1.5,
    "valor": 0.0
  },
  "multa": {
    "tipo": 2,
    "data": "11.09.2025",
    "porcentagem": 2.0,
    "valor": 0.0
  },

  "pagador": {
    "tipoInscricao": 1,
    "numeroInscricao": 12345678901,
    "nome": "João da Silva",
    "endereco": "Rua das Palmeiras, 123",
    "cep": 47850000,
    "cidade": "Luís Eduardo Magalhães",
    "bairro": "Centro",
    "uf": "BA",
    "telefone": "77999999999",
    "email": "joao.silva@email.com"
  },

  "beneficiarioFinal": {
    "tipoInscricao": 2,
    "numeroInscricao": 12345678000199,
    "nome": "Empresa XYZ Ltda"
  },

  "indicadorPix": "S"
}
```