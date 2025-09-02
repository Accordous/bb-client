<?php

namespace Accordous\BbClient\Data;

use Accordous\BbClient\Enums\TipoInscricao;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Attributes\Validation\IntegerType;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Data;

class BeneficiarioData extends Data
{
    public function __construct(
        #[Required]
        #[IntegerType]
        #[Min(1)]
        #[Max(2)]
        #[MapInputName('tipoInscricao')]
        #[MapOutputName('tipoInscricao')]
        public int $tipoInscricao,

        #[Required]
        #[StringType]
        #[MapInputName('numeroInscricao')]
        #[MapOutputName('numeroInscricao')]
        public string $numeroInscricao,

        #[Required]
        #[StringType]
        #[Max(100)]
        public string $nome,

        #[StringType]
        #[Max(100)]
        public string $endereco = '',

        #[StringType]
        #[Max(8)]
        public string $cep = '',

        #[StringType]
        #[Max(50)]
        public string $cidade = '',

        #[StringType]
        #[Max(50)]
        public string $bairro = '',

        #[StringType]
        #[Max(2)]
        public string $uf = '',

        #[StringType]
        #[Max(15)]
        public string $telefone = ''
    ) {}

    public static function fromEnum(
        TipoInscricao $tipoInscricao,
        string $numeroInscricao,
        string $nome,
        string $endereco = '',
        string $cep = '',
        string $cidade = '',
        string $bairro = '',
        string $uf = '',
        string $telefone = ''
    ): self {
        return new self(
            tipoInscricao: $tipoInscricao->value,
            numeroInscricao: $numeroInscricao,
            nome: $nome,
            endereco: $endereco,
            cep: $cep,
            cidade: $cidade,
            bairro: $bairro,
            uf: $uf,
            telefone: $telefone
        );
    }

    public function getTipoInscricaoEnum(): TipoInscricao
    {
        return TipoInscricao::from($this->tipoInscricao);
    }

    public function isValidDocument(): bool
    {
        $tipoEnum = $this->getTipoInscricaoEnum();
        $cleanDocument = preg_replace('/[^0-9]/', '', $this->numeroInscricao);
        
        return strlen($cleanDocument) === $tipoEnum->getDocumentLength();
    }

    public function getFormattedDocument(): string
    {
        $tipoEnum = $this->getTipoInscricaoEnum();
        $cleanDocument = preg_replace('/[^0-9]/', '', $this->numeroInscricao);
        
        if (!$this->isValidDocument()) {
            return $this->numeroInscricao;
        }

        $mask = $tipoEnum->getDocumentMask();
        $formatted = '';
        $docIndex = 0;

        for ($i = 0; $i < strlen($mask); $i++) {
            if ($mask[$i] === '#') {
                $formatted .= $cleanDocument[$docIndex] ?? '';
                $docIndex++;
            } else {
                $formatted .= $mask[$i];
            }
        }

        return $formatted;
    }
}