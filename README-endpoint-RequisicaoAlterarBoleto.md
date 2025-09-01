# 🧾 Schema: `RequisicaoAlterarBoletos` (com descrições completas)

Schema completo para o endpoint que "Realiza alterações nos valores dos campos presentes em um boleto bancário já criado".

## Endpoint: `PATCH /boletos/{id}`

> id: Número do título de cobrança.

| Campo | Tipo | Descrição Completa |
|-------|------|-------------------|
| `numeroConvenio` | `integer (int32)` | Identificador determinado pelo sistema de boleto bancário para fornecer a emissão e liquidação do boleto e, portanto, usado para creditar o Beneficiário. |

---

### 📅 Alteração de Data de Vencimento

| Campo | Tipo | Descrição Completa |
|-------|------|-------------------|
| `indicadorNovaDataVencimento` | `string` | Indica a intenção de atribuir nova data de vencimento ao boleto. Valores a informar: `"S"` → Sim, desejo alterar; `"N"` → Não, não desejo alterar. |
| `alteracaoData` | `object` | Alteração de data de vencimento do boleto. |
| → `novaDataVencimento` | `string` | Nova data de vencimento do boleto. |

---

### 💰 Alteração de Valor Nominal

| Campo | Tipo | Descrição Completa |
|-------|------|-------------------|
| `indicadorNovoValorNominal` | `string` | Indica a intenção de alterar valor nominal do boleto. Valores a informar: `"S"` → Sim, desejo alterar; `"N"` → Não, não desejo alterar. |
| `alteracaoValor` | `object` | Alteração do valor nominal do boleto. |
| → `novoValorNominal` | `number (double)` | Novo valor nominal do boleto. |

---

### 💸 Inclusão e Alteração de Descontos

| Campo | Tipo | Descrição Completa |
|-------|------|-------------------|
| `indicadorAtribuirDesconto` | `string` | Indica a intenção de atribuir desconto ao boleto. Valores a informar: `"S"` → Sim, desejo alterar; `"N"` → Não, não desejo alterar. |
| `desconto` | `object` | Inclusão de desconto em Boleto Bancário. |

#### → Campos de `desconto`

| Campo | Tipo | Descrição Completa |
|-------|------|-------------------|
| `tipoPrimeiroDesconto` | `integer (int32)` | Código para identificação do tipo de desconto que deverá ser concedido como Primeiro Desconto. Valores: `0` - Sem desconto; `1` - Valor fixo até a data; `2` - Percentual até a data informada; `3` - Desconto por dia de antecipação. |
| `valorPrimeiroDesconto` | `number (double)` | Valor do Primeiro Desconto. |
| `percentualPrimeiroDesconto` | `number (double)` | Percentual de desconto sobre o valor do boleto no Primeiro Desconto. |
| `dataPrimeiroDesconto` | `string` | Data limite do Primeiro Desconto. |
| `tipoSegundoDesconto` | `integer (int32)` | Código para identificação do tipo de desconto que deverá ser concedido como Segundo Desconto. |
| `valorSegundoDesconto` | `number (double)` | Valor do Segundo Desconto. |
| `percentualSegundoDesconto` | `number (double)` | Percentual de desconto sobre o valor do boleto no Segundo Desconto. |
| `dataSegundoDesconto` | `string` | Data limite do Segundo Desconto. |
| `tipoTerceiroDesconto` | `integer (int32)` | Código para identificação do tipo de desconto que deverá ser concedido como Terceiro Desconto. |
| `valorTerceiroDesconto` | `number (double)` | Valor do Terceiro Desconto. |
| `percentualTerceiroDesconto` | `number (double)` | Percentual de desconto sobre o valor do boleto no Terceiro Desconto. |
| `dataTerceiroDesconto` | `string` | Data limite do Terceiro Desconto. |

---

### ✏️ Alteração de Desconto Existente

| Campo | Tipo | Descrição Completa |
|-------|------|-------------------|
| `indicadorAlterarDesconto` | `string` | Indica a intenção de alterar desconto do boleto. Valores: `"S"` → Sim; `"N"` → Não. |
| `alteracaoDesconto` | `object` | Alteração de desconto em Boletos Bancários. |

