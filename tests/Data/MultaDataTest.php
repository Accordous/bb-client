<?php

namespace Tests\Data;

use Accordous\BbClient\Data\MultaData;
use Tests\TestCase;

class MultaDataTest extends TestCase
{
    public function test_multa_calculations()
    {
        $valorOriginal = 1000.00;

        // Teste sem multa
        $semMulta = MultaData::semMulta();
        $this->assertEquals(0, $semMulta->calcularMulta($valorOriginal));
        $this->assertEquals('Sem multa', $semMulta->getDescricaoTipo());

        // Teste multa percentual
        $multaPercentual = MultaData::porcentagem(2, '23.08.2025', 2.0);
        $this->assertTrue($multaPercentual->isPercentual());
        $this->assertEquals(20.0, $multaPercentual->calcularMulta($valorOriginal));
        $this->assertEquals('Percentual', $multaPercentual->getDescricaoTipo());

        // Teste multa valor fixo
        $multaValor = MultaData::valor(1, '23.08.2025', 50.0);
        $this->assertTrue($multaValor->isValorFixo());
        $this->assertEquals(50.0, $multaValor->calcularMulta($valorOriginal));
        $this->assertEquals('Valor fixo', $multaValor->getDescricaoTipo());
    }
}