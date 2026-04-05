# 05 - Infraestrutura: Gerenciador de Schema & Repositorio de Registros

## Objetivo

Implementar o gerenciamento de schema do banco (criar/alterar/apagar tabelas a partir das definicoes YAML) e o repositorio de registros (insert/update/verificar existencia). Implementam as interfaces do dominio.

---

## Gerenciador de Schema

### `GerenciadorSchemaDoctrine`

```php
namespace CvdwCli\Infraestrutura\BancoDados;

final class GerenciadorSchemaDoctrine implements GerenciadorSchemaInterface
{
    public function tabelaExiste(string $tabela): bool;
    public function criarTabela(ObjetoSincronizacao $objeto): void;
    public function criarTodasTabelas(array $objetos): RelatorioSchema;
    public function sincronizarSchema(ObjetoSincronizacao $objeto): array;
    public function truncarTabela(string $tabela): void;
    public function removerTabela(string $tabela): void;
    public function listarTabelas(): array;
    public function limparDataReferencia(string $tabela): void;
    public function garantirTabelaRequisicoes(): void;
}
```

### `RelatorioSchema`

Resultado das operacoes de schema para exibicao na UI.

```php
final class RelatorioSchema
{
    public function adicionarCriada(string $tabela): void;
    public function adicionarIgnorada(string $tabela, string $motivo): void;
    public function adicionarColunaAdicionada(string $tabela, string $coluna): void;
    public function adicionarColunaModificada(string $tabela, string $coluna): void;
    public function adicionarColunaRemovida(string $tabela, string $coluna): void;
    public function adicionarErro(string $tabela, string $mensagem): void;
    public function obterMudancas(): array;
    public function temErros(): bool;
    public function obterResumo(): array;
}
```

### Melhorias chave em relacao a v1

1. **InnoDB ao inves de MyISAM** - transacoes, crash recovery
2. **Sem DROP+CREATE para `_requests`** - usa `garantirTabelaRequisicoes()` que so cria se nao existir
3. **Todo SQL usa `quoteIdentifier()`** - sem SQL injection via nomes de tabela/coluna
4. **Sem `echo`** - erros vao pro `RelatorioSchema`
5. **Nomes de indice deterministicos** - `{tabela}_{coluna}_idx` com sufixo hash pra unicidade

### Considerar UPSERT

Em vez de SELECT + INSERT/UPDATE (2 queries por registro), usar UPSERT (1 query):

**MySQL:**
```sql
INSERT INTO tabela (...) VALUES (...) ON DUPLICATE KEY UPDATE ...
```

**PostgreSQL:**
```sql
INSERT INTO tabela (...) VALUES (...) ON CONFLICT (referencia) DO UPDATE SET ...
```

Reduziria queries no banco em ~50% e eliminaria condicoes de corrida.

---

## Repositorio de Registros

### `RepositorioRegistroDoctrine`

```php
namespace CvdwCli\Infraestrutura\BancoDados;

final class RepositorioRegistroDoctrine implements RepositorioRegistroInterface
{
    public function existe(string $tabela, string $referencia): bool;
    public function inserir(string $tabela, array $dados): void;
    public function atualizar(string $tabela, string $referencia, array $dados): void;
    public function obterUltimaDataReferencia(string $tabela): ?\DateTimeImmutable;
    public function upsert(string $tabela, string $referencia, array $dados): ResultadoUpsert;
}
```

```php
enum ResultadoUpsert
{
    case Inserido;
    case Atualizado;
}
```

---

## Processamento de Sub-tabelas

### `ProcessadorSubTabela`

```php
namespace CvdwCli\Infraestrutura\BancoDados;

final class ProcessadorSubTabela
{
    /**
     * Processa dados de array aninhado em sub-tabelas.
     * 1. Deleta linhas existentes para esta referencia
     * 2. Insere novas linhas
     */
    public function processar(
        string $tabelaPai,
        string $nomeCampo,
        string $referenciaPai,
        string $dataReferenciaPai,
        array $linhas,
        ObjetoSincronizacao $objetoSubTabela,
    ): int;
}
```

---

## Testes

### Gerenciador de Schema
- Criar tabela a partir de ObjetoSincronizacao
- Tabela com sub-tabelas cria tabelas filhas
- sincronizarSchema detecta colunas adicionadas
- sincronizarSchema detecta mudancas de tipo
- quoteIdentifier usado em todo SQL
- garantirTabelaRequisicoes e idempotente (sem DROP)
- Engine InnoDB para MySQL

### Repositorio de Registros
- existe retorna false para registro ausente
- existe retorna true para registro presente
- inserir cria registro
- atualizar modifica registro
- obterUltimaDataReferencia retorna data maxima
- obterUltimaDataReferencia retorna null para tabela vazia
- upsert insere registro novo
- upsert atualiza registro existente

### Sub-tabelas
- processar limpa linhas antigas antes de inserir
- Referencia composta a partir do pai + primeiro valor do campo
- Array vazio e no-op

---

## Criterios de Aceite

- [ ] Gerenciador de schema cria todas as 70+ tabelas corretamente
- [ ] Engine InnoDB para MySQL (nao MyISAM)
- [ ] Todos os nomes de tabela/coluna usam `quoteIdentifier()`
- [ ] Tabela `_requests` criada uma vez (sem DROP+CREATE)
- [ ] UPSERT reduz queries por registro
- [ ] Sub-tabelas tratadas de forma limpa
- [ ] `RelatorioSchema` captura todas as operacoes para a UI
- [ ] Testes de integracao rodam contra MySQL e PostgreSQL
