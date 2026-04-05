# 06 - Camada de Aplicacao: Casos de Uso

## Objetivo

Construir a camada de aplicacao que orquestra servicos de dominio e adaptadores de infraestrutura. Cada caso de uso e uma classe unica com um unico metodo publico `executar()`.

---

## Casos de Uso

### `CasoUsoSincronizarObjeto`

O caso de uso central. Busca dados da API e persiste no banco.

```php
namespace CvdwCli\Aplicacao\CasoUso;

final class CasoUsoSincronizarObjeto
{
    public function executar(RequisicaoSincronizarObjeto $requisicao): RespostaSincronizarObjeto;
}
```

#### Fluxo de Execucao

```
1. Determinar data de inicio:
   - Se ignorarDataReferencia -> null (busca tudo)
   - Se $dataDesde informado -> usa ele
   - Senao -> consulta obterUltimaDataReferencia() do banco

2. Buscar primeira pagina da API

3. Se vazio ou erro -> retorna cedo

4. Para cada pagina (1..totalPaginas, limitado por $maximoPaginas):
   a. Verificar limite de tempo
   b. Buscar pagina via RateLimitedClient (rate limit transparente)
   c. Para cada registro na pagina:
      - Sanitizar dados (limpar datas, truncar, anonimizar)
      - UPSERT no banco
      - Processar sub-tabelas se houver
      - Atualizar contadores do ResultadoSync
      - Notificar observador
   d. Verificar limite de erros (3 consecutivos -> aborta)

5. Retornar RespostaSincronizarObjeto
```

### `CasoUsoSincronizarTudo`

Orquestra sincronizacao de todos (ou selecionados) objetos.

```php
final class CasoUsoSincronizarTudo
{
    public function executar(RequisicaoSincronizarTudo $requisicao): RespostaSincronizarTudo;
}
```

O `RespostaSincronizarTudo` agrega resultados de todos os syncs:
- Total de inseridos, alterados, erros
- Objetos que falharam
- Duracao total

### `CasoUsoConfigurarBanco`

Cria todas as tabelas a partir das definicoes de objetos.

```php
final class CasoUsoConfigurarBanco
{
    public function executar(?ObservadorSetup $observador = null): RelatorioSchema;
}
```

### `CasoUsoConfigurar`

Gerencia salvamento de configuracao.

```php
final class CasoUsoConfigurar
{
    public function salvarCredenciaisApi(Credentials $credenciais): bool;
    public function salvarConfigBanco(DatabaseConfig $config): bool;
    public function salvarAnonimizacao(AnonymizationMethod $metodo): void;
    public function testarConexaoApi(Credentials $credenciais): bool;
    public function testarConexaoBanco(DatabaseConfig $config): bool;
}
```

### `CasoUsoVerificarVersao`

```php
final class CasoUsoVerificarVersao
{
    public function executar(): ResultadoVerificacaoVersao;
}
```

---

## Observer para Progresso (desacoplamento da UI)

```php
namespace CvdwCli\Aplicacao\Porta;

interface ObservadorProgressoSync
{
    public function aoSyncIniciado(string $nomeObjeto, int $totalRegistros, int $totalPaginas): void;
    public function aoPaginaIniciada(int $pagina, int $totalPaginas): void;
    public function aoRegistroProcessado(ResultadoSync $resultado): void;
    public function aoPaginaConcluida(int $pagina): void;
    public function aoSyncConcluido(ResultadoSync $resultado): void;
    public function aoErro(string $mensagem): void;
    public function aoAguardarRateLimit(int $segundos): void;
    public function aoContagemRegressivaRateLimit(int $restante): void;
}
```

Isso e crucial: a camada Application **nao sabe** que existe um terminal. Ela so notifica um observador. A camada UI implementa esse observador pra mostrar barra de progresso, contadores, etc.

---

## Controle de Tempo de Execucao

### `TemporizadorExecucao`

Substitui o `exit` do RateLimit por mecanismo baseado em excecoes.

```php
namespace CvdwCli\Aplicacao\Servico;

final class TemporizadorExecucao
{
    public function __construct(
        private readonly ?int $limiteSegundos = null,
    ) {}

    public function obterSegundosDecorridos(): float;
    public function obterSegundosRestantes(): ?float;
    public function temLimiteTempo(): bool;

    /** @throws ExcecaoLimiteTempoExecucao */
    public function verificarLimite(): void;
}
```

---

## Testes

### CasoUsoSincronizarObjeto
- Sincroniza registros: insere novos, atualiza existentes
- Respeita limite de $maximoPaginas
- Respeita limite de tempo (lanca ExcecaoLimiteTempoExecucao)
- Usa $dataDesde quando fornecido
- Usa obterUltimaDataReferencia quando sem $dataDesde
- ignorarDataReferencia busca tudo
- Aborta apos 3 erros consecutivos
- Observador notificado em cada etapa
- Resposta vazia da API retorna cedo
- Sub-tabelas processadas apos insert do pai

### CasoUsoSincronizarTudo
- Sincroniza todos quando nenhum nome dado
- Sincroniza apenas os especificados
- Agrega resultados de todos os syncs
- Continua pro proximo objeto apos falha de um

### TemporizadorExecucao
- Sem limite -> verificarLimite nunca lanca
- Limite excedido -> lanca ExcecaoLimiteTempoExecucao
- obterSegundosDecorridos retorna valor correto

---

## Criterios de Aceite

- [ ] Todos os casos de uso implementados com DTOs apropriados
- [ ] Padrao Observer para desacoplamento da UI
- [ ] TemporizadorExecucao substitui `exit` por excecoes
- [ ] CasoUsoSincronizarObjeto trata fluxo completo de sync
- [ ] CasoUsoSincronizarTudo orquestra multiplos objetos
- [ ] Todos os casos de uso testados com repositorios mockados
- [ ] Zero dependencias de Infrastructure/UI na camada Application
