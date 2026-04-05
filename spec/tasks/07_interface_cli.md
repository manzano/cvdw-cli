# 07 - CLI: Comandos & Interface Bonita no Terminal

## Objetivo

Construir uma interface de terminal polida e amigavel. Investir em UX - tabelas bonitas, progresso claro, saida colorida, mensagens uteis. O terminal e a cara do nosso produto.

---

## Principios de Design

1. **Hierarquia clara**: titulos, secoes, subsecoes com estilo consistente
2. **Progresso visivel**: barras de progresso, contadores, ETA
3. **Erros uteis**: explicam o que deu errado E o que fazer em seguida
4. **Cores com significado**: verde=sucesso, amarelo=aviso, vermelho=erro, azul=info, cinza=secundario
5. **Identidade visual**: cabecalho consistente com nome e versao do app
6. **Sem poluicao**: nao imprima o obvio, use espacamento

---

## Estilo Customizado

### `EstiloApp`

Extende `SymfonyStyle` com nossa linguagem visual.

```php
namespace CvdwCli\Interface\Estilo;

final class EstiloApp extends SymfonyStyle
{
    public function banner(string $versao): void;
    public function passo(int $atual, int $total, string $mensagem): void;
    public function resultado(string $rotulo, string $valor, string $cor = 'green'): void;
    public function chaveValor(array $pares): void;
    public function separador(): void;
    public function contagemRegressiva(int $segundos, string $motivo): void;
    public function estatisticas(ResultadoSync $resultado): void;
    public function listaObjetos(array $objetos): void;
}
```

### Exemplo do Banner

```
 ╔══════════════════════════════════════╗
 ║  CVDW-CLI  v2.0.0                   ║
 ║  CV CRM Data Warehouse              ║
 ╚══════════════════════════════════════╝
```

### Formato da Barra de Progresso

```
 Sincronizando: reservas (pagina 3/12)
 ━━━━━━━━━━━━━━━━━━░░░░░░░░░░  42%  [1.247 / 2.891 registros]

   Inseridos: 892    Alterados: 341    Erros: 0
   Tempo: 00:01:34    Velocidade: ~14 registros/seg
```

### Resumo de Estatisticas

```
 ┌─────────────────────────────────────────────┐
 │  Sincronizacao Completa                     │
 ├──────────────┬──────────────┬───────────────┤
 │  Inseridos   │  Alterados   │  Erros        │
 │  892         │  341         │  0            │
 ├──────────────┴──────────────┴───────────────┤
 │  Duracao: 00:01:34    Paginas: 12/12        │
 └─────────────────────────────────────────────┘
```

---

## Comandos

### `ComandoSync` (substitui `Executar`)

```php
#[AsCommand(name: 'sync', description: 'Sincronizar dados da API CV CRM')]
// Aliases: 'executar', 'execute'
```

**Argumentos & Opcoes:**

```
Uso:
  cvdw sync [objetos] [opcoes]

Argumentos:
  objetos              Objeto(s) para sincronizar, separados por + (padrao: interativo)

Opcoes:
  -a, --since          Sincronizar registros a partir da data (Y-m-d ou Y-m-dTH:i:s)
  -f, --full           Ignorar data de referencia, sync completo
  -q, --per-page       Registros por pagina (padrao: 500, max: 500)
  -m, --max-pages      Maximo de paginas a processar
  -t, --time-limit     Limite de tempo em segundos
  -e, --env            Nome do ambiente
      --log            Salvar log de execucao em arquivo
      --no-interaction Executar sem prompts interativos

Exemplos:
  cvdw sync                         # Selecao interativa
  cvdw sync all                     # Sincronizar tudo
  cvdw sync reservas                # Um objeto
  cvdw sync reservas+leads          # Multiplos objetos
  cvdw sync all --since=2024-01-01  # Sync completo a partir de data
  cvdw sync all -t 3600             # Com limite de 1 hora
```

**Fluxo do Modo Interativo:**

```
 CVDW-CLI v2.0.0

 Ambiente: PRODUCAO (acme.cvcrm.com.br)
 Banco: mysql://localhost:3306/cvdw

 O que voce gostaria de sincronizar?

   [1] Todos os objetos (70)
   [2] Escolher por categoria
   [3] Escolher objetos especificos
   [0] Sair

 > 2

 Escolha as categorias:

   [ ] Reservas (13 objetos)
   [ ] Leads (11 objetos)
   [ ] Pessoas (7 objetos)
   [x] Atendimentos (7 objetos)
   ...

 Selecionados: 7 objetos de 1 categoria

 Iniciando sincronizacao...
```

