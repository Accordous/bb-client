<?php

namespace Tests\Feature;

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
use Illuminate\Support\Facades\Http;

class BancoDoBrasilIntegrationTest extends TestCase
{
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

        $pagador = new Pagador(
            TipoInscricao::CPF,
            '12345678901',
            'João da Silva',
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

        $builder = (new BoletoBuilder())
            ->numeroConvenio(3128557)
            ->numeroCarteira(17)
            ->numeroVariacaoCarteira(35)
            ->codigoModalidade(CodigoModalidade::SIMPLES)
            ->dataEmissao('22.08.2025')
            ->dataVencimento('22.09.2025')
            ->valorOriginal(100.00)
            ->codigoTipoTitulo(TipoTitulo::DUPLICATA_MERCANTIL)
            ->descricaoTipoTitulo('Duplicata Mercantil')
            ->pagador($pagador)
            ->desconto($desconto)
            ->jurosMora($jurosMora)
            ->multa($multa);

        $boletoData = $builder->build();

        $response = BancoDoBrasil::boletos()->create($boletoData);

        $this->assertTrue($response->successful(), 'Registrar boleto failed: ' . $response->body());
    }

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
            'indicadorSituacao' => 'A',
            'pagina' => 1,
            'quantidadePorPagina' => 50
        ]);

        $this->assertTrue($response->successful());
        $data = $response->json();
        $this->assertArrayHasKey('boletos', $data);
    }

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

    public function test_webhook_baixa_operacional()
    {
        Http::fake([
            'oauth.hm.bb.com.br/oauth/token' => Http::response([
                'access_token' => 'fake-token',
                'expires_in' => 3600,
                'token_type' => 'bearer'
            ]),
            'api.hm.bb.com.br/cobrancas/v2/baixa-operacional' => Http::response([
                'status' => 'SUCESSO',
                'mensagem' => 'A notificação foi enviada com sucesso ao cliente.'
            ])
        ]);

        $webhookData = [
            'id' => '00031285570000104055',
            'dataRegistro' => '11.06.2025',
            'dataVencimento' => '11.06.2025',
            'valorOriginal' => 1000,
            'valorPagoSacado' => 1000,
            'numeroConvenio' => 3128557,
            'numeroOperacao' => 10055680,
            'carteiraConvenio' => 17,
            'variacaoCarteiraConvenio' => 35,
            'codigoEstadoBaixaOperacional' => 1,
            'dataLiquidacao' => '12/06/2025 16:29:30',
            'instituicaoLiquidacao' => '001',
            'canalLiquidacao' => 4,
            'codigoModalidadeBoleto' => 1,
            'tipoPessoaPortador' => 2,
            'identidadePortador' => '98959112000179', // Changed to string
            'nomePortador' => 'CINE VENTURA DE PADUA',
            'formaPagamento' => 2
        ];

        $response = BancoDoBrasil::webhooks()->processarBaixaOperacional($webhookData);

        $this->assertTrue($response->successful());
        $data = $response->json();
        $this->assertEquals('SUCESSO', $data['status']);
    }
}