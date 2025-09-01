# 🧾 Schema: `ListarBoletos` (com descrições completas)

## Endpoint: `GET /boletos`

### 🔹 Parâmetros de Consulta (Query)

| Parâmetro | Tipo | Obrigatório | Descrição Completa |
|-----------|------|-------------|---------------------|
| `indicadorSituacao` | `string` | ✅ Sim | Situação do boleto. Deve estar em MAIÚSCULO. Domínios: `A` - Em Ser; `B` - Baixados, Protestados, Liquidados. |
| `agenciaBeneficiario` | `integer` | ✅ Sim | Número da agência do beneficiário (sem dígito). Ex: `452`. |
| `contaBeneficiario` | `integer` | ✅ Sim | Número da conta do beneficiário (sem dígito). Ex: `123873`. |
| `carteiraConvenio` | `integer` | ❌ Não | Número da carteira do convênio de cobrança. Ex: `17`. |
| `variacaoCarteiraConvenio` | `integer` | ❌ Não | Variação da carteira do convênio. Ex: `35`. |
| `modalidadeCobranca` | `integer` | ❌ Não | Modalidade de cobrança. Domínios: `1` - Simples com registro; `2` - Simples sem registro; `4` - Vinculada; `6` - Descontada; `8` - Financiada Vendor. |
| `cnpjPagador` | `integer` | ❌ Não | CNPJ do pagador (sem pontuação). |
| `digitoCNPJPagador` | `integer` | ❌ Não | Dígito verificador do CNPJ. |
| `cpfPagador` | `integer` | ❌ Não | CPF do pagador (sem pontuação). |
| `digitoCPFPagador` | `integer` | ❌ Não | Dígito verificador do CPF. |
| `dataInicioVencimento` | `string` | ❌ Não | Data inicial de vencimento (`dd.mm.aaaa`). Se informado sem data fim, assume data atual como fim. |
| `dataFimVencimento` | `string` | ❌ Não | Data final de vencimento. Deve ser maior que a data de início. |
| `dataInicioRegistro` | `string` | ❌ Não | Data inicial de registro do boleto. |
| `dataFimRegistro` | `string` | ❌ Não | Data final de registro. Deve ser maior que a data de início. |
| `dataInicioMovimento` | `string` | ❌ Não | Data inicial de movimentação (liquidação, baixa, protesto). Usado com `codigoEstadoTituloCobranca` = 05, 06, 07 ou 09. |
| `dataFimMovimento` | `string` | ❌ Não | Data final de movimentação. Deve ser maior que a data de início. |
| `codigoEstadoTituloCobranca` | `integer` | ❌ Não | Código da situação atual do boleto. Domínios: `01` - Normal; `02` a `13` - Situações cartoriais; `18` - Pago parcialmente. |
| `boletoVencido` | `string` | ❌ Não | Indica se o boleto está vencido. Domínios: `S` - Sim; `N` - Não. |
| `indice` | `integer` | ❌ Não | Usado para paginação. Se resposta anterior tiver `indicadorContinuidade = S`, informe o `proximoIndice` retornado. |
| `gw-dev-app-key` | `string` | ✅ Sim | Chave da aplicação (developer_application_key). Máx. 31 caracteres. |
| `Authorization` | `string` | ✅ Sim | Token JWT de acesso via OAuth 2.0. Ex: `Bearer eyJhbGciOi...` |

---

### 📤 Resposta: `200 OK`

#### 🔹 Estrutura do Payload

```json
{
  "indicadorContinuidade": "S",
  "quantidadeRegistros": 300,
  "proximoIndice": 300,
  "boletos": [
    {
      "numeroBoletoBB": "00024589070000000412",
      "estadoTituloCobranca": "Mvto. Cartorio",
      "dataRegistro": "01.05.2020",
      "dataVencimento": "01.05.2020",
      "dataMovimento": "01.05.2020",
      "valorOriginal": 1000.00,
      "valorAtual": 10000.00,
      "valorPago": 9000.00,
      "contrato": 0,
      "carteiraConvenio": 17,
      "variacaoCarteiraConvenio": 27,
      "codigoEstadoTituloCobranca": 1,
      "dataCredito": "01.05.2020"
    }
  ]
}
```

#### 🔹 Campos da Resposta

| Campo | Tipo | Descrição |
|-------|------|-----------|
| `indicadorContinuidade` | `string` | 'S' - Há mais registros; 'N' - Última página. |
| `quantidadeRegistros` | `integer` | Total de registros retornados na página. |
| `proximoIndice` | `integer` | Índice para próxima consulta, se houver mais registros. |
| `boletos` | `array` | Lista de boletos encontrados. Cada item contém: |
| → `numeroBoletoBB` | `string` | Número do boleto gerado pelo Banco do Brasil. |
| → `estadoTituloCobranca` | `string` | Situação textual do boleto. |
| → `dataRegistro` | `string` | Data de registro do boleto. |
| → `dataVencimento` | `string` | Data de vencimento. |
| → `dataMovimento` | `string` | Data de movimentação (baixa, liquidação, protesto). |
| → `valorOriginal` | `float` | Valor original do boleto. |
| → `valorAtual` | `float` | Valor atualizado (com encargos). |
| → `valorPago` | `float` | Valor efetivamente pago. |
| → `contrato` | `integer` | Número do contrato vinculado. |
| → `carteiraConvenio` | `integer` | Número da carteira do convênio. |
| → `variacaoCarteiraConvenio` | `integer` | Variação da carteira. |
| → `codigoEstadoTituloCobranca` | `integer` | Código da situação atual. |
| → `dataCredito` | `string` | Data em que o valor foi creditado ao beneficiário. |
