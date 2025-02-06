# Usando a imagem oficial do PHP 8.3 Alpine (mais leve e compatível)
FROM php:8.3-cli-alpine

# Instalando dependências do sistema
RUN apk add --no-cache \
    git \
    bash \
    unzip \
    mariadb-client \
    libzip-dev \
    dcron \
    curl \
    zip \
    vim \
    && docker-php-ext-install pdo pdo_mysql zip

# Instalando o Composer antes de clonar o repositório
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php \
    && mv composer.phar /usr/local/bin/composer \
    && rm composer-setup.php

# Criando diretório da aplicação
WORKDIR /app

# Clonando o repositório e instalando dependências
RUN git clone https://github.com/manzano/cvdw-cli.git . && \
    git checkout main && \
    composer install --no-dev --optimize-autoloader && \
    chmod u+w /app

# Garantindo que o script tenha permissão de execução
RUN chmod +x /app/src/cvdw \
    && chmod +x /app/Executar_CVDW.sh

# Criar um alias para rodar o cvdw diretamente
RUN echo "alias cvdw='php /app/src/cvdw'" >> /root/.bashrc && \
    echo "alias cvdw='php /app/src/cvdw'" >> /root/.profile && \
    echo "alias cvdw='php /app/src/cvdw'" >> /etc/profile && \
    echo "alias cvdw='php /app/src/cvdw'" >> /etc/bash.bashrc

# Criar um link simbólico para permitir rodar `cvdw` diretamente
RUN ln -s /app/src/cvdw /usr/local/bin/cvdw

RUN apk add envsubst
COPY env.tmpl.ini /app/src/envs/env.tmpl.ini

# Adicionando o crontab
COPY crontab /etc/crontabs/root

COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

ENTRYPOINT ["/entrypoint.sh"]

# Script de inicialização para garantir que o cron esteja rodando
COPY start.sh /start.sh
RUN chmod +x /start.sh

# Comando padrão do container
CMD ["/start.sh"]
