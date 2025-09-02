<?php

namespace Tests\Data;

use Accordous\BbClient\Data\PagadorData;
use Accordous\BbClient\Enums\TipoInscricao;
use Tests\TestCase;

class PagadorDataTest extends TestCase
{
    public function test_pagador_data_validation()
    {
        // Test valid CPF
        $pagadorCPF = PagadorData::fromEnum(
            TipoInscricao::CPF,
            '12345678901',
            'João da Silva'
        );

        $this->assertTrue($pagadorCPF->isValidDocument());
        $this->assertEquals('123.456.789-01', $pagadorCPF->getFormattedDocument());
        $this->assertEquals('CPF - Pessoa Física', $pagadorCPF->getDocumentType());

        // Test valid CNPJ
        $pagadorCNPJ = PagadorData::fromEnum(
            TipoInscricao::CNPJ,
            '12345678000195',
            'Empresa Ltda'
        );

        $this->assertTrue($pagadorCNPJ->isValidDocument());
        $this->assertEquals('12.345.678/0001-95', $pagadorCNPJ->getFormattedDocument());
        $this->assertEquals('CNPJ - Pessoa Jurídica', $pagadorCNPJ->getDocumentType());

        // Test invalid document length
        $pagadorInvalido = PagadorData::fromEnum(
            TipoInscricao::CPF,
            '123456789', // Documento muito curto
            'João da Silva'
        );

        $this->assertFalse($pagadorInvalido->isValidDocument());
    }

    public function test_data_validation_attributes()
    {
        // Teste validação manual de dados
        $pagador = PagadorData::fromEnum(
            TipoInscricao::CPF,
            '123', // Documento inválido
            'João da Silva'
        );

        // Verificar que a validação detecta documento inválido
        $this->assertFalse($pagador->isValidDocument());

        // Teste com documento válido
        $pagadorValido = PagadorData::fromEnum(
            TipoInscricao::CPF,
            '12345678901', // 11 dígitos - válido para CPF
            'João da Silva'
        );

        $this->assertTrue($pagadorValido->isValidDocument());
    }
}