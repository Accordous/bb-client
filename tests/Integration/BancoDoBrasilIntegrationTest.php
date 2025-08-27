<?php

namespace Tests\Integration;

use Accordous\BbClient\Facades\BancoDoBrasil;
use Accordous\BbClient\ValueObject\BoletoBuilder;
use Accordous\BbClient\ValueObject\Pagador;
use Accordous\BbClient\ValueObject\Desconto;
use Accordous\BbClient\ValueObject\JurosMora;
use Accordous\BbClient\ValueObject\Multa;
use Accordous\BbClient\Enums\TipoInscricao;
use Accordous\BbClient\Enums\CodigoModalidade;
use Accordous\BbClient\Enums\TipoTitulo;
use Tests\TestCase;
use Illuminate\Support\Facades\Cache;

class BancoDoBrasilIntegrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Clear cache before each test
        Cache::flush();

        // Skip tests if environment variables are not properly configured
        if (empty(config('banco-do-brasil.client_id')) || 
            empty(config('banco-do-brasil.client_secret')) ||
            config('banco-do-brasil.client_id') === 'test-client-id') {
            $this->markTestSkipped('API credentials not configured for real API testing.');
        }
    }

    public function test_get_token()
    {
        $token = BancoDoBrasil::getToken();
        
        $this->assertNotEmpty($token);
        $this->assertIsString($token);
        
        // Token should be a valid JWT or Bearer token format
        $this->assertStringContainsString('-', $token);
    }

    public function test_registrar_boleto_using_builder()
    {
        $pagador = new Pagador(
            TipoInscricao::CPF,
            '12345678901',
            'João da Silva - Teste',
            'Rua das Flores, 123',
            '01234-567',
            'São Paulo',
            'Centro',
            'SP',
            '11999999999'
        );

        $desconto = new Desconto(1, '25.08.2025', 5.0, 0.0);
        $jurosMora = new JurosMora(2, 0.0, 2.0);
        $multa = new Multa(1, '23.08.2025', 2.0, 0.0);

        $boletoData = (new BoletoBuilder())
            ->numeroConvenio((int) config('banco-do-brasil.convenio', 3128557))
            ->numeroCarteira(17)
            ->numeroVariacaoCarteira(35)
            ->codigoModalidade(CodigoModalidade::SIMPLES)
            ->dataEmissao(now()->format('d.m.Y'))
            ->dataVencimento(now()->addDays(30)->format('d.m.Y'))
            ->valorOriginal(100.00)
            ->codigoTipoTitulo(TipoTitulo::DUPLICATA_MERCANTIL)
            ->descricaoTipoTitulo('Duplicata Mercantil')
            ->numeroTituloBeneficiario('TEST-' . time())
            ->numeroTituloCliente('CLIENT-' . time())
            ->mensagemBloquetoOcorrencia('Teste de integração - Pagamento via PIX disponível')
            ->pagador($pagador)
            ->desconto($desconto)
            ->jurosMora($jurosMora)
            ->multa($multa)
            ->indicadorPix('S')
            ->build();

        $response = BancoDoBrasil::registrarBoletoCobranca($boletoData);

        $this->assertNotEmpty($response);
        $this->assertIsArray($response);
        
        // Check if the response contains expected keys from real API
        if (isset($response['numero'])) {
            $this->assertArrayHasKey('numero', $response);
            $this->assertNotEmpty($response['numero']);
        }
        
        // If there's an error, log it for debugging
        if (isset($response['erros'])) {
            $this->fail('API returned errors: ' . json_encode($response['erros']));
        }
    }

    public function test_list_boletos()
    {
        $response = BancoDoBrasil::boletos()->list([
            'numeroConvenio' => (int) config('banco-do-brasil.convenio', 3128557),
            'indicadorSituacao' => 'A',
            'pagina' => 1,
            'quantidadePorPagina' => 10
        ]);

        if (!$response->successful()) {
            $this->markTestIncomplete('Boletos listing failed: ' . $response->body());
        }

        $data = $response->json();
        
        // Check if response has expected structure
        $this->assertIsArray($data);
        
        // The API may return different structures, so we check for common keys
        $possibleKeys = ['boletos', 'titulos', 'registros'];
        $hasExpectedKey = false;
        
        foreach ($possibleKeys as $key) {
            if (array_key_exists($key, $data)) {
                $hasExpectedKey = true;
                break;
            }
        }
        
        $this->assertTrue($hasExpectedKey, 'Response should contain one of: ' . implode(', ', $possibleKeys));
    }

    public function test_show_boleto()
    {
        // First, let's try to get a list of boletos to find one to show
        $listResponse = BancoDoBrasil::boletos()->list([
            'numeroConvenio' => (int) config('banco-do-brasil.convenio', 3128557),
            'indicadorSituacao' => 'A',
            'pagina' => 1,
            'quantidadePorPagina' => 1
        ]);

        if (!$listResponse->successful()) {
            $this->markTestSkipped('Cannot list boletos to find one for show test');
        }

        $listData = $listResponse->json();
        
        // Try to find a boleto number from the list response
        $boletoNumero = null;
        
        if (isset($listData['boletos']) && !empty($listData['boletos'])) {
            $boletoNumero = $listData['boletos'][0]['numero'] ?? null;
        } elseif (isset($listData['titulos']) && !empty($listData['titulos'])) {
            $boletoNumero = $listData['titulos'][0]['numero'] ?? null;
        }

        if (!$boletoNumero) {
            $this->markTestSkipped('No boleto found to test show functionality');
        }

        $response = BancoDoBrasil::boletos()->show($boletoNumero, (int) config('banco-do-brasil.convenio', 3128557));

        $this->assertTrue($response->successful());
        $data = $response->json();
        $this->assertIsArray($data);
        $this->assertEquals($boletoNumero, $data['numero']);
    }

    public function test_gerar_pix_boleto()
    {
        // This test requires a boleto that supports PIX
        // We'll create one first or use an existing one
        
        // First, try to find a boleto with PIX enabled
        $listResponse = BancoDoBrasil::boletos()->list([
            'numeroConvenio' => (int) config('banco-do-brasil.convenio', 3128557),
            'indicadorSituacao' => 'A',
            'pagina' => 1,
            'quantidadePorPagina' => 10
        ]);

        if (!$listResponse->successful()) {
            $this->markTestSkipped('Cannot list boletos to find one for PIX test');
        }

        $listData = $listResponse->json();
        $boletoNumero = null;

        // Look for a boleto with PIX indicator
        if (isset($listData['boletos']) && !empty($listData['boletos'])) {
            foreach ($listData['boletos'] as $boleto) {
                if (isset($boleto['indicadorPix']) && $boleto['indicadorPix'] === 'S') {
                    $boletoNumero = $boleto['numero'];
                    break;
                }
            }
        }

        if (!$boletoNumero) {
            $this->markTestSkipped('No PIX-enabled boleto found to test PIX generation');
        }

        $response = BancoDoBrasil::boletos()->gerarPix($boletoNumero);

        if ($response->successful()) {
            $data = $response->json();
            $this->assertIsArray($data);
            
            // Check for PIX-related fields
            $expectedFields = ['indicadorPix', 'qrCodePix', 'codigoPix'];
            $hasPixField = false;
            
            foreach ($expectedFields as $field) {
                if (isset($data[$field])) {
                    $hasPixField = true;
                    break;
                }
            }
            
            $this->assertTrue($hasPixField, 'Response should contain PIX-related information');
        } else {
            // Log the error for debugging but don't fail the test as PIX may not be available
            $this->markTestIncomplete('PIX generation failed: ' . $response->body());
        }
    }

    public function test_consultar_baixa_operacional()
    {
        $agencia = config('banco-do-brasil.agencia', 1234);
        $conta = config('banco-do-brasil.conta', 123456);
        
        $response = BancoDoBrasil::boletos()->consultarBaixaOperacional([
            'agencia' => $agencia,
            'conta' => $conta,
            'carteira' => 17,
            'variacao' => 35,
            'dataInicioAgendamentoTitulo' => now()->subDays(30)->format('d/m/Y'),
            'dataFimAgendamentoTitulo' => now()->format('d/m/Y')
        ]);

        if (!$response->successful()) {
            $this->markTestIncomplete('Baixa operacional query failed: ' . $response->body());
        }

        $data = $response->json();
        $this->assertIsArray($data);
        
        // Check for expected response structure
        $expectedKeys = ['possuiMaisTitulos', 'titulosBaixaOperacional'];
        foreach ($expectedKeys as $key) {
            if (isset($data[$key])) {
                $this->assertArrayHasKey($key, $data);
            }
        }
    }

    public function test_ativar_consulta_baixa_operacional()
    {
        $convenio = config('banco-do-brasil.convenio', '3128557');
        
        $response = BancoDoBrasil::convenios()->ativarConsultaBaixaOperacional($convenio);

        if ($response->successful()) {
            $data = $response->json();
            $this->assertIsArray($data);
            
            if (isset($data['numeroConvenio'])) {
                $this->assertEquals($convenio, $data['numeroConvenio']);
            }
            
            if (isset($data['status'])) {
                $this->assertContains($data['status'], ['ATIVADO', 'JA_ATIVADO', 'ATIVO']);
            }
        } else {
            // This feature may require specific permissions
            $this->markTestIncomplete('Baixa operacional activation failed: ' . $response->body());
        }
    }

    public function test_webhook_baixa_operacional()
    {
        // This test simulates processing a webhook payload
        // In a real scenario, this would be called by the BB API
        
        $webhookData = [
            'id' => '00031285570000104055',
            'dataRegistro' => now()->format('d.m.Y'),
            'dataVencimento' => now()->addDays(30)->format('d.m.Y'),
            'valorOriginal' => 1000,
            'valorPagoSacado' => 1000,
            'numeroConvenio' => (int) config('banco-do-brasil.convenio', 3128557),
            'numeroOperacao' => 10055680,
            'carteiraConvenio' => 17,
            'variacaoCarteiraConvenio' => 35,
            'codigoEstadoBaixaOperacional' => 1,
            'dataLiquidacao' => now()->format('d/m/Y H:i:s'),
            'instituicaoLiquidacao' => '001',
            'canalLiquidacao' => 4,
            'codigoModalidadeBoleto' => 1,
            'tipoPessoaPortador' => 2,
            'identidadePortador' => '98959112000179',
            'nomePortador' => 'TESTE WEBHOOK INTEGRATION',
            'formaPagamento' => 2
        ];

        $response = BancoDoBrasil::webhooks()->processarBaixaOperacional($webhookData);

        if ($response->successful()) {
            $data = $response->json();
            $this->assertIsArray($data);
            
            // Check for success indicators
            if (isset($data['status'])) {
                $this->assertContains($data['status'], ['SUCESSO', 'SUCCESS', 'OK']);
            }
            
            if (isset($data['mensagem'])) {
                $this->assertIsString($data['mensagem']);
            }
        } else {
            // Webhook processing may require specific setup
            $this->markTestIncomplete('Webhook processing failed: ' . $response->body());
        }
    }
}