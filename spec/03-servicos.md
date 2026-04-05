# Servicos

Toda a logica de negocio esta encapsulada em classes de servico no diretorio `src/app/Services/`.

---

## 1. Cvdw (Processador Principal)

**Arquivo:** `src/app/Services/Cvdw.php`
**Namespace:** `Manzano\CvdwCli\Services\Cvdw`

Orquestra a sincronizacao de dados entre a API e o banco de dados.

### Propriedades

| Propriedade | Tipo | Descricao |
|-------------|------|-----------|
| `$conexao` | Connection | Conexao Doctrine DBAL |
| `$http` | Http | Servico HTTP para API |
| `$rateLimit` | RateLimit | Servico de rate limiting |
| `$inseridos` | int | Contador de registros inseridos |
| `$alterados` | int | Contador de registros alterados |
| `$erros` | int | Contador de erros |
| `$totalRegistros` | int | Total de registros processados |
| `$totalPaginas` | int | Total de paginas da API |
| `$ultimaReferencia` | string | Ultima data de referencia |

### Metodos Principais

#### `conectar(): Connection`
Estabelece conexao com o banco usando `Conexao::conectarDB()`.

#### `processar(objeto, pagina, io, opcoes): array`
Loop principal de processamento:
1. Faz requisicao HTTP paginada via `Http::requestCVDW()`
2. Valida resposta com `verificaPadrao()`
3. Para cada registro no array `dados`:
   - Chama `processaSql()` para INSERT/UPDATE
4. Retorna estatisticas: `[inseridos, alterados, erros, totalRegistros, totalPaginas, ultimaReferencia]`

#### `processaSql(tabela, objeto, registro, io): void`
Roteamento INSERT/UPDATE:
1. Verifica se campo `referencia` existe no registro
2. Chama `verificaSeExiste()` para checar existencia
3. Se nao existe: `executaInsert()`
4. Se existe: `executaUpdate()`

#### `executaInsert(tabela, objeto, registro): void`
1. Obtem colunas da tabela via `retornarEstruturaTabela()`
2. Mapeia campos do registro para colunas
3. Aplica `trataDados()` em cada campo
4. Executa INSERT via Doctrine DBAL
5. Incrementa `$inseridos`

#### `executaUpdate(tabela, objeto, registro): void`
1. Obtem colunas da tabela
2. Mapeia campos para colunas existentes
3. Aplica `trataDados()`
4. Executa UPDATE com WHERE `referencia = ?`
5. Incrementa `$alterados`

#### `trataDados(campo, valor, objeto): mixed`
Transformacoes aplicadas:
- **Sub-tabelas:** Se valor e array, insere em tabela `{objeto}_sub_{campo}`
- **Anonimizacao:** Se campo marcado como `sensivel: true` no YAML:
  - Tipo "Asteriscos": `Helper::substituirPorAsteriscos()`
  - Tipo "Hash": `Helper::substituirPorHash()`
- **Truncamento:** Limita strings ao tamanho maximo da coluna

#### `buscaUltimaData(tabela): string|null`
Busca `MAX(referencia_data)` na tabela para sync incremental.

#### `verificaSeExiste(tabela, referencia): bool`
Executa `SELECT COUNT(*) WHERE referencia = ?`.

#### `verificaPadrao(resposta): bool`
Valida que a resposta contem as chaves obrigatorias:
- `total_de_registros`
- `total_de_paginas`
- `pagina`
- `registros_por_pagina`
- `dados` (array)

#### `corrigeRetornoJson(dados): array`
Normaliza campos de resposta JSON que possam vir em formato inconsistente.

---

## 2. Http (Cliente API)

**Arquivo:** `src/app/Services/Http.php`
**Namespace:** `Manzano\CvdwCli\Services\Http`

Gerencia toda comunicacao HTTP com a API do CV CRM.

### Metodos Principais

#### `requestCVDW(path, pagina, registros, dataReferencia, opcoes): array`
Requisicao principal a API CVDW:
1. Monta URL: `{CV_URL}/api/v1/cvdw/{path}`
2. Define headers: `Content-Type`, `email`, `token`
3. Define parametros: `pagina`, `registros_por_pagina`, `a_partir_data_referencia`
4. Executa CURL GET
5. Retorna array decodificado do JSON

#### `gerenciarRateLimit(rateLimit, io): void`
Aplica rate limiting:
1. Consulta `RateLimit::getDiferencaSegundosUltimaRequisicao()`
2. Se menos de 3 segundos desde a ultima: aguarda
3. Se 20+ requisicoes no ultimo minuto: aguarda ate completar 60s

#### `aguardar(segundos, io): void`
Exibe contagem regressiva visual durante espera de rate limit.

