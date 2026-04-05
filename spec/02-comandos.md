# Comandos CLI

O CVDW-CLI possui tres comandos principais, todos registrados como comandos Symfony Console.

---

## 1. Comando `executar`

**Classe:** `Manzano\CvdwCli\Executar`
**Aliases:** `executar`, `Execute`, `execute`
**Descricao:** Busca dados da API CV CRM e salva no banco de dados.

### Argumentos

| Nome | Obrigatorio | Descricao |
|------|-------------|-----------|
| `objeto` | Nao | Nome do objeto a executar. Se omitido, exibe menu interativo. Aceita multiplos objetos separados por `+` (ex: `reservas+leads`). Valor `all` executa todos. |

### Opcoes

| Opcao | Alias | Tipo | Default | Descricao |
|-------|-------|------|---------|-----------|
| `--ignorar-data-referencia` | `-idr` | Flag | false | Ignora data de referencia, busca todos os registros desde o inicio |
| `--salvarlog` | `-log` | Flag | false | Salva log de execucao em arquivo (`logs/`) |
| `--set-env` | `-env` | String | null | Define ambiente (dev, homologacao, producao) |
| `--set-qtd` | `-qtd` | Integer | 500 | Registros por pagina (max 500) |
| `--apartir` | `-a` | String | null | Data inicial para busca (formato: `Y-m-d` ou `Y-m-dTH:i:s`) |
| `--max-pag` | `-m` | Integer | null | Numero maximo de paginas a processar |
| `--tempo-execucao` | `-t` | Integer | null | Limite de tempo de execucao em segundos |

### Fluxo de Execucao

1. Carrega ambiente via `Ambientes`
2. Inicializa `RateLimit` e conecta ao banco
3. Se argumento `objeto` informado:
   - Se `all`: executa todos os objetos
   - Se contem `+`: executa multiplos objetos
   - Senao: executa objeto especifico
4. Se argumento nao informado: exibe menu interativo com opcoes:
   - Listar objetos disponiveis
   - Executar todos os objetos
   - Executar objeto especifico (menu de selecao)
   - Sair

### Exemplos de Uso

```bash
# Menu interativo
cvdw executar

# Objeto especifico
cvdw executar reservas

# Multiplos objetos
cvdw executar reservas+leads+atendimentos

# Todos os objetos
cvdw executar all

# Com opcoes
cvdw executar reservas --apartir=2024-01-01 --set-qtd=100 --max-pag=5

# Ambiente especifico
cvdw executar reservas --set-env=producao

# Com limite de tempo
cvdw executar all --tempo-execucao=3600
```

---

## 2. Comando `configurar`

**Classe:** `Manzano\CvdwCli\Configurar`
**Aliases:** `configurar`, `config`, `cfg`, `configuracao`, `configuração`
**Descricao:** Assistente interativo para configuracao do CVDW-CLI.

### Opcoes

| Opcao | Alias | Tipo | Default | Descricao |
|-------|-------|------|---------|-----------|
| `--set-env` | `-env` | String | null | Define ambiente alvo |
| `--force` | `-f` | Flag | false | Forca respostas positivas (modo nao-interativo) |

### Menu de Configuracao

O comando exibe um menu com as seguintes opcoes:

| # | Opcao | Descricao |
|---|-------|-----------|
| 1 | Acesso a API CV | Configura subdominio, email e token da API CV CRM |
| 2 | Acesso ao Banco de Dados | Configura host, porta, database, usuario, senha e tipo de banco |
| 3 | Criar tabelas no banco | Cria todas as tabelas baseado nas definicoes YAML dos objetos |
| 4 | Anonimizar dados | Configura anonimizacao de dados sensiveis (Asteriscos ou Hash) |
| 5 | Verificar/atualizar ambiente | Exibe status do ambiente e permite atualizar configuracoes |
| 6 | Limpar data referencia | Remove datas de referencia das tabelas (forca re-sync completo) |
| 7 | Limpar tabelas CVDW | Executa TRUNCATE nas tabelas selecionadas |
| 8 | Apagar tabelas CVDW | Executa DROP nas tabelas selecionadas |
| 9 | Cadastrar novo ambiente | Registra um novo ambiente com nome customizado |
| 10 | Listar/remover ambientes | Lista ambientes existentes e permite remocao |
| 11 | Atualizar CVDW-CLI | Executa script de atualizacao via curl |
| 12 | Executar CVDW-CLI | Redireciona para o comando `executar` |

### Sub-comando `autoupdate`

Usado internamente pelo Docker para configurar automaticamente:
- Verifica/cria ambiente
- Aplica configuracoes do `.env`
- Cria tabelas automaticamente
- Atualiza schema se necessario

### Fluxo de Configuracao Inicial

```
1. cvdw configurar
2. Opcao 1: Configurar credenciais API (subdominio, email, token)
3. Opcao 2: Configurar banco de dados
4. Opcao 3: Criar tabelas
5. Pronto para executar: cvdw executar
```

---

## 3. Comando `opcoes`

**Classe:** `Manzano\CvdwCli\Opcoes`
**Descricao:** Exibe informacoes de ajuda e exemplos de uso.

### Opcoes

| Opcao | Alias | Tipo | Descricao |
|-------|-------|------|-----------|
| `--dir` | `--diretorio` | Flag | Exibe diretorio de instalacao |

### Saida

Exibe:
- Nome e versao da aplicacao
- Lista de comandos disponiveis
- Exemplos de uso
- Opcoes globais

---

## Entry Point

**Arquivo:** `src/cvdw`

O entry point e um script PHP que:
1. Carrega autoloader do Composer
2. Instancia `Symfony\Component\Console\Application`
3. Carrega servico `Ambientes` para obter versao
4. Registra os tres comandos (`Opcoes`, `Configurar`, `Executar`)
5. Define `Opcoes` como comando padrao
6. Executa a aplicacao Symfony Console
