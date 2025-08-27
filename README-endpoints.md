# Endpoints

Abaixo segue as URLs base para cada ambiente do BB Client:

- https://api.sandbox.bb.com.br/cobrancas/v2 (Sandbox)
- https://api.hm.bb.com.br/cobrancas/v2 (Homologação)
- https://api.bb.com.br/cobrancas/v2 (Produção)

## `GET /boletos`

### 📌 Descrição
Consulta boletos registrados no convênio, com filtros por situação, data, pagador e outros critérios.

### 🌐 URL (Relativa)
`/boletos`

---

### 🧾 Headers obrigatórios

| Header | Valor | Descrição |
|--------|-------|-----------|
| `Authorization` | `Bearer {access_token}` | Token OAuth2 com escopo válido |
| `gw-dev-app-key` | `{sua_app_key}` | Chave da aplicação cadastrada no portal BB |
| `Content-Type` | `application/json` | Tipo de conteúdo da requisição |

---

### 🔍 Parâmetros de Query

Todos os parâmetros são opcionais, mas podem ser combinados para refinar a busca:

| Parâmetro | Tipo | Descrição |
|-----------|------|-----------|
| `numeroConvenio` | `string` | Número do convênio de cobrança |
| `agenciaBeneficiario` | `string` | Agência do beneficiário |
| `contaBeneficiario` | `string` | Conta do beneficiário |
| `indicadorSituacao` | `string` | Situação do boleto (`A` = Ativo, `B` = Baixado, `C` = Cancelado, `P` = Pago) |
| `codigoEstadoTituloCobranca` | `string` | Estado do título (ex: `01` = registrado, `02` = liquidado) |
| `dataInicioVencimento` | `string` | Data inicial do vencimento (formato `YYYY-MM-DD`) |
| `dataFimVencimento` | `string` | Data final do vencimento (formato `YYYY-MM-DD`) |
| `dataInicioRegistro` | `string` | Data inicial de registro do boleto |
| `dataFimRegistro` | `string` | Data final de registro do boleto |
| `cpfCnpjPagador` | `string` | CPF ou CNPJ do pagador |
| `pagina` | `integer` | Número da página para paginação |
| `quantidadePorPagina` | `integer` | Quantidade de boletos por página (máximo: 100) |

---

### ✅ Exemplo de Requisição

```http
GET /boletos?numeroConvenio=123456&indicadorSituacao=A&pagina=1&quantidadePorPagina=50
Host: api.sandbox.bb.com.br
Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...
gw-dev-app-key: sua_app_key_aqui
Content-Type: application/json
```

---

### 📥 Resposta (200 OK)

```json
{
  "boletos": [
    {
      "numeroConvenio": "123456",
      "numeroCarteira": "17",
      "numeroVariacaoCarteira": "35",
      "codigoModalidadeTitulo": "01",
      "numeroTituloCliente": "000000000001",
      "dataVencimento": "2025-08-30",
      "valorOriginalTitulo": 150.00,
      "cpfCnpjPagador": "12345678901",
      "nomePagador": "Rodrigo Vieira",
      "situacao": "A",
      "codigoEstadoTituloCobranca": "01"
    }
  ],
  "quantidadeTotal": 1,
  "pagina": 1,
  "quantidadePorPagina": 50
}
```

---

### ⚠️ Possíveis Códigos de Erro

| Código | Descrição |
|--------|-----------|
| `400 Bad Request` | Parâmetros inválidos ou malformados |
| `401 Unauthorized` | Token inválido ou ausente |
| `403 Forbidden` | Escopo insuficiente ou app-key incorreta |
| `500 Internal Server Error` | Erro interno na API do BB |

---

Se quiser, posso gerar esse mesmo parse para os próximos endpoints (`POST /boletos`, `GET /boletos/{id}`, etc.) ou montar uma collection para Postman com todos os headers e exemplos prontos. Me avisa como prefere seguir!

Perfeito, Rodrigo — acabei de extrair do `article.json-schema-2020-12` da documentação oficial do BB todos os campos do **body** da rota `POST /boletos`.  
Segue no mesmo formato que usamos antes, mas agora **completo** conforme o schema.

---
---

## `POST /boletos`

### 📌 Descrição
Registra um novo boleto de cobrança vinculado ao convênio do cliente no Banco do Brasil.

### 🌐 URL (Relativa)
`/boletos`

---

### 📋 Headers obrigatórios

| Header | Valor | Descrição |
|--------|-------|-----------|
| `Authorization` | `Bearer {access_token}` | Token OAuth2 obtido no fluxo de autenticação BB |
| `gw-dev-app-key` | `{sua_app_key}` | Chave da aplicação cadastrada no portal BB Developers |
| `Content-Type` | `application/json` | Tipo de conteúdo da requisição |

---

### 📤 Corpo da Requisição (JSON)

```json
{
  "numeroConvenio": 1234567,
  "numeroCarteira": 17,
  "numeroVariacaoCarteira": 35,
  "codigoModalidade": 1,
  "dataEmissao": "21.08.2025",
  "dataVencimento": "30.08.2025",
  "valorOriginal": 150.00,
  "valorAbatimento": 0.00,
  "quantidadeDiasProtesto": 0,
  "quantidadeDiasNegativacao": 0,
  "orgaoNegativador": 10,
  "indicadorAceiteTituloVencido": "N",
  "numeroDiasLimiteRecebimento": 0,
  "codigoAceite": "N",
  "codigoTipoTitulo": 2,
  "descricaoTipoTitulo": "DUPLICATA MERCANTIL",
  "indicadorPermissaoRecebimentoParcial": "N",
  "numeroTituloBeneficiario": "FATURA-123",
  "campoUtilizacaoBeneficiario": "Observação interna",
  "numeroTituloCliente": "00012345670000000001",
  "mensagemBloquetoOcorrencia": "Pagamento até o vencimento",
  "desconto": {
    "tipo": 0,
    "dataExpiracao": null,
    "porcentagem": 0.00,
    "valor": 0.00
  },
  "segundoDesconto": {
    "dataExpiracao": null,
    "porcentagem": 0.00,
    "valor": 0.00
  },
  "terceiroDesconto": {
    "dataExpiracao": null,
    "porcentagem": 0.00,
    "valor": 0.00
  },
  "jurosMora": {
    "tipo": 0,
    "porcentagem": 0.00,
    "valor": 0.00
  },
  "multa": {
    "tipo": 0,
    "data": null,
    "porcentagem": 0.00,
    "valor": 0.00
  },
  "pagador": {
    "tipoInscricao": 1,
    "numeroInscricao": 12345678901,
    "nome": "Rodrigo Vieira",
    "endereco": "Rua das Palmeiras",
    "cep": 47850000,
    "cidade": "Luís Eduardo Magalhães",
    "bairro": "Centro",
    "uf": "BA",
    "telefone": "77999999999",
    "email": "rodrigo@email.com"
  },
  "beneficiarioFinal": {
    "tipoInscricao": 2,
    "numeroInscricao": 12345678000199,
    "nome": "Empresa XYZ Ltda"
  },
  "indicadorPix": "S"
}
```

