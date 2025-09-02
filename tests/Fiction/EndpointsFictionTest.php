<?php

namespace Tests\Feature;

use Accordous\BbClient\Facades\BancoDoBrasil;
use Accordous\BbClient\Data\BoletoData;
use Accordous\BbClient\Data\PagadorData;
use Accordous\BbClient\Data\DescontoData;
use Accordous\BbClient\Data\JurosMoraData;
use Accordous\BbClient\Data\MultaData;
use Accordous\BbClient\Enums\TipoInscricao;
use Accordous\BbClient\Enums\CodigoModalidade;
use Accordous\BbClient\Enums\TipoTitulo;
use Tests\TestCase;
use Illuminate\Support\Facades\Http;

/**
 * Testes mockados
 */
class EndpointsFictionTest extends TestCase
{
    // # Auth tests
    public function test_get_token()
    {
        Http::fake([
            'oauth.hm.bb.com.br/oauth/token' => Http::response([
                'access_token' => 'fake-token',
                'expires_in' => 3600,
                'token_type' => 'bearer'
            ])
        ]);

        $token = BancoDoBrasil::getToken();
        
        $this->assertNotEmpty($token);
        $this->assertEquals('fake-token', $token);
    }

    // # Boleto tests
    // Registra Boleto de Cobrança
    public function test_registrar_boleto_using_builder()
    {
        Http::fake([
            'oauth.hm.bb.com.br/oauth/token' => Http::response([
                'access_token' => 'fake-token',
                'expires_in' => 3600,
                'token_type' => 'bearer'
            ]),
            'api.hm.bb.com.br/cobrancas/v2/boletos' => Http::response([
                'numero' => '12345678901234567890',
                'numeroContratoCobranca' => 123456,
                'dataRegistro' => '2025-08-22'
            ])
        ]);

        // Criar pagador usando factory method com enum
        $pagador = PagadorData::fromEnum(
            TipoInscricao::CPF,
            12345678901,
            'João da Silva',
            'Rua das Flores, 123',
            12345567,
            'São Paulo',
            'Centro',
            'SP',
            '11999999999'
        );

        // Criar desconto usando factory method
        $desconto = DescontoData::porcentagem(1, '25.08.2025', 5.0);
        
        // Criar juros usando factory method
        $jurosMora = JurosMoraData::valor(1, 2.0);
        
        // Criar multa usando factory method
        $multa = MultaData::porcentagem(2, '23.08.2025', 2.0);

        // Usar o builder moderno
        $boleto = BoletoData::builder()
            ->numeroConvenio(3128557)
            ->numeroCarteira(17)
            ->numeroVariacaoCarteira(35)
            ->codigoModalidade(CodigoModalidade::SIMPLES)
            ->dataEmissao('22.08.2025')
            ->dataVencimento('22.09.2025')
            ->valorOriginal(100.00)
            ->codigoTipoTitulo(TipoTitulo::DUPLICATA_MERCANTIL)
            ->pagador($pagador)
            ->desconto($desconto)
            ->jurosMora($jurosMora)
            ->multa($multa)
            ->indicadorPix(true)
            ->build();

        // Verificar validações automáticas
        $this->assertTrue($pagador->isValidDocument());
        $this->assertTrue($boleto->isPixEnabled());
        $this->assertEquals('Modalidade Simples', $boleto->getCodigoModalidadeEnum()->getDescription());
        $this->assertEquals('Duplicata Mercantil', $boleto->getTipoTituloEnum()->getDescription());

        $boletoArray = $boleto->toApiArray();

        $response = BancoDoBrasil::boletos()->create($boletoArray);

        $this->assertTrue($response->successful(), 'Registrar boleto failed: ' . $response->body());
    }
    // Listar Boletos
    public function test_list_boletos()
    {
        Http::fake([
            'oauth.hm.bb.com.br/oauth/token' => Http::response([
                'access_token' => 'fake-token',
                'expires_in' => 3600,
                'token_type' => 'bearer'
            ]),
            'api.hm.bb.com.br/cobrancas/v2/boletos*' => Http::response([
                'boletos' => [
                    [
                        'numero' => '12345678901234567890',
                        'numeroContratoCobranca' => 123456,
                        'dataRegistro' => '2025-08-22'
                    ]
                ],
                'quantidadeRegistros' => 1,
                'quantidadePorPagina' => 50
            ])
        ]);

        $response = BancoDoBrasil::boletos()->list([
            'numeroConvenio' => 1234567,
            'agenciaBeneficiario' => 1234,
            'contaBeneficiario' => 123456,
            'indicadorSituacao' => 'A',
            'pagina' => 1,
            'quantidadePorPagina' => 50
        ]);

        $this->assertTrue($response->successful());
        $data = $response->json();
        $this->assertArrayHasKey('boletos', $data);
    }
    // Detalha um boleto bancário
    public function test_show_boleto()
    {
        Http::fake([
            'oauth.hm.bb.com.br/oauth/token' => Http::response([
                'access_token' => 'fake-token',
                'expires_in' => 3600,
                'token_type' => 'bearer'
            ]),
            'api.hm.bb.com.br/cobrancas/v2/boletos/*' => Http::response([
                'numero' => '12345678901234567890',
                'numeroContratoCobranca' => 123456,
                'dataRegistro' => '2025-08-22',
                'dataVencimento' => '2025-09-22',
                'valorOriginal' => 100.00
            ])
        ]);

        $response = BancoDoBrasil::boletos()->show('12345678901234567890', 1234567);

        $this->assertTrue($response->successful());
        $data = $response->json();
        $this->assertEquals('12345678901234567890', $data['numero']);
    }
    // Altera um boleto bancário
    public function test_update_boleto()
    {
        Http::fake([
            'oauth.hm.bb.com.br/oauth/token' => Http::response([
                'access_token' => 'fake-token',
                'expires_in' => 3600,
                'token_type' => 'bearer'
            ]),
            'api.hm.bb.com.br/cobrancas/v2/boletos/*' => Http::response([
                'numero' => '12345678901234567890',
                'numeroContratoCobranca' => 123456,
                'dataVencimento' => '2025-10-22',
                'valorOriginal' => 100.00,
                'situacao' => 'Normal'
            ])
        ]);

        $updateData = [
            'numeroConvenio' => 1234567,
            'indicadorNovaDataVencimento' => 'S',
            'alteracaoData' => [
                'novaDataVencimento' => '22.10.2025'
            ]
        ];

        $response = BancoDoBrasil::boletos()->update('12345678901234567890', $updateData);

        $this->assertTrue($response->successful());
        $data = $response->json();
        $this->assertEquals('12345678901234567890', $data['numero']);
        $this->assertEquals('2025-10-22', $data['dataVencimento']);
    }

