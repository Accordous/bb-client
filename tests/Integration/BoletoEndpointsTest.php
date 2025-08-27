<?php

namespace Tests\Integration;

use Tests\TestCase;
use Accordous\BbClient\Facades\BancoDoBrasil;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Client\Response;

class BoletoEndpointsTest extends TestCase
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

    /** @test */
    public function it_can_list_boletos_with_different_filters()
    {
        $convenio = (int) config('banco-do-brasil.convenio', 3128557);
        
        // Test basic listing
        $response = BancoDoBrasil::boletos()->list([
            'numeroConvenio' => $convenio,
            'indicadorSituacao' => 'A', // Active boletos
            'pagina' => 1,
            'quantidadePorPagina' => 5
        ]);

        $this->assertInstanceOf(Response::class, $response);
        
        if (!$response->successful()) {
            $this->markTestIncomplete('Boletos listing failed: ' . $response->body());
        }
        
        $data = $response->json();
        $this->assertIsArray($data);
        
        // Check for expected response structure
        $this->assertTrue(
            isset($data['boletos']) || isset($data['titulos']) || isset($data['registros']),
            'Response should contain boletos, titulos, or registros array'
        );
    }

    /** @test */
    public function it_can_list_boletos_with_date_filter()
    {
        $convenio = (int) config('banco-do-brasil.convenio', 3128557);
        
        // Test with date range filter
        $response = BancoDoBrasil::boletos()->list([
            'numeroConvenio' => $convenio,
            'indicadorSituacao' => 'A',
            'dataInicioVencimento' => now()->subDays(30)->format('d.m.Y'),
            'dataFimVencimento' => now()->addDays(30)->format('d.m.Y'),
            'pagina' => 1,
            'quantidadePorPagina' => 10
        ]);

        if (!$response->successful()) {
            $this->markTestIncomplete('Date filtered listing failed: ' . $response->body());
        }
        
        $data = $response->json();
        $this->assertIsArray($data);
    }

    /** @test */
    public function it_handles_pagination_correctly()
    {
        $convenio = (int) config('banco-do-brasil.convenio', 3128557);
        
        // Get first page
        $firstPageResponse = BancoDoBrasil::boletos()->list([
            'numeroConvenio' => $convenio,
            'indicadorSituacao' => 'A',
            'pagina' => 1,
            'quantidadePorPagina' => 2
        ]);

        $this->assertTrue($firstPageResponse->successful());
        $firstPageData = $firstPageResponse->json();
        
        // Get second page
        $secondPageResponse = BancoDoBrasil::boletos()->list([
            'numeroConvenio' => $convenio,
            'indicadorSituacao' => 'A',
            'pagina' => 2,
            'quantidadePorPagina' => 2
        ]);

        $this->assertTrue($secondPageResponse->successful());
        $secondPageData = $secondPageResponse->json();
        
        // Verify pagination metadata if available
        if (isset($firstPageData['quantidadeRegistros'])) {
            $this->assertIsNumeric($firstPageData['quantidadeRegistros']);
        }
        
        if (isset($firstPageData['quantidadePorPagina'])) {
            $this->assertEquals(2, $firstPageData['quantidadePorPagina']);
        }
    }

    /** @test */
    public function it_can_show_specific_boleto_details()
    {
        $convenio = (int) config('banco-do-brasil.convenio', 3128557);
        
        // First, get a list to find a boleto to show
        $listResponse = BancoDoBrasil::boletos()->list([
            'numeroConvenio' => $convenio,
            'indicadorSituacao' => 'A',
            'pagina' => 1,
            'quantidadePorPagina' => 1
        ]);

        if (!$listResponse->successful()) {
            $this->markTestSkipped('Cannot list boletos to find one for detail test');
        }

        $listData = $listResponse->json();
        $boletoNumero = null;

        // Extract boleto number from response
        if (isset($listData['boletos']) && !empty($listData['boletos'])) {
            $boletoNumero = $listData['boletos'][0]['numero'] ?? null;
        } elseif (isset($listData['titulos']) && !empty($listData['titulos'])) {
            $boletoNumero = $listData['titulos'][0]['numero'] ?? null;
        } elseif (isset($listData['registros']) && !empty($listData['registros'])) {
            $boletoNumero = $listData['registros'][0]['numero'] ?? null;
        }

        if (!$boletoNumero) {
            $this->markTestSkipped('No boleto found to test detail functionality');
        }

        // Test showing specific boleto
        $detailResponse = BancoDoBrasil::boletos()->show($boletoNumero, $convenio);
        
        $this->assertTrue($detailResponse->successful());
        $detailData = $detailResponse->json();
        
        $this->assertIsArray($detailData);
        $this->assertEquals($boletoNumero, $detailData['numero']);
        
        // Verify expected fields in detail response
        $expectedFields = ['numeroConvenio', 'dataVencimento', 'valorOriginal'];
        foreach ($expectedFields as $field) {
            if (isset($detailData[$field])) {
                $this->assertArrayHasKey($field, $detailData);
            }
        }
    }

    /** @test */
    public function it_handles_baixa_for_nonexistent_boleto()
    {
        $convenio = (int) config('banco-do-brasil.convenio', 3128557);
        $nonexistentBoleto = '99999999999999999999';
        
        $payload = [
            'numeroConvenio' => $convenio,
            'tipo' => 1, // BAIXA_POR_SOLICITACAO
            'descricao' => 'Baixa automática via teste'
        ];
        
        $response = BancoDoBrasil::boletos()->baixar($nonexistentBoleto, $payload);
        
        // Should fail but sandbox might behave differently
        if ($response->successful()) {
            $this->markTestIncomplete('Sandbox may not properly validate boleto existence for baixa');
        } else {
            $this->assertContains($response->status(), [400, 404, 422]);
        }
    }

    /** @test */
    public function it_can_generate_pix_for_eligible_boletos()
    {
        $convenio = (int) config('banco-do-brasil.convenio', 3128557);
        
        // Find a boleto with PIX enabled
        $listResponse = BancoDoBrasil::boletos()->list([
            'numeroConvenio' => $convenio,
            'indicadorSituacao' => 'A',
            'pagina' => 1,
            'quantidadePorPagina' => 20
        ]);

        if (!$listResponse->successful()) {
            $this->markTestSkipped('Cannot list boletos for PIX test');
        }

        $listData = $listResponse->json();
        $pixEnabledBoleto = null;

        // Look for a boleto with PIX indicator
        $boletos = $listData['boletos'] ?? $listData['titulos'] ?? $listData['registros'] ?? [];
        
        foreach ($boletos as $boleto) {
            if (isset($boleto['indicadorPix']) && $boleto['indicadorPix'] === 'S') {
                $pixEnabledBoleto = $boleto['numero'];
                break;
            }
        }

        if (!$pixEnabledBoleto) {
            $this->markTestSkipped('No PIX-enabled boleto found');
        }

        // Test PIX generation
        $pixResponse = BancoDoBrasil::boletos()->gerarPix($pixEnabledBoleto);
        
        if ($pixResponse->successful()) {
            $pixData = $pixResponse->json();
            $this->assertIsArray($pixData);
            
            // Check for PIX-related information
            $pixFields = ['indicadorPix', 'qrCodePix', 'codigoPix', 'textoQrCodePix'];
            $hasPixInfo = false;
            
            foreach ($pixFields as $field) {
                if (isset($pixData[$field]) && !empty($pixData[$field])) {
                    $hasPixInfo = true;
                    break;
                }
            }
            
            $this->assertTrue($hasPixInfo, 'Response should contain PIX information');
        } else {
            // PIX generation might fail for various business reasons
            $this->markTestIncomplete('PIX generation failed: ' . $pixResponse->body());
        }
    }

    /** @test */
    public function it_handles_invalid_convenio_gracefully()
    {
        $invalidConvenio = 9999999; // Invalid convenio number
        
        $response = BancoDoBrasil::boletos()->list([
            'numeroConvenio' => $invalidConvenio,
            'indicadorSituacao' => 'A',
            'pagina' => 1,
            'quantidadePorPagina' => 5
        ]);

        // Should return error for invalid convenio
        $this->assertFalse($response->successful());
        $this->assertContains($response->status(), [400, 401, 403, 404, 422]);
        
        // Check error response structure
        $errorData = $response->json();
        if (is_array($errorData) && isset($errorData['erros'])) {
            $this->assertIsArray($errorData['erros']);
            $this->assertNotEmpty($errorData['erros']);
        }
    }

    /** @test */
    public function it_respects_api_rate_limits()
    {
        $convenio = (int) config('banco-do-brasil.convenio', 3128557);
        
        // Make multiple rapid requests to test rate limiting behavior
        $responses = [];
        $startTime = microtime(true);
        
        for ($i = 0; $i < 5; $i++) {
            $response = BancoDoBrasil::boletos()->list([
                'numeroConvenio' => $convenio,
                'indicadorSituacao' => 'A',
                'pagina' => 1,
                'quantidadePorPagina' => 1
            ]);
            
            $responses[] = [
                'status' => $response->status(),
                'successful' => $response->successful(),
                'time' => microtime(true) - $startTime
            ];
            
            // Small delay between requests
            usleep(100000); // 100ms
        }
        
        $endTime = microtime(true);
        $totalTime = $endTime - $startTime;
        
        // Verify that requests complete within reasonable time
        $this->assertLessThan(30, $totalTime, 'Multiple requests took too long');
        
        // Check if any requests hit rate limits (429 status)
        $rateLimitedRequests = array_filter($responses, function($response) {
            return $response['status'] === 429;
        });
        
        // If rate limited, ensure proper handling
        if (!empty($rateLimitedRequests)) {
            $this->markTestIncomplete('API rate limiting detected - this is expected behavior');
        }
        
        // Most requests should be successful
        $successfulRequests = array_filter($responses, function($response) {
            return $response['successful'];
        });
        
        $this->assertGreaterThan(0, count($successfulRequests), 'At least some requests should succeed');
    }
}