---

### 🗝️ Observações importantes

- **Datas**: formato `dd.MM.aaaa` (com pontos).
- **Valores monetários**: separados por ponto (`.`), ex.: `150.00`.
- **`tipoInscricao`**: `1` = CPF, `2` = CNPJ.
- **`codigoModalidade`**: `01` = Simples, `04` = Vinculada.
- **`codigoTipoTitulo`**: segue tabela FEBRABAN (1 = Cheque, 2 = Duplicata Mercantil, etc.).
- **`indicadorPix`**: `"S"` para gerar QR Code Pix dinâmico no boleto.

---
---

## `GET /boletos-baixa-operacional`

### 📌 Descrição

Consulta informações de baixa operacional de boletos de uma carteira de cobrança. O uso deste recurso exige habilitação prévia via PATCH do convênio e pode ser desativado da mesma forma.

### 🌐 URL (Relativa)
`/boletos-baixa-operacional`

---

### 🧾 Headers obrigatórios

- **Authorization**: Bearer {access_token} — token OAuth2 válido.
- **Accept**: application/json.

> Observação: neste endpoint, a chave da aplicação é enviada na query como gw-dev-app-key (obrigatória).

---

### 🔍 Parâmetros de query

| Parâmetro | Tipo | Obrigatório | Descrição |
|---|---|---:|---|
| agencia | integer | Sim | Número da agência do beneficiário, sem dígito verificador. |
| conta | integer | Sim | Número da conta do beneficiário, sem dígito verificador. |
| carteira | integer | Sim | Número da carteira do convênio de cobrança. |
| variacao | integer | Sim | Variação da carteira do convênio de cobrança. |
| dataInicioAgendamentoTitulo | string | Sim | Data inicial do período de agendamento (formato dd/MM/yyyy). |
| dataFimAgendamentoTitulo | string | Sim | Data final do período de agendamento (formato dd/MM/yyyy). |
| horarioInicioAgendamentoTitulo | string | Não | Hora inicial (HH:mm:ss) para delimitar o período. |
| horarioFimAgendamentoTitulo | string | Não | Hora final (HH:mm:ss) para delimitar o período. |
| estadoBaixaOperacional | integer | Não | Estado da baixa: 1=BB; 2=Outros bancos; 10=Cancelamento da baixa operacional. |
| modalidadeTitulo | integer | Não | Modalidade do título: 1=Simples; 4=Vinculada. |
| dataInicioVencimentoTitulo | string | Não | Data de vencimento inicial (dd/MM/yyyy). |
| dataFimVencimentoTitulo | string | Não | Data de vencimento final (dd/MM/yyyy). |
| dataInicioRegistroTitulo | string | Não | Data de registro inicial (dd/MM/yyyy). |
| dataFimRegistroTitulo | string | Não | Data de registro final (dd/MM/yyyy). |
| idProximoTitulo | string | Não | Ponteiro de paginação para consultas com mais de 650 boletos. |
| gw-dev-app-key | string | Sim | Chave da aplicação obtida no Portal Developers BB. |

> Sources: 

---

### ✅ Exemplo de requisição

```http
GET /cobrancas/v2/boletos-baixa-operacional?agencia=1234&conta=123456&carteira=17&variacao=19&dataInicioAgendamentoTitulo=01/05/2021&dataFimAgendamentoTitulo=31/05/2021&horarioInicioAgendamentoTitulo=07:00:00&horarioFimAgendamentoTitulo=17:00:00&modalidadeTitulo=1&estadoBaixaOperacional=1&gw-dev-app-key=0021239456d80136bebf00505689bed HTTP/1.1
Host: api.sandbox.bb.com.br
Authorization: Bearer eyJhbGciOi...
Accept: application/json
```

Os parâmetros mínimos exigidos são agência, conta, carteira, variação, data de início e fim de agendamento, além da app key (na query) e do token OAuth no header.

---

### 📥 Respostas

- 200 OK — sucesso, retorna lista de eventos de baixa operacional com suporte a paginação via possuiMaisTitulos e proximoTitulo.

```json
{
  "possuiMaisTitulos": "S",
  "proximoTitulo": "00012345670000000001",
  "lista": [
    {
      "carteira": 17,
      "variacao": 19,
      "convenio": 1,
      "titulo": {
        "id": "00012345670000000001",
        "estadoBaixaOperacional": 1,
        "modalidade": 1,
        "dataRegistro": "2021-05-23",
        "dataVencimento": "2021-05-23",
        "valorOriginal": 0.1,
        "agendamentoPagamento": {
          "momento": "2021-05-14 09:00:40",
          "instituicaoFinanceira": 1,
          "canal": 1,
          "valorCIP": 0.1
        }
      }
    }
  ]
}
```

- 400 Bad Request — requisição inválida (exemplo de payload de erro).

```json
{
  "erros": [
    {
      "codigo": "4874915",
      "versao": "1",
      "mensagem": "Nosso Número já incluído anteriormente.",
      "providencia": "Informar outro Nosso Número.",
      "ocorrencia": "2AzBShK/zFE=C0700051620C"
    }
  ]
}
```

- 401 Unauthorized — autenticação necessária/ inválida.

```json
{
  "statusCode": 0,
  "error": "string",
  "message": "string",
  "attributes": {
    "error": "string"
  }
}
```

- 500 Internal Server Error — erro interno do servidor (exemplo de payload).

```json
{
  "erros": [
    {
      "codigo": "4874915",
      "versao": "1",
      "mensagem": "Nosso Número já incluído anteriormente.",
      "providencia": "Informar outro Nosso Número.",
      "ocorrencia": "2AzBShK/zFE=C0700051620C"
    }
  ]
}
```

> Sources: 

---

### 🗝️ Observações importantes

- **Habilitação do recurso**: é necessário ativar a consulta de baixa operacional no convênio via PATCH /convenios/{id}/ativar-consulta-baixa-operacional; para desativar, use PATCH /convenios/{id}/desativar-consulta-baixa-operacional.
- **Paginação > 650 títulos**: se possuiMaisTitulos retornar “S”, use o valor de proximoTitulo na próxima chamada via idProximoTitulo para continuar a paginação a partir daquele título.