    // # Baixa Operacional tests
    // Desativar
    public function test_desativar_consulta_baixa_operacional()
    {
        Http::fake([
            'oauth.hm.bb.com.br/oauth/token' => Http::response([
                'access_token' => 'fake-token',
                'expires_in' => 3600,
                'token_type' => 'bearer'
            ]),
            'api.hm.bb.com.br/cobrancas/v2/convenios/*/desativar-consulta-baixa-operacional' => Http::response([
                'numeroConvenio' => 1234567,
                'status' => 'DESATIVADO'
            ])
        ]);

        $response = BancoDoBrasil::convenios()->desativarConsultaBaixaOperacional('1234567');

        $this->assertTrue($response->successful());
        $data = $response->json();
        $this->assertEquals('DESATIVADO', $data['status']);
    }
    // Ativar
    public function test_ativar_consulta_baixa_operacional()
    {
        Http::fake([
            'oauth.hm.bb.com.br/oauth/token' => Http::response([
                'access_token' => 'fake-token',
                'expires_in' => 3600,
                'token_type' => 'bearer'
            ]),
            'api.hm.bb.com.br/cobrancas/v2/convenios/*/ativar-consulta-baixa-operacional' => Http::response([
                'numeroConvenio' => 1234567,
                'status' => 'ATIVADO'
            ])
        ]);

        $response = BancoDoBrasil::convenios()->ativarConsultaBaixaOperacional('1234567');

        $this->assertTrue($response->successful());
        $data = $response->json();
        $this->assertEquals('ATIVADO', $data['status']);
    }
    // Consultar
    public function test_consultar_baixa_operacional()
    {
        Http::fake([
            'oauth.hm.bb.com.br/oauth/token' => Http::response([
                'access_token' => 'fake-token',
                'expires_in' => 3600,
                'token_type' => 'bearer'
            ]),
            'api.hm.bb.com.br/cobrancas/v2/boletos-baixa-operacional*' => Http::response([
                'possuiMaisTitulos' => 'N',
                'titulosBaixaOperacional' => [
                    [
                        'numeroTitulo' => '12345678901234567890',
                        'dataVencimento' => '22/09/2025',
                        'valorOriginal' => 100.00,
                        'dataLiquidacao' => '20/09/2025 14:30:00'
                    ]
                ]
            ])
        ]);

        $response = BancoDoBrasil::boletos()->consultarBaixaOperacional([
            'agencia' => 1234,
            'conta' => 123456,
            'carteira' => 17,
            'variacao' => 35,
            'dataInicioAgendamentoTitulo' => '01/08/2025',
            'dataFimAgendamentoTitulo' => '31/08/2025'
        ]);

        $this->assertTrue($response->successful());
        $data = $response->json();
        $this->assertArrayHasKey('titulosBaixaOperacional', $data);
    }

