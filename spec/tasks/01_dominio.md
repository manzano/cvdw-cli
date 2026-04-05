# 01 - Camada de Dominio

## Objetivo

Construir a camada de dominio pura, sem nenhuma dependencia externa. Esta camada contem entidades, value objects, interfaces de repositorios e politicas de dominio. Tudo aqui e agnositco de framework e totalmente testavel.

**Lembrete:** Todo codigo em portugues. Documentacao em portugues.

---

## Entidades

### `ObjetoSync`

Representa um objeto da API carregado do YAML (ex: "reservas", "leads").

```php
namespace CvdwCli\Dominio\Entidade;

final class ObjetoSync
{
    public function __construct(
        private readonly string $nome,
        private readonly string $caminhoApi,
        private readonly string $nomeTabela,
        private readonly string $descricao,
        private readonly array $campos,           // Campo[]
        private readonly array $parametros,
    ) {}

    public function temSubTabelas(): bool;
    public function obterSubTabelas(): array;         // ObjetoSync[] para arrays aninhados
    public function obterCamposSensiveis(): array;   // Campo[] onde sensivel=true
    public function obterCampo(string $nome): ?Campo;
}
```

### `Campo`

Representa um campo individual na definicao de um objeto.

```php
namespace CvdwCli\Dominio\Entidade;

final class Campo
{
    public function __construct(
        private readonly string $nome,
        private readonly TipoCampo $tipo,
        private readonly ?int $tamanho,
        private readonly ?string $descricao,
        private readonly bool $sensivel,
        private readonly bool $nulavel,
        private readonly ?string $prefixo,
        private readonly ?string $sufixo,
    ) {}

    public function obterNomeColuna(): string;       // sanitizado, max 60 chars
    public function ehSubTabela(): bool;             // tipo e array/object
}
```

### `RequisicaoApi`

Rastreia uma requisicao individual da API para rate limiting.

```php
namespace CvdwCli\Dominio\Entidade;

final class RequisicaoApi
{
    public function __construct(
        private ?int $id,
        private readonly string $objeto,
        private readonly \DateTimeImmutable $iniciadoEm,
        private ?\DateTimeImmutable $concluidoEm = null,
        private ?int $quantidadeRegistros = null,
        private ?int $statusHttp = null,
    ) {}

    public function concluir(int $quantidadeRegistros, int $statusHttp): void;
    public function obterDuracao(): ?float;
}
```

---

## Objetos de Valor

### `Referencia`

Identificador unico do registro vindo da API.

```php
namespace CvdwCli\Dominio\ObjetoValor;

final readonly class Referencia
{
    public function __construct(
        public string $valor,
        public \DateTimeImmutable $data,
    ) {}
}
```

### `Paginacao`

Estado de paginacao da API.

```php
namespace CvdwCli\Dominio\ObjetoValor;

final readonly class Paginacao
{
    public const MAXIMO_POR_PAGINA = 500;

    public function __construct(
        public int $paginaAtual,
        public int $totalPaginas,
        public int $totalRegistros,
        public int $porPagina,
    ) {}

    public function temProximaPagina(): bool;
    public function proximaPagina(): self;
}
```

### `Credenciais`

Autenticacao da API.

```php
namespace CvdwCli\Dominio\ObjetoValor;

final readonly class Credenciais
{
    public function __construct(
        public string $subdominio,
        public string $email,
        public string $token,
    ) {}

    public function obterUrlBase(): string;  // https://{subdominio}.cvcrm.com.br/api/v1/cvdw
    public function estaCompleto(): bool;
}
```

### `ConfiguracaoBanco`

```php
namespace CvdwCli\Dominio\ObjetoValor;

final readonly class ConfiguracaoBanco
{
    public function __construct(
        public DriverBanco $driver,
        public string $host,
        public int $porta,
        public string $banco,
        public string $usuario,
        public string $senha,
        public string $esquema,
    ) {}
}
```

### `ResultadoSync`

Resultado da sincronizacao de um objeto.

```php
namespace CvdwCli\Dominio\ObjetoValor;

final class ResultadoSync
{
    public function __construct(
        private int $inseridos = 0,
        private int $atualizados = 0,
        private int $erros = 0,
        private int $paginasProcessadas = 0,
    ) {}

    public function registrarInsercao(): void;
    public function registrarAtualizacao(): void;
    public function registrarErro(): void;
    public function avancarPagina(): void;
    public function atingiuLimiteErros(int $limite = 3): bool;
}
```

---

## Enums

### `TipoCampo`

```php
namespace CvdwCli\Dominio\ObjetoValor;

enum TipoCampo: string
{
    case Texto = 'string';
    case Inteiro = 'int';
    case Decimal = 'number';
    case DataHora = 'datetime';
    case TextoLongo = 'text';
    case Booleano = 'boolean';
    case SubTabela = 'array';

    public function paraTypeDoctrine(): string;
    public function ehEscalar(): bool;
}
```

### `DriverBanco`

```php
namespace CvdwCli\Dominio\ObjetoValor;

enum DriverBanco: string
{
    case MySQL = 'pdo_mysql';
    case PostgreSQL = 'pdo_pgsql';
    case SQLServer = 'sqlsrv';

    public function portaPadrao(): int;
    public function nomeExibicao(): string;
}
```

### `MetodoAnonimizacao`

```php
namespace CvdwCli\Dominio\ObjetoValor;

enum MetodoAnonimizacao: string
{
    case Asteriscos = 'asterisks';
    case Hash = 'hash';
    case Nenhum = 'none';
}
```

---

## Interfaces de Repositorio