---
---

## `GET /boletos/{id}`

### 📌 Descrição
Consulta os detalhes de um boleto bancário.  
O parâmetro `{id}` representa o número do título de cobrança.

### 🌐 URL (Relativa)
`/boletos/{id}?numeroConvenio={numeroConvenio}&gw-dev-app-key={gw-dev-app-key}`

### 🗂️ Parâmetros de Caminho
- **id** *(string, obrigatório)* — Número do título de cobrança.

### 🔍 Parâmetros de Query
- **numeroConvenio** *(number, obrigatório)* — Número do convênio.  
- **gw-dev-app-key** *(string, obrigatório)* — Chave da aplicação (developer_application_key).

### 🧾 Headers obrigatórios
- **Authorization:** Bearer `<token_de_acesso_OAuth2.0_JWT>`

### ✅ Exemplo de Requisição
```bash
curl -X GET \
  'https://api.sandbox.bb.com.br/cobrancas/v2/boletos/12345678901234567890?numeroConvenio=1234567&gw-dev-app-key=0021239456d80136bebf00505689bed' \
  -H 'Authorization: Bearer eyJhbGciOi...<restante_do_token>'
```

### 📥 Resposta (200 OK)
```json
{
  "codigoLinhaDigitavel": "string",
  "textoEmailPagador": "string",
  "textoMensagemBloquetoTitulo": "string",
  "codigoTipoMulta": 0,
  "codigoCanalPagamento": 0,
  "numeroContratoCobranca": 0,
  "codigoTipoInscricaoSacado": 0,
  "numeroInscricaoSacadoCobranca": 0,
  "codigoEstadoTituloCobranca": 0,
  "codigoTipoTituloCobranca": 0,
  "codigoModalidadeTitulo": 0,
  "codigoAceiteTituloCobranca": "string",
  "codigoPrefixoDependenciaCobrador": 0,
  "codigoIndicadorEconomico": 0,
  "numeroTituloCedenteCobranca": "string",
  "codigoTipoJuroMora": 0,
  "dataEmissaoTituloCobranca": "string",
  "dataRegistroTituloCobranca": "string",
  "dataVencimentoTituloCobranca": "string",
  "valorOriginalTituloCobranca": 0.1,
  "valorAtualTituloCobranca": 0.1,
  "valorPagamentoParcialTitulo": 0.1,
  "valorAbatimentoTituloCobranca": 0.1,
  "percentualImpostoSobreOprFinanceirasTituloCobranca": 0.1,
  "valorImpostoSobreOprFinanceirasTituloCobranca": 0.1,
  "valorMoedaTituloCobranca": 0.1,
  "percentualJuroMoraTitulo": 0,
  "valorJuroMoraTitulo": 0.1,
  "percentualMultaTitulo": 0.1,
  "valorMultaTituloCobranca": 0.1,
  "quantidadeParcelaTituloCobranca": 0,
  "dataBaixaAutomaticoTitulo": "string",
  "textoCampoUtilizacaoCedente": "string",
  "indicadorCobrancaPartilhadoTitulo": "string",
  "nomeSacadoCobranca": "string",
  "textoEnderecoSacadoCobranca": "string",
  "nomeBairroSacadoCobranca": "string",
  "nomeMunicipioSacadoCobranca": "string",
  "siglaUnidadeFederacaoSacadoCobranca": "string",
  "numeroCepSacadoCobranca": 0,
  "valorMoedaAbatimentoTitulo": 0.1,
  "dataProtestoTituloCobranca": "string",
  "codigoTipoInscricaoSacador": 0,
  "numeroInscricaoSacadorAvalista": 0,
  "nomeSacadorAvalistaTitulo": "string",
  "percentualDescontoTitulo": 0.1,
  "dataDescontoTitulo": "string",
  "valorDescontoTitulo": 0.1,
  "codigoDescontoTitulo": 0,
  "percentualSegundoDescontoTitulo": 0.1,
  "dataSegundoDescontoTitulo": "string",
  "valorSegundoDescontoTitulo": 0.1,
  "codigoSegundoDescontoTitulo": 0,
  "percentualTerceiroDescontoTitulo": 0.1,
  "dataTerceiroDescontoTitulo": "string",
  "valorTerceiroDescontoTitulo": 0.1,
  "codigoTerceiroDescontoTitulo": 0,
  "dataMultaTitulo": "string",
  "numeroCarteiraCobranca": 0,
  "numeroVariacaoCarteiraCobranca": 0,
  "quantidadeDiaProtesto": 0,
  "quantidadeDiaPrazoLimiteRecebimento": 0,
  "dataLimiteRecebimentoTitulo": "string",
  "indicadorPermissaoRecebimentoParcial": "string",
  "textoCodigoBarrasTituloCobranca": "string",
  "codigoOcorrenciaCartorio": 0,
  "valorImpostoSobreOprFinanceirasRecebidoTitulo": 0.1,
  "valorAbatimentoTotal": 0.1,
  "valorJuroMoraRecebido": 0.1,
  "valorDescontoUtilizado": 0.1,
  "valorPagoSacado": 0.1,
  "valorCreditoCedente": 0.1,
  "codigoTipoLiquidacao": 0,
  "dataCreditoLiquidacao": "string",
  "dataRecebimentoTitulo": "string",
  "codigoPrefixoDependenciaRecebedor": 0,
  "codigoNaturezaRecebimento": 0,
  "numeroIdentidadeSacadoTituloCobranca": "string",
  "codigoResponsavelAtualizacao": "string",
  "codigoTipoBaixaTitulo": 0,
  "valorMultaRecebido": 0.1,
  "valorReajuste": 0.1,
  "valorOutroRecebido": 0.1,
  "codigoIndicadorEconomicoUtilizadoInadimplencia": 0.1
}
```

#### 400 – Requisição inválida
```json
{
  "errors": [
    {
      "code": "4874915.1",
      "message": "Nosso Número já incluído anteriormente.",
      "action": "Informar outro Nosso Número."
    }
  ]
}
```

#### 401 – Não autorizado
```json
{
  "statusCode": 0,
  "error": "string",
  "message": "string",
  "attributes": {
    "error": "string"
  }
}
```

#### 404 – Não encontrado
*(Sem exemplo JSON na documentação)*

#### 500 – Erro interno
```json
{
  "errors": [
    {
      "code": "4874915.1",
      "message": "Nosso Número já incluído anteriormente.",
      "action": "Informar outro Nosso Número."
    }
  ]
}
```

