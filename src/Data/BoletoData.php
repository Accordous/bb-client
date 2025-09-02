<?php

namespace Accordous\BbClient\Data;

use Accordous\BbClient\Enums\CodigoModalidade;
use Accordous\BbClient\Enums\TipoTitulo;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Attributes\Validation\Date;
use Spatie\LaravelData\Attributes\Validation\In;
use Spatie\LaravelData\Attributes\Validation\IntegerType;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Numeric;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Data;

class BoletoData extends Data
{
    public function __construct(
        #[Required]
        #[IntegerType]
        #[Min(1)]
        #[MapInputName('numeroConvenio')]
        #[MapOutputName('numeroConvenio')]
        public int $numeroConvenio,

        #[Required]
        #[IntegerType]
        #[Min(1)]
        #[MapInputName('numeroCarteira')]
        #[MapOutputName('numeroCarteira')]
        public int $numeroCarteira,

        #[Required]
        #[IntegerType]
        #[Min(1)]
        #[MapInputName('numeroVariacaoCarteira')]
        #[MapOutputName('numeroVariacaoCarteira')]
        public int $numeroVariacaoCarteira,

        #[Required]
        #[IntegerType]
        #[In([1, 4])]
        #[MapInputName('codigoModalidade')]
        #[MapOutputName('codigoModalidade')]
        public int $codigoModalidade,

        #[Required]
        #[StringType]
        #[Date]
        #[MapInputName('dataEmissao')]
        #[MapOutputName('dataEmissao')]
        public string $dataEmissao,

        #[Required]
        #[StringType]
        #[Date]
        #[MapInputName('dataVencimento')]
        #[MapOutputName('dataVencimento')]
        public string $dataVencimento,

        #[Required]
        #[Numeric]
        #[Min(0.01)]
        #[MapInputName('valorOriginal')]
        #[MapOutputName('valorOriginal')]
        public float $valorOriginal,

        #[Required]
        #[IntegerType]
        #[Min(1)]
        #[Max(99)]
        #[MapInputName('codigoTipoTitulo')]
        #[MapOutputName('codigoTipoTitulo')]
        public int $codigoTipoTitulo,

        #[Required]
        public PagadorData $pagador,

        #[StringType]
        #[Max(100)]
        #[MapInputName('descricaoTipoTitulo')]
        #[MapOutputName('descricaoTipoTitulo')]
        public string $descricaoTipoTitulo = '',

        #[StringType]
        #[Max(15)]
        #[MapInputName('numeroTituloBeneficiario')]
        #[MapOutputName('numeroTituloBeneficiario')]
        public string $numeroTituloBeneficiario = '',

        #[StringType]
        #[Max(50)]
        #[MapInputName('numeroTituloCliente')]
        #[MapOutputName('numeroTituloCliente')]
        public string $numeroTituloCliente = '',

        #[Numeric]
        #[Min(0)]
        #[MapInputName('valorAbatimento')]
        #[MapOutputName('valorAbatimento')]
        public float $valorAbatimento = 0,

        #[IntegerType]
        #[Min(0)]
        #[MapInputName('quantidadeDiasProtesto')]
        #[MapOutputName('quantidadeDiasProtesto')]
        public int $quantidadeDiasProtesto = 0,

        #[IntegerType]
        #[Min(0)]
        #[MapInputName('quantidadeDiasNegativacao')]
        #[MapOutputName('quantidadeDiasNegativacao')]
        public int $quantidadeDiasNegativacao = 0,

        #[StringType]
        #[Max(500)]
        #[MapInputName('mensagemBloquetoOcorrencia')]
        #[MapOutputName('mensagemBloquetoOcorrencia')]
        public string $mensagemBloquetoOcorrencia = '',

        #[StringType]
        #[In(['S', 'N'])]
        #[MapInputName('indicadorPix')]
        #[MapOutputName('indicadorPix')]
        public string $indicadorPix = 'S',

        #[StringType]
        #[In(['S', 'N'])]
        #[MapInputName('indicadorAceiteTituloVencido')]
        #[MapOutputName('indicadorAceiteTituloVencido')]
        public string $indicadorAceiteTituloVencido = 'N',

        #[StringType]
        #[In(['S', 'N'])]
        #[MapInputName('indicadorPermissaoRecebimentoParcial')]
        #[MapOutputName('indicadorPermissaoRecebimentoParcial')]
        public string $indicadorPermissaoRecebimentoParcial = 'N',

        #[IntegerType]
        #[Min(0)]
        #[Max(999)]
        #[MapInputName('numeroDiasLimiteRecebimento')]
        #[MapOutputName('numeroDiasLimiteRecebimento')]
        public int $numeroDiasLimiteRecebimento = 0,

        #[StringType]
        #[In(['A', 'N'])]
        #[MapInputName('codigoAceite')]
        #[MapOutputName('codigoAceite')]
        public string $codigoAceite = 'N',

        #[StringType]
        #[Max(50)]
        #[MapInputName('orgaoNegativador')]
        #[MapOutputName('orgaoNegativador')]
        public string $orgaoNegativador = '',

        public ?BeneficiarioData $beneficiarioFinal = null,
        public ?DescontoData $desconto = null,
        public ?DescontoData $segundoDesconto = null,
        public ?DescontoData $terceiroDesconto = null,
        public ?JurosMoraData $jurosMora = null,
        public ?MultaData $multa = null,
    ) {}

