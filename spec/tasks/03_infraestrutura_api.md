# 03 - Infraestrutura: Cliente API & Rate Limiter

## Objetivo

Construir um cliente HTTP adequado para a API do CV CRM, substituindo cURL cru pelo Symfony HttpClient. Implementar o rate limiter como adaptador de infraestrutura respaldado pela tabela `_requests`.

---

## Cliente da API

### `ClienteCvCrm`

Cliente HTTP limpo com tratamento de erros, verificacao SSL e sem efeitos colaterais.

```php
namespace CvdwCli\Infraestrutura\Api;

final class ClienteCvCrm
{
    public function __construct(
        private readonly HttpClientInterface $clienteHttp,
        private readonly Credenciais $credenciais,
    ) {}

    /**
     * Busca uma pagina de dados da API CVDW.
     * @throws ExcecaoApi em erros HTTP
     * @throws ExcecaoAutenticacao em 401/403
     * @throws ExcecaoRateLimit em 429
     */
    public function buscarPagina(
        string $caminho,
        int $pagina = 1,
        int $porPagina = 500,
        ?\DateTimeImmutable $dataDesde = null,
    ): RespostaApi;

    /** Testa conectividade (nao autenticado) */
    public function ping(): bool;

    /** Testa autenticacao */
    public function autenticar(): bool;

    /** Busca versao mais recente do GitHub */
    public function obterUltimaVersao(): ?string;
}
```

### `RespostaApi`

Objeto de resposta tipado. Acabou o stdClass cru.

```php
namespace CvdwCli\Infraestrutura\Api;

final readonly class RespostaApi
{
    public function __construct(
        public Paginacao $paginacao,
        public array $registros,
        public int $codigoHttpStatus,
        public float $duracao,
    ) {}

    public function estaVazia(): bool;
    public function obterContagemRegistros(): int;
}
```

### Hierarquia de excecoes

```php
class ExcecaoApi extends \RuntimeException {
    public readonly int $codigoHttpStatus;
    public readonly ?string $corpoResposta;
}
class ExcecaoAutenticacao extends ExcecaoApi {}   // 401, 403
class ExcecaoRateLimit extends ExcecaoApi {}      // 429
class ExcecaoServidor extends ExcecaoApi {}       // 5xx
```

---

## Rate Limiter

### `LimitadorRequisicoesBanco`

Implementa `InterfaceRepositorioLogRequisicoes`. Usa a tabela `_requests`.

```php
namespace CvdwCli\Infraestrutura\BancoDeDados;

final class LimitadorRequisicoesBanco implements InterfaceRepositorioLogRequisicoes
{
    public function __construct(
        private readonly \Doctrine\DBAL\Connection $conexao,
        private readonly AjudantePlataforma $ajudantePlataforma,
    ) {}

    public function registrar(RequisicaoApi $requisicao): int;
    public function completar(int $id, int $contagemRegistros, int $codigoHttpStatus): void;
    public function contarRequisicoesDesdeDe(\DateTimeImmutable $desde): int;
    public function obterSegundosDesdeVigesimaRequisicao(): ?int;
    public function limparAntigosDe(int $dias = 30): int;
}
```

### `AjudantePlataforma`

Encapsula diferencas de dialeto SQL entre MySQL e PostgreSQL.

```php
namespace CvdwCli\Infraestrutura\BancoDeDados;

final class AjudantePlataforma
{
    public function obterExpressaoAgora(): string;
    public function obterDiferencaDataEmSegundos(string $coluna): string;
    public function obterExpressaoIntervalo(int $valor, string $unidade): string;
    public function ehPostgres(): bool;
    public function ehMySQL(): bool;
}
```

### `ClienteComRateLimit` (Decorator)

Envelopa `ClienteCvCrm` e aplica rate limiting de forma transparente.

```php
namespace CvdwCli\Infraestrutura\Api;

final class ClienteComRateLimit
{
    public function __construct(
        private readonly ClienteCvCrm $cliente,
        private readonly InterfaceRepositorioLogRequisicoes $logRequisicoes,
        private readonly PoliticaRateLimit $politica,
        private readonly ?ObservadorRateLimit $observador = null,
    ) {}

    public function buscarPagina(...): RespostaApi
    {
        // 1. Calcula tempo de espera
        // 2. Se precisa esperar, notifica observador e aguarda
        // 3. Registra requisicao no log
        // 4. Faz a requisicao
        // 5. Completa o registro no log
        // 6. Retorna resposta
    }
}
```

### `ObservadorRateLimit` (Interface para UI)

```php
interface ObservadorRateLimit
{
    public function aoEsperar(int $segundos): void;
    public function aoContagemRegressiva(int $restante): void;
    public function aoCompletarRequisicao(RespostaApi $resposta): void;
}
```

A camada CLI implementa isso para mostrar mensagens na barra de progresso.

---

## Schema da tabela `_requests`

```sql
CREATE TABLE _requests (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    object_path     VARCHAR(255) NOT NULL,
    started_at      DATETIME NOT NULL,
    completed_at    DATETIME NULL,
    record_count    INT NULL,
    http_status     INT NULL
);
```

Mudancas em relacao a v1:
- Colunas renomeadas para ingles
- `header_resultado` (tipo integer armazenando texto) removido
- Adicionado `http_status` (inteiro real)
- Engine: InnoDB (nao MyISAM)

---

## Testes

### ClienteCvCrm
- Resposta de sucesso parseada corretamente
- 401 lanca ExcecaoAutenticacao
- 429 lanca ExcecaoRateLimit
- 500 lanca ExcecaoServidor
- Timeout lanca ExcecaoApi
- SSL habilitado (VERIFYPEER=true)
- Parametro dataDesde formatado corretamente

### ClienteComRateLimit
- Espera segundos corretos quando a politica manda esperar
- Sem espera quando abaixo do limite
- Registra requisicao antes de chamar o cliente
- Completa requisicao apos resposta
- Observador notificado da espera/contagem regressiva

### LimitadorRequisicoesBanco (integracao)
- Inserir e completar requisicao
- Contar requisicoes na janela de tempo
- obterSegundosDesdeVigesimaRequisicao com < 20 requisicoes retorna null
- obterSegundosDesdeVigesimaRequisicao com >= 20 retorna valor correto
- Limpar remove registros antigos

---

## Criterios de Aceite

- [ ] `ClienteCvCrm` usa Symfony HttpClient (sem cURL cru)
- [ ] Verificacao SSL habilitada por padrao
- [ ] `ClienteComRateLimit` decora cliente com rate limiting
- [ ] `LimitadorRequisicoesBanco` funciona com MySQL e PostgreSQL
- [ ] Tabela `_requests` criada com nomes de colunas em ingles
- [ ] Sem `echo`, sem `exit`, sem `sleep` na camada de dominio
- [ ] Padrao Observador desacopla UI do rate limit da logica
- [ ] Toda logica de retry retorna a resposta (sem resultados perdidos)
- [ ] Todos os testes passam