#### → Campos de `alteracaoDesconto`

Mesma estrutura do campo `desconto`, com prefixo `novo`.

---

### 📆 Alteração de Datas de Desconto

| Campo | Tipo | Descrição Completa |
|-------|------|-------------------|
| `indicadorAlterarDataDesconto` | `string` | Indica a intenção de alterar a data do desconto do boleto. |
| `alteracaoDataDesconto` | `object` | Alteração das datas limites para concessão de desconto no Boleto Bancário. |

---

### 📜 Protesto

| Campo | Tipo | Descrição Completa |
|-------|------|-------------------|
| `indicadorProtestar` | `string` | Indica a intenção de protestar o boleto. |
| `protesto` | `object` | Inclusão de Protesto em Boleto Bancário. |
| → `quantidadeDiasProtesto` | `number (float)` | Quantos dias após a data de vencimento do boleto para iniciar o processo de cobrança através de protesto. (valor inteiro ≥ 0). |
| `indicadorSustacaoProtesto` | `string` | Indica a intenção de sustar/cancelar um comando de protesto do boleto que já tenha sido processado pelo Banco. |
| `indicadorCancelarProtesto` | `string` | Deve ser utilizada para cancelar uma instrução de protesto enviada ao Banco na mesma data ou que ainda não tenha sido processada. |

---

### 💳 Abatimento

| Campo | Tipo | Descrição Completa |
|-------|------|-------------------|
| `indicadorIncluirAbatimento` | `string` | Indica a intenção de incluir abatimento no boleto. |
| `abatimento` | `object` | Inclusão de Abatimento em Boleto Bancário. |
| → `valorAbatimento` | `number (double)` | Valor do abatimento (reduz valor do boleto) expresso em moeda corrente. |
| `indicadorAlterarAbatimento` | `string` | Indica a intenção de alterar o valor do abatimento no boleto. |
| `alteracaoAbatimento` | `object` | Alterar valor do abatimento concedido. |
| → `novoValorAbatimento` | `number (double)` | Novo valor do abatimento. |

---

### 📈 Juros de Mora

| Campo | Tipo | Descrição Completa |
|-------|------|-------------------|
| `indicadorCobrarJuros` | `string` | Indica a intenção de cobrar juros no boleto. |
| `juros` | `object` | Inclusão de Juros de Mora em Boleto Bancário. |
| → `tipoJuros` | `integer (int32)` | Código para identificação do tipo de Juros de Mora. Valores: `0` - Dispensar; `1` - Valor por dia; `2` - Taxa Mensal; `3` - Isento. |
| → `valorJuros` | `number (double)` | Valor fixo por dia. |
| → `taxaJuros` | `number (double)` | Percentual mensal. |
| `indicadorDispensarJuros` | `string` | Indica a intenção de dispensar juros no boleto. |

---

### 🔥 Multa

| Campo | Tipo | Descrição Completa |
|-------|------|-------------------|
| `indicadorCobrarMulta` | `string` | Indica a intenção de cobrar multa no boleto. |
| `multa` | `object` | Inclusão de multa em Boleto Bancário. |
| → `tipoMulta` | `integer (int32)` | Código para identificação do tipo de multa. |
| → `valorMulta` | `number (float)` | Valor fixo da multa. |
| → `taxaMulta` | `number (float)` | Percentual da multa. |
| → `dataInicioMulta` | `string` | Data para início da cobrança da multa. |
| `indicadorDispensarMulta` | `string` | Indica a intenção de dispensar cobrança de multa. |

---

### 🧾 Negativação

| Campo | Tipo | Descrição Completa |
|-------|------|-------------------|
| `indicadorNegativar` | `string` | Indica a intenção de negativar ou cancelar negativação do boleto. |
| `negativacao` | `object` | Inclui condições de Negativação de Boleto Bancário. |
| → `quantidadeDiasNegativacao` | `integer (int32)` | Dias após vencimento para negativar. |
| → `tipoNegativacao` | `integer (int32)` | Código do tipo de negativação: `1` - Incluir; `2` - Alterar; `3`