    public static function builder(): BoletoDataBuilder
    {
        return new BoletoDataBuilder();
    }

    public function getCodigoModalidadeEnum(): CodigoModalidade
    {
        return CodigoModalidade::from($this->codigoModalidade);
    }

    public function getTipoTituloEnum(): TipoTitulo
    {
        return TipoTitulo::from($this->codigoTipoTitulo);
    }

    public function isPixEnabled(): bool
    {
        return $this->indicadorPix === 'S';
    }

    public function isAceiteTituloVencido(): bool
    {
        return $this->indicadorAceiteTituloVencido === 'S';
    }

    public function isRecebimentoParcialPermitido(): bool
    {
        return $this->indicadorPermissaoRecebimentoParcial === 'S';
    }

    public function isAceite(): bool
    {
        return $this->codigoAceite === 'A';
    }

    public function getDataVencimentoDateTime(): \DateTime
    {
        return \DateTime::createFromFormat('d.m.Y', $this->dataVencimento);
    }

    public function getDataEmissaoDateTime(): \DateTime
    {
        return \DateTime::createFromFormat('d.m.Y', $this->dataEmissao);
    }

    public function isVencido(): bool
    {
        $dataVencimento = $this->getDataVencimentoDateTime();
        $hoje = new \DateTime();
        
        return $dataVencimento < $hoje;
    }

    public function getDiasParaVencimento(): int
    {
        $dataVencimento = $this->getDataVencimentoDateTime();
        $hoje = new \DateTime();
        
        $diff = $hoje->diff($dataVencimento);
        return $diff->invert ? -$diff->days : $diff->days;
    }

    public function getValorComDesconto(): float
    {
        $valor = $this->valorOriginal;

        if ($this->desconto) {
            $valor -= $this->desconto->calcularDesconto($this->valorOriginal);
        }

        if ($this->segundoDesconto) {
            $valor -= $this->segundoDesconto->calcularDesconto($this->valorOriginal);
        }

        if ($this->terceiroDesconto) {
            $valor -= $this->terceiroDesconto->calcularDesconto($this->valorOriginal);
        }

        return max(0, $valor - $this->valorAbatimento);
    }

    public function getValorComJurosEMulta(?int $diasAtraso = null): float
    {
        if (!$this->isVencido() && $diasAtraso === null) {
            return $this->getValorComDesconto();
        }

        $diasAtraso = $diasAtraso ?? abs($this->getDiasParaVencimento());
        $valor = $this->getValorComDesconto();

        if ($this->multa && $diasAtraso > 0) {
            $valor += $this->multa->calcularMulta($this->valorOriginal);
        }

        if ($this->jurosMora && $diasAtraso > 0) {
            $valor += $this->jurosMora->calcularJuros($this->valorOriginal, $diasAtraso);
        }

        return $valor;
    }