#### `pingAmbienteCVDW(url): bool`
Testa conectividade com o ambiente CV CRM (nao autenticado).

#### `pingAmbienteAutenticadoCVDW(url, email, token): bool`
Testa autenticacao validando credenciais contra a API.

---

## 3. DatabaseSetup (Gerenciamento de Schema)

**Arquivo:** `src/app/Services/DatabaseSetup.php`
**Namespace:** `Manzano\CvdwCli\Services\DatabaseSetup`

Gerencia criacao, alteracao e manutencao do schema do banco de dados.

### Metodos Principais

#### `executarCriarTabelas(conexao, io): void`
Itera sobre todos os objetos YAML e cria as tabelas correspondentes.

#### `criarTabela(conexao, objeto, io): void`
Cria uma tabela individual:
1. Carrega definicao YAML do objeto
2. Verifica se tabela ja existe
3. Se nao existe: `criarTabelaSchema()`
4. Se existe: `compararTabelaObjeto()` para sync de schema

#### `criarTabelaSchema(conexao, nomeTabela, campos): void`
Constroi tabela usando Doctrine Schema:
- Cria coluna `referencia` (VARCHAR 255, UNIQUE)
- Cria coluna `referencia_data` (DATETIME)
- Cria coluna `ativo` (VARCHAR 1)
- Mapeia campos do YAML para tipos Doctrine:
  - `string` -> `VARCHAR(tamanho)`
  - `integer` -> `INTEGER`
  - `float/number` -> `DECIMAL`
  - `date/datetime` -> `DATETIME`
  - `boolean` -> `VARCHAR(5)`
  - `text` -> `TEXT`
  - `array/object` -> Cria sub-tabela

#### `compararTabelaObjeto(conexao, tabela, objeto): array`
Compara schema atual do banco com definicao YAML:
- Detecta colunas adicionadas
- Detecta colunas removidas
- Detecta alteracoes de tipo/tamanho

#### `inserirColuna(conexao, tabela, coluna, tipo): void`
Adiciona nova coluna a tabela existente via ALTER TABLE.

#### `removerColuna(conexao, tabela, coluna): void`
Remove coluna da tabela via ALTER TABLE.

#### `alterarColuna(conexao, tabela, coluna, tipo): void`
Altera tipo/propriedades de coluna existente.

#### `limparTabela(conexao, tabela): void`
Executa TRUNCATE TABLE.

#### `limparDataReferenciaTabela(conexao, tabela): void`
Executa `UPDATE SET referencia_data = NULL` para forcar re-sync.

#### `executarApagarTabelas(conexao, tabelas): void`
Executa DROP TABLE para lista de tabelas.

#### `listarTabelas(conexao): void`
Lista todas as tabelas CVDW no banco.

#### `verificarSeTabelaExiste(conexao, tabela): bool`
Verifica existencia de tabela no schema.

#### `retornarEstruturaTabela(conexao, tabela): array`
Retorna estrutura atual da tabela (colunas, tipos).

---

## 4. RateLimit (Controle de Taxa)

**Arquivo:** `src/app/Services/RateLimit.php`
**Namespace:** `Manzano\CvdwCli\Services\RateLimit`

Rastreia e controla a taxa de requisicoes a API.

### Tabela de Controle: `_requisicoes`

| Coluna | Tipo | Descricao |
|--------|------|-----------|
| `idrequisicao` | INT (PK, AUTO) | ID da requisicao |
| `data_inicio` | DATETIME | Inicio da requisicao |
| `data_fim` | DATETIME | Fim da requisicao |
| `objeto` | VARCHAR | Endpoint/objeto da requisicao |
| `dados_retorno_qtd` | INT | Quantidade de registros retornados |
| `header_resultado` | TEXT | Headers da resposta HTTP |

### Metodos Principais

#### `inserirRequisicao(conexao, objeto): int`
Registra inicio de nova requisicao. Retorna ID.

#### `atualizarRequisicao(conexao, id, qtdRetorno, headers): void`
Atualiza requisicao com dados de retorno e timestamp de fim.

#### `getDiferencaSegundosUltimaRequisicao(conexao): int`
Calcula segundos desde a 20a requisicao mais recente.
- Compativel com MySQL e PostgreSQL (usa deteccao de plataforma)
- MySQL: `TIMESTAMPDIFF(SECOND, data_inicio, NOW())`
- PostgreSQL: `EXTRACT(EPOCH FROM (NOW() - data_inicio))`

#### `getRequisicoesNoUltimoMinuto(conexao): int`
Conta requisicoes feitas no ultimo minuto.

#### `limparRequisicoesAntigas(conexao): void`
Remove registros de requisicoes com mais de 30 dias.

#### `verificarTempoExecucao(tempoInicio, limiteSegundos): bool`
Verifica se o tempo de execucao excedeu o limite configurado.