```php
namespace CvdwCli\Dominio\Repositorio;

interface RepositorioObjetoInterface
{
    /** Carrega todos os objetos de sincronizacao das definicoes YAML */
    public function buscarTodos(): array;
    public function buscarPorNome(string $nome): ?ObjetoSync;
    public function buscarPorNomes(array $nomes): array;
}
```

```php
namespace CvdwCli\Dominio\Repositorio;

interface RepositorioRegistroInterface
{
    public function existe(string $tabela, string $referencia): bool;
    public function inserir(string $tabela, array $dados): void;
    public function atualizar(string $tabela, string $referencia, array $dados): void;
    public function obterUltimaDataReferencia(string $tabela): ?\DateTimeImmutable;
}
```

```php
namespace CvdwCli\Dominio\Repositorio;

interface RepositorioLogRequisicaoInterface
{
    public function registrar(RequisicaoApi $requisicao): int;
    public function concluir(int $id, int $quantidadeRegistros, int $statusHttp): void;
    public function contarRequisicoesDesde(\DateTimeImmutable $desde): int;
    public function obterSegundosDesde20aRequisicao(): ?int;
    public function limparAntigosDe(int $dias = 30): int;
}
```

```php
namespace CvdwCli\Dominio\Repositorio;

interface RepositorioEsquemaInterface
{
    public function tabelaExiste(string $tabela): bool;
    public function criarTabela(ObjetoSync $objeto): void;
    public function sincronizarEsquema(ObjetoSync $objeto): array;
    public function truncarTabela(string $tabela): void;
    public function removerTabela(string $tabela): void;
    public function listarTabelas(): array;
}
```

---

## Servicos de Dominio

### `PoliticaRateLimit`

Logica pura, sem I/O. Decide se deve esperar e por quanto tempo.

```php
namespace CvdwCli\Dominio\Servico;

final class PoliticaRateLimit
{
    public const MAXIMO_REQUISICOES_POR_MINUTO = 20;
    public const JANELA_SEGUNDOS = 60;
    public const ATRASO_SEGURANCA = 3;

    public function calcularSegundosEspera(int $requisicoesNoUltimoMinuto, ?int $segundosDesde20aRequisicao): int;
    public function deveEsperar(int $requisicoesNoUltimoMinuto, ?int $segundosDesde20aRequisicao): bool;
}
```

A regra: se temos 20+ requisicoes no ultimo minuto e a 20a foi ha menos de 60s, esperamos `(60 - segundos_desde_a_20a + 3)` segundos.

### `SanitizadorDados`

Transforma/limpa dados antes de persistir.

```php
namespace CvdwCli\Dominio\Servico;

final class SanitizadorDados
{
    public function sanitizarValor(mixed $valor, Campo $campo): mixed;
    public function anonimizar(string $valor, MetodoAnonimizacao $metodo): string;
    public function limparData(string $valor): ?string;
    public function truncarParaTamanho(string $valor, int $tamanhoMaximo): string;
}
```

### `ResolvedorNomeColuna`

Geracao deterministica de nomes de coluna a partir de nomes de campo.

```php
namespace CvdwCli\Dominio\Servico;

final class ResolvedorNomeColuna
{
    public const TAMANHO_MAXIMO = 60;

    public function resolver(string $nomeCampo, ?string $prefixo = null, ?string $sufixo = null): string;
}
```

---

## Excecoes de Dominio

```php
namespace CvdwCli\Dominio\Excecao;

class ExcecaoSync extends \RuntimeException {}
class RateLimitExcedidoException extends ExcecaoSync {}
class ObjetoInvalidoException extends \InvalidArgumentException {}
class FalhaConexaoException extends \RuntimeException {}
class LimiteTempoExecucaoException extends ExcecaoSync {}
```

---

## Testes

```
tests/Unit/Dominio/
├── Entidade/
│   ├── ObjetoSyncTest.php
│   ├── CampoTest.php
│   └── RequisicaoApiTest.php
├── ObjetoValor/
│   ├── PaginacaoTest.php
│   ├── CredenciaisTest.php
│   ├── ResultadoSyncTest.php
│   └── TipoCampoTest.php
├── Servico/
│   ├── PoliticaRateLimitTest.php
│   ├── SanitizadorDadosTest.php
│   └── ResolvedorNomeColunaTest.php
```

### Cenarios chave de teste

**PoliticaRateLimit:**
- 0 requisicoes -> espera 0
- 19 requisicoes -> espera 0
- 20 requisicoes, 30s atras -> espera 33s (60 - 30 + 3)
- 20 requisicoes, 61s atras -> espera 0
- null para segundosDesde20a -> espera 0

**SanitizadorDados:**
- Anonimizacao com asteriscos preserva inicio/fim
- Anonimizacao com hash retorna hash deterministico
- Datas `0000-00-00` viram null
- Valores truncados ao tamanho maximo
- Strings vazias viram null

**Paginacao:**
- temProximaPagina true quando paginaAtual < totalPaginas
- proximaPagina incrementa paginaAtual
- MAXIMO_POR_PAGINA e 500

---

## Criterios de Aceite

- [ ] Todas as entidades, objetos de valor e enums criados
- [ ] Todas as interfaces de repositorio definidas
- [ ] PoliticaRateLimit totalmente testado (100% cobertura)
- [ ] SanitizadorDados totalmente testado
- [ ] ResolvedorNomeColuna totalmente testado
- [ ] Zero dependencias externas no namespace `Dominio/`
- [ ] PHPStan level 8 passa
- [ ] Todas as classes sao `final` ou `readonly` onde aplicavel