## Exemplo completo (JSON)
```json
{
  "numeroConvenio": 1234567,
  "indicadorNovaDataVencimento": "S",
  "alteracaoData": {
    "novaDataVencimento": "10.09.2025"
  },
  "indicadorNovoValorNominal": "S",
  "alteracaoValor": {
    "valorNominal": 1500.00
  },
  "indicadorAtribuirDesconto": "S",
  "desconto": {
    "tipoPrimeiroDesconto": 1,
    "valorPrimeiroDesconto": 100.00,
    "percentualPrimeiroDesconto": 0.0,
    "dataPrimeiroDesconto": "05.09.2025",
    "tipoSegundoDesconto": 2,
    "valorSegundoDesconto": 0.0,
    "percentualSegundoDesconto": 5.0,
    "dataSegundoDesconto": "07.09.2025",
    "tipoTerceiroDesconto": 0,
    "valorTerceiroDesconto": 0.0,
    "percentualTerceiroDesconto": 0.0,
    "dataTerceiroDesconto": "00.00.0000"
  },
  "indicadorAlterarDesconto": "S",
  "alteracaoDesconto": {
    "tipoPrimeiroDesconto": 1,
    "novoValorPrimeiroDesconto": 120.00,
    "novoPercentualPrimeiroDesconto": 0.0,
    "novaDataLimitePrimeiroDesconto": "06.09.2025",
    "tipoSegundoDesconto": 2,
    "novoValorSegundoDesconto": 0.0,
    "novoPercentualSegundoDesconto": 6.0,
    "novaDataLimiteSegundoDesconto": "08.09.2025",
    "tipoTerceiroDesconto": 0,
    "novoValorTerceiroDesconto": 0.0,
    "novoPercentualTerceiroDesconto": 0.0,
    "novaDataLimiteTerceiroDesconto": "00.00.0000"
  },
  "indicadorAlterarDataDesconto": "S",
  "alteracaoDataDesconto": {
    "novaDataLimitePrimeiroDesconto": "07.09.2025",
    "novaDataLimiteSegundoDesconto": "09.09.2025",
    "novaDataLimiteTerceiroDesconto": "00.00.0000"
  },
  "indicadorProtestar": "S",
  "protesto": {
    "quantidadeDiasProtesto": 5
  },
  "indicadorSustacaoProtesto": "N",
  "indicadorCancelarProtesto": "N",
  "indicadorIncluirAbatimento": "S",
  "abatimento": {
    "valorAbatimento": 50.00
  },
  "indicadorAlterarAbatimento": "S",
  "alteracaoAbatimento": {
    "novoValorAbatimento": 75.00
  },
  "indicadorCobrarJuros": "S",
  "juros": {
    "tipoJuros": 2,
    "valorJuros": 0.0,
    "taxaJuros": 1.5
  },
  "indicadorDispensarJuros": "N",
  "indicadorCobrarMulta": "S",
  "multa": {
    "tipoMulta": 2,
    "valorMulta": 0.0,
    "taxaMulta": 2.0,
    "dataInicioMulta": "11.09.2025"
  },
  "indicadorDispensarMulta": "N",
  "indicadorNegativar": "S",
  "negativacao": {
    "quantidadeDiasNegativacao": 10,
    "tipoNegativacao": 1,
    "orgaoNegativador": 10
  },
  "indicadorAlterarSeuNumero": "S",
  "alteracaoSeuNumero": {
    "codigoSeuNumero": "ABC123456789"
  },
  "indicadorAlterarEnderecoPagador": "S",
  "alteracaoEndereco": {
    "enderecoPagador": "Rua das Palmeiras, 123",
    "bairroPagador": "Centro",
    "cidadePagador": "Luís Eduardo Magalhães",
    "UFPagador": "BA",
    "CEPPagador": 47850000
  },
  "indicadorAlterarPrazoBoletoVencido": "S",
  "alteracaoPrazo": {
    "quantidadeDiasAceite": 15
  }
}
```