### 🗝️ Observações importantes
- Consultas repetidas ao mesmo boleto em até 30 segundos retornam a mesma resposta para otimizar desempenho.  
- Para notificações de pagamento ou cancelamento em tempo real, utilize o webhook de baixa operacional.

---
---

## `PATCH /boletos/{id}`

### 📌 Descrição
Realiza alterações nos valores dos campos presentes em um boleto bancário já criado.

### 🌐 URL (Relativa)
`/boletos/{id}?gw-dev-app-key={gw-dev-app-key}`

### 🧾 Headers obrigatórios
- **Authorization**: Bearer `<token_de_acesso_OAuth2.0_JWT>`
- **Content-Type**: application/json

### 📤 Corpo da Requisição (JSON)
```json
{
  "numeroConvenio": 0,
  "indicadorNovaDataVencimento": "string",
  "alteracaoData": {
    "novaDataVencimento": "string"
  },
  "indicadorNovoValorNominal": "string",
  "alteracaoValor": {
    "novoValorNominal": 0.1
  },
  "indicadorAtribuirDesconto": "string",
  "desconto": {
    "tipoPrimeiroDesconto": 0,
    "valorPrimeiroDesconto": 0.1,
    "percentualPrimeiroDesconto": 0.1,
    "dataPrimeiroDesconto": "string",
    "tipoSegundoDesconto": 0,
    "valorSegundoDesconto": 0.1,
    "percentualSegundoDesconto": 0.1,
    "dataSegundoDesconto": "string",
    "tipoTerceiroDesconto": 0,
    "valorTerceiroDesconto": 0.1,
    "percentualTerceiroDesconto": 0.1,
    "dataTerceiroDesconto": "string"
  },
  "indicadorAlterarDesconto": "string",
  "alteracaoDesconto": {
    "tipoPrimeiroDesconto": 0,
    "novoValorPrimeiroDesconto": 0.1,
    "novoPercentualPrimeiroDesconto": 0.1,
    "novaDataLimitePrimeiroDesconto": "string",
    "tipoSegundoDesconto": 0,
    "novoValorSegundoDesconto": 0.1,
    "novoPercentualSegundoDesconto": 0.1,
    "novaDataLimiteSegundoDesconto": "string",
    "tipoTerceiroDesconto": 0,
    "novoValorTerceiroDesconto": 0.1,
    "novoPercentualTerceiroDesconto": 0.1,
    "novaDataLimiteTerceiroDesconto": "string"
  },
  "indicadorAlterarDataDesconto": "string",
  "alteracaoDataDesconto": {
    "novaDataLimitePrimeiroDesconto": "string",
    "novaDataLimiteSegundoDesconto": "string",
    "novaDataLimiteTerceiroDesconto": "string"
  },
  "indicadorProtestar": "string",
  "protesto": {
    "quantidadeDiasProtesto": 0.1
  },
  "indicadorSustacaoProtesto": "string",
  "indicadorCancelarProtesto": "string",
  "indicadorIncluirAbatimento": "string",
  "abatimento": {
    "valorAbatimento": 0.1
  },
  "indicadorAlterarAbatimento": "string",
  "alteracaoAbatimento": {
    "novoValorAbatimento": 0.1
  },
  "indicadorCobrarJuros": "string",
  "juros": {
    "tipoJuros": 0,
    "valorJuros": 0.1,
    "taxaJuros": 0.1
  },
  "indicadorDispensarJuros": "string",
  "indicadorCobrarMulta": "string",
  "multa": {
    "tipoMulta": 0,
    "valorMulta": 0.1,
    "dataInicioMulta": "string",
    "taxaMulta": 0.1
  },
  "indicadorDispensarMulta": "string",
  "indicadorNegativar": "string",
  "negativacao": {
    "quantidadeDiasNegativacao": 0,
    "tipoNegativacao": 0,
    "orgaoNegativador": 0
  },
  "indicadorAlterarSeuNumero": "string",
  "alteracaoSeuNumero": {
    "codigoSeuNumero": "string"
  },
  "indicadorAlterarEnderecoPagador": "string",
  "alteracaoEndereco": {
    "enderecoPagador": "string",
    "bairroPagador": "string",
    "cidadePagador": "string",
    "UFPagador": "string",
    "CEPPagador": 0
  },
  "indicadorAlterarPrazoBoletoVencido": "string",
  "alteracaoPrazo": {
    "quantidadeDiasAceite": 0
  }
}
```

### 🗂️ Parâmetros de Caminho
- **id** *(string, obrigatório)* — Número do título de cobrança.

### 🔍 Parâmetros de Query
- **gw-dev-app-key** *(string, obrigatório)* — Chave da aplicação (developer_application_key).

### ✅ Exemplo de Requisição
```bash
curl -X PATCH \
  'https://api.sandbox.bb.com.br/cobrancas/v2/boletos/12345678901234567890?gw-dev-app-key=0021239456d80136bebf00505689bed' \
  -H 'Authorization: Bearer eyJhbGciOi...<restante_do_token>' \
  -H 'Content-Type: application/json' \
  -d '{
        "numeroConvenio": 1234567,
        "indicadorNovaDataVencimento": "S",
        "alteracaoData": { "novaDataVencimento": "2025-12-31" }
      }'
```

### 📥 Resposta (200 OK)
```json
{
  "numeroContratoCobranca": 0,
  "dataAtualizacao": "string",
  "horarioAtualizacao": "string"
}
```

### ⚠️ Possíveis Códigos de Erro
- **400 – Requisição inválida**
```json
{
  "errors": [
    {
      "code": "4874915.1",
      "message": "Nosso Número já incluído anteriormente.",
      "action": "Informar outro Nosso Número."
    }
  ]
}
```
- **401 – Não autorizado**
```json
{
  "statusCode": 0,
  "error": "string",
  "message": "string",
  "attributes": {
    "error": "string"
  }
}
```
- **403 – Proibido**
```json
{
  "errors": [
    {
      "code": "4874915.1",
      "message": "Nosso Número já incluído anteriormente.",
      "action": "Informar outro Nosso Número."
    }
  ]
}
```
- **404 – Não encontrado** *(sem exemplo JSON na documentação)*
- **500 – Erro interno**
```json
{
  "errors": [
    {
      "code": "4874915.1",
      "message": "Nosso Número já incluído anteriormente.",
      "action": "Informar outro Nosso Número."
    }
  ]
}
```

