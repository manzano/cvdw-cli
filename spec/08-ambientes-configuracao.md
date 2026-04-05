# Ambientes e Configuracao

## Sistema de Multi-Ambientes

O CVDW-CLI suporta multiplos ambientes, cada um com suas proprias credenciais de API e banco de dados.

---

## Estrutura de Arquivos

```
src/envs/
├── .env                    # Ambiente padrao
├── dev.env                 # Desenvolvimento
├── homologacao.env         # Homologacao
├── producao.env            # Producao
├── {custom}.env            # Ambientes customizados
```

---

## Variaveis de Ambiente

### Arquivo `.env` Completo

```env
# === API CV CRM ===
CV_URL=seu_subdominio           # Subdominio do CV CRM (sem https://)
CV_EMAIL=usuario@empresa.com    # Email de autenticacao
CV_TOKEN=abc123def456...        # Token de acesso (40 caracteres)

# === Banco de Dados ===
DB_CONNECTION=pdo_mysql          # Driver: pdo_mysql, pdo_pgsql, sqlsrv
DB_HOST=localhost                # Host do servidor de banco
DB_PORT=3306                     # Porta do banco
DB_DATABASE=cvdw                 # Nome do banco de dados
DB_USERNAME=cvdw_user            # Usuario do banco
DB_PASSWORD=cvdw_pwd             # Senha do banco
DB_SCHEMA=                       # Schema PostgreSQL (default: public)

# === Anonimizacao ===
ANONIMIZAR=false                 # Ativar anonimizacao: true/false
ANONIMIZAR_TIPO=Asteriscos       # Metodo: Asteriscos ou Hash

# === Ambiente ===
CVDW_AMBIENTE=PRD                # Nome/identificador do ambiente
```

### Drivers de Banco Disponiveis

| Valor | Banco |
|-------|-------|
| `pdo_mysql` | MySQL / MariaDB |
| `pdo_pgsql` | PostgreSQL |
| `sqlsrv` | SQL Server |

### Portas Padrao

| Banco | Porta |
|-------|-------|
| MySQL/MariaDB | 3306 |
| PostgreSQL | 5432 |
| SQL Server | 1433 |

---

## Gerenciamento de Ambientes

### Selecionar Ambiente

Via opcao de linha de comando:
```bash
cvdw executar reservas --set-env=producao
cvdw configurar --set-env=dev
```

Via menu interativo do `configurar`.

### Criar Novo Ambiente

1. `cvdw configurar` -> Opcao 9 (Cadastrar novo ambiente)
2. Informar nome do ambiente
3. Sistema cria `{nome}.env` com template padrao
4. Configurar credenciais via opcoes 1 e 2

### Listar Ambientes

```bash
cvdw configurar  # -> Opcao 10 (Listar/remover ambientes)
```

Exibe todos os arquivos `.env` no diretorio `envs/`.

### Remover Ambiente

1. `cvdw configurar` -> Opcao 10
2. Selecionar ambiente a remover
3. Confirmar remocao (deleta arquivo `.env`)

---

## Carregamento de Configuracao

### Fluxo de Carregamento

```
1. Comando recebe --set-env=X
2. Ambientes::retornarEnvs(X) carrega {X}.env
3. Symfony Dotenv processa variaveis
4. EnvironmentManager disponibiliza via getters
5. Services acessam config via EnvironmentManager
```

### Fallback

Se `--set-env` nao informado:
1. Carrega `.env` padrao
2. Se `.env` nao existe: solicita configuracao via `configurar`

### EnvironmentManager

Camada de acesso com:
1. Leitura de `$_ENV` (carregado pelo Symfony Dotenv)
2. Fallback: leitura direta do arquivo `.env`
3. Retorna string vazia se variavel nao encontrada

---

## Verificacao de Ambiente

O comando `configurar` (Opcao 5) permite verificar o estado do ambiente:

- Exibe todas as variaveis configuradas
- Testa conectividade com API (`pingAmbienteCVDW`)
- Testa autenticacao (`pingAmbienteAutenticadoCVDW`)
- Testa conexao com banco de dados
- Exibe versao do CVDW-CLI

---

## Atualizacao do CVDW-CLI

Via menu do `configurar` (Opcao 11) ou via curl:

```bash
curl -sSL https://raw.githubusercontent.com/manzano/cvdw-cli/main/install.sh | bash
```

---

## Docker: Configuracao via Environment

No Docker, as variaveis sao injetadas via `docker-compose.yaml`:

```yaml
environment:
  CV_URL: ${CV_URL}
  CV_EMAIL: ${CV_EMAIL}
  CV_TOKEN: ${CV_TOKEN}
  DB_CONNECTION: ${DB_CONNECTION:-pdo_mysql}
  DB_HOST: ${DB_HOST:-mysql}
  DB_PORT: ${DB_PORT:-3306}
  DB_DATABASE: ${DB_DATABASE:-cvdw}
  DB_USERNAME: ${DB_USERNAME:-cvdw_user}
  DB_PASSWORD: ${DB_PASSWORD:-cvdw_pwd}
```

O `entrypoint.sh` usa `envsubst` para renderizar o template `env.tmpl.ini` em um arquivo `.env` dentro do container.
