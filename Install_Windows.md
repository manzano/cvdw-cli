
# Configurando o Ambiente de Desenvolvimento PHP no WSL

Este guia detalha a instalação do Windows Subsystem for Linux (WSL), seguido pela instalação do Git, PHP 8.2 e Composer, incluindo extensões específicas para PHP dentro do WSL.

## Instalando o WSL no Windows 10/11

1. Abra o PowerShell como Administrador e execute o seguinte comando para habilitar o WSL:

    ```powershell
    wsl --install
    ```

2. Siga as instruções na tela para concluir a instalação do WSL. Você pode precisar reiniciar o seu computador.

3. Após reiniciar, abra a Microsoft Store e escolha sua distribuição Linux preferida (por exemplo, Ubuntu).

4. Siga as instruções para instalar a distribuição Linux escolhida.

5. Inicie sua distribuição Linux através do menu Iniciar.

## Atualize o WSL

Com o WSL instalado e a distribuição Linux escolhida configurada, atualize os pacotes do seu sistema:

```console
sudo apt update
sudo apt upgrade -y
```

## Instale o Git

Instale o Git com o seguinte comando:

```console
sudo apt install git -y
```

Para verificar a instalação, execute:

```console
git --version
```

## Instale o PHP 8.2 e Extensões

Para instalar o PHP 8.2 e as extensões necessárias, incluindo suporte para Phar, PDO_MySQL, PDO, PDO_SQLSRV (para SQL Server) e PDO_PGSQL (para PostgreSQL), siga os comandos abaixo. Note que PDO_SQLSRV pode requerer passos adicionais para instalação em algumas distribuições Linux:

```console
sudo apt install -y software-properties-common
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install php8.2 php8.2-cli php8.2-xml php8.2-mbstring php8.2-curl php8.2-phar php8.2-pdo php8.2-mysql php8.2-pgsql php8.2-zip -y
```

Para o PDO_SQLSRV em distribuições Linux baseadas em Debian/Ubuntu, você pode precisar seguir instruções específicas da Microsoft para instalar o driver ODBC necessário e a extensão pdo_sqlsrv.

Verifique a instalação do PHP:

```console
php -v
```

## Instale o Composer

O Composer pode ser instalado globalmente com os seguintes comandos:

```console
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php
php -r "unlink('composer-setup.php');"
sudo mv composer.phar /usr/local/bin/composer
sudo chmod +x /usr/local/bin/composer
```

Para verificar a instalação do Composer:

```console
composer --version
```

## Banco de Dados

Certifique-se de algum dos bancos de dados está configurado em seu ambiente:

- mysql --version

- mariadb --version

- psql --version

- /opt/mssql/bin/sqlservr --version

Caso não tenho nenhum banco configurado, por favor, siga o [guia de banco de dados](Guia_DB_Windows.md).

## Instale o CVDW-CLI

Agora é so executar o comando:

```console
curl -sSL https://raw.githubusercontent.com/manzano/cvdw-cli/main/install.sh | bash
```

## Conclusão

Seu ambiente de desenvolvimento PHP no WSL está pronto. Você agora tem acesso ao Git, PHP 8.2, Composer e as extensões PHP necessárias para iniciar seus projetos PHP no Windows.
