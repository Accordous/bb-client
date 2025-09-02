<?php

namespace Accordous\BbClient\Data;

use Spatie\LaravelData\Attributes\Validation\IntegerType;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Numeric;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

class JurosMoraData extends Data
{
    public function __construct(
        #[Required]
        #[IntegerType]
        #[Min(0)]
        #[Max(3)]
        public int $tipo,

        #[Numeric]
        #[Min(0)]
        #[Max(100)]
        public ?float $porcentagem = null,

        #[Numeric]
        #[Min(0)]
        public ?float $valor = null
    ) {}

    public static function porcentagem(int $tipo, float $porcentagem): self
    {
        return new self(
            tipo: $tipo,
            porcentagem: $porcentagem,
            valor: null
        );
    }

    public static function valor(int $tipo, float $valor): self
    {
        return new self(
            tipo: $tipo,
            porcentagem: null,
            valor: $valor
        );
    }

    public static function semJuros(): self
    {
        return new self(
            tipo: 0,
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
            0 => 'Sem juros de mora',
            1 => 'Valor por dia',
            2 => 'Taxa mensal',
            3 => 'Isento',
            default => 'Tipo desconhecido'
        };
    }

    public function calcularJuros(float $valorOriginal, int $diasAtraso = 1): float
    {
        if ($this->tipo === 0 || $this->tipo === 3) {
            return 0;
        }

        if ($this->isValorFixo()) {
            return $this->valor * $diasAtraso;
        }

        if ($this->isPercentual()) {
            if ($this->tipo === 2) { // Taxa mensal
                $taxaDiaria = ($this->porcentagem / 100) / 30;
                return $valorOriginal * $taxaDiaria * $diasAtraso;
            }
            
            // Percentual por dia
            return $valorOriginal * ($this->porcentagem / 100) * $diasAtraso;
        }

        return 0;
    }
}