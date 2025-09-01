<?php

namespace Tests\Feature;

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
    }

    /** @test */
    public function it_can_register_boleto_cobranca()
    {
        // Skip this test if environment variables are not set
        if (empty(config('banco-do-brasil.client_id')) || 
            empty(config('banco-do-brasil.client_secret')) ||
            config('banco-do-brasil.client_id') === 'test-client-id') {
            $this->markTestSkipped('API credentials not configured for real API testing.');
        }

        // Prepare test data
        $data = [
            "numeroConvenio" => 3128557,
            "numeroCarteira" => 17,
            "numeroVariacaoCarteira" => 35,
            "codigoModalidade" => 1,
            "dataEmissao" => "06.05.2024",
            "dataVencimento" => "12.12.2029",
            "valorOriginal" => 3.00,
            "valorAbatimento" => 0,
            "quantidadeDiasProtesto" => 15,
            "indicadorAceiteTituloVencido" => "s",
            "numeroDiasLimiteRecebimento" => "",
            "codigoAceite" => "A",
            "codigoTipoTitulo" => "02",
            "descricaoTipoTitulo" => "DM",
            "indicadorPermissaoRecebimentoParcial" => "S",
            "numeroTituloBeneficiario" => "0DABC-DSD-1",
            "textoCampoUtilizacaoBeneficiario" => "ORTOPLANMATRIZFOZDOIG",
            "numeroTituloCliente" => "00031285576000090001",
            "mensagemBloquetoOcorrencia" => "",
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
                "tipoInscricao" => 2,
                "numeroInscricao" => 74910037000193,
                "nome" => "TECIDOS FARIA DUARTE",
                "endereco" => "RUA SAO CLEMENTE N 00325",
                "cep" => 22260009,
                "cidade" => "RIODEJANEIRO",
                "bairro" => "BOTAFOGO",
                "uf" => "RJ",
                "telefone" => "2122461388"
            ],
            "beneficiarioFinal" => [
                "tipoInscricao" => 2,
                "numeroInscricao" => 2518688000121,
                "nome" => "CLINICADEREABILITA"
            ],
            "indicadorPix" => "S",
            "textoEnderecoEmail" => "teste@teste.com.br"
        ];

        // Call the method with specific parameters
        $response = BancoDoBrasil::boletos()->create($data);

        // Assert that the response has the expected structure
        $this->assertTrue($response->successful(), 'Boleto registration failed');
        
        // Check if response contains expected keys
        // Note: these assertions might need to be adjusted based on the actual API response
        $this->assertArrayHasKey('numero', $response);
        // Add more assertions based on the real API response
    }
} 