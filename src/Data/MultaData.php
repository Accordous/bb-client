<?php

namespace Accordous\BbClient\Data;

use Spatie\LaravelData\Attributes\Validation\IntegerType;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Numeric;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Data;

class MultaData extends Data
{
    public function __construct(
        #[Required]
        #[IntegerType]
        #[Min(0)]
        #[Max(2)]
        public int $tipo,

        #[StringType]
        #[Max(10)]
        public string $data = '',

        #[Numeric]
        #[Min(0)]
        #[Max(100)]
        public ?float $porcentagem = null,

        #[Numeric]
        #[Min(0)]
        public ?float $valor = null
    ) {}

    public static function porcentagem(int $tipo, string $data, float $porcentagem): self
    {
        return new self(
            tipo: $tipo,
            data: $data,
            porcentagem: $porcentagem,
            valor: null
        );
    }

    public static function valor(int $tipo, string $data, float $valor): self
    {
        return new self(
            tipo: $tipo,
            data: $data,
            porcentagem: null,
            valor: $valor
        );
    }

    public static function semMulta(): self
    {
        return new self(
            tipo: 0,
            data: '',
            porcentagem: null,
            valor: null
        );
    }

    public function isPercentual(): bool
    {
        return $this->porcentagem !== null;
    }

    public function isValorFixo(): bool
    {
        return $this->valor !== null;
    }

    public function getDescricaoTipo(): string
    {
        return match($this->tipo) {
            0 => 'Sem multa',
            1 => 'Valor fixo',
            2 => 'Percentual',
            default => 'Tipo desconhecido'
        };
    }

    public function calcularMulta(float $valorOriginal): float
    {
        if ($this->tipo === 0) {
            return 0;
        }

        if ($this->isPercentual()) {
            return $valorOriginal * ($this->porcentagem / 100);
        }

        if ($this->isValorFixo()) {
            return $this->valor;
        }

        return 0;
    }

    public function isDataVencimentoValida(\DateTime $dataVencimento): bool
    {
        if (empty($this->data)) {
            return true;
        }

        try {
            $dataMulta = \DateTime::createFromFormat('d.m.Y', $this->data);
            return $dataMulta >= $dataVencimento;
        } catch (\Exception $e) {
            return false;
        }
    }
}