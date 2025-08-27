<?php

namespace Tests\Integration;

use Tests\TestCase;
use Accordous\BbClient\Facades\BancoDoBrasil;
use Illuminate\Support\Facades\Cache;

class RegistrarBoletoCobrancaTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Clear the cache before each test
        Cache::flush();
        
        // Skip this test if environment variables are not set
        if (empty(config('banco-do-brasil.client_id')) || 
            empty(config('banco-do-brasil.client_secret')) ||
            config('banco-do-brasil.client_id') === 'test-client-id') {
            $this->markTestSkipped('API credentials not configured for real API testing.');
        }
    }

    /** @test */
    public function it_can_register_boleto_cobranca_with_real_api()
    {
        // Clear any cached tokens to ensure fresh API calls
        Cache::forget('bb_api_token');

        // Prepare test data with real structure expected by BB API
        $uniqueId = time();
        $data = [
            "numeroConvenio" => (int) config('banco-do-brasil.convenio', 3128557),
            "numeroCarteira" => 17,
            "numeroVariacaoCarteira" => 35,
            "codigoModalidade" => 1,
            "dataEmissao" => now()->format('d.m.Y'),
            "dataVencimento" => now()->addDays(30)->format('d.m.Y'),
            "valorOriginal" => 10.00, // Small amount for testing
            "valorAbatimento" => 0,
            "quantidadeDiasProtesto" => 15,
            "indicadorAceiteTituloVencido" => "S",
            "numeroDiasLimiteRecebimento" => "",
            "codigoAceite" => "A",
            "codigoTipoTitulo" => "02",
            "descricaoTipoTitulo" => "DM",
            "indicadorPermissaoRecebimentoParcial" => "S",
            "numeroTituloBeneficiario" => "TEST-{$uniqueId}",
            "textoCampoUtilizacaoBeneficiario" => "TESTE INTEGRACAO BB CLIENT",
            "numeroTituloCliente" => "CLIENT-{$uniqueId}",
            "mensagemBloquetoOcorrencia" => "Teste de integração - Pagamento disponível",
            "desconto" => [
                "tipo" => 0
            ],
            "jurosMora" => [
                "tipo" => 1,
                "valor" => 1.0,
                "porcentagem" => 0
            ],
            "multa" => [
                "tipo" => 0,
                "dados" => "",
                "porcentagem" => 0,
                "valor" => 0
            ],
            "pagador" => [
                "tipoInscricao" => 1, // CPF
                "numeroInscricao" => "12345678901", // Test CPF
                "nome" => "TESTE INTEGRATION USER",
                "endereco" => "RUA DE TESTE N 123",
                "cep" => 12345678,
                "cidade" => "SAOPAULO",
                "bairro" => "CENTRO",
                "uf" => "SP",
                "telefone" => "11999999999"
            ],
            "beneficiarioFinal" => [
                "tipoInscricao" => 2, // CNPJ
                "numeroInscricao" => "12345678000123", // Test CNPJ
                "nome" => "EMPRESA TESTE INTEGRACAO"
            ],
            "indicadorPix" => "S",
            "textoEnderecoEmail" => "teste.integracao@exemplo.com.br"
        ];

        // Call the registration method
        $response = BancoDoBrasil::registrarBoletoCobranca($data);

        // Assert that the response is valid
        $this->assertIsArray($response);
        
        // Check for success indicators
        if (isset($response['numero'])) {
            // Success case - boleto was registered
            $this->assertArrayHasKey('numero', $response);
            $this->assertNotEmpty($response['numero']);
            $this->assertIsString($response['numero']);
            
            // BB boleto numbers are typically 20 digits
            $this->assertMatchesRegularExpression('/^\d{20}$/', $response['numero']);
            
            // Check for other expected success fields
            if (isset($response['numeroContratoCobranca'])) {
                $this->assertIsNumeric($response['numeroContratoCobranca']);
            }
            
            if (isset($response['dataRegistro'])) {
                $this->assertNotEmpty($response['dataRegistro']);
            }
            
        } elseif (isset($response['erros'])) {
            // Error case - analyze the errors
            $this->assertIsArray($response['erros']);
            
            // Log errors for debugging
            $errorMessages = array_map(function($erro) {
                return isset($erro['mensagem']) ? $erro['mensagem'] : json_encode($erro);
            }, $response['erros']);
            
            // Common recoverable errors that we can handle in tests
            $recoverableErrors = [
                'numero do titulo beneficiario ja existe', // Duplicate title
                'convenio nao encontrado', // Invalid convenio
                'carteira nao encontrada', // Invalid carteira
            ];
            
            $hasRecoverableError = false;
            foreach ($errorMessages as $message) {
                foreach ($recoverableErrors as $recoverableError) {
                    if (stripos($message, $recoverableError) !== false) {
                        $hasRecoverableError = true;
                        break 2;
                    }
                }
            }
            
            if ($hasRecoverableError) {
                $this->markTestIncomplete('Recoverable API error: ' . implode('; ', $errorMessages));
            } else {
                $this->fail('API returned unexpected errors: ' . implode('; ', $errorMessages));
            }
            
        } else {
            // Unknown response format - check if it's a 404 error
            if (isset($response['message']) && strpos($response['message'], 'Cannot POST') !== false) {
                $this->markTestIncomplete('Boleto registration endpoint not available in sandbox environment');
            } else {
                $this->fail('Unexpected API response format: ' . json_encode($response));
            }
        }
    }

    /** @test */
    public function it_handles_duplicate_boleto_registration()
    {
        $uniqueId = time();
        $data = [
            "numeroConvenio" => (int) config('banco-do-brasil.convenio', 3128557),
            "numeroCarteira" => 17,
            "numeroVariacaoCarteira" => 35,
            "codigoModalidade" => 1,
            "dataEmissao" => now()->format('d.m.Y'),
            "dataVencimento" => now()->addDays(30)->format('d.m.Y'),
            "valorOriginal" => 10.00,
            "valorAbatimento" => 0,
            "codigoAceite" => "A",
            "codigoTipoTitulo" => "02",
            "descricaoTipoTitulo" => "DM",
            "numeroTituloBeneficiario" => "DUP-{$uniqueId}",
            "numeroTituloCliente" => "CLI-D-{$uniqueId}",
            "desconto" => ["tipo" => 0],
            "jurosMora" => ["tipo" => 0],
            "multa" => ["tipo" => 0],
            "pagador" => [
                "tipoInscricao" => 1,
                "numeroInscricao" => "12345678901",
                "nome" => "TESTE DUPLICATE USER",
                "endereco" => "RUA DE TESTE N 123",
                "cep" => 12345678,
                "cidade" => "SAOPAULO",
                "bairro" => "CENTRO",
                "uf" => "SP",
                "telefone" => "11999999999"
            ],
            "indicadorPix" => "S"
        ];

        // First registration attempt
        $firstResponse = BancoDoBrasil::registrarBoletoCobranca($data);
        
        // If first attempt was successful, try to register the same boleto again
        if (isset($firstResponse['numero'])) {
            $secondResponse = BancoDoBrasil::registrarBoletoCobranca($data);
            
            // Second attempt should fail with duplicate error
            $this->assertIsArray($secondResponse);
            $this->assertArrayHasKey('erros', $secondResponse);
            
            // Check if the error indicates duplicate registration
            $errorMessages = array_map(function($erro) {
                return isset($erro['mensagem']) ? strtolower($erro['mensagem']) : '';
            }, $secondResponse['erros']);
            
            $hasDuplicateError = false;
            foreach ($errorMessages as $message) {
                if (strpos($message, 'ja existe') !== false || 
                    strpos($message, 'duplicat') !== false ||
                    strpos($message, 'already exists') !== false) {
                    $hasDuplicateError = true;
                    break;
                }
            }
            
            $this->assertTrue($hasDuplicateError, 'Expected duplicate error not found in: ' . implode('; ', $errorMessages));
        } else {
            $this->markTestIncomplete('Could not complete duplicate test - first registration failed');
        }
    }

    /** @test */
    public function it_validates_required_fields()
    {
        // Test with minimal required data to see what validations the API enforces
        $minimalData = [
            "numeroConvenio" => (int) config('banco-do-brasil.convenio', 3128557),
            "numeroCarteira" => 17,
            "numeroVariacaoCarteira" => 35,
            "codigoModalidade" => 1,
            "dataEmissao" => now()->format('d.m.Y'),
            "dataVencimento" => now()->addDays(30)->format('d.m.Y'),
            "valorOriginal" => 5.00,
            "codigoTipoTitulo" => "02", // Add required field
            "descricaoTipoTitulo" => "DM", // Add required field
            "pagador" => [
                "tipoInscricao" => 1,
                "numeroInscricao" => "12345678901",
                "nome" => "TESTE MINIMAL",
                // Missing other pagador fields intentionally
            ]
            // Missing other fields intentionally
        ];

        $response = BancoDoBrasil::registrarBoletoCobranca($minimalData);
        
        $this->assertIsArray($response);
        
        // Should return validation errors for missing required fields
        if (isset($response['erros'])) {
            $this->assertIsArray($response['erros']);
            $this->assertNotEmpty($response['erros']);
            
            // Common required field errors
            $errorMessages = array_map(function($erro) {
                return isset($erro['mensagem']) ? strtolower($erro['mensagem']) : '';
            }, $response['erros']);
            
            $requiredFieldErrors = [
                'pagador', 'numero titulo beneficiario', 'codigo tipo titulo',
                'descricao tipo titulo', 'required', 'obrigatorio'
            ];
            
            $hasRequiredFieldError = false;
            foreach ($errorMessages as $message) {
                foreach ($requiredFieldErrors as $requiredError) {
                    if (strpos($message, $requiredError) !== false) {
                        $hasRequiredFieldError = true;
                        break 2;
                    }
                }
            }
            
            $this->assertTrue($hasRequiredFieldError, 
                'Expected required field error not found in: ' . implode('; ', $errorMessages));
        } else {
            // If no errors, the API might have defaults for missing fields
            $this->markTestIncomplete('API accepted minimal data without errors - check if this is expected behavior');
        }
    }

    /** @test */
    public function it_handles_invalid_dates()
    {
        $uniqueId = time();
        $data = [
            "numeroConvenio" => (int) config('banco-do-brasil.convenio', 3128557),
            "numeroCarteira" => 17,
            "numeroVariacaoCarteira" => 35,
            "codigoModalidade" => 1,
            "dataEmissao" => "32.13.2025", // Invalid date
            "dataVencimento" => "01.01.2020", // Past date
            "valorOriginal" => 10.00,
            "codigoAceite" => "A",
            "codigoTipoTitulo" => "02",
            "descricaoTipoTitulo" => "DM",
            "numeroTituloBeneficiario" => "INV-{$uniqueId}",
            "numeroTituloCliente" => "CLI-INV-{$uniqueId}",
            "desconto" => ["tipo" => 0],
            "jurosMora" => ["tipo" => 0],
            "multa" => ["tipo" => 0],
            "pagador" => [
                "tipoInscricao" => 1,
                "numeroInscricao" => "12345678901",
                "nome" => "TESTE INVALID DATE",
                "endereco" => "RUA DE TESTE N 123",
                "cep" => 12345678,
                "cidade" => "SAOPAULO",
                "bairro" => "CENTRO",
                "uf" => "SP"
            ]
        ];

        $response = BancoDoBrasil::registrarBoletoCobranca($data);
        
        $this->assertIsArray($response);
        
        // Check if it's a 404 error (endpoint not found in sandbox)
        if (isset($response['message']) && strpos($response['message'], 'Cannot POST') !== false) {
            $this->markTestIncomplete('Boleto registration endpoint not available in sandbox environment');
            return;
        }
        
        if (isset($response['erros'])) {
            $this->assertIsArray($response['erros']);
        } else {
            // If no validation errors are returned, mark as incomplete
            $this->markTestIncomplete('API did not return expected validation errors for invalid dates');
        }
        
        // Should contain date validation errors
        $errorMessages = array_map(function($erro) {
            return isset($erro['mensagem']) ? strtolower($erro['mensagem']) : '';
        }, $response['erros']);
        
        $dateErrorKeywords = ['data', 'date', 'vencimento', 'emissao', 'invalida', 'invalid'];
        
        $hasDateError = false;
        foreach ($errorMessages as $message) {
            foreach ($dateErrorKeywords as $keyword) {
                if (strpos($message, $keyword) !== false) {
                    $hasDateError = true;
                    break 2;
                }
            }
        }
        
        $this->assertTrue($hasDateError, 
            'Expected date validation error not found in: ' . implode('; ', $errorMessages));
    }
}