    // # PIX tests
    // Gerar PIX boleto
    public function test_gerar_pix_boleto()
    {
        Http::fake([
            'oauth.hm.bb.com.br/oauth/token' => Http::response([
                'access_token' => 'fake-token',
                'expires_in' => 3600,
                'token_type' => 'bearer'
            ]),
            'api.hm.bb.com.br/cobrancas/v2/boletos/*/gerar-pix' => Http::response([
                'numero' => '12345678901234567890',
                'indicadorPix' => 'S',
                'qrCodePix' => 'base64-encoded-qr-code'
            ])
        ]);

        $response = BancoDoBrasil::boletos()->gerarPix('12345678901234567890');

        $this->assertTrue($response->successful());
        $data = $response->json();
        $this->assertEquals('S', $data['indicadorPix']);
    }
    // Cancelar PIX boleto
    public function test_cancelar_pix_boleto()
    {
        Http::fake([
            'oauth.hm.bb.com.br/oauth/token' => Http::response([
                'access_token' => 'fake-token',
                'expires_in' => 3600,
                'token_type' => 'bearer'
            ]),
            'api.hm.bb.com.br/cobrancas/v2/boletos/*/cancelar-pix' => Http::response([
                'numero' => '12345678901234567890',
                'indicadorPix' => 'N',
                'mensagem' => 'PIX cancelado com sucesso'
            ])
        ]);

        $response = BancoDoBrasil::boletos()->cancelarPix('12345678901234567890');

        $this->assertTrue($response->successful());
        $data = $response->json();
        $this->assertEquals('N', $data['indicadorPix']);
        $this->assertArrayHasKey('mensagem', $data);
    }
    // Consultar PIX boleto
    public function test_consultar_pix_boleto()
    {
        Http::fake([
            'oauth.hm.bb.com.br/oauth/token' => Http::response([
                'access_token' => 'fake-token',
                'expires_in' => 3600,
                'token_type' => 'bearer'
            ]),
            'api.hm.bb.com.br/cobrancas/v2/boletos/*/consultar-pix' => Http::response([
                'numero' => '12345678901234567890',
                'indicadorPix' => 'S',
                'statusPix' => 'ATIVO',
                'qrCodePix' => 'base64-encoded-qr-code',
                'codigoPix' => 'BR.PIX.123456789'
            ])
        ]);

        $response = BancoDoBrasil::boletos()->consultarPix('12345678901234567890');

        $this->assertTrue($response->successful());
        $data = $response->json();
        $this->assertEquals('S', $data['indicadorPix']);
        $this->assertEquals('ATIVO', $data['statusPix']);
        $this->assertArrayHasKey('qrCodePix', $data);
        $this->assertArrayHasKey('codigoPix', $data);
    }

    // # Webhook tests
    public function test_webhook_baixa_operacional()
    {
        Http::fake([
            'oauth.hm.bb.com.br/oauth/token' => Http::response([
                'access_token' => 'fake-token',
                'expires_in' => 3600,
                'token_type' => 'bearer'
            ]),
            'api.hm.bb.com.br/cobrancas/v2/webhooks/baixa-operacional' => Http::response([
                'status' => 'SUCESSO',
                'mensagem' => 'Webhook processado com sucesso',
                'numeroOperacao' => 10055680
            ])
        ]);

        $webhookData = [
            'id' => '00031285570000104055',
            'dataRegistro' => '02.09.2025',
            'dataVencimento' => '02.10.2025',
            'valorOriginal' => 1000,
            'valorPagoSacado' => 1000,
            'numeroConvenio' => 1234567,
            'numeroOperacao' => 10055680,
            'carteiraConvenio' => 17,
            'variacaoCarteiraConvenio' => 35,
            'codigoEstadoBaixaOperacional' => 1,
            'dataLiquidacao' => '02/09/2025 14:30:00',
            'instituicaoLiquidacao' => '001',
            'canalLiquidacao' => 4,
            'codigoModalidadeBoleto' => 1,
            'tipoPessoaPortador' => 2,
            'identidadePortador' => '98959112000179',
            'nomePortador' => 'TESTE WEBHOOK INTEGRATION',
            'formaPagamento' => 2
        ];

        $response = BancoDoBrasil::webhooks()->processarBaixaOperacional($webhookData);

        $this->assertTrue($response->successful());
        $data = $response->json();
        $this->assertEquals('SUCESSO', $data['status']);
        $this->assertArrayHasKey('mensagem', $data);
        $this->assertEquals(10055680, $data['numeroOperacao']);
    }
}