<?php

namespace Tests\Data;

use Accordous\BbClient\Data\JurosMoraData;
use Tests\TestCase;

class JurosMoraDataTest extends TestCase
{
    public function test_juros_mora_calculations()
    {
        $valorOriginal = 1000.00;
        $diasAtraso = 5;

        // Teste sem juros
        $semJuros = JurosMoraData::semJuros();
        $this->assertEquals(0, $semJuros->calcularJuros($valorOriginal, $diasAtraso));
        $this->assertEquals('Sem juros de mora', $semJuros->getDescricaoTipo());

        // Teste valor por dia
        $jurosPorDia = JurosMoraData::valor(1, 2.0);
        $this->assertTrue($jurosPorDia->isValorFixo());
        $this->assertEquals(10.0, $jurosPorDia->calcularJuros($valorOriginal, $diasAtraso));
        $this->assertEquals('Valor por dia', $jurosPorDia->getDescricaoTipo());

        // Teste taxa mensal
        $taxaMensal = JurosMoraData::porcentagem(2, 2.0); // 2% ao mês
        $this->assertTrue($taxaMensal->isPercentual());
        $expectedJuros = $valorOriginal * (2.0 / 100) / 30 * $diasAtraso; // 2% / 30 dias * 5 dias
        $this->assertEquals($expectedJuros, $taxaMensal->calcularJuros($valorOriginal, $diasAtraso));
    }
}