### 🗝️ Observações importantes
- Permite alterar múltiplos atributos do boleto em uma única requisição.
- Campos de alteração são controlados por indicadores (`indicador...`) que determinam se a modificação será aplicada.
- É necessário informar o **número do convênio** e a **chave da aplicação** para autenticação e autorização.
- Alterações podem estar sujeitas a regras contratuais e prazos definidos pelo Banco do Brasil.

---
---

## `PATCH /boletos/{id}/baixar`

### 📌 Descrição
Permite a baixa (cancelamento) de um título de cobrança registrado no Banco do Brasil.

### 🌐 URL (Relativa)
`/boletos/{id}/baixar?gw-dev-app-key={gw-dev-app-key}`

### 🧾 Headers obrigatórios
- **Authorization**: Bearer `<token_de_acesso_OAuth2.0_JWT>`
- **Content-Type**: application/json

### 📤 Corpo da Requisição (JSON)
```json
{
  "numeroConvenio": 0
}
```

### 🗂️ Parâmetros de Caminho
- **id** *(string, obrigatório)* — Número do boleto bancário (único e exclusivo) que identifica o título e é usado para pagá-lo.

### 🔍 Parâmetros de Query
- **gw-dev-app-key** *(string, obrigatório)* — Chave da aplicação (developer_application_key) obtida no Portal Developers BB.

### ✅ Exemplo de Requisição
```bash
curl -X PATCH \
  'https://api.sandbox.bb.com.br/cobrancas/v2/boletos/12345678901234567890/baixar?gw-dev-app-key=0021239456d80136bebf00505689bed' \
  -H 'Authorization: Bearer eyJhbGciOi...<restante_do_token>' \
  -H 'Content-Type: application/json' \
  -d '{
        "numeroConvenio": 1234567
      }'
```

### 📥 Resposta (200 OK)
```json
{
  "numeroContratoCobranca": "string",
  "dataBaixa": "string",
  "horarioBaixa": "string"
}
```

### ⚠️ Possíveis Códigos de Erro

#### 400 – Solicitação incorreta
```json
{
  "errors": [
    {
      "code": "4874915.1",
      "message": "Nosso Número já incluído anteriormente.",
      "action": "Informar outro Nosso Número."
    }
  ]
}
```

#### 401 – Não autorizado
```json
{
  "statusCode": 0,
  "error": "string",
  "message": "string",
  "attributes": {
    "error": "string"
  }
}
```

#### 403 – Proibido
```json
{
  "errors": [
    {
      "code": "4874915.1",
      "message": "Nosso Número já incluído anteriormente.",
      "action": "Informar outro Nosso Número."
    }
  ]
}
```

#### 500 – Erro interno
```json
{
  "errors": [
    {
      "code": "4874915.1",
      "message": "Nosso Número já incluído anteriormente.",
      "action": "Informar outro Nosso Número."
    }
  ]
}
```

### 🗝️ Observações importantes
- A baixa de um boleto é uma operação irreversível.
- É necessário informar o **número do convênio** no corpo da requisição.
- O boleto precisa estar em situação que permita a baixa; boletos já liquidados ou baixados não podem ser cancelados novamente.

---
---

## `PATCH /boletos/{id}/cancelar-pix`

### 📌 Descrição
Cancela o Pix vinculado a um boleto de cobrança existente.

### 🌐 URL (Relativa)
`/boletos/{id}/cancelar-pix?gw-dev-app-key={gw-dev-app-key}`

### 🧾 Headers obrigatórios
- **Authorization**: Bearer `<token_de_acesso_OAuth2.0_JWT>`
- **Content-Type**: application/json

### 📤 Corpo da Requisição (JSON)
```json
{
  "numeroConvenio": 0
}
```

### 🗂️ Parâmetros de Caminho
- **id** *(string, obrigatório)* — Número de identificação do boleto (correspondente ao **NOSSO NÚMERO** / `numeroTituloCliente`), no formato STRING com 20 dígitos: `"000"` + (número do convênio com 7 dígitos) + (10 algarismos, completando com zeros à esquerda se necessário).

### 🔍 Parâmetros de Query
- **gw-dev-app-key** *(string, obrigatório)* — Chave da aplicação (`developer_application_key`) obtida no Portal Developers BB.

### ✅ Exemplo de Requisição
```bash
curl -X PATCH \
  'https://api.sandbox.bb.com.br/cobrancas/v2/boletos/00012345671234567890/cancelar-pix?gw-dev-app-key=0021239456d80136bebf00505689bed' \
  -H 'Authorization: Bearer eyJhbGciOi...<restante_do_token>' \
  -H 'Content-Type: application/json' \
  -d '{
        "numeroConvenio": 1234567
      }'
```

### 📥 Resposta (200 OK)
```json
{
  "pix": {
    "chave": "string"
  },
  "qrCode": {
    "url": "string",
    "txId": "string",
    "emv": "string"
  }
}
```

### ⚠️ Possíveis Códigos de Erro

#### 400 – Requisição inválida
```json
{
  "erros": [
    {
      "codigo": "4874915",
      "versao": "1",
      "mensagem": "Nosso Número já incluído anteriormente.",
      "providencia": "Informar outro Nosso Número.",
      "ocorrencia": "2AzBShK/zFE=C0700051620C"
    }
  ]
}
```

#### 401 – Não autorizado
```json
{
  "statusCode": 0,
  "error": "string",
  "message": "string",
  "attributes": {
    "error": "string"
  }
}
```

#### 403 – Proibido
```json
{
  "erros": [
    {
      "codigo": "4874915",
      "versao": "1",
      "mensagem": "Nosso Número já incluído anteriormente.",
      "providencia": "Informar outro Nosso Número.",
      "ocorrencia": "2AzBShK/zFE=C0700051620C"
    }
  ]
}
```

#### 404 – Não encontrado
*(Sem exemplo JSON na documentação)*

#### 500 – Erro interno
```json
{
  "erros": [
    {
      "codigo": "4874915",
      "versao": "1",
      "mensagem": "Nosso Número já incluído anteriormente.",
      "providencia": "Informar outro Nosso Número.",
      "ocorrencia": "2AzBShK/zFE=C0700051620C"
    }
  ]
}
```

### 🗝️ Observações importantes
- O cancelamento do Pix remove a possibilidade de pagamento via Pix para o boleto informado.
- É necessário informar o **número do convênio** no corpo da requisição.
- O boleto deve estar em situação que permita o cancelamento do Pix.

---
---

## `POST /boletos/{id}/gerar-pix`

### 📌 Descrição
Gera um Pix vinculado a um boleto de cobrança, retornando um QR Code dinâmico ou estático para pagamento.

### 🌐 URL (Relativa)
`/boletos/{id}/gerar-pix?gw-dev-app-key={gw-dev-app-key}`

### 🧾 Headers obrigatórios
- **Authorization**: Bearer `<token_de_acesso_OAuth2.0_JWT>`
- **Content-Type**: application/json

### 📤 Corpo da Requisição (JSON)
```json
{
  "numeroConvenio": 0
}
```

### 🗂️ Parâmetros de Caminho
- **id** *(string, obrigatório)* — Número de identificação do boleto (correspondente ao **NOSSO NÚMERO** / `numeroTituloCliente`), no formato STRING com 20 dígitos: `"000"` + (número do convênio com 7 dígitos) + (10 algarismos, completando com zeros à esquerda se necessário).

### 🔍 Parâmetros de Query
- **gw-dev-app-key** *(string, obrigatório)* — Chave da aplicação (`developer_application_key`) obtida no Portal Developers BB.

### ✅ Exemplo de Requisição
```bash
curl -X POST \
  'https://api.sandbox.bb.com.br/cobrancas/v2/boletos/00012345671234567890/gerar-pix?gw-dev-app-key=0021239456d80136bebf00505689bed' \
  -H 'Authorization: Bearer eyJhbGciOi...<restante_do_token>' \
  -H 'Content-Type: application/json' \
  -d '{
        "numeroConvenio": 1234567
      }'
```

### 📥 Resposta (200 OK)
```json
{
  "pix": {
    "chave": "string"
  },
  "qrCode": {
    "url": "string",
    "txId": "string",
    "emv": "string"
  }
}
```

### ⚠️ Possíveis Códigos de Erro

#### 400 – Requisição inválida
```json
{
  "erros": [
    {
      "codigo": "4874915",
      "versao": "1",
      "mensagem": "Nosso Número já incluído anteriormente.",
      "providencia": "Informar outro Nosso Número.",
      "ocorrencia": "2AzBShK/zFE=C0700051620C"
    }
  ]
}
```

#### 401 – Não autorizado
```json
{
  "statusCode": 0,
  "error": "string",
  "message": "string",
  "attributes": {
    "error": "string"
  }
}
```

#### 403 – Proibido
```json
{
  "erros": [
    {
      "codigo": "4874915",
      "versao": "1",
      "mensagem": "Nosso Número já incluído anteriormente.",
      "providencia": "Informar outro Nosso Número.",
      "ocorrencia": "2AzBShK/zFE=C0700051620C"
    }
  ]
}
```

#### 404 – Não encontrado
*(Sem exemplo JSON na documentação)*

#### 500 – Erro interno
```json
{
  "erros": [
    {
      "codigo": "4874915",
      "versao": "1",
      "mensagem": "Nosso Número já incluído anteriormente.",
      "providencia": "Informar outro Nosso Número.",
      "ocorrencia": "2AzBShK/zFE=C0700051620C"
    }
  ]
}
```

### 🗝️ Observações importantes
- O Pix gerado estará vinculado ao boleto informado e poderá ser pago via QR Code.
- É necessário informar o **número do convênio** no corpo da requisição.
- O boleto deve estar em situação que permita a geração de Pix.

---
---

## `GET /boletos/{id}/pix`

### 📌 Descrição
Consulta os dados de um Pix vinculado a um boleto de cobrança.

### 🌐 URL (Relativa)
`/boletos/{id}/pix?numeroConvenio={numeroConvenio}&gw-dev-app-key={gw-dev-app-key}`

### 🧾 Headers obrigatórios
- **Authorization**: Bearer `<token_de_acesso_OAuth2.0_JWT>`
- **Accept**: application/json

### 🗂️ Parâmetros de Caminho
- **id** *(string, obrigatório)* — Número de identificação do boleto (correspondente ao **NOSSO NÚMERO** / `numeroTituloCliente`), no formato STRING com 20 dígitos: `"000"` + (número do convênio com 7 dígitos) + (10 algarismos, completando com zeros à esquerda se necessário).

### 🔍 Parâmetros de Query
- **numeroConvenio** *(number, obrigatório)* — Número do convênio de Cobrança do Cliente.
- **gw-dev-app-key** *(string, obrigatório)* — Chave da aplicação (`developer_application_key`) obtida no Portal Developers BB.

### ✅ Exemplo de Requisição
```bash
curl -X GET \
  'https://api.sandbox.bb.com.br/cobrancas/v2/boletos/00012345671234567890/pix?numeroConvenio=1234567&gw-dev-app-key=0021239456d80136bebf00505689bed' \
  -H 'Authorization: Bearer eyJhbGciOi...<restante_do_token>' \
  -H 'Accept: application/json'
```

### 📥 Resposta (200 OK)
```json
{
  "id": "stringstringstringst",
  "dataRegistroTituloCobranca": "string",
  "agenciaBeneficiario": 0,
  "contaBeneficiario": 0,
  "valorOriginalTituloCobranca": 0.1,
  "validadeTituloCobranca": "string",
  "pix": {
    "valorRecebido": 0.1,
    "timestamp": "string",
    "chave": "string",
    "textoRetorno": "string",
    "idInstituicaoPagador": 0,
    "agenciaPagador": 0,
    "contaPagador": 0,
    "tipoPessoaPagador": 1,
    "idPagador": 0.1
  },
  "qrCode": {
    "url": "string",
    "txId": "string",
    "emv": "string",
    "tipo": 1
  }
}
```

### ⚠️ Possíveis Códigos de Erro

#### 400 – Requisição inválida
```json
{
  "erros": [
    {
      "codigo": "4874915",
      "versao": "1",
      "mensagem": "Nosso Número já incluído anteriormente.",
      "providencia": "Informar outro Nosso Número.",
      "ocorrencia": "2AzBShK/zFE=C0700051620C"
    }
  ]
}
```

#### 401 – Não autorizado
```json
{
  "statusCode": 0,
  "error": "string",
  "message": "string",
  "attributes": {
    "error": "string"
  }
}
```

#### 404 – Não encontrado
*(Sem exemplo JSON na documentação)*

#### 500 – Erro interno
```json
{
  "erros": [
    {
      "codigo": "4874915",
      "versao": "1",
      "mensagem": "Nosso Número já incluído anteriormente.",
      "providencia": "Informar outro Nosso Número.",
      "ocorrencia": "2AzBShK/zFE=C0700051620C"
    }
  ]
}
```

