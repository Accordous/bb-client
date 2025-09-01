<?php

namespace Tests\Unit;

use Accordous\BbClient\Enums\TipoInscricao;
use Accordous\BbClient\Enums\CodigoModalidade;
use Accordous\BbClient\Enums\TipoTitulo;
use Accordous\BbClient\Enums\BoletoSituacao;
use Accordous\BbClient\ValueObject\Pagador;
use Accordous\BbClient\ValueObject\BoletoBuilder;
use PHPUnit\Framework\TestCase;

class EnumsTest extends TestCase
{
    public function test_tipo_inscricao_enum_works()
    {
        // Testa valores do enum
        $this->assertEquals(1, TipoInscricao::CPF->value);
        $this->assertEquals(2, TipoInscricao::CNPJ->value);

        // Testa validação
        $this->assertTrue(TipoInscricao::isValid(1));
        $this->assertTrue(TipoInscricao::isValid(2));
        $this->assertFalse(TipoInscricao::isValid(3));

        // Testa descrições
        $this->assertEquals('CPF - Pessoa Física', TipoInscricao::CPF->getDescription());
        $this->assertEquals('CNPJ - Pessoa Jurídica', TipoInscricao::CNPJ->getDescription());

        // Testa método fromDocument
        $this->assertEquals(TipoInscricao::CPF, TipoInscricao::fromDocument('12345678901'));
        $this->assertEquals(TipoInscricao::CNPJ, TipoInscricao::fromDocument('12345678901234'));
    }

    public function test_codigo_modalidade_enum_works()
    {
        $this->assertEquals('01', CodigoModalidade::SIMPLES->value);
        $this->assertEquals('04', CodigoModalidade::VINCULADA->value);

        $this->assertTrue(CodigoModalidade::isValid('01'));
        $this->assertTrue(CodigoModalidade::isValid('04'));
        $this->assertFalse(CodigoModalidade::isValid('99'));

        $this->assertEquals('Modalidade Simples', CodigoModalidade::SIMPLES->getDescription());
    }

    public function test_tipo_titulo_enum_works()
    {
        $this->assertEquals(2, TipoTitulo::DUPLICATA_MERCANTIL->value);
        $this->assertTrue(TipoTitulo::isValid(2));
        $this->assertFalse(TipoTitulo::isValid(999));

        $this->assertEquals('Duplicata Mercantil', TipoTitulo::DUPLICATA_MERCANTIL->getDescription());
        $this->assertTrue(TipoTitulo::DUPLICATA_MERCANTIL->isCommercial());
        $this->assertFalse(TipoTitulo::CHEQUE->isCommercial());
    }

    public function test_boleto_situacao_enum_works()
    {
        $this->assertEquals('A', BoletoSituacao::ATIVO->value);
        $this->assertTrue(BoletoSituacao::isValid('A'));
        $this->assertFalse(BoletoSituacao::isValid('X'));

        $this->assertEquals('Ativo', BoletoSituacao::ATIVO->getDescription());
    }

    public function test_pagador_accepts_enum()
    {
        // Testando com enum
        $pagador = new Pagador(
            TipoInscricao::CPF,
            '12345678901',
            'João da Silva'
        );

        $this->assertEquals(1, $pagador->tipoInscricao);
        $this->assertEquals('12345678901', $pagador->numeroInscricao);
        $this->assertEquals('João da Silva', $pagador->nome);
    }

    public function test_pagador_accepts_int()
    {
        // Testando com int (compatibilidade)
        $pagador = new Pagador(
            1,
            '12345678901',
            'João da Silva'
        );

        $this->assertEquals(1, $pagador->tipoInscricao);
    }

    public function test_boleto_builder_accepts_enums()
    {
        $pagador = new Pagador(
            TipoInscricao::CPF,
            '12345678901',
            'João da Silva'
        );

        $builder = (new BoletoBuilder())
            ->numeroConvenio(123456)
            ->numeroCarteira(17)
            ->numeroVariacaoCarteira(35)
            ->codigoModalidade(CodigoModalidade::SIMPLES)
            ->dataEmissao('01.01.2024')
            ->dataVencimento('31.01.2024')
            ->valorOriginal(100.00)
            ->codigoTipoTitulo(TipoTitulo::DUPLICATA_MERCANTIL)
            ->pagador($pagador);

        $data = $builder->build();

        $this->assertEquals('01', $data['codigoModalidade']);
        $this->assertEquals(2, $data['codigoTipoTitulo']);
        $this->assertEquals(1, $data['pagador']['tipoInscricao']);
    }
}