    public function toApiArray(): array
    {
        // Implementação manual para evitar problemas de compatibilidade
        $data = [
            'numeroConvenio' => $this->numeroConvenio,
            'numeroCarteira' => $this->numeroCarteira,
            'numeroVariacaoCarteira' => $this->numeroVariacaoCarteira,
            'codigoModalidade' => $this->codigoModalidade,
            'dataEmissao' => $this->dataEmissao,
            'dataVencimento' => $this->dataVencimento,
            'valorOriginal' => $this->valorOriginal,
            'codigoTipoTitulo' => $this->codigoTipoTitulo,
            'pagador' => $this->pagador ? [
                'tipoInscricao' => $this->pagador->tipoInscricao,
                'numeroInscricao' => $this->pagador->numeroInscricao,
                'nome' => $this->pagador->nome,
                'endereco' => $this->pagador->endereco,
                'cep' => $this->pagador->cep,
                'cidade' => $this->pagador->cidade,
                'bairro' => $this->pagador->bairro,
                'uf' => $this->pagador->uf,
                'telefone' => $this->pagador->telefone,
            ] : null,
        ];

        // Adicionar campos opcionais se não estiverem vazios
        if (!empty($this->descricaoTipoTitulo)) {
            $data['descricaoTipoTitulo'] = $this->descricaoTipoTitulo;
        }
        
        if (!empty($this->numeroTituloBeneficiario)) {
            $data['numeroTituloBeneficiario'] = $this->numeroTituloBeneficiario;
        }
        
        if (!empty($this->numeroTituloCliente)) {
            $data['numeroTituloCliente'] = $this->numeroTituloCliente;
        }
        
        if ($this->valorAbatimento > 0) {
            $data['valorAbatimento'] = $this->valorAbatimento;
        }
        
        if ($this->quantidadeDiasProtesto > 0) {
            $data['quantidadeDiasProtesto'] = $this->quantidadeDiasProtesto;
        }
        
        if ($this->quantidadeDiasNegativacao > 0) {
            $data['quantidadeDiasNegativacao'] = $this->quantidadeDiasNegativacao;
        }
        
        if (!empty($this->mensagemBloquetoOcorrencia)) {
            $data['mensagemBloquetoOcorrencia'] = $this->mensagemBloquetoOcorrencia;
        }
        
        if ($this->indicadorPix !== 'S') {
            $data['indicadorPix'] = $this->indicadorPix;
        } else {
            $data['indicadorPix'] = $this->indicadorPix;
        }
        
        if ($this->indicadorAceiteTituloVencido !== 'N') {
            $data['indicadorAceiteTituloVencido'] = $this->indicadorAceiteTituloVencido;
        }
        
        if ($this->indicadorPermissaoRecebimentoParcial !== 'N') {
            $data['indicadorPermissaoRecebimentoParcial'] = $this->indicadorPermissaoRecebimentoParcial;
        }
        
        if ($this->numeroDiasLimiteRecebimento > 0) {
            $data['numeroDiasLimiteRecebimento'] = $this->numeroDiasLimiteRecebimento;
        }
        
        if ($this->codigoAceite !== 'N') {
            $data['codigoAceite'] = $this->codigoAceite;
        }

        // Adicionar objetos complexos se existirem
        if ($this->beneficiarioFinal) {
            $data['beneficiarioFinal'] = [
                'tipoInscricao' => $this->beneficiarioFinal->tipoInscricao,
                'numeroInscricao' => $this->beneficiarioFinal->numeroInscricao,
                'nome' => $this->beneficiarioFinal->nome,
                'endereco' => $this->beneficiarioFinal->endereco,
                'cep' => $this->beneficiarioFinal->cep,
                'cidade' => $this->beneficiarioFinal->cidade,
                'bairro' => $this->beneficiarioFinal->bairro,
                'uf' => $this->beneficiarioFinal->uf,
                'telefone' => $this->beneficiarioFinal->telefone,
            ];
        }

        if ($this->desconto) {
            $data['desconto'] = [
                'tipo' => $this->desconto->tipo,
                'dataExpiracao' => $this->desconto->dataExpiracao,
                'porcentagem' => $this->desconto->porcentagem,
                'valor' => $this->desconto->valor,
            ];
        }

        if ($this->segundoDesconto) {
            $data['segundoDesconto'] = [
                'tipo' => $this->segundoDesconto->tipo,
                'dataExpiracao' => $this->segundoDesconto->dataExpiracao,
                'porcentagem' => $this->segundoDesconto->porcentagem,
                'valor' => $this->segundoDesconto->valor,
            ];
        }

        if ($this->terceiroDesconto) {
            $data['terceiroDesconto'] = [
                'tipo' => $this->terceiroDesconto->tipo,
                'dataExpiracao' => $this->terceiroDesconto->dataExpiracao,
                'porcentagem' => $this->terceiroDesconto->porcentagem,
                'valor' => $this->terceiroDesconto->valor,
            ];
        }

        if ($this->jurosMora) {
            $data['jurosMora'] = [
                'tipo' => $this->jurosMora->tipo,
                'porcentagem' => $this->jurosMora->porcentagem,
                'valor' => $this->jurosMora->valor,
            ];
        }

        if ($this->multa) {
            $data['multa'] = [
                'tipo' => $this->multa->tipo,
                'data' => $this->multa->data,
                'porcentagem' => $this->multa->porcentagem,
                'valor' => $this->multa->valor,
            ];
        }

        return $data;
    }
}

