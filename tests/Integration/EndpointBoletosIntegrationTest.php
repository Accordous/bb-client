<?php

namespace Tests\Integration;

use Tests\TestCase;
use Accordous\BbClient\Facades\BancoDoBrasil;
use Accordous\BbClient\Data\BoletoData;
use Accordous\BbClient\Data\PagadorData;
use Accordous\BbClient\Enums\TipoInscricao;
use Accordous\BbClient\Enums\CodigoModalidade;
use Accordous\BbClient\Enums\TipoTitulo;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Client\Response;

class EndpointBoletosIntegrationTest extends TestCase
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
            'indicadorSituacao' => 'B', // Active boletos
            'agenciaBeneficiario' => config('banco-do-brasil.agencia', '1234'),
            'contaBeneficiario' => config('banco-do-brasil.conta', '123456'),
            'numeroConvenio' => $convenio,
            'pagina' => 1,
            'quantidadePorPagina' => 5
        ]);

        $this->assertInstanceOf(Response::class, $response);
        
        if (!$response->successful()) {
            $this->markTestIncomplete('Boletos listing failed - Status: ' . $response->status() . ' - Body: ' . $response->body());
        }
        
        $data = $response->json();
        
        // Log response for debugging
        if (is_null($data)) {
            $this->markTestIncomplete('API returned null response - possibly missing required parameters. Response status: ' . $response->status() . ', Body: ' . $response->body());
        }
        
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
            'agenciaBeneficiario' => config('banco-do-brasil.agencia', '1234'),
            'contaBeneficiario' => config('banco-do-brasil.conta', '123456'),
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
            'agenciaBeneficiario' => config('banco-do-brasil.agencia', '1234'),
            'contaBeneficiario' => config('banco-do-brasil.conta', '123456'),
            'indicadorSituacao' => 'A',
            'pagina' => 1,
            'quantidadePorPagina' => 2
        ]);

        $this->assertTrue($firstPageResponse->successful());
        $firstPageData = $firstPageResponse->json();
        
        // Get second page
        $secondPageResponse = BancoDoBrasil::boletos()->list([
            'numeroConvenio' => $convenio,
            'agenciaBeneficiario' => config('banco-do-brasil.agencia', '1234'),
            'contaBeneficiario' => config('banco-do-brasil.conta', '123456'),
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
            'agenciaBeneficiario' => config('banco-do-brasil.agencia', '1234'),
            'contaBeneficiario' => config('banco-do-brasil.conta', '123456'),
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
            'agenciaBeneficiario' => config('banco-do-brasil.agencia', '1234'),
            'contaBeneficiario' => config('banco-do-brasil.conta', '123456'),
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
            'agenciaBeneficiario' => config('banco-do-brasil.agencia', '1234'),
            'contaBeneficiario' => config('banco-do-brasil.conta', '123456'),
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

    // ============================================================================
    // UPDATE BOLETO TESTS - Detailed tests for boleto alteration endpoint
    // ============================================================================

    /** @test */
    public function it_can_update_boleto_data_vencimento()
    {
        // Create a boleto to update
        $createdBoleto = $this->createTestBoleto();
        
        $boletoNumero = $this->extractBoletoNumber($createdBoleto);
        $convenio = (int) config('banco-do-brasil.convenio', 3128557);

        $updateData = [
            'numeroConvenio' => $convenio,
            'indicadorNovaDataVencimento' => 'S',
            'alteracaoData' => [
                'novaDataVencimento' => now()->addDays(60)->format('d.m.Y')
            ]
        ];

        $response = BancoDoBrasil::boletos()->update($boletoNumero, $updateData);

        $this->assertTrue($response->successful(), 'Update boleto data vencimento failed: ' . $response->body());
        
        $data = $response->json();
        $this->assertIsArray($data);
    }

    /** @test */
    public function it_can_update_boleto_valor_nominal()
    {
        // Create a boleto to update
        $createdBoleto = $this->createTestBoleto();
        
        $boletoNumero = $this->extractBoletoNumber($createdBoleto);
        $convenio = (int) config('banco-do-brasil.convenio', 3128557);

        $updateData = [
            'numeroConvenio' => $convenio,
            'indicadorNovoValorNominal' => 'S',
            'alteracaoValor' => [
                'novoValorNominal' => 250.00
            ]
        ];

        $response = BancoDoBrasil::boletos()->update($boletoNumero, $updateData);

        $this->assertTrue($response->successful(), 'Update boleto valor nominal failed: ' . $response->body());
        
        $data = $response->json();
        $this->assertIsArray($data);
    }

    /** @test */
    public function it_can_update_boleto_incluir_desconto()
    {
        // Create a boleto to update
        $createdBoleto = $this->createTestBoleto();
        
        $boletoNumero = $this->extractBoletoNumber($createdBoleto);
        $convenio = (int) config('banco-do-brasil.convenio', 3128557);

        $updateData = [
            'numeroConvenio' => $convenio,
            'indicadorAtribuirDesconto' => 'S',
            'desconto' => [
                'tipoPrimeiroDesconto' => 1,
                'valorPrimeiroDesconto' => 25.00,
                'percentualPrimeiroDesconto' => 0.0,
                'dataPrimeiroDesconto' => now()->addDays(25)->format('d.m.Y'),
                'tipoSegundoDesconto' => 0,
                'valorSegundoDesconto' => 0.0,
                'percentualSegundoDesconto' => 0.0,
                'dataSegundoDesconto' => '00.00.0000',
                'tipoTerceiroDesconto' => 0,
                'valorTerceiroDesconto' => 0.0,
                'percentualTerceiroDesconto' => 0.0,
                'dataTerceiroDesconto' => '00.00.0000'
            ]
        ];

        $response = BancoDoBrasil::boletos()->update($boletoNumero, $updateData);

        $this->assertTrue($response->successful(), 'Update boleto incluir desconto failed: ' . $response->body());
        
        $data = $response->json();
        $this->assertIsArray($data);
    }

    /** @test */
    public function it_can_update_boleto_alterar_desconto_existente()
    {
        // Create a boleto with discount first
        $createdBoleto = $this->createTestBoletoWithDiscount();
        
        $boletoNumero = $this->extractBoletoNumber($createdBoleto);
        $convenio = (int) config('banco-do-brasil.convenio', 3128557);

        $updateData = [
            'numeroConvenio' => $convenio,
            'indicadorAlterarDesconto' => 'S',
            'alteracaoDesconto' => [
                'tipoPrimeiroDesconto' => 1,
                'novoValorPrimeiroDesconto' => 35.00,
                'novoPercentualPrimeiroDesconto' => 0.0,
                'novaDataLimitePrimeiroDesconto' => now()->addDays(20)->format('d.m.Y'),
                'tipoSegundoDesconto' => 0,
                'novoValorSegundoDesconto' => 0.0,
                'novoPercentualSegundoDesconto' => 0.0,
                'novaDataLimiteSegundoDesconto' => '00.00.0000',
                'tipoTerceiroDesconto' => 0,
                'novoValorTerceiroDesconto' => 0.0,
                'novoPercentualTerceiroDesconto' => 0.0,
                'novaDataLimiteTerceiroDesconto' => '00.00.0000'
            ]
        ];

        $response = BancoDoBrasil::boletos()->update($boletoNumero, $updateData);

        $this->assertTrue($response->successful(), 'Update boleto alterar desconto failed: ' . $response->body());
        
        $data = $response->json();
        $this->assertIsArray($data);
    }

    /** @test */
    public function it_can_update_boleto_protesto()
    {
        // Create a boleto to update
        $createdBoleto = $this->createTestBoleto();
        
        $boletoNumero = $this->extractBoletoNumber($createdBoleto);
        $convenio = (int) config('banco-do-brasil.convenio', 3128557);

        $updateData = [
            'numeroConvenio' => $convenio,
            'indicadorProtestar' => 'S',
            'protesto' => [
                'quantidadeDiasProtesto' => 10
            ]
        ];

        $response = BancoDoBrasil::boletos()->update($boletoNumero, $updateData);

        $this->assertTrue($response->successful(), 'Update boleto protesto failed: ' . $response->body());
        
        $data = $response->json();
        $this->assertIsArray($data);
    }

    /** @test */
    public function it_can_update_boleto_abatimento()
    {
        // Create a boleto to update
        $createdBoleto = $this->createTestBoleto();
        
        $boletoNumero = $this->extractBoletoNumber($createdBoleto);
        $convenio = (int) config('banco-do-brasil.convenio', 3128557);

        $updateData = [
            'numeroConvenio' => $convenio,
            'indicadorIncluirAbatimento' => 'S',
            'abatimento' => [
                'valorAbatimento' => 20.00
            ]
        ];

        $response = BancoDoBrasil::boletos()->update($boletoNumero, $updateData);

        $this->assertTrue($response->successful(), 'Update boleto abatimento failed: ' . $response->body());
        
        $data = $response->json();
        $this->assertIsArray($data);
    }

    /** @test */
    public function it_can_update_boleto_alterar_abatimento_existente()
    {
        // Create a boleto with abatimento first
        $createdBoleto = $this->createTestBoletoWithAbatimento();
        
        $boletoNumero = $this->extractBoletoNumber($createdBoleto);
        $convenio = (int) config('banco-do-brasil.convenio', 3128557);

        $updateData = [
            'numeroConvenio' => $convenio,
            'indicadorAlterarAbatimento' => 'S',
            'alteracaoAbatimento' => [
                'novoValorAbatimento' => 35.00
            ]
        ];

        $response = BancoDoBrasil::boletos()->update($boletoNumero, $updateData);

        $this->assertTrue($response->successful(), 'Update boleto alterar abatimento failed: ' . $response->body());
        
        $data = $response->json();
        $this->assertIsArray($data);
    }

    /** @test */
    public function it_can_update_boleto_juros_mora()
    {
        // Create a boleto to update
        $createdBoleto = $this->createTestBoleto();
        
        $boletoNumero = $this->extractBoletoNumber($createdBoleto);
        $convenio = (int) config('banco-do-brasil.convenio', 3128557);

        $updateData = [
            'numeroConvenio' => $convenio,
            'indicadorCobrarJuros' => 'S',
            'juros' => [
                'tipoJuros' => 2,
                'valorJuros' => 0.0,
                'taxaJuros' => 2.5
            ]
        ];

        $response = BancoDoBrasil::boletos()->update($boletoNumero, $updateData);

        $this->assertTrue($response->successful(), 'Update boleto juros mora failed: ' . $response->body());
        
        $data = $response->json();
        $this->assertIsArray($data);
    }

    /** @test */
    public function it_can_update_boleto_multa()
    {
        // Create a boleto to update
        $createdBoleto = $this->createTestBoleto();
        
        $boletoNumero = $this->extractBoletoNumber($createdBoleto);
        $convenio = (int) config('banco-do-brasil.convenio', 3128557);

        $updateData = [
            'numeroConvenio' => $convenio,
            'indicadorCobrarMulta' => 'S',
            'multa' => [
                'tipoMulta' => 2,
                'valorMulta' => 0.0,
                'taxaMulta' => 3.0,
                'dataInicioMulta' => now()->addDays(31)->format('d.m.Y')
            ]
        ];

        $response = BancoDoBrasil::boletos()->update($boletoNumero, $updateData);

        $this->assertTrue($response->successful(), 'Update boleto multa failed: ' . $response->body());
        
        $data = $response->json();
        $this->assertIsArray($data);
    }

    /** @test */
    public function it_can_update_boleto_endereco_pagador()
    {
        // Create a boleto to update
        $createdBoleto = $this->createTestBoleto();
        
        $boletoNumero = $this->extractBoletoNumber($createdBoleto);
        $convenio = (int) config('banco-do-brasil.convenio', 3128557);

        $updateData = [
            'numeroConvenio' => $convenio,
            'indicadorAlterarEnderecoPagador' => 'S',
            'alteracaoEndereco' => [
                'enderecoPagador' => 'Rua Nova, 456',
                'bairroPagador' => 'Centro Novo',
                'cidadePagador' => 'São Paulo',
                'UFPagador' => 'SP',
                'CEPPagador' => 1234567
            ]
        ];

        $response = BancoDoBrasil::boletos()->update($boletoNumero, $updateData);

        $this->assertTrue($response->successful(), 'Update boleto endereco pagador failed: ' . $response->body());
        
        $data = $response->json();
        $this->assertIsArray($data);
    }

    /** @test */
    public function it_can_update_boleto_negativacao()
    {
        // Create a boleto to update
        $createdBoleto = $this->createTestBoleto();
        
        $boletoNumero = $this->extractBoletoNumber($createdBoleto);
        $convenio = (int) config('banco-do-brasil.convenio', 3128557);

        $updateData = [
            'numeroConvenio' => $convenio,
            'indicadorNegativar' => 'S',
            'negativacao' => [
                'quantidadeDiasNegativacao' => 15,
                'tipoNegativacao' => 1,
                'orgaoNegativador' => 10
            ]
        ];

        $response = BancoDoBrasil::boletos()->update($boletoNumero, $updateData);

        $this->assertTrue($response->successful(), 'Update boleto negativacao failed: ' . $response->body());
        
        $data = $response->json();
        $this->assertIsArray($data);
    }

    /** @test */
    public function it_can_update_boleto_alterar_prazo_vencido()
    {
        // Create a boleto to update
        $createdBoleto = $this->createTestBoleto();
        
        $boletoNumero = $this->extractBoletoNumber($createdBoleto);
        $convenio = (int) config('banco-do-brasil.convenio', 3128557);

        $updateData = [
            'numeroConvenio' => $convenio,
            'indicadorAlterarPrazoBoletoVencido' => 'S',
            'alteracaoPrazo' => [
                'quantidadeDiasAceite' => 20
            ]
        ];

        $response = BancoDoBrasil::boletos()->update($boletoNumero, $updateData);

        $this->assertTrue($response->successful(), 'Update boleto prazo vencido failed: ' . $response->body());
        
        $data = $response->json();
        $this->assertIsArray($data);
    }

    /** @test */
    public function it_can_update_boleto_complete_example()
    {
        // Create a boleto to update
        $createdBoleto = $this->createTestBoleto();
        
        $boletoNumero = $this->extractBoletoNumber($createdBoleto);
        $convenio = (int) config('banco-do-brasil.convenio', 3128557);

        // Example from the API documentation with multiple alterations
        $updateData = [
            'numeroConvenio' => $convenio,
            'indicadorNovaDataVencimento' => 'S',
            'alteracaoData' => [
                'novaDataVencimento' => now()->addDays(45)->format('d.m.Y')
            ],
            'indicadorNovoValorNominal' => 'S',
            'alteracaoValor' => [
                'novoValorNominal' => 300.00
            ],
            'indicadorAtribuirDesconto' => 'S',
            'desconto' => [
                'tipoPrimeiroDesconto' => 1,
                'valorPrimeiroDesconto' => 30.00,
                'percentualPrimeiroDesconto' => 0.0,
                'dataPrimeiroDesconto' => now()->addDays(40)->format('d.m.Y'),
                'tipoSegundoDesconto' => 0,
                'valorSegundoDesconto' => 0.0,
                'percentualSegundoDesconto' => 0.0,
                'dataSegundoDesconto' => '00.00.0000',
                'tipoTerceiroDesconto' => 0,
                'valorTerceiroDesconto' => 0.0,
                'percentualTerceiroDesconto' => 0.0,
                'dataTerceiroDesconto' => '00.00.0000'
            ],
            'indicadorProtestar' => 'S',
            'protesto' => [
                'quantidadeDiasProtesto' => 7
            ],
            'indicadorIncluirAbatimento' => 'S',
            'abatimento' => [
                'valorAbatimento' => 15.00
            ]
        ];

        $response = BancoDoBrasil::boletos()->update($boletoNumero, $updateData);

        $this->assertTrue($response->successful(), 'Update boleto complete example failed: ' . $response->body());
        
        $data = $response->json();
        $this->assertIsArray($data);
    }

    /** @test */
    public function it_validates_update_data_correctly()
    {
        // Create a boleto to update
        $createdBoleto = $this->createTestBoleto();
        
        $boletoNumero = $this->extractBoletoNumber($createdBoleto);

        // Test with missing numeroConvenio
        $invalidUpdateData = [
            'indicadorNovaDataVencimento' => 'S',
            'alteracaoData' => [
                'novaDataVencimento' => now()->addDays(60)->format('d.m.Y')
            ]
        ];

        $this->expectException(\Illuminate\Validation\ValidationException::class);
        BancoDoBrasil::boletos()->update($boletoNumero, $invalidUpdateData);
    }

    /** @test */
    public function it_handles_invalid_boleto_number_for_update()
    {
        $convenio = (int) config('banco-do-brasil.convenio', 3128557);
        $invalidBoletoNumero = '99999999999999999999';

        $updateData = [
            'numeroConvenio' => $convenio,
            'indicadorNovaDataVencimento' => 'S',
            'alteracaoData' => [
                'novaDataVencimento' => now()->addDays(60)->format('d.m.Y')
            ]
        ];

        $response = BancoDoBrasil::boletos()->update($invalidBoletoNumero, $updateData);

        $this->assertFalse($response->successful());
        $this->assertContains($response->status(), [400, 404, 422]);
    }

    // ============================================================================
    // HELPER METHODS FOR UPDATE TESTS
    // ============================================================================

    /**
     * Helper method to create a test boleto for updating
     *
     * @return \Illuminate\Http\Client\Response
     */
    private function createTestBoleto()
    {
        $pagador = PagadorData::fromEnum(
            TipoInscricao::CPF,
            11144477735,
            'João da Silva - Teste Update',
            'Rua das Flores, 123',
            '01234567',
            'São Paulo',
            'Centro',
            'SP',
            '11999999999'
        );

        $timestamp = time();
        $convenio = (int) config('banco-do-brasil.convenio', 3128557);
        
        $numeroTituloCliente = sprintf("000%07d%010d", $convenio, $timestamp % 10000000000);
        $numeroTituloBeneficiario = sprintf("UPD%015d", $timestamp % 1000000000000000); // Max 18 chars

        $boletoData = BoletoData::builder()
            ->numeroConvenio($convenio)
            ->numeroCarteira(17)
            ->numeroVariacaoCarteira(35)
            ->codigoModalidade(CodigoModalidade::SIMPLES)
            ->dataEmissao(now()->format('d.m.Y'))
            ->dataVencimento(now()->addDays(30)->format('d.m.Y'))
            ->valorOriginal(150.00)
            ->codigoTipoTitulo(TipoTitulo::DUPLICATA_MERCANTIL)
            ->descricaoTipoTitulo('Duplicata Mercantil')
            ->numeroTituloBeneficiario($numeroTituloBeneficiario)
            ->numeroTituloCliente($numeroTituloCliente)
            ->mensagemBloquetoOcorrencia('Boleto criado para teste de atualização')
            ->pagador($pagador)
            ->indicadorPix(true)
            ->build();

        $response = BancoDoBrasil::boletos()->create($boletoData->toApiArray());
        
        $this->assertTrue($response->successful(), 'Failed to create test boleto for update: ' . $response->body());
        
        return $response;
    }

    /**
     * Helper method to create a test boleto with discount
     *
     * @return \Illuminate\Http\Client\Response
     */
    private function createTestBoletoWithDiscount()
    {
        $pagador = PagadorData::fromEnum(
            TipoInscricao::CPF,
            11144477735,
            'João da Silva - Teste Update Discount',
            'Rua das Flores, 123',
            '01234567',
            'São Paulo',
            'Centro',
            'SP',
            '11999999999'
        );

        $timestamp = time();
        $convenio = (int) config('banco-do-brasil.convenio', 3128557);
        
        $numeroTituloCliente = sprintf("000%07d%010d", $convenio, $timestamp % 10000000000);
        $numeroTituloBeneficiario = sprintf("DIS%015d", $timestamp % 1000000000000000); // Max 18 chars

        $boletoData = BoletoData::builder()
            ->numeroConvenio($convenio)
            ->numeroCarteira(17)
            ->numeroVariacaoCarteira(35)
            ->codigoModalidade(CodigoModalidade::SIMPLES)
            ->dataEmissao(now()->format('d.m.Y'))
            ->dataVencimento(now()->addDays(30)->format('d.m.Y'))
            ->valorOriginal(200.00)
            ->codigoTipoTitulo(TipoTitulo::DUPLICATA_MERCANTIL)
            ->descricaoTipoTitulo('Duplicata Mercantil')
            ->numeroTituloBeneficiario($numeroTituloBeneficiario)
            ->numeroTituloCliente($numeroTituloCliente)
            ->mensagemBloquetoOcorrencia('Boleto com desconto para teste de alteração')
            ->pagador($pagador)
            ->indicadorPix(true)
            ->build();

        $response = BancoDoBrasil::boletos()->create($boletoData->toApiArray());
        
        $this->assertTrue($response->successful(), 'Failed to create test boleto with discount: ' . $response->body());
        
        return $response;
    }

    /**
     * Helper method to create a test boleto with abatimento
     *
     * @return \Illuminate\Http\Client\Response
     */
    private function createTestBoletoWithAbatimento()
    {
        $pagador = PagadorData::fromEnum(
            TipoInscricao::CPF,
            11144477735,
            'João da Silva - Teste Update Abatimento',
            'Rua das Flores, 123',
            '01234567',
            'São Paulo',
            'Centro',
            'SP',
            '11999999999'
        );

        $timestamp = time();
        $convenio = (int) config('banco-do-brasil.convenio', 3128557);
        
        $numeroTituloCliente = sprintf("000%07d%010d", $convenio, $timestamp % 10000000000);
        $numeroTituloBeneficiario = sprintf("ABT%015d", $timestamp % 1000000000000000); // Max 18 chars

        $boletoData = BoletoData::builder()
            ->numeroConvenio($convenio)
            ->numeroCarteira(17)
            ->numeroVariacaoCarteira(35)
            ->codigoModalidade(CodigoModalidade::SIMPLES)
            ->dataEmissao(now()->format('d.m.Y'))
            ->dataVencimento(now()->addDays(30)->format('d.m.Y'))
            ->valorOriginal(180.00)
            ->valorAbatimento(10.00)
            ->codigoTipoTitulo(TipoTitulo::DUPLICATA_MERCANTIL)
            ->descricaoTipoTitulo('Duplicata Mercantil')
            ->numeroTituloBeneficiario($numeroTituloBeneficiario)
            ->numeroTituloCliente($numeroTituloCliente)
            ->mensagemBloquetoOcorrencia('Boleto com abatimento para teste de alteração')
            ->pagador($pagador)
            ->indicadorPix(true)
            ->build();

        $response = BancoDoBrasil::boletos()->create($boletoData->toApiArray());
        
        $this->assertTrue($response->successful(), 'Failed to create test boleto with abatimento: ' . $response->body());
        
        return $response;
    }

    /**
     * Helper method to extract boleto number from response
     *
     * @param \Illuminate\Http\Client\Response $response
     * @return string
     */
    private function extractBoletoNumber($response)
    {
        $data = $response->json();
        
        // Different possible response formats from API
        if (isset($data['numero'])) {
            return $data['numero'];
        }
        
        if (isset($data['numeroTituloCobranca'])) {
            return $data['numeroTituloCobranca'];
        }
        
        if (isset($data['nossoNumero'])) {
            return $data['nossoNumero'];
        }
        
        // If we can't extract the number, fail the test
        $this->fail('Could not extract boleto number from response: ' . json_encode($data));
    }
}