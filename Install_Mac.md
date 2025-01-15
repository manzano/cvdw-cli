
# Configurando o Ambiente de Desenvolvimento PHP no macOS

Este guia detalha a instalação do Git, PHP 8.2, Composer e extensões específicas do PHP no macOS.

## Instale o Homebrew

Homebrew é um gerenciador de pacotes para macOS. Instale-o executando o seguinte comando no Terminal:

```console
/bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"
```

## Instale o Git

Com o Homebrew instalado, instale o Git:

```console
brew install git
```

Verifique a instalação do Git:

```console
git --version
```

## Instale o PHP 8.2

Instale o PHP 8.2 usando o Homebrew:

```console
brew install php@8.2
```

Adicione o PHP ao seu PATH para usar a versão instalada pelo Homebrew:

```console
echo 'export PATH="/usr/local/opt/php@8.2/bin:$PATH"' >> ~/.zshrc
echo 'export PATH="/usr/local/opt/php@8.2/sbin:$PATH"' >> ~/.zshrc
source ~/.zshrc
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
mv composer.phar /usr/local/bin/composer
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

Seu ambiente de desenvolvimento PHP está pronto no macOS. Você tem o Git, PHP 8.2 e Composer instalados, prontos para iniciar seus projetos PHP.
