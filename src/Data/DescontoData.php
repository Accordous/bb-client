<?php

namespace Accordous\BbClient\Data;

use Spatie\LaravelData\Attributes\Validation\IntegerType;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Numeric;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Data;

class DescontoData extends Data
{
    public function __construct(
        #[Required]
        #[IntegerType]
        #[Min(0)]
        #[Max(3)]
        public int $tipo,

        #[StringType]
        #[Max(10)]
        public string $dataExpiracao = '',

        #[Numeric]
        #[Min(0)]
        #[Max(100)]
        public ?float $porcentagem = null,

        #[Numeric]
        #[Min(0)]
        public ?float $valor = null
    ) {}

    public static function porcentagem(int $tipo, string $dataExpiracao, float $porcentagem): self
    {
        return new self(
            tipo: $tipo,
            dataExpiracao: $dataExpiracao,
            porcentagem: $porcentagem,
            valor: null
        );
    }

    public static function valor(int $tipo, string $dataExpiracao, float $valor): self
    {
        return new self(
            tipo: $tipo,
            dataExpiracao: $dataExpiracao,
            porcentagem: null,
            valor: $valor
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
            0 => 'Sem desconto',
            1 => 'Valor fixo até a data informada',
            2 => 'Percentual até a data informada',
            3 => 'Valor por antecipação dia corrido',
            default => 'Tipo desconhecido'
        };
    }

    public function calcularDesconto(float $valorOriginal): float
    {
        if ($this->isPercentual()) {
            return $valorOriginal * ($this->porcentagem / 100);
        }

        if ($this->isValorFixo()) {
            return min($this->valor, $valorOriginal);
        }

        return 0;
    }
}