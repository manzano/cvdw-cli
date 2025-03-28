# Usando a imagem oficial do PHP 8.3 Alpine (mais leve e compatível)
FROM php:8.3-cli-alpine

WORKDIR /app

# Instalando dependências do sistema
RUN apk add --no-cache \
    bash \
    unzip \
    mariadb-client \
    libzip-dev \
    dcron \
    curl \
    zip \
    envsubst \
    && docker-php-ext-install pdo pdo_mysql zip

# Instalando o Composer antes de clonar o repositório
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php \
    && mv composer.phar /usr/local/bin/composer \
    && rm composer-setup.php

COPY ./docker/env.tmpl.ini /app/src/envs/env.tmpl.ini
COPY ./docker/crontab /etc/crontabs/root
COPY . /app

RUN composer install \
    && composer dump-autoload --optimize \
    && chmod u+w /app \
    && chmod +x /app/src/cvdw \
    && chmod +x /app/Executar_CVDW.sh \
    && ln -s /app/src/cvdw /usr/local/bin/cvdw


COPY ./docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

ENTRYPOINT ["/entrypoint.sh"]

# Script de inicialização para garantir que o cron esteja rodando
COPY ./docker/start.sh /start.sh
RUN chmod +x /start.sh

# Comando padrão do container
CMD ["/start.sh"]
