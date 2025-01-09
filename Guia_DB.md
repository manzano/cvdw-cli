# Guia de Instalação de Bancos de Dados no WSL

Este documento fornece instruções para instalar e configurar os bancos de dados MySQL, MariaDB, PostgreSQL e SQL Server no Windows Subsystem for Linux (WSL).

---

## MySQL

### Passos para Instalação:
1. **Atualize os pacotes do sistema:**
   ```bash
   sudo apt update && sudo apt upgrade -y
   ```

2. **Instale o MySQL:**
   ```bash
   sudo apt install mysql-server -y
   ```

3. **Inicie o serviço do MySQL:**
   ```bash
   sudo service mysql start
   ```

4. **Proteja a instalação do MySQL:**
   ```bash
   sudo mysql_secure_installation
   ```
   Responda às perguntas conforme necessário para configurar a segurança.

5. **Acesse o MySQL:**
   ```bash
   sudo mysql -u root -p
   ```

---

## MariaDB

### Passos para Instalação:
1. **Atualize os pacotes do sistema:**
   ```bash
   sudo apt update && sudo apt upgrade -y
   ```

2. **Instale o MariaDB:**
   ```bash
   sudo apt install mariadb-server mariadb-client -y
   ```

3. **Inicie o serviço do MariaDB:**
   ```bash
   sudo service mysql start
   ```

4. **Proteja a instalação do MariaDB:**
   ```bash
   sudo mysql_secure_installation
   ```

5. **Acesse o MariaDB:**
   ```bash
   sudo mysql -u root -p
   ```

---

## PostgreSQL

### Passos para Instalação:
1. **Atualize os pacotes do sistema:**
   ```bash
   sudo apt update && sudo apt upgrade -y
   ```

2. **Instale o PostgreSQL:**
   ```bash
   sudo apt install postgresql postgresql-contrib -y
   ```

3. **Inicie o serviço do PostgreSQL:**
   ```bash
   sudo service postgresql start
   ```

4. **Acesse o PostgreSQL:**
   Troque para o usuário `postgres` e acesse o banco de dados:
   ```bash
   sudo -i -u postgres
   psql
   ```

5. **Configure uma senha para o usuário `postgres` (opcional):**
   No terminal do PostgreSQL:
   ```sql
      \u ALTER USER postgres PASSWORD 'sua_senha';
   ```

---

## SQL Server

### Passos para Instalação:
1. **Atualize os pacotes do sistema:**
   ```bash
   sudo apt update && sudo apt upgrade -y
   ```

2. **Importe a chave GPG do Microsoft SQL Server:**
   ```bash
   curl -sSL https://packages.microsoft.com/keys/microsoft.asc | sudo apt-key add -
   ```

3. **Adicione o repositório do SQL Server:**
   ```bash
   sudo add-apt-repository "$(wget -qO- https://packages.microsoft.com/config/ubuntu/$(lsb_release -rs)/prod.list)"
   ```

4. **Instale o SQL Server:**
   ```bash
   sudo apt update
   sudo apt install -y mssql-server
   ```

5. **Configure o SQL Server:**
   ```bash
   sudo /opt/mssql/bin/mssql-conf setup
   ```
   Siga as instruções e defina uma senha para o usuário `sa`.

6. **Instale as ferramentas de linha de comando do SQL Server (opcional):**
   ```bash
   sudo apt install -y mssql-tools unixodbc-dev
   ```
   Adicione as ferramentas ao PATH (no `~/.bashrc` ou `~/.zshrc`):
   ```bash
   export PATH="/opt/mssql-tools/bin:$PATH"
   ```
   Atualize o terminal:
   ```bash
   source ~/.bashrc
   ```

7. **Verifique o status do SQL Server:**
   ```bash
   systemctl status mssql-server
   ```

8. **Acesse o SQL Server:**
   ```bash
   sqlcmd -S localhost -U sa -P 'sua_senha'
   ```

---

Repita os passos para o banco de dados desejado. Certifique-se de que o WSL está configurado corretamente para o seu sistema operacional. Caso tenha dúvidas, consulte a documentação oficial do banco de dados ou do WSL.

