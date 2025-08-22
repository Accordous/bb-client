# Webhooks

## `POST /baixa-operacional`

### 📌 Descrição
Webhook da API de Cobrança que notifica o emissor do boleto bancário sobre o recebimento, pelo Banco do Brasil, de uma **Baixa Operacional** de um boleto — seja liquidação (pagamento) ou solicitação de baixa.  
A Baixa Operacional é o meio pelo qual a Instituição Recebedora (onde o boleto foi pago) informa à Base Centralizada de Cobrança (PCR) que o boleto está sendo pago, e esta repassa a informação ao Banco emissor.

### 🌐 URL (Sandbox)
`https://api.sandbox.bb.com.br/cobrancas/v2/baixa-operacional`

### 🧾 Headers obrigatórios
- **Accept**: application/json

### 📤 Corpo da Requisição (JSON)
```json
{
  "id": "00031285570000104055",
  "dataRegistro": "11.06.2025",
  "dataVencimento": "11.06.2025",
  "valorOriginal": 1000,
  "valorPagoSacado": 1000,
  "numeroConvenio": 3128557,
  "numeroOperacao": 10055680,
  "carteiraConvenio": 17,
  "variacaoCarteiraConvenio": 35,
  "codigoEstadoBaixaOperacional": 1,
  "dataLiquidacao": "12/06/2025 16:29:30",
  "instituicaoLiquidacao": "001",
  "canalLiquidacao": 4,
  "codigoModalidadeBoleto": 1,
  "tipoPessoaPortador": 2,
  "identidadePortador": 98959112000179,
  "nomePortador": "CINE VENTURA DE PADUA",
  "formaPagamento": 2
}
```

### ✅ Exemplo de Requisição
```bash
curl -X POST \
  'https://api.sandbox.bb.com.br/cobrancas/v2/baixa-operacional' \
  -H 'Accept: application/json' \
  -d '{
        "id": "00031285570000104055",
        "dataRegistro": "11.06.2025",
        "dataVencimento": "11.06.2025",
        "valorOriginal": 1000,
        "valorPagoSacado": 1000,
        "numeroConvenio": 3128557,
        "numeroOperacao": 10055680,
        "carteiraConvenio": 17,
        "variacaoCarteiraConvenio": 35,
        "codigoEstadoBaixaOperacional": 1,
        "dataLiquidacao": "12/06/2025 16:29:30",
        "instituicaoLiquidacao": "001",
        "canalLiquidacao": 4,
        "codigoModalidadeBoleto": 1,
        "tipoPessoaPortador": 2,
        "identidadePortador": 98959112000179,
        "nomePortador": "CINE VENTURA DE PADUA",
        "formaPagamento": 2
      }'
```

### 📥 Resposta (200 OK)
```json
{
  "status": "SUCESSO",
  "mensagem": "A notificação foi enviada com sucesso ao cliente."
}
```

### ⚠️ Possíveis Códigos de Erro

#### 401 – Não autorizado
O certificado mTLS não foi reconhecido pelo Banco do Brasil e, por isso, a notificação não pôde ser entregue.

```json
{
  "statusCode": 401,
  "error": "NÃO AUTORIZADO",
  "message": "O certificado de mTLS não foi reconhecido pelo BB."
}
```

### 🗝️ Observações importantes
- Este endpoint é **um webhook**: o Banco do Brasil envia notificações para a URL configurada pelo cliente.
- É necessário que o cliente tenha configurado previamente o serviço de webhook junto ao Banco do Brasil.
- A autenticação é feita via **certificado mTLS**.
- O conteúdo do corpo da requisição contém todos os dados relevantes da baixa ou liquidação do boleto.