### 🗝️ Observações importantes
- O Pix consultado estará vinculado ao boleto informado.
- É necessário informar o **número do convênio** como parâmetro de query.
- O boleto deve ter um Pix previamente gerado para que a consulta retorne dados.


---
---

## `POST /convenios/{id}/listar-retorno-movimento`

### 📌 Descrição
Lista os dados do retorno de movimento do convênio de Cobranças, permitindo consultar registros de liquidação, baixa e outros eventos ocorridos em um período informado.  
**Atenção:** Para utilização deste serviço, é necessário entrar em contato com o Gerente de Cash ou Gerente de Relacionamento do Banco do Brasil.

### 🌐 URL (Relativa)
`/convenios/{id}/listar-retorno-movimento?gw-dev-app-key={gw-dev-app-key}`

### 🧾 Headers obrigatórios
- **Authorization**: Bearer `<token_de_acesso_OAuth2.0_JWT>`
- **Content-Type**: application/json
- **Accept**: application/json

### 📤 Corpo da Requisição (JSON)
```json
{
  "dataMovimentoRetornoInicial": "01.03.2022",
  "dataMovimentoRetornoFinal": "20.03.2022",
  "codigoPrefixoAgencia": 1,
  "numeroContaCorrente": 12345678,
  "numeroCarteiraCobranca": 17,
  "numeroVariacaoCarteiraCobranca": 35,
  "numeroRegistroPretendido": 1,
  "quantidadeRegistroPretendido": 10000
}
```

### 🗂️ Parâmetros de Caminho
- **id** *(string, obrigatório)* — Número identificador do convênio de intercâmbio de dados em meio eletrônico, pelo qual serão fornecidos os dados dos títulos de um ou mais serviços de cobrança contratados. Exemplo: `1234567`.

### 🔍 Parâmetros de Query
- **gw-dev-app-key** *(string, obrigatório)* — Chave da aplicação (`developer_application_key`) obtida no Portal Developers BB.

### ✅ Exemplo de Requisição
```bash
curl -X POST \
  'https://api.sandbox.bb.com.br/cobrancas/v2/convenios/1234567/listar-retorno-movimento?gw-dev-app-key=0021239456d80136bebf00505689bed' \
  -H 'Authorization: Bearer IId7jeRGJVeMTpFq0MhictygJfXqiiidLKrKf2Y8WkUBxuu0qVu8mWMCNVFuucbw...' \
  -H 'Content-Type: application/json' \
  -H 'Accept: application/json' \
  -d '{
        "dataMovimentoRetornoInicial": "01.03.2022",
        "dataMovimentoRetornoFinal": "20.03.2022",
        "codigoPrefixoAgencia": 1,
        "numeroContaCorrente": 12345678,
        "numeroCarteiraCobranca": 17,
        "numeroVariacaoCarteiraCobranca": 35,
        "numeroRegistroPretendido": 1,
        "quantidadeRegistroPretendido": 10000
      }'
```

### 📥 Resposta (200 OK)
```json
{
  "indicadorContinuidade": "S",
  "numeroUltimoRegistro": 8900,
  "listaRegistro": [
    {
      "dataMovimentoRetorno": "24.03.2022",
      "numeroConvenio": 1234567,
      "numeroTituloCobranca": "00012345670000000001",
      "codigoComandoAcao": 2,
      "codigoPrefixoAgencia": 1,
      "numeroContaCorrente": 123456789,
      "numeroCarteiraCobranca": 17,
      "numeroVariacaoCarteiraCobranca": 35,
      "tipoCobranca": 1,
      "codigoControleParticipante": "A123456",
      "codigoEspecieBoleto": 0,
      "dataVencimentoBoleto": "31.12.2022",
      "valorBoleto": 100,
      "codigoBancoRecebedor": 237,
      "codigoPrefixoAgenciaRecebedora": 2,
      "dataCreditoPagamentoBoleto": "03.02.2023",
      "valorTarifa": 9,
      "valorOutrasDespesasCalculadas": 0,
      "valorJurosDesconto": 1,
      "valorIofDesconto": 0.5,
      "valorAbatimento": 10,
      "valorDesconto": 1,
      "valorRecebido": 100,
      "valorJurosMora": 5,
      "valorOutrosValoresRecebidos": 2,
      "valorAbatimentoNaoUtilizado": 1,
      "valorLancamento": 30,
      "codigoFormaPagamento": 0,
      "codigoValorAjuste": 0,
      "valorAjuste": 0.51,
      "codigoAutorizacaoPagamentoParcial": 1,
      "codigoCanalPagamento": 11,
      "URL": "qrcode.sed.desenv.bb.com.br/pix/v2/cobv/ce8a678e-0a0c-414e-938a-88dc072708a0",
      "textoIdentificadorQRCode": "BOLETO19221240800005487DATA17012022 ",
      "quantidadeDiasCalculo": 12,
      "valorTaxaDesconto": 0.33,
      "valorTaxaIOF": 1.2323,
      "naturezaRecebimento": 7,
      "codigoTipoCobrancaComando": 0,
      "dataLiquidacaoBoleto": "dd.mm.aaaa"
    }
  ]
}
```

### ⚠️ Possíveis Códigos de Erro

#### 400 – Requisição inválida
```json
{
  "erros": [
    {
      "codigo": "4874915",
      "versao": "1",
      "mensagem": "Nosso Número já incluído anteriormente.",
      "providencia": "Informar outro Nosso Número.",
      "ocorrencia": "2AzBShK/zFE=C0700051620C"
    }
  ]
}
```

#### 401 – Não autorizado
```json
{
  "statusCode": 0,
  "error": "string",
  "message": "string",
  "attributes": {
    "error": "string"
  }
}
```

#### 403 – Proibido
```json
{
  "erros": [
    {
      "codigo": "4874915",
      "versao": "1",
      "mensagem": "Nosso Número já incluído anteriormente.",
      "providencia": "Informar outro Nosso Número.",
      "ocorrencia": "2AzBShK/zFE=C0700051620C"
    }
  ]
}
```

#### 404 – Não encontrado
*(Sem exemplo JSON na documentação)*

#### 500 – Erro interno
```json
{
  "erros": [
    {
      "codigo": "4874915",
      "versao": "1",
      "mensagem": "Nosso Número já incluído anteriormente.",
      "providencia": "Informar outro Nosso Número.",
      "ocorrencia": "2AzBShK/zFE=C0700051620C"
    }
  ]
}
```

