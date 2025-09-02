<?php

namespace Tests\Data;

use Accordous\BbClient\Data\BoletoData;
use Accordous\BbClient\Data\PagadorData;
use Accordous\BbClient\Data\DescontoData;
use Accordous\BbClient\Data\JurosMoraData;
use Accordous\BbClient\Data\MultaData;
use Accordous\BbClient\Enums\TipoInscricao;
use Accordous\BbClient\Enums\CodigoModalidade;
use Accordous\BbClient\Enums\TipoTitulo;
use Tests\TestCase;

class BoletoDataTest extends TestCase
{
    public function test_boleto_comprehensive_calculations()
    {
        $pagador = PagadorData::fromEnum(
            TipoInscricao::CPF,
            '12345678901',
            'João da Silva'
        );

        $desconto = DescontoData::porcentagem(2, '25.08.2025', 5.0); // 5% de desconto
        $jurosMora = JurosMoraData::valor(1, 2.0); // R$ 2,00 por dia
        $multa = MultaData::porcentagem(2, '23.08.2025', 2.0); // 2% de multa

        $boleto = BoletoData::builder()
            ->numeroConvenio(3128557)
            ->numeroCarteira(17)
            ->numeroVariacaoCarteira(35)
            ->codigoModalidade(CodigoModalidade::SIMPLES)
            ->dataEmissao('22.08.2025')
            ->dataVencimento('22.08.2025') // Vencido
            ->valorOriginal(1000.00)
            ->codigoTipoTitulo(TipoTitulo::DUPLICATA_MERCANTIL)
            ->pagador($pagador)
            ->desconto($desconto)
            ->jurosMora($jurosMora)
            ->multa($multa)
            ->build();

        // Teste cálculos
        $this->assertEquals(950.0, $boleto->getValorComDesconto()); // 1000 - 5% = 950
        $this->assertTrue($boleto->isVencido());
        
        // Teste valor com multa e juros (supondo 5 dias de atraso)
        $valorComJurosEMulta = $boleto->getValorComJurosEMulta(5);
        $expectedValue = 950.0 + 20.0 + 10.0; // valor com desconto + multa (2%) + juros (5 dias * R$ 2)
        $this->assertEquals($expectedValue, $valorComJurosEMulta);
    }

    public function test_spatie_data_serialization()
    {
        $pagador = PagadorData::fromEnum(
            TipoInscricao::CPF,
            '12345678901',
            'João da Silva',
            'Rua das Flores, 123'
        );

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
            ->build();

        // Teste array para API (implementação manual)
        $apiArray = $boleto->toApiArray();
        $this->assertIsArray($apiArray);
        $this->assertArrayHasKey('numeroConvenio', $apiArray);
        $this->assertArrayHasKey('pagador', $apiArray);
        
        // Verificar que campos obrigatórios estão presentes
        $this->assertEquals(3128557, $apiArray['numeroConvenio']);
        $this->assertEquals('João da Silva', $apiArray['pagador']['nome']);
    }
}