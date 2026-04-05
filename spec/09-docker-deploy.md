# Docker e Deploy

## Visao Geral

O CVDW-CLI oferece suporte completo a Docker para execucao containerizada com agendamento automatico via cron.

---

## Estrutura Docker

```
cvdw-cli/
├── Dockerfile                # Definicao da imagem (na raiz)
├── docker-compose.yaml       # Orquestracao dos servicos
└── docker/
    ├── env.tmpl.ini          # Template de variaveis de ambiente
    ├── crontab               # Agendamento cron
    ├── entrypoint.sh         # Script de inicializacao
    └── start.sh              # Script de execucao do cron daemon
```

---

## Dockerfile

### Imagem Base
- `php:8.3-cli-alpine` (Alpine Linux, imagem leve)

### Dependencias Instaladas

**Sistema:**
- bash, unzip, curl, zip, gettext
- mariadb-client (cliente MySQL)
- libpq (cliente PostgreSQL)
- dcron (cron daemon para Alpine)

**Extensoes PHP:**
- `pdo_mysql` - Conexao MySQL/MariaDB
- `pdo_pgsql` - Conexao PostgreSQL
- `zip` - Manipulacao de arquivos ZIP

### Build Steps

1. Instala dependencias de sistema e PHP
2. Instala Composer
3. Clona repositorio do GitHub
4. Copia templates de configuracao
5. Instala dependencias PHP via `composer install --optimize-autoloader`
6. Configura crontab
7. Cria symlink `cvdw` em `/usr/local/bin/`
8. Define permissoes de execucao

### Entrypoints

- **ENTRYPOINT:** `/entrypoint.sh` (configuracao inicial)
- **CMD:** `/start.sh` (cron daemon)

---

## Docker Compose

### Servicos

#### `app` (CVDW-CLI)

```yaml
image: gabrielmanzano/cvdw-cli:latest
depends_on:
  - mysql
  - postgres
environment:
  CV_URL, CV_EMAIL, CV_TOKEN
  DB_CONNECTION, DB_HOST, DB_PORT, DB_DATABASE
  DB_USERNAME, DB_PASSWORD, DB_SCHEMA
  ANONIMIZAR, ANONIMIZAR_TIPO, CVDW_AMBIENTE
```

#### `mysql` (MySQL 8.0)

```yaml
image: mysql:8.0
ports: "3306:3306"
volumes: cvdw-cli-mysql_data:/var/lib/mysql
environment:
  MYSQL_ROOT_PASSWORD: root
  MYSQL_DATABASE: cvdw
  MYSQL_USER: cvdw_user
  MYSQL_PASSWORD: cvdw_pwd
```

#### `postgres` (PostgreSQL 16)

```yaml
image: postgres:16
ports: "5432:5432"
volumes: cvdw-cli-postgres_data:/var/lib/postgresql/data
environment:
  POSTGRES_DB: cvdw
  POSTGRES_USER: cvdw_user
  POSTGRES_PASSWORD: cvdw_pwd
```

### Volumes Persistentes

| Volume | Descricao |
|--------|-----------|
| `cvdw-cli-mysql_data` | Dados do MySQL |
| `cvdw-cli-postgres_data` | Dados do PostgreSQL |

---

## Scripts Docker

### entrypoint.sh

Executado na inicializacao do container:

1. **Renderiza template:** Usa `envsubst` para converter `env.tmpl.ini` em `cvdw.env`, substituindo variaveis de ambiente
2. **Auto-configuracao:** Executa `php src/cvdw configurar autoupdate --force=true --set-env=cvdw`
   - Cria ambiente se nao existe
   - Aplica configuracoes do `.env`
   - Cria/atualiza tabelas
3. **Execucao inicial:** Roda `Executar_CVDW.sh` para primeira sincronizacao
4. **Passa controle:** `exec "$@"` para CMD

### start.sh

Script simples que inicia o daemon cron:

```bash
/usr/sbin/crond -f
```

O `-f` mantém o cron em foreground (necessario para Docker).

### Executar_CVDW.sh

Script auxiliar que executa o CVDW-CLI:

```bash
php src/cvdw executar all --set-env=cvdw
```

---

## Agendamento (Cron)

O arquivo `docker/crontab` define agendamentos de execucao automatica dentro do container.

Exemplo tipico:
```cron
*/30 * * * * /app/Executar_CVDW.sh >> /app/logs/cron.log 2>&1
```

---

## Deploy Manual (sem Docker)

### Pre-requisitos

- PHP >= 8.2
- Composer
- Extensoes: pdo_mysql ou pdo_pgsql
- Acesso ao banco de dados

### Instalacao

```bash
# Via script de instalacao
curl -sSL https://raw.githubusercontent.com/manzano/cvdw-cli/main/install.sh | bash

# Ou via Git
git clone https://github.com/manzano/cvdw-cli.git
cd cvdw-cli
composer install

# Configurar
php src/cvdw configurar

# Executar
php src/cvdw executar all
```

### Agendamento (Linux/macOS)

```cron
# Executar a cada 30 minutos
*/30 * * * * cd /path/to/cvdw-cli && php src/cvdw executar all --set-env=producao >> logs/cron.log 2>&1
```

---

## Imagem Docker Publicada

```
gabrielmanzano/cvdw-cli:latest
```

Disponivel no Docker Hub para uso direto com `docker-compose pull`.
