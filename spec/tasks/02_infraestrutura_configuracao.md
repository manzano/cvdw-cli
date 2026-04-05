# 02 - Infraestrutura: Configuracao & Conexao com Banco

## Objetivo

Implementar a camada de configuracao (gerenciamento de ambientes, arquivos .env, multi-ambiente) e a fabrica de conexoes com banco de dados. Estes sao os primeiros adaptadores de infraestrutura.

---

## Sistema de Ambientes

### `CarregadorAmbiente`

Substitui tanto `Ambientes` quanto `EnvironmentManager` por um unico servico limpo.

```php
namespace CvdwCli\Infraestrutura\Configuracao;

final class CarregadorAmbiente
{
    public function __construct(
        private readonly string $caminhoBase,
    ) {}

    /** Carrega variaveis de ambiente do arquivo .env para $_ENV */
    public function carregar(?string $nomeAmbiente = null): void;

    /** Salva variaveis de ambiente no arquivo .env */
    public function salvar(array $variaveis, ?string $nomeAmbiente = null): void;

    /** Lista todos os ambientes disponiveis com metadados */
    public function listarAmbientes(): array;

    /** Cria um novo ambiente em branco */
    public function criar(string $nome): void;

    /** Remove um ambiente */
    public function remover(string $nome): void;

    /** Verifica se ambiente existe */
    public function existe(?string $nome = null): bool;

    /** Resolve o caminho do arquivo .env para um ambiente */
    public function resolverCaminho(?string $nome = null): string;
}
```

### `ConfigApp`

Objeto de configuracao imutavel construido a partir das variaveis de ambiente. Fonte unica da verdade.

```php
namespace CvdwCli\Infraestrutura\Configuracao;

final readonly class ConfigApp
{
    public function __construct(
        public Credenciais $credenciais,
        public ConfigBancoDados $bancoDados,
        public MetodoAnonimizacao $anonimizacao,
        public string $nomeAmbiente,
        public string $versao,
    ) {}

    public static function doAmbiente(): self;
    public function temCredenciaisApi(): bool;
    public function temConfigBancoDados(): bool;
    public function estaCompleto(): bool;
}
```

---

## Conexao com Banco

### `FabricaConexao`

Unico lugar para criar conexoes. Acabou com chamadas `Conexao::conectarDB()` espalhadas.

```php
namespace CvdwCli\Infraestrutura\BancoDados;

final class FabricaConexao
{
    public function criar(ConfigBancoDados $config): \Doctrine\DBAL\Connection;
}
```

Logica:
1. Constroi parametros a partir de `ConfigBancoDados`
2. Para PostgreSQL: define `search_path`, cria schema se necessario
3. Retorna conexao
4. **Sem `echo`, sem saida no console** - lanca excecoes em caso de falha

### `PoolConexao`

Reutiliza uma unica conexao em todos os servicos em vez de criar 3+ conexoes.

```php
namespace CvdwCli\Infraestrutura\BancoDados;

final class PoolConexao
{
    private ?\Doctrine\DBAL\Connection $conexao = null;

    public function __construct(
        private readonly FabricaConexao $fabrica,
        private readonly ConfigApp $config,
    ) {}

    public function obter(): \Doctrine\DBAL\Connection;
    public function fechar(): void;
}
```

Registrar `PoolConexao` no container DI como **singleton**.

---

## Formato do Arquivo de Ambiente

Manter o mesmo formato `.env` para compatibilidade retroativa:

```env
CV_URL=subdominio
CV_EMAIL=usuario@empresa.com
CV_TOKEN=abc123
DB_CONNECTION=pdo_mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=cvdw
DB_USERNAME=usuario
DB_PASSWORD=senha
DB_SCHEMA=
ANONYMIZE=none
ENVIRONMENT=PRD
```

Mudancas em relacao a v1:
- `ANONIMIZAR` -> `ANONYMIZE` (valores: `none`, `asterisks`, `hash`)
- `ANONIMIZAR_TIPO` -> removido (incorporado ao ANONYMIZE)
- `CVDW_AMBIENTE` -> `ENVIRONMENT`
- Manter leitura do formato antigo para compatibilidade de migracao

---

## Testes

### CarregadorAmbiente
- Carrega .env padrao
- Carrega ambiente nomeado
- Salvar e recarregar preserva valores
- Lista ambientes retorna metadados corretos
- Criar novo ambiente cria o arquivo
- Remover ambiente deleta o arquivo
- Ambiente inexistente lanca excecao

### ConfigApp
- doAmbiente() le todas as variaveis
- estaCompleto() retorna false quando faltam campos
- temCredenciaisApi() verifica subdominio + email + token
- Enum de anonimizacao mapeia corretamente de string
- Compatibilidade retroativa: le `ANONIMIZAR=true` + `ANONIMIZAR_TIPO=Hash` como `MetodoAnonimizacao::Hash`

### FabricaConexao
- Cria conexao MySQL com parametros corretos
- Cria conexao PostgreSQL com schema
- Lanca FalhaConexaoException em config invalida
- Portas padrao funcionam por driver

---

## Criterios de Aceite

- [ ] `CarregadorAmbiente` gerencia todas as operacoes de arquivo .env
- [ ] `ConfigApp` e o unico objeto de configuracao usado por todos os servicos
- [ ] `FabricaConexao` cria conexoes sem efeitos colaterais
- [ ] `PoolConexao` garante uma conexao por execucao
- [ ] Formato .env antigo (v1) pode ser lido durante migracao
- [ ] Sem `echo`, sem `exit` - excecoes apropriadas
- [ ] Todos os testes passam
- [ ] PHPStan level 8
