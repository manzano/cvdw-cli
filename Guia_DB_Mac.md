
# Guia de Instalação de Bancos de Dados no macOS

Este guia fornece instruções para instalar e configurar os bancos de dados MySQL, MariaDB, PostgreSQL e SQL Server no macOS.

---

## MySQL

### Passos para Instalação:
1. **Instale o Homebrew (se ainda não tiver instalado):**
   ```bash
   /bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"
   ```

2. **Instale o MySQL via Homebrew:**
   ```bash
   brew install mysql
   ```

3. **Inicie o serviço do MySQL:**
   ```bash
   brew services start mysql
   ```

4. **Configure a instalação:**
   ```bash
   mysql_secure_installation
   ```
   Siga as instruções para configurar a segurança.

5. **Verifique a versão do MySQL instalada:**
   ```bash
   mysql --version
   ```

6. **Acesse o MySQL:**
   ```bash
   mysql -u root -p
   ```

---

## MariaDB

### Passos para Instalação:
1. **Instale o MariaDB via Homebrew:**
   ```bash
   brew install mariadb
   ```

2. **Inicie o serviço do MariaDB:**
   ```bash
   brew services start mariadb
   ```

3. **Configure a instalação:**
   ```bash
   mysql_secure_installation
   ```

4. **Verifique a versão do MariaDB instalada:**
   ```bash
   mariadb --version
   ```

5. **Acesse o MariaDB:**
   ```bash
   mariadb -u root -p
   ```

---

## PostgreSQL

### Passos para Instalação:
1. **Instale o PostgreSQL via Homebrew:**
   ```bash
   brew install postgresql
   ```

2. **Inicie o serviço do PostgreSQL:**
   ```bash
   brew services start postgresql
   ```

3. **Verifique a versão do PostgreSQL instalada:**
   ```bash
   psql --version
   ```

4. **Acesse o PostgreSQL:**
   Troque para o usuário `postgres` e acesse o banco de dados:
   ```bash
   psql postgres
   ```

5. **Configure uma senha para o usuário `postgres` (opcional):**
   No terminal do PostgreSQL:
   ```sql
   ALTER USER postgres PASSWORD 'sua_senha';
   ```

---

## SQL Server

### Passos para Instalação:
1. **Adicione o repositório do SQL Server:**
   Baixe e instale o pacote de configuração do SQL Server para macOS.

2. **Instale o Docker (necessário para o SQL Server no macOS):**
   Baixe o Docker para macOS [aqui](https://www.docker.com/products/docker-desktop) e siga as instruções de instalação.

3. **Baixe a imagem do SQL Server no Docker:**
   ```bash
   docker pull mcr.microsoft.com/mssql/server:2019-latest
   ```

4. **Inicie um contêiner com o SQL Server:**
   ```bash
   docker run -e 'ACCEPT_EULA=Y' -e 'SA_PASSWORD=SuaSenhaForte123' \
   -p 1433:1433 --name sqlserver \
   -d mcr.microsoft.com/mssql/server:2019-latest
   ```

5. **Verifique a versão do SQL Server:**
   Entre no contêiner e execute:
   ```bash
   docker exec -it sqlserver /opt/mssql-tools/bin/sqlcmd -S localhost -U sa -P 'SuaSenhaForte123' -Q "SELECT @@VERSION;"
   ```

6. **Acesse o SQL Server:**
   ```bash
   sqlcmd -S localhost -U sa -P 'SuaSenhaForte123'
   ```

---

Siga os passos para o banco de dados desejado. Certifique-se de que o macOS está atualizado para evitar problemas durante a instalação.

