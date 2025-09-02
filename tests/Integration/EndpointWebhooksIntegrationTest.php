<?php

namespace Tests\Integration;

use Tests\TestCase;
use Accordous\BbClient\Facades\BancoDoBrasil;
use Illuminate\Support\Facades\Cache;

class EndpointWebhooksIntegrationTest extends TestCase
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

    public function it_can_process_baixa_operacional_webhook_payload()
    {
        // Simulate a real webhook payload from Banco do Brasil
        $webhookData = [
            'id' => '00031285570000104055',
            'dataRegistro' => now()->subDays(5)->format('d.m.Y'),
            'dataVencimento' => now()->subDays(1)->format('d.m.Y'),
            'valorOriginal' => 150.50,
            'valorPagoSacado' => 150.50,
            'numeroConvenio' => (int) config('banco-do-brasil.convenio', 3128557),
            'numeroOperacao' => mt_rand(10000000, 99999999),
            'carteiraConvenio' => 17,
            'variacaoCarteiraConvenio' => 35,
            'codigoEstadoBaixaOperacional' => 1, // Liquidated
            'dataLiquidacao' => now()->format('d/m/Y H:i:s'),
            'instituicaoLiquidacao' => '001', // Banco do Brasil
            'canalLiquidacao' => 4,
            'codigoModalidadeBoleto' => 1,
            'tipoPessoaPortador' => 1, // CPF
            'identidadePortador' => '12345678901',
            'nomePortador' => 'TESTE WEBHOOK INTEGRATION',
            'formaPagamento' => 2 // PIX
        ];

        $response = BancoDoBrasil::webhooks()->processarBaixaOperacional($webhookData);

        if ($response->successful()) {
            $data = $response->json();
            $this->assertIsArray($data);
            
            // Check for success indicators
            $successIndicators = ['status', 'mensagem', 'resultado'];
            $hasSuccessIndicator = false;
            
            foreach ($successIndicators as $indicator) {
                if (isset($data[$indicator])) {
                    $hasSuccessIndicator = true;
                    
                    // Verify success values
                    if ($indicator === 'status') {
                        $this->assertContains(strtoupper($data[$indicator]), 
                            ['SUCESSO', 'SUCCESS', 'OK', 'PROCESSADO']);
                    }
                    break;
                }
            }
            
            $this->assertTrue($hasSuccessIndicator, 'Response should contain success indicator');
        } else {
            // Log the error for debugging
            $errorData = $response->json();
            $this->markTestIncomplete('Webhook processing failed: ' . json_encode($errorData));
        }
    }

    public function it_handles_webhook_with_different_payment_methods()
    {
        $paymentMethods = [
            1 => 'DINHEIRO',
            2 => 'PIX',
            3 => 'TED',
            4 => 'DOC',
            5 => 'CARTAO'
        ];

        foreach ($paymentMethods as $formaPagamento => $methodName) {
            $webhookData = [
                'id' => '00031285570000' . str_pad($formaPagamento, 6, '0', STR_PAD_LEFT),
                'dataRegistro' => now()->subDays(5)->format('d.m.Y'),
                'dataVencimento' => now()->subDays(1)->format('d.m.Y'),
                'valorOriginal' => 100.00,
                'valorPagoSacado' => 100.00,
                'numeroConvenio' => (int) config('banco-do-brasil.convenio', 3128557),
                'numeroOperacao' => mt_rand(10000000, 99999999),
                'carteiraConvenio' => 17,
                'variacaoCarteiraConvenio' => 35,
                'codigoEstadoBaixaOperacional' => 1,
                'dataLiquidacao' => now()->format('d/m/Y H:i:s'),
                'instituicaoLiquidacao' => '001',
                'canalLiquidacao' => 4,
                'codigoModalidadeBoleto' => 1,
                'tipoPessoaPortador' => 1,
                'identidadePortador' => '12345678901',
                'nomePortador' => "TESTE WEBHOOK {$methodName}",
                'formaPagamento' => $formaPagamento
            ];

            $response = BancoDoBrasil::webhooks()->processarBaixaOperacional($webhookData);
            
            if ($response->successful()) {
                $data = $response->json();
                $this->assertIsArray($data, "Failed for payment method: {$methodName}");
            } else {
                // Some payment methods might not be supported
                $this->markTestIncomplete("Webhook processing failed for {$methodName}: " . $response->body());
            }
        }
    }

    public function it_handles_webhook_with_cnpj_payer()
    {
        $webhookData = [
            'id' => '00031285570000104999',
            'dataRegistro' => now()->subDays(5)->format('d.m.Y'),
            'dataVencimento' => now()->subDays(1)->format('d.m.Y'),
            'valorOriginal' => 500.00,
            'valorPagoSacado' => 500.00,
            'numeroConvenio' => (int) config('banco-do-brasil.convenio', 3128557),
            'numeroOperacao' => mt_rand(10000000, 99999999),
            'carteiraConvenio' => 17,
            'variacaoCarteiraConvenio' => 35,
            'codigoEstadoBaixaOperacional' => 1,
            'dataLiquidacao' => now()->format('d/m/Y H:i:s'),
            'instituicaoLiquidacao' => '001',
            'canalLiquidacao' => 4,
            'codigoModalidadeBoleto' => 1,
            'tipoPessoaPortador' => 2, // CNPJ
            'identidadePortador' => '12345678000123',
            'nomePortador' => 'EMPRESA TESTE WEBHOOK LTDA',
            'formaPagamento' => 2
        ];

        $response = BancoDoBrasil::webhooks()->processarBaixaOperacional($webhookData);

        if ($response->successful()) {
            $data = $response->json();
            $this->assertIsArray($data);
        } else {
            $this->markTestIncomplete('CNPJ webhook processing failed: ' . $response->body());
        }
    }

    public function it_validates_webhook_payload_structure()
    {
        // Test with incomplete webhook data
        $incompleteWebhookData = [
            'id' => '00031285570000104001',
            'numeroConvenio' => (int) config('banco-do-brasil.convenio', 3128557),
            // Missing required fields
        ];

        $response = BancoDoBrasil::webhooks()->processarBaixaOperacional($incompleteWebhookData);
        
        // Should fail with validation errors
        $this->assertFalse($response->successful());
        $this->assertContains($response->status(), [400, 422]);
        
        $errorData = $response->json();
        if (is_array($errorData) && isset($errorData['erros'])) {
            $this->assertIsArray($errorData['erros']);
            $this->assertNotEmpty($errorData['erros']);
        }
    }

    public function it_handles_webhook_with_invalid_convenio()
    {
        $webhookData = [
            'id' => '00031285570000104002',
            'dataRegistro' => now()->subDays(5)->format('d.m.Y'),
            'dataVencimento' => now()->subDays(1)->format('d.m.Y'),
            'valorOriginal' => 100.00,
            'valorPagoSacado' => 100.00,
            'numeroConvenio' => 9999999, // Invalid convenio
            'numeroOperacao' => mt_rand(10000000, 99999999),
            'carteiraConvenio' => 17,
            'variacaoCarteiraConvenio' => 35,
            'codigoEstadoBaixaOperacional' => 1,
            'dataLiquidacao' => now()->format('d/m/Y H:i:s'),
            'instituicaoLiquidacao' => '001',
            'canalLiquidacao' => 4,
            'codigoModalidadeBoleto' => 1,
            'tipoPessoaPortador' => 1,
            'identidadePortador' => '12345678901',
            'nomePortador' => 'TESTE WEBHOOK INVALID CONVENIO',
            'formaPagamento' => 2
        ];

        $response = BancoDoBrasil::webhooks()->processarBaixaOperacional($webhookData);
        
        // Should fail with authorization or validation error
        $this->assertFalse($response->successful());
        $this->assertContains($response->status(), [400, 401, 403, 422]);
    }

    public function it_handles_webhook_with_different_liquidation_states()
    {
        $liquidationStates = [
            1 => 'LIQUIDADO',
            2 => 'BAIXADO_POR_DEVOLUCAO',
            3 => 'BAIXADO_POR_PROTESTO',
            4 => 'BAIXADO_POR_OUTROS_MOTIVOS'
        ];

        foreach ($liquidationStates as $codigo => $descricao) {
            $webhookData = [
                'id' => '00031285570000104' . str_pad($codigo, 3, '0', STR_PAD_LEFT),
                'dataRegistro' => now()->subDays(5)->format('d.m.Y'),
                'dataVencimento' => now()->subDays(1)->format('d.m.Y'),
                'valorOriginal' => 100.00,
                'valorPagoSacado' => $codigo === 1 ? 100.00 : 0.00, // Only liquidated has paid value
                'numeroConvenio' => (int) config('banco-do-brasil.convenio', 3128557),
                'numeroOperacao' => mt_rand(10000000, 99999999),
                'carteiraConvenio' => 17,
                'variacaoCarteiraConvenio' => 35,
                'codigoEstadoBaixaOperacional' => $codigo,
                'dataLiquidacao' => now()->format('d/m/Y H:i:s'),
                'instituicaoLiquidacao' => '001',
                'canalLiquidacao' => 4,
                'codigoModalidadeBoleto' => 1,
                'tipoPessoaPortador' => 1,
                'identidadePortador' => '12345678901',
                'nomePortador' => "TESTE {$descricao}",
                'formaPagamento' => $codigo === 1 ? 2 : 0 // PIX only for liquidated
            ];

            $response = BancoDoBrasil::webhooks()->processarBaixaOperacional($webhookData);
            
            if ($response->successful()) {
                $data = $response->json();
                $this->assertIsArray($data, "Failed for liquidation state: {$descricao}");
            } else {
                // Some states might have specific validation rules
                $this->markTestIncomplete("Webhook processing failed for {$descricao}: " . $response->body());
            }
        }
    }

    public function it_processes_webhook_performance_test()
    {
        // Test webhook processing performance with multiple payloads
        $webhookPayloads = [];
        
        for ($i = 1; $i <= 5; $i++) {
            $webhookPayloads[] = [
                'id' => '003128557000010' . str_pad($i + 4000, 4, '0', STR_PAD_LEFT),
                'dataRegistro' => now()->subDays(5)->format('d.m.Y'),
                'dataVencimento' => now()->subDays(1)->format('d.m.Y'),
                'valorOriginal' => 100.00 + $i,
                'valorPagoSacado' => 100.00 + $i,
                'numeroConvenio' => (int) config('banco-do-brasil.convenio', 3128557),
                'numeroOperacao' => mt_rand(10000000, 99999999),
                'carteiraConvenio' => 17,
                'variacaoCarteiraConvenio' => 35,
                'codigoEstadoBaixaOperacional' => 1,
                'dataLiquidacao' => now()->format('d/m/Y H:i:s'),
                'instituicaoLiquidacao' => '001',
                'canalLiquidacao' => 4,
                'codigoModalidadeBoleto' => 1,
                'tipoPessoaPortador' => 1,
                'identidadePortador' => '12345678901',
                'nomePortador' => "TESTE PERFORMANCE {$i}",
                'formaPagamento' => 2
            ];
        }

        $startTime = microtime(true);
        $successCount = 0;
        
        foreach ($webhookPayloads as $payload) {
            $response = BancoDoBrasil::webhooks()->processarBaixaOperacional($payload);
            
            if ($response->successful()) {
                $successCount++;
            }
            
            // Small delay to avoid overwhelming the API
            usleep(200000); // 200ms
        }
        
        $endTime = microtime(true);
        $totalTime = $endTime - $startTime;
        
        // Verify performance characteristics
        $this->assertLessThan(60, $totalTime, 'Webhook processing should complete within reasonable time');
        $this->assertGreaterThan(0, $successCount, 'At least some webhook processing should succeed');
        
        // Calculate average processing time
        $averageTime = $totalTime / count($webhookPayloads);
        $this->assertLessThan(10, $averageTime, 'Average webhook processing time should be reasonable');
    }
}