<?php

namespace App\Http\Controllers;

use Accordous\BbClient\Facades\BancoDoBrasil;
use Accordous\BbClient\ValueObject\BoletoBuilder;
use Accordous\BbClient\ValueObject\Pagador;
use Accordous\BbClient\ValueObject\Desconto;
use Accordous\BbClient\ValueObject\JurosMora;
use Accordous\BbClient\ValueObject\Multa;
use Accordous\BbClient\Enums\TipoInscricao;
use Accordous\BbClient\Enums\CodigoModalidade;
use Accordous\BbClient\Enums\TipoTitulo;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class BoletoController extends Controller
{
    /**
     * Lista boletos com filtros
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only([
                'numeroConvenio',
                'agenciaBeneficiario',
                'contaBeneficiario',
                'indicadorSituacao',
                'codigoEstadoTituloCobranca',
                'dataInicioVencimento',
                'dataFimVencimento',
                'dataInicioRegistro',
                'dataFimRegistro',
                'cpfCnpjPagador',
                'pagina',
                'quantidadePorPagina'
            ]);

            $response = BancoDoBrasil::boletos()->list($filters);

            if ($response->successful()) {
                return response()->json($response->json());
            }

            return response()->json([
                'error' => 'Erro ao consultar boletos',
                'details' => $response->json()
            ], $response->status());

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro interno',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cria um novo boleto
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'numeroConvenio' => 'required|integer',
                'numeroCarteira' => 'required|integer',
                'numeroVariacaoCarteira' => 'required|integer',
                'dataVencimento' => 'required|date_format:d.m.Y',
                'valorOriginal' => 'required|numeric|min:0.01',
                'pagador' => 'required|array',
                'pagador.tipoInscricao' => 'required|integer|in:1,2',
                'pagador.numeroInscricao' => 'required|string',
                'pagador.nome' => 'required|string|max:100',
            ]);

            $data = $request->all();

            // Criar pagador
            $pagador = new Pagador(
                $data['pagador']['tipoInscricao'],
                $data['pagador']['numeroInscricao'],
                $data['pagador']['nome'],
                $data['pagador']['endereco'] ?? '',
                $data['pagador']['cep'] ?? '',
                $data['pagador']['cidade'] ?? '',
                $data['pagador']['bairro'] ?? '',
                $data['pagador']['uf'] ?? '',
                $data['pagador']['telefone'] ?? ''
            );

            // Usar o builder para criar o boleto
            $builder = (new BoletoBuilder())
                ->numeroConvenio($data['numeroConvenio'])
                ->numeroCarteira($data['numeroCarteira'])
                ->numeroVariacaoCarteira($data['numeroVariacaoCarteira'])
                ->codigoModalidade($data['codigoModalidade'] ?? CodigoModalidade::SIMPLES)
                ->dataEmissao(now()->format('d.m.Y'))
                ->dataVencimento($data['dataVencimento'])
                ->valorOriginal($data['valorOriginal'])
                ->codigoTipoTitulo($data['codigoTipoTitulo'] ?? TipoTitulo::DUPLICATA_MERCANTIL)
                ->pagador($pagador);

            // Adicionar campos opcionais
            if (isset($data['valorAbatimento'])) {
                $builder->valorAbatimento($data['valorAbatimento']);
            }

            if (isset($data['numeroTituloBeneficiario'])) {
                $builder->numeroTituloBeneficiario($data['numeroTituloBeneficiario']);
            }

            if (isset($data['mensagemBloquetoOcorrencia'])) {
                $builder->mensagemBloquetoOcorrencia($data['mensagemBloquetoOcorrencia']);
            }

            // Adicionar desconto se fornecido
            if (isset($data['desconto'])) {
                $desconto = new Desconto(
                    $data['desconto']['tipo'],
                    $data['desconto']['dataExpiracao'] ?? '',
                    $data['desconto']['porcentagem'] ?? 0.0,
                    $data['desconto']['valor'] ?? 0.0
                );
                $builder->desconto($desconto);
            }

            // Adicionar juros se fornecido
            if (isset($data['jurosMora'])) {
                $jurosMora = new JurosMora(
                    $data['jurosMora']['tipo'],
                    $data['jurosMora']['porcentagem'] ?? 0.0,
                    $data['jurosMora']['valor'] ?? 0.0
                );
                $builder->jurosMora($jurosMora);
            }

            // Adicionar multa se fornecida
            if (isset($data['multa'])) {
                $multa = new Multa(
                    $data['multa']['tipo'],
                    $data['multa']['data'] ?? '',
                    $data['multa']['porcentagem'] ?? 0.0,
                    $data['multa']['valor'] ?? 0.0
                );
                $builder->multa($multa);
            }

            $boletoData = $builder->build();

            $response = BancoDoBrasil::boletos()->create($boletoData);

            return response()->json([
                'success' => true,
                'data' => $response->json()
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro ao criar boleto',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Consulta um boleto específico
     */
    public function show(Request $request, string $id): JsonResponse
    {
        try {
            $numeroConvenio = $request->query('numeroConvenio');
            
            if (!$numeroConvenio) {
                return response()->json([
                    'error' => 'Número do convênio é obrigatório'
                ], 400);
            }

            $response = BancoDoBrasil::boletos()->show($id, $numeroConvenio);

            if ($response->successful()) {
                return response()->json($response->json());
            }

            return response()->json([
                'error' => 'Boleto não encontrado',
                'details' => $response->json()
            ], $response->status());

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro interno',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Atualiza um boleto
     */
    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $data = $request->all();

            $response = BancoDoBrasil::boletos()->update($id, $data);

            if ($response->successful()) {
                return response()->json($response->json());
            }

            return response()->json([
                'error' => 'Erro ao atualizar boleto',
                'details' => $response->json()
            ], $response->status());

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro interno',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Gera PIX para o boleto
     */
    public function gerarPix(string $id): JsonResponse
    {
        try {
            $response = BancoDoBrasil::boletos()->gerarPix($id);

            if ($response->successful()) {
                return response()->json($response->json());
            }

            return response()->json([
                'error' => 'Erro ao gerar PIX',
                'details' => $response->json()
            ], $response->status());

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro interno',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancela PIX do boleto
     */
    public function cancelarPix(string $id): JsonResponse
    {
        try {
            $response = BancoDoBrasil::boletos()->cancelarPix($id);

            if ($response->successful()) {
                return response()->json($response->json());
            }

            return response()->json([
                'error' => 'Erro ao cancelar PIX',
                'details' => $response->json()
            ], $response->status());

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro interno',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Consulta PIX do boleto
     */
    public function consultarPix(string $id): JsonResponse
    {
        try {
            $response = BancoDoBrasil::boletos()->consultarPix($id);

            if ($response->successful()) {
                return response()->json($response->json());
            }

            return response()->json([
                'error' => 'PIX não encontrado',
                'details' => $response->json()
            ], $response->status());

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro interno',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Realiza baixa do boleto
     */
    public function baixar(Request $request, string $id): JsonResponse
    {
        try {
            $data = $request->all();

            $response = BancoDoBrasil::boletos()->baixar($id, $data);

            if ($response->successful()) {
                return response()->json($response->json());
            }

            return response()->json([
                'error' => 'Erro ao baixar boleto',
                'details' => $response->json()
            ], $response->status());

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro interno',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Webhook para receber notificações de baixa operacional
     */
    public function webhookBaixaOperacional(Request $request): JsonResponse
    {
        try {
            $data = $request->all();

            // Aqui você pode implementar sua lógica de negócio
            // Por exemplo, atualizar o status do boleto no banco de dados
            
            // Log da notificação recebida
            Log::info('Webhook Baixa Operacional recebido', $data);

            // Processar a notificação
            $response = BancoDoBrasil::webhooks()->processarBaixaOperacional($data);

            return response()->json([
                'status' => 'SUCESSO',
                'mensagem' => 'Notificação processada com sucesso'
            ]);

        } catch (\Exception $e) {
            Log::error('Erro no webhook de baixa operacional', [
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);

            return response()->json([
                'error' => 'Erro ao processar webhook',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}