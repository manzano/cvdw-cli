
# Guia de Instalação de Bancos de Dados no Windows

Este guia fornece instruções para instalar e configurar os bancos de dados MySQL, MariaDB, PostgreSQL e SQL Server no sistema operacional Windows.

---

## MySQL

### Passos para Instalação:
1. **Baixe o instalador oficial do MySQL:**
   - Acesse: [MySQL Installer](https://dev.mysql.com/downloads/installer/).

2. **Execute o instalador:**
   - Escolha a opção "Developer Default" ou personalize conforme necessário.

3. **Configure o servidor MySQL:**
   - Defina a senha para o usuário `root`.
   - Escolha o tipo de configuração (Standalone ou Server).

4. **Complete a instalação e inicie o serviço.**

### Verificar a Versão:
   ```cmd
   mysql --version
   ```

---

## MariaDB

### Passos para Instalação:
1. **Baixe o instalador oficial do MariaDB:**
   - Acesse: [MariaDB Downloads](https://mariadb.org/download/).

2. **Execute o instalador:**
   - Escolha os componentes necessários (ex.: servidor e cliente).

3. **Configure o servidor MariaDB:**
   - Defina a senha do usuário `root`.

4. **Inicie o serviço MariaDB:**
   - Use o "MariaDB Service Monitor" ou inicie manualmente pelo Gerenciador de Tarefas.

### Verificar a Versão:
   ```cmd
   mariadb --version
   ```

---

## PostgreSQL

### Passos para Instalação:
1. **Baixe o instalador oficial do PostgreSQL:**
   - Acesse: [PostgreSQL Downloads](https://www.postgresql.org/download/).

2. **Execute o instalador:**
   - Selecione os componentes desejados (ex.: pgAdmin e ferramentas de linha de comando).

3. **Defina uma senha para o usuário `postgres`.**

4. **Complete a instalação e inicie o serviço.**

### Verificar a Versão:
   ```cmd
   psql --version
   ```

---

## SQL Server

### Passos para Instalação:
1. **Baixe o instalador oficial do SQL Server:**
   - Acesse: [SQL Server Downloads](https://www.microsoft.com/en-us/sql-server/sql-server-downloads).

2. **Execute o instalador:**
   - Escolha a edição desejada (ex.: Developer ou Express).

3. **Configure o SQL Server:**
   - Defina uma senha para o usuário `sa`.
   - Escolha o tipo de autenticação (Windows ou mista).

4. **Instale o SQL Server Management Studio (SSMS):**
   - Acesse: [SSMS Downloads](https://learn.microsoft.com/en-us/sql/ssms/download-sql-server-management-studio-ssms).

5. **Inicie o serviço SQL Server:**
   - Use o "SQL Server Configuration Manager" ou o "Serviços do Windows".

### Verificar a Versão:
   ```cmd
   sqlcmd -? | findstr Version
   ```

---

Repita os passos para o banco de dados desejado. Certifique-se de que o sistema operacional está atualizado e que você tem as permissões necessárias para instalar softwares.

