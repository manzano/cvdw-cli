#!/bin/sh

# Iniciar o cron
/usr/sbin/crond -f

# Executar o comando original do Dockerfile
exec "$@"

# rodando o cvdw
php /app/src/cvdw executar all

mv .env /app/src/envs/.env