### 🗝️ Observações importantes
- O serviço retorna registros de movimentação de cobrança dentro do intervalo de datas informado.
- É possível paginar os resultados usando os campos `numeroRegistroPretendido` e `quantidadeRegistroPretendido`.
- O campo `indicadorContinuidade` indica se há mais registros a serem consultados (`S` para sim).
- Necessário habilitar o serviço junto ao Banco do Brasil antes da utilização.

---
---

## `PATCH /convenios/{id}/ativar-consulta-baixa-operacional`

### 📌 Descrição
Habilita a personalização de um convênio, permitindo realizar a consulta das informações de **baixa operacional** de boletos da carteira de cobranças do cliente no mesmo dia.

### 🌐 URL (Relativa)
`/convenios/{id}/ativar-consulta-baixa-operacional?gw-dev-app-key={gw-dev-app-key}`

### 🧾 Headers obrigatórios
- **Authorization**: Bearer `<token_de_acesso_OAuth2.0_JWT>`
- **Accept**: application/json

### 🗂️ Parâmetros de Caminho
- **id** *(string, obrigatório)* — Número do convênio de Cobrança do Cliente.

### 🔍 Parâmetros de Query
- **gw-dev-app-key** *(string, obrigatório)* — Chave da aplicação (`developer_application_key`) obtida no Portal Developers BB.

### ✅ Exemplo de Requisição
```bash
curl -X PATCH \
  'https://api.sandbox.bb.com.br/cobrancas/v2/convenios/1234567/ativar-consulta-baixa-operacional?gw-dev-app-key=0021239456d80136bebf00505689bed' \
  -H 'Authorization: Bearer IId7jeRGJVeMTpFq0MhictygJfXqiiidLKrKf2Y8WkUBxuu0qVu8mWMCNVFuucbw...' \
  -H 'Accept: application/json'
```

### 📥 Resposta (200 OK)
```json
{
  "estadoPersonalizacao": "string",
  "dataHoraEstado": "string"
}
```

### ⚠️ Possíveis Códigos de Erro

#### 400 – Requisição inválida
```json
{
  "erros": [
    {
      "codigo": "4874915",
      "versao": "1",
      "mensagem": "Nosso Número já incluído anteriormente.",
      "providencia": "Informar outro Nosso Número.",
      "ocorrencia": "2AzBShK/zFE=C0700051620C"
    }
  ]
}
```

#### 401 – Não autorizado
```json
{
  "statusCode": 0,
  "error": "string",
  "message": "string",
  "attributes": {
    "error": "string"
  }
}
```

#### 403 – Proibido
```json
{
  "erros": [
    {
      "codigo": "4874915",
      "versao": "1",
      "mensagem": "Nosso Número já incluído anteriormente.",
      "providencia": "Informar outro Nosso Número.",
      "ocorrencia": "2AzBShK/zFE=C0700051620C"
    }
  ]
}
```

#### 404 – Não encontrado
*(Sem exemplo JSON na documentação)*

#### 500 – Erro interno
```json
{
  "erros": [
    {
      "codigo": "4874915",
      "versao": "1",
      "mensagem": "Nosso Número já incluído anteriormente.",
      "providencia": "Informar outro Nosso Número.",
      "ocorrencia": "2AzBShK/zFE=C0700051620C"
    }
  ]
}
```

### 🗝️ Observações importantes
- Este endpoint **não requer corpo de requisição**.
- A ativação permite que o cliente consulte baixas operacionais de boletos no mesmo dia.
- É necessário que o convênio esteja previamente configurado para utilização deste recurso junto ao Banco do Brasil.

---
---

## `PATCH /convenios/{id}/desativar-consulta-baixa-operacional`

### 📌 Descrição
Desativa a personalização de um convênio, não permitindo realizar a consulta das informações de **baixa operacional** de boletos da carteira de cobranças do cliente no mesmo dia.

### 🌐 URL (Relativa)
`/convenios/{id}/desativar-consulta-baixa-operacional?gw-dev-app-key={gw-dev-app-key}`

### 🧾 Headers obrigatórios
- **Authorization**: Bearer `<token_de_acesso_OAuth2.0_JWT>`
- **Accept**: application/json

### 🗂️ Parâmetros de Caminho
- **id** *(string, obrigatório)* — Número do convênio de Cobrança do Cliente.

### 🔍 Parâmetros de Query
- **gw-dev-app-key** *(string, obrigatório)* — Chave da aplicação (`developer_application_key`) obtida no Portal Developers BB.

### ✅ Exemplo de Requisição
```bash
curl -X PATCH \
  'https://api.sandbox.bb.com.br/cobrancas/v2/convenios/1234567/desativar-consulta-baixa-operacional?gw-dev-app-key=0021239456d80136bebf00505689bed' \
  -H 'Authorization: Bearer IId7jeRGJVeMTpFq0MhictygJfXqiiidLKrKf2Y8WkUBxuu0qVu8mWMCNVFuucbw...' \
  -H 'Accept: application/json'
```

### 📥 Resposta (200 OK)
```json
{
  "estadoPersonalizacao": "string",
  "dataHoraEstado": "string"
}
```

### ⚠️ Possíveis Códigos de Erro

#### 400 – Requisição inválida
```json
{
  "erros": [
    {
      "codigo": "4874915",
      "versao": "1",
      "mensagem": "Nosso Número já incluído anteriormente.",
      "providencia": "Informar outro Nosso Número.",
      "ocorrencia": "2AzBShK/zFE=C0700051620C"
    }
  ]
}
```

#### 401 – Não autorizado
```json
{
  "statusCode": 0,
  "error": "string",
  "message": "string",
  "attributes": {
    "error": "string"
  }
}
```

#### 403 – Proibido
```json
{
  "erros": [
    {
      "codigo": "4874915",
      "versao": "1",
      "mensagem": "Nosso Número já incluído anteriormente.",
      "providencia": "Informar outro Nosso Número.",
      "ocorrencia": "2AzBShK/zFE=C0700051620C"
    }
  ]
}
```

#### 404 – Não encontrado
*(Sem exemplo JSON na documentação)*

#### 500 – Erro interno
```json
{
  "erros": [
    {
      "codigo": "4874915",
      "versao": "1",
      "mensagem": "Nosso Número já incluído anteriormente.",
      "providencia": "Informar outro Nosso Número.",
      "ocorrencia": "2AzBShK/zFE=C0700051620C"
    }
  ]
}
```

### 🗝️ Observações importantes
- Este endpoint **não requer corpo de requisição**.
- A desativação impede que o cliente consulte baixas operacionais de boletos no mesmo dia.
- É necessário que o convênio esteja previamente configurado para utilização deste recurso junto ao Banco do Brasil.
