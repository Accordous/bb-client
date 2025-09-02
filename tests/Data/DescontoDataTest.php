<?php

namespace Tests\Data;

use Accordous\BbClient\Data\DescontoData;
use Tests\TestCase;

class DescontoDataTest extends TestCase
{
    public function test_desconto_calculations()
    {
        $valorOriginal = 1000.00;

        // Teste desconto percentual
        $descontoPercentual = DescontoData::porcentagem(2, '25.08.2025', 10.0);
        $this->assertTrue($descontoPercentual->isPercentual());
        $this->assertFalse($descontoPercentual->isValorFixo());
        $this->assertEquals(100.0, $descontoPercentual->calcularDesconto($valorOriginal));
        $this->assertEquals('Percentual até a data informada', $descontoPercentual->getDescricaoTipo());

        // Teste desconto valor fixo
        $descontoValor = DescontoData::valor(1, '25.08.2025', 50.0);
        $this->assertFalse($descontoValor->isPercentual());
        $this->assertTrue($descontoValor->isValorFixo());
        $this->assertEquals(50.0, $descontoValor->calcularDesconto($valorOriginal));
        $this->assertEquals('Valor fixo até a data informada', $descontoValor->getDescricaoTipo());
    }
}