### `ComandoSetup` (substitui `Configurar`)

```php
#[AsCommand(name: 'setup', description: 'Configurar o CVDW-CLI')]
// Aliases: 'configurar', 'config', 'cfg'
```

**Subcomandos (mais limpo que menu numerado):**

```
Uso:
  cvdw setup                    # Assistente de configuracao interativo
  cvdw setup api                # Configurar credenciais da API
  cvdw setup database           # Configurar conexao com banco
  cvdw setup tables             # Criar/atualizar tabelas
  cvdw setup anonymize          # Configurar anonimizacao de dados
  cvdw setup status             # Mostrar configuracao atual
  cvdw setup env:create <nome>  # Criar novo ambiente
  cvdw setup env:list           # Listar ambientes
  cvdw setup env:remove <nome>  # Remover ambiente
```

**Assistente de Primeira Execucao:**

```
 ╔══════════════════════════════════════╗
 ║  Assistente de Configuracao         ║
 ╚══════════════════════════════════════╝

 Passo 1/3: Credenciais da API
 ─────────────────────────────

 Subdominio CV CRM: acme
 ✓ Conectado a acme.cvcrm.com.br

 Email: admin@acme.com
 Token: ****************************************
 ✓ Autenticacao bem sucedida

 Passo 2/3: Banco de Dados
 ─────────────────────────

 Driver [MySQL]:
   [1] MySQL / MariaDB
   [2] PostgreSQL
   [3] SQL Server
 > 1

 Host [localhost]:
 Porta [3306]:
 Banco: cvdw
 Usuario: cvdw_user
 Senha: ********
 ✓ Conectado ao banco de dados

 Passo 3/3: Criar Tabelas
 ────────────────────────

 Criando 70 tabelas...
 ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━  100%

 ✓ 70 tabelas criadas, 15 sub-tabelas criadas

 ╔══════════════════════════════════════╗
 ║  Configuracao completa!             ║
 ║  Execute: cvdw sync                 ║
 ╚══════════════════════════════════════╝
```

### `ComandoStatus`

```
 CVDW-CLI v2.0.0
 ─────────────────

 Ambiente:       producao
 API:            acme.cvcrm.com.br ✓
 Banco:          mysql://localhost:3306/cvdw ✓
 Tabelas:        70 criadas, 15 sub-tabelas
 Anonimizacao:   desligada
 Ultimo sync:    2024-03-15 14:30:00 (2 horas atras)
 Requisicoes:    1.247 nas ultimas 24h
```

### `ComandoListarObjetos`

```
 Objetos Disponiveis (70)
 ────────────────────────

 Reservas (13)
   reservas                    Reservas de unidades
   reservas_associados         Associados das reservas
   reservas_comissoes          Comissoes vinculadas
   ...

 Leads (11)
   leads                       Leads (prospects)
   leads_conversoes            Conversoes de leads
   ...
```

---

## Observer de Progresso no CLI

Implementa `SyncProgressObserver` para conectar casos de uso com a UI do terminal.

```php
namespace CvdwCli\Interface\Console;

final class ObservadorSyncCli implements SyncProgressObserver, RateLimitObserver
{
    // Cria e gerencia ProgressBar
    // Formata estatisticas em tempo real
    // Mostra contagem regressiva do rate limit
}
```

---

## Testes

Usar `CommandTester` do Symfony para todos os testes de comando:
- Testar parsing de argumentos
- Testar valores padrao de opcoes
- Testar prompts interativos
- Testar que saida contem strings esperadas
- Testar codigos de saida

---

## Criterios de Aceite

- [ ] `cvdw sync` funciona em modos interativo e nao-interativo
- [ ] `cvdw setup` guia pela configuracao inicial
- [ ] `cvdw status` mostra visao clara do ambiente
- [ ] `cvdw list:objects` mostra objetos categorizados
- [ ] Barra de progresso mostra estatisticas em tempo real durante sync
- [ ] Espera do rate limit mostra contagem regressiva
- [ ] Erros mostram mensagens uteis com proximos passos
- [ ] Todos os comandos testados com CommandTester
- [ ] Nenhuma logica de negocio nos comandos (delegar para casos de uso)
- [ ] Saida bonita e consistente no terminal
