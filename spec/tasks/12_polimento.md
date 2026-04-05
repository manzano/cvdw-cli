# 12 - Polimento, Logging & Release

## Objetivo

Passada final: logging estruturado, reporte de erros, documentacao e preparacao para release.

---

## Logging Estruturado

### Setup com Monolog

```php
namespace CvdwCli\Infraestrutura\Monitoramento;

final class FabricaLogger
{
    public static function criar(string $diretorioLog, bool $logEmArquivo = false): LoggerInterface;
}
```

- Sempre: stderr para erros
- Opcional: log em arquivo quando flag `--log` ativo

### Pontos de Log

| Evento | Nivel | Dados |
|--------|-------|-------|
| Sync iniciado | INFO | objeto, data_desde, por_pagina |
| Pagina buscada | DEBUG | objeto, pagina, registros, duracao |
| Registro inserido | DEBUG | objeto, referencia |
| Registro alterado | DEBUG | objeto, referencia |
| Erro em registro | ERROR | objeto, referencia, mensagem_erro |
| Espera rate limit | INFO | segundos_espera, qtd_requisicoes |
| Sync concluido | INFO | objeto, inseridos, alterados, erros, duracao |
| Mudanca de schema | INFO | tabela, tipo_mudanca, coluna |
| Config salva | INFO | ambiente, chaves_alteradas |
| Erro de API | ERROR | url, status, corpo_resposta |
| Erro de banco | ERROR | query, mensagem_erro |

---

## Reporte de Erros (Sentry)

```php
namespace CvdwCli\Infraestrutura\Monitoramento;

final class ReportadorErros
{
    public function capturar(\Throwable $e, array $contexto = []): void;
    public function adicionarMigalha(string $message, array $dados = []): void;
}
```

Sentry e opcional, configurado via variavel de ambiente.

---

## Tratamento Global de Erros

Em vez de `exit` e `echo` espalhados, um handler centralizado que:

- Captura excecoes nao tratadas
- Envia para Sentry (se configurado)
- Exibe mensagem amigavel no terminal
- Sugere proximo passo ao usuario

Exemplos:
- `FalhaConexaoException` -> "Nao foi possivel conectar ao banco. Execute `cvdw setup database`."
- `AutenticacaoException` -> "Autenticacao falhou. Execute `cvdw setup api` para atualizar credenciais."
- `LimiteTempoExecucaoException` -> "Limite de tempo atingido. O sync continuara na proxima execucao."

---

## Texto de Ajuda

### Comando raiz (`cvdw`)

```
 CVDW-CLI v2.0.0 — CV CRM Data Warehouse

 Uso:
   cvdw <comando> [opcoes] [argumentos]

 Comandos:
   sync              Sincronizar dados da API CV CRM
   setup             Configurar API, banco e tabelas
   status            Mostrar status do ambiente e conexao
   list:objects      Listar objetos disponiveis da API
   table:status      Visao geral das tabelas do banco
   table:sync        Sincronizar schema com definicoes da API
   table:truncate    Limpar dados das tabelas
   table:drop        Apagar tabelas
   env:list          Listar ambientes configurados
   env:create        Criar novo ambiente
   env:remove        Remover ambiente
   migrate           Migrar do CVDW-CLI v1
   self-update       Atualizar para ultima versao

 Execute "cvdw <comando> --help" para uso detalhado.
```

---

## Checklist de Release

### Antes da v2.0.0

- [ ] Todas as tasks (00-11) concluidas
- [ ] PHPStan level 8 passa
- [ ] PHP-CS-Fixer passa
- [ ] Cobertura de testes >= 85%
- [ ] `cvdw migrate` testado contra instalacao v1
- [ ] Imagem Docker constroi e roda
- [ ] README.md atualizado para v2
- [ ] CHANGELOG.md criado
- [ ] install.sh atualizado

### Versionamento

```php
// src/Infraestrutura/Config/ConfigApp.php
public const VERSAO = '2.0.0';
```

### Release no GitHub

1. Tag: `v2.0.0`
2. Titulo: "CVDW-CLI v2.0.0 - Reescrita Completa"
3. Corpo: guia de migracao + changelog

### Docker Hub

```bash
docker build -t manzano/cvdw-cli:2.0.0 -t manzano/cvdw-cli:latest .
docker push manzano/cvdw-cli:2.0.0
docker push manzano/cvdw-cli:latest
```

---

## Otimizacoes de Performance

1. **UPSERT** reduz queries no banco em ~50%
2. **Conexao unica** (ConnectionPool) ao inves de 3+
3. **Cache de YAML** apos primeiro carregamento
4. **Operacoes em lote** onde possivel (futuro: batch INSERT)
5. **InnoDB** melhor para escritas concorrentes

---

## Bugs da v1 Corrigidos nesta Versao

Todos os bugs identificados na analise de codigo da v1 sao corrigidos pela nova arquitetura:

1. INSERT com erro retornava `true` -> UPSERT com excecoes
2. SQL injection via nomes de tabela -> `quoteIdentifier()` em tudo
3. SSL desabilitado -> Symfony HttpClient com SSL habilitado
4. `exit` abrupto -> excecoes com tratamento global
5. Acesso `->` em array -> tipos fortes com `ApiResponse`
6. Resource leak no cURL -> Symfony HttpClient gerencia recursos
7. Contador duplicado -> `SyncResult` com contadores unicos
8. `return $this->console` como dados -> `ApiResponse` tipado
9. Retry sem return -> fluxo linear com excecoes
10. EnvironmentManager instanciado N vezes -> singleton via DI
11. Multiplas conexoes -> `ConnectionPool`
12. MyISAM -> InnoDB
13. DROP+CREATE `_requisicoes` -> `ensureRequestsTable()`
14. `header_resultado` tipo errado -> `http_status` integer

---

## Criterios de Aceite

- [ ] Logging estruturado com Monolog
- [ ] Sentry para reporte de erros (opcional, via env)
- [ ] Handler global de erros com mensagens amigaveis
- [ ] Texto de ajuda completo para todos os comandos
- [ ] README.md atualizado
- [ ] CHANGELOG.md criado
- [ ] Imagem Docker publicada
- [ ] Release no GitHub criada
- [ ] install.sh atualizado para v2
- [ ] Todos os bugs da v1 corrigidos
