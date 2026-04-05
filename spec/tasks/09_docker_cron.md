# 09 - Docker & Cron

## Objetivo

Construir setup Docker pronto para producao e agendamento via cron para sincronizacao automatica de dados.

---

## Dockerfile

```dockerfile
FROM php:8.3-cli-alpine

RUN apk add --no-cache bash dcron curl zip unzip libzip-dev libpq-dev gettext
RUN docker-php-ext-install pdo_mysql pdo_pgsql zip

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-scripts

COPY src/ src/
COPY envs/ envs/

RUN chmod +x src/cvdw && ln -s /app/src/cvdw /usr/local/bin/cvdw

COPY docker/crontab /etc/crontabs/root
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

ENTRYPOINT ["/entrypoint.sh"]
CMD ["crond", "-f", "-l", "2"]
```

Melhorias chave:
- Sem git clone dentro do container (COPY direto)
- `composer install --no-dev` em producao
- Tratamento de sinais com `crond -f`

---

## Docker Compose

```yaml
services:
  app:
    build: .
    depends_on:
      mysql:
        condition: service_healthy
    environment:
      CV_URL: ${CV_URL}
      CV_EMAIL: ${CV_EMAIL}
      CV_TOKEN: ${CV_TOKEN}
      DB_CONNECTION: ${DB_CONNECTION:-pdo_mysql}
      DB_HOST: ${DB_HOST:-mysql}
      DB_PORT: ${DB_PORT:-3306}
      DB_DATABASE: ${DB_DATABASE:-cvdw}
      DB_USERNAME: ${DB_USERNAME:-cvdw}
      DB_PASSWORD: ${DB_PASSWORD:-cvdw}
      AGENDA_SINCRONIZACAO: ${AGENDA_SINCRONIZACAO:-*/30 * * * *}
      LIMITE_TEMPO_SINCRONIZACAO: ${LIMITE_TEMPO_SINCRONIZACAO:-1500}
    volumes:
      - ./logs:/app/logs
    restart: unless-stopped

  mysql:
    image: mysql:8.0
    environment:
      MYSQL_ROOT_PASSWORD: root
      MYSQL_DATABASE: ${DB_DATABASE:-cvdw}
      MYSQL_USER: ${DB_USERNAME:-cvdw}
      MYSQL_PASSWORD: ${DB_PASSWORD:-cvdw}
    volumes:
      - dados_mysql:/var/lib/mysql
    healthcheck:
      test: ["CMD", "mysqladmin", "ping", "-h", "localhost"]
      interval: 10s
      retries: 5

  postgres:
    image: postgres:16
    profiles: ["postgres"]
    environment:
      POSTGRES_DB: ${DB_DATABASE:-cvdw}
      POSTGRES_USER: ${DB_USERNAME:-cvdw}
      POSTGRES_PASSWORD: ${DB_PASSWORD:-cvdw}
    volumes:
      - dados_postgres:/var/lib/postgresql/data
    healthcheck:
      test: ["CMD-SHELL", "pg_isready"]
      interval: 10s
      retries: 5

volumes:
  dados_mysql:
  dados_postgres:
```

Melhorias:
- `depends_on` com healthcheck (espera banco ficar pronto)
- PostgreSQL como profile opcional
- `AGENDA_SINCRONIZACAO` configuravel via env
- `LIMITE_TEMPO_SINCRONIZACAO` para evitar crons sobrepostos

---

## Entrypoint

```bash
#!/bin/bash
set -e

echo "=== CVDW-CLI Container Iniciando ==="

# Gerar .env a partir das variaveis de ambiente
envsubst < /app/docker/env.template > /app/envs/docker.env

# Esperar banco ficar disponivel
echo "Aguardando banco de dados..."
for i in $(seq 1 30); do
    if cvdw status --env=docker --no-interaction 2>/dev/null; then
        echo "Banco conectado."
        break
    fi
    echo "  Tentativa $i/30..."
    sleep 2
done

# Criar/atualizar tabelas
echo "Configurando tabelas..."
cvdw setup tables --env=docker --force

# Rodar sincronizacao inicial
echo "Executando sincronizacao inicial..."
cvdw sync all --env=docker --time-limit=${LIMITE_TEMPO_SINCRONIZACAO:-1500} --no-interaction --log

echo "Sincronizacao inicial completa. Iniciando agendador cron."

# Configurar cron dinamico
AGENDA="${AGENDA_SINCRONIZACAO:-*/30 * * * *}"
echo "${AGENDA} /app/docker/sincronizar.sh >> /app/logs/cron.log 2>&1" > /etc/crontabs/root

exec "$@"
```

---

## Script de Sincronizacao (`docker/sincronizar.sh`)

```bash
#!/bin/bash
set -e

echo "$(date '+%Y-%m-%d %H:%M:%S') - Sincronizacao iniciada"

# Verificar se outra sincronizacao esta rodando (evitar sobreposicao)
ARQUIVO_TRAVA="/tmp/cvdw-sync.lock"
if [ -f "$ARQUIVO_TRAVA" ]; then
    PID=$(cat "$ARQUIVO_TRAVA")
    if kill -0 "$PID" 2>/dev/null; then
        echo "Outra sincronizacao ainda rodando (PID: $PID). Pulando."
        exit 0
    fi
fi

echo $$ > "$ARQUIVO_TRAVA"
trap "rm -f $ARQUIVO_TRAVA" EXIT

cvdw sync all \
    --env=docker \
    --time-limit=${LIMITE_TEMPO_SINCRONIZACAO:-1500} \
    --no-interaction \
    --log

echo "$(date '+%Y-%m-%d %H:%M:%S') - Sincronizacao concluida"
```

---

## Como Usar

```bash
# Iniciar com MySQL (padrao)
docker compose up -d

# Iniciar com PostgreSQL
docker compose --profile postgres up -d

# Ver logs
docker compose logs -f app

# Sincronizacao manual
docker compose exec app cvdw sync all --env=docker

# Verificar status
docker compose exec app cvdw status --env=docker
```

---

## Criterios de Aceite

- [ ] Dockerfile constroi imagem limpa
- [ ] `docker compose up` inicia stack completa
- [ ] Sincronizacao inicial roda no primeiro start
- [ ] Agenda do cron configuravel via variavel de ambiente
- [ ] Arquivo de trava previne sincronizacoes sobrepostas
- [ ] Healthcheck do banco previne conexao prematura
- [ ] PostgreSQL disponivel como profile opcional
- [ ] Logs acessiveis do host
- [ ] `LIMITE_TEMPO_SINCRONIZACAO` previne execucoes infinitas