class BoletoDataBuilder
{
    private array $data = [];

    public function __construct()
    {
        $this->data['indicadorPix'] = 'S';
        $this->data['indicadorAceiteTituloVencido'] = 'N';
        $this->data['indicadorPermissaoRecebimentoParcial'] = 'N';
        $this->data['codigoAceite'] = 'N';
        $this->data['valorAbatimento'] = 0;
        $this->data['quantidadeDiasProtesto'] = 0;
        $this->data['quantidadeDiasNegativacao'] = 0;
        $this->data['numeroDiasLimiteRecebimento'] = 0;
    }

    public function numeroConvenio(int $numeroConvenio): self
    {
        $this->data['numeroConvenio'] = $numeroConvenio;
        return $this;
    }

    public function numeroCarteira(int $numeroCarteira): self
    {
        $this->data['numeroCarteira'] = $numeroCarteira;
        return $this;
    }

    public function numeroVariacaoCarteira(int $numeroVariacaoCarteira): self
    {
        $this->data['numeroVariacaoCarteira'] = $numeroVariacaoCarteira;
        return $this;
    }

    public function codigoModalidade(CodigoModalidade|int $codigoModalidade): self
    {
        $this->data['codigoModalidade'] = $codigoModalidade instanceof CodigoModalidade 
            ? $codigoModalidade->value 
            : $codigoModalidade;
        return $this;
    }

    public function dataEmissao(string $dataEmissao): self
    {
        $this->data['dataEmissao'] = $dataEmissao;
        return $this;
    }

    public function dataVencimento(string $dataVencimento): self
    {
        $this->data['dataVencimento'] = $dataVencimento;
        return $this;
    }

    public function valorOriginal(float $valorOriginal): self
    {
        $this->data['valorOriginal'] = $valorOriginal;
        return $this;
    }

    public function codigoTipoTitulo(TipoTitulo|int $codigoTipoTitulo): self
    {
        $this->data['codigoTipoTitulo'] = $codigoTipoTitulo instanceof TipoTitulo 
            ? $codigoTipoTitulo->value 
            : $codigoTipoTitulo;
        return $this;
    }

    public function pagador(PagadorData $pagador): self
    {
        $this->data['pagador'] = $pagador;
        return $this;
    }

    public function descricaoTipoTitulo(string $descricaoTipoTitulo): self
    {
        $this->data['descricaoTipoTitulo'] = $descricaoTipoTitulo;
        return $this;
    }

    public function numeroTituloBeneficiario(string $numeroTituloBeneficiario): self
    {
        $this->data['numeroTituloBeneficiario'] = $numeroTituloBeneficiario;
        return $this;
    }

    public function numeroTituloCliente(string $numeroTituloCliente): self
    {
        $this->data['numeroTituloCliente'] = $numeroTituloCliente;
        return $this;
    }

    public function valorAbatimento(float $valorAbatimento): self
    {
        $this->data['valorAbatimento'] = $valorAbatimento;
        return $this;
    }

    public function quantidadeDiasProtesto(int $quantidadeDiasProtesto): self
    {
        $this->data['quantidadeDiasProtesto'] = $quantidadeDiasProtesto;
        return $this;
    }

    public function quantidadeDiasNegativacao(int $quantidadeDiasNegativacao): self
    {
        $this->data['quantidadeDiasNegativacao'] = $quantidadeDiasNegativacao;
        return $this;
    }

    public function mensagemBloquetoOcorrencia(string $mensagemBloquetoOcorrencia): self
    {
        $this->data['mensagemBloquetoOcorrencia'] = $mensagemBloquetoOcorrencia;
        return $this;
    }

    public function beneficiarioFinal(BeneficiarioData $beneficiarioFinal): self
    {
        $this->data['beneficiarioFinal'] = $beneficiarioFinal;
        return $this;
    }

    public function desconto(DescontoData $desconto): self
    {
        $this->data['desconto'] = $desconto;
        return $this;
    }

