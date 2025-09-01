<?php

namespace Accordous\BbClient\Enums;

enum TipoInscricao: int
{
    case CPF = 1;
    case CNPJ = 2;

    public function getDescription(): string
    {
        return match($this) {
            self::CPF => 'CPF - Pessoa Física',
            self::CNPJ => 'CNPJ - Pessoa Jurídica',
        };
    }

    public function getDocumentLength(): int
    {
        return match($this) {
            self::CPF => 11,
            self::CNPJ => 14,
        };
    }

    public function getDocumentMask(): string
    {
        return match($this) {
            self::CPF => '###.###.###-##',
            self::CNPJ => '##.###.###/####-##',
        };
    }

    public static function isValid(int $value): bool
    {
        return self::tryFrom($value) !== null;
    }

    public static function fromDocument(string $document): self
    {
        $cleanDocument = preg_replace('/[^0-9]/', '', $document);
        $length = strlen($cleanDocument);

        return match($length) {
            11 => self::CPF,
            14 => self::CNPJ,
            default => throw new \InvalidArgumentException('Documento deve ter 11 ou 14 dígitos'),
        };
    }
}