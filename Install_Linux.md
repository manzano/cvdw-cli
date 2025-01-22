
# Configurando o Ambiente de Desenvolvimento PHP no Linux

Este guia detalha a instalação do Git, PHP 8.2, Composer e extensões específicas do PHP no Linux.

## Atualize o Sistema

Atualize os pacotes do seu sistema:

```console
sudo apt update
sudo apt upgrade -y
```

## Instale o Git

Instale o Git:

```console
sudo apt install git -y
```

Verifique a instalação do Git:

```console
git --version
```

## Instale o PHP 8.2 e Extensões

Adicione o repositório PPA do PHP e instale o PHP 8.2 e as extensões necessárias:

```console
sudo apt install -y software-properties-common
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install php8.2 php8.2-cli php8.2-xml php8.2-mbstring php8.2-curl php8.2-phar php8.2-pdo php8.2-mysql php8.2-pgsql php8.2-zip -y
```

Verifique a instalação do PHP:

```console
php -v
```

## Instale o Composer

Instale o Composer globalmente:

```console
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php
sudo mv composer.phar /usr/local/bin/composer
```

Verifique a instalação do Composer:

```console
composer --version
```

## Banco de Dados

Certifique-se de algum dos bancos de dados está configurado em seu ambiente:

- mysql --version

- mariadb --version

- psql --version

- /opt/mssql/bin/sqlservr --version

Caso não tenho nenhum banco configurado, por favor, siga o [guia de banco de dados](Guia_DB.md).

## Instale o CVDW-CLI

Agora é so executar o comando:

```console
curl -sSL https://raw.githubusercontent.com/manzano/cvdw-cli/main/install.sh | bash
```

## Conclusão

Seu ambiente de desenvolvimento PHP está pronto no Linux. Você tem o Git, PHP 8.2 e Composer instalados, prontos para iniciar seus projetos PHP.