    public function segundoDesconto(DescontoData $segundoDesconto): self
    {
        $this->data['segundoDesconto'] = $segundoDesconto;
        return $this;
    }

    public function terceiroDesconto(DescontoData $terceiroDesconto): self
    {
        $this->data['terceiroDesconto'] = $terceiroDesconto;
        return $this;
    }

    public function jurosMora(JurosMoraData $jurosMora): self
    {
        $this->data['jurosMora'] = $jurosMora;
        return $this;
    }

    public function multa(MultaData $multa): self
    {
        $this->data['multa'] = $multa;
        return $this;
    }

    public function indicadorPix(bool $indicadorPix): self
    {
        $this->data['indicadorPix'] = $indicadorPix ? 'S' : 'N';
        return $this;
    }

    public function indicadorAceiteTituloVencido(bool $indicador): self
    {
        $this->data['indicadorAceiteTituloVencido'] = $indicador ? 'S' : 'N';
        return $this;
    }

    public function indicadorPermissaoRecebimentoParcial(bool $indicador): self
    {
        $this->data['indicadorPermissaoRecebimentoParcial'] = $indicador ? 'S' : 'N';
        return $this;
    }

    public function numeroDiasLimiteRecebimento(int $numeroDias): self
    {
        $this->data['numeroDiasLimiteRecebimento'] = $numeroDias;
        return $this;
    }

    public function codigoAceite(bool $aceite): self
    {
        $this->data['codigoAceite'] = $aceite ? 'A' : 'N';
        return $this;
    }

    public function orgaoNegativador(string $orgaoNegativador): self
    {
        $this->data['orgaoNegativador'] = $orgaoNegativador;
        return $this;
    }

    public function build(): BoletoData
    {
        // Validate required fields
        $requiredFields = [
            'numeroConvenio',
            'numeroCarteira', 
            'numeroVariacaoCarteira',
            'codigoModalidade',
            'dataEmissao',
            'dataVencimento',
            'valorOriginal',
            'codigoTipoTitulo',
            'pagador'
        ];

        foreach ($requiredFields as $field) {
            if (!array_key_exists($field, $this->data)) {
                throw new \InvalidArgumentException("Campo obrigatório não informado: {$field}");
            }
        }

        return new BoletoData(
            numeroConvenio: $this->data['numeroConvenio'],
            numeroCarteira: $this->data['numeroCarteira'],
            numeroVariacaoCarteira: $this->data['numeroVariacaoCarteira'],
            codigoModalidade: $this->data['codigoModalidade'],
            dataEmissao: $this->data['dataEmissao'],
            dataVencimento: $this->data['dataVencimento'],
            valorOriginal: $this->data['valorOriginal'],
            codigoTipoTitulo: $this->data['codigoTipoTitulo'],
            pagador: $this->data['pagador'],
            descricaoTipoTitulo: $this->data['descricaoTipoTitulo'] ?? '',
            numeroTituloBeneficiario: $this->data['numeroTituloBeneficiario'] ?? '',
            numeroTituloCliente: $this->data['numeroTituloCliente'] ?? '',
            valorAbatimento: $this->data['valorAbatimento'] ?? 0,
            quantidadeDiasProtesto: $this->data['quantidadeDiasProtesto'] ?? 0,
            quantidadeDiasNegativacao: $this->data['quantidadeDiasNegativacao'] ?? 0,
            mensagemBloquetoOcorrencia: $this->data['mensagemBloquetoOcorrencia'] ?? '',
            indicadorPix: $this->data['indicadorPix'] ?? 'S',
            indicadorAceiteTituloVencido: $this->data['indicadorAceiteTituloVencido'] ?? 'N',
            indicadorPermissaoRecebimentoParcial: $this->data['indicadorPermissaoRecebimentoParcial'] ?? 'N',
            numeroDiasLimiteRecebimento: $this->data['numeroDiasLimiteRecebimento'] ?? 0,
            codigoAceite: $this->data['codigoAceite'] ?? 'N',
            orgaoNegativador: $this->data['orgaoNegativador'] ?? '',
            beneficiarioFinal: $this->data['beneficiarioFinal'] ?? null,
            desconto: $this->data['desconto'] ?? null,
            segundoDesconto: $this->data['segundoDesconto'] ?? null,
            terceiroDesconto: $this->data['terceiroDesconto'] ?? null,
            jurosMora: $this->data['jurosMora'] ?? null,
            multa: $this->data['multa'] ?? null,
        );
    }
}