---

## 5. Ambientes (Gerenciamento de Ambientes)

**Arquivo:** `src/app/Services/Ambientes.php`
**Namespace:** `Manzano\CvdwCli\Services\Ambientes`

Gerencia configuracoes de ambiente e arquivos `.env`.

### Metodos Principais

#### `retornarEnvs(ambiente): array`
Carrega variaveis do arquivo `.env` do ambiente especificado.

#### `salvarEnv(ambiente, variaveis): void`
Persiste configuracoes no arquivo `.env` do ambiente.

#### `getEnvDir(ambiente): string`
Retorna caminho completo do arquivo `.env` do ambiente.

#### `listarAmbientes(): array`
Lista todos os ambientes disponiveis no diretorio `envs/`.

#### `ambienteAtivo(io): void`
Exibe status do ambiente atual com todas as configuracoes.

#### `retornarVersao(): string`
Retorna versao atual do CVDW-CLI (hardcoded como constante).

#### `criarAmbiente(nome): void`
Cria novo arquivo `.env` com template padrao.

#### `removerAmbiente(nome): void`
Remove arquivo `.env` do ambiente.

---

## 6. EnvironmentManager (Variaveis de Ambiente)

**Arquivo:** `src/app/Services/EnvironmentManager.php`
**Namespace:** `Manzano\CvdwCli\Services\EnvironmentManager`

Camada de acesso a variaveis de ambiente com getters/setters tipados.

### Variaveis Gerenciadas

| Variavel | Getter | Descricao |
|----------|--------|-----------|
| `CV_URL` | `getCvUrl()` | Subdominio do CV CRM |
| `CV_EMAIL` | `getCvEmail()` | Email de autenticacao |
| `CV_TOKEN` | `getCvToken()` | Token de autenticacao |
| `DB_CONNECTION` | `getDbConnection()` | Driver do banco (pdo_mysql, pdo_pgsql, sqlsrv) |
| `DB_HOST` | `getDbHost()` | Host do banco |
| `DB_PORT` | `getDbPort()` | Porta do banco |
| `DB_DATABASE` | `getDbDatabase()` | Nome do banco |
| `DB_USERNAME` | `getDbUsername()` | Usuario do banco |
| `DB_PASSWORD` | `getDbPassword()` | Senha do banco |
| `DB_SCHEMA` | `getDbSchema()` | Schema PostgreSQL (default: public) |
| `ANONIMIZAR` | `getAnonimizar()` | Flag de anonimizacao (true/false) |
| `ANONIMIZAR_TIPO` | `getAnonimizarTipo()` | Tipo: Asteriscos ou Hash |
| `CVDW_AMBIENTE` | `getCvdwAmbiente()` | Nome do ambiente |

### Metodos Genericos

- `get(chave): string` - Retorna valor da variavel
- `set(chave, valor): void` - Define valor da variavel
- `has(chave): bool` - Verifica se variavel existe

### Estrategia de Carregamento

1. Tenta ler de `$_ENV`
2. Fallback: le diretamente do arquivo `.env`
3. Retorna string vazia se nao encontrado

---

## 7. Objeto (Definicoes de Objetos)

**Arquivo:** `src/app/Services/Objeto.php`
**Namespace:** `Manzano\CvdwCli\Services\Objeto`

Gerencia definicoes de objetos da API baseadas em arquivos YAML.

### Metodos Principais

#### `retornarObjetos(): array`
Retorna lista de todos os objetos disponiveis com metadados.

#### `retornarObjeto(nome): array`
Carrega e retorna definicao completa de um objeto especifico do YAML.

#### `retornarObjetosPorNome(nomes): array`
Aceita nomes separados por `+` ou `,` e retorna array de objetos.

#### `identificarTipoDado(tipo): string`
Mapeia tipos do YAML para tipos Doctrine DBAL.

---

## 8. Log (Logging)

**Arquivo:** `src/app/Services/Log.php`
**Namespace:** `Manzano\CvdwCli\Services\Log`

Servico simples de logging em arquivo.

### Metodos

#### `criarLog(): void`
Cria novo arquivo de log com nome: `log_Y-m-d_H-i-s.log` em `logs/`.

#### `escreverLog(mensagem): void`
Adiciona linha com timestamp ao arquivo de log atual.

---

## 9. CvdwSymfonyStyle (Console Customizado)

**Arquivo:** `src/app/Services/Console/CvdwSymfonyStyle.php`
**Namespace:** `Manzano\CvdwCli\Services\Console`

Extende `SymfonyStyle` com funcionalidades adicionais:
- Integracao com servico de Log
- Metodos de saida coloridos customizados
- Suporte a perguntas interativas com estilo CVDW
