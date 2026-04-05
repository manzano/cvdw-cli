# Banco de Dados

## Bancos Suportados

| Banco | Driver Doctrine | Porta Padrao |
|-------|-----------------|--------------|
| MySQL | `pdo_mysql` | 3306 |
| MariaDB | `pdo_mysql` | 3306 |
| PostgreSQL | `pdo_pgsql` | 5432 |
| SQL Server | `sqlsrv` | 1433 |

---

## Conexao

**Classe:** `Manzano\CvdwCli\Inc\Conexao`
**Metodo:** `conectarDB(envManager): Connection`

Parametros de conexao lidos do `EnvironmentManager`:

```php
[
    'dbname'   => $envManager->getDbDatabase(),
    'user'     => $envManager->getDbUsername(),
    'password' => $envManager->getDbPassword(),
    'host'     => $envManager->getDbHost(),
    'port'     => $envManager->getDbPort(),
    'driver'   => $envManager->getDbConnection(),
]
```

### Tratamento Especial para PostgreSQL

- Se `DB_SCHEMA` definido (e nao vazio), cria schema automaticamente:
  ```sql
  CREATE SCHEMA IF NOT EXISTS {schema}
  ```
- Define search_path:
  ```sql
  SET search_path TO {schema}
  ```
- Default: `public`

---

## Tabela de Sistema: `_requisicoes`

Tabela interna usada pelo servico `RateLimit` para rastrear requisicoes a API.

### Schema

```sql
CREATE TABLE _requisicoes (
    idrequisicao     INT AUTO_INCREMENT PRIMARY KEY,
    data_inicio      DATETIME NOT NULL,
    data_fim         DATETIME NULL,
    objeto           VARCHAR(255) NOT NULL,
    dados_retorno_qtd INT NULL DEFAULT 0,
    header_resultado TEXT NULL
);
```

### Operacoes

| Operacao | Descricao |
|----------|-----------|
| INSERT | Registra inicio de requisicao |
| UPDATE | Atualiza com dados de retorno e hora de fim |
| SELECT | Consulta rate limit (requisicoes no ultimo minuto) |
| DELETE | Remove registros com mais de 30 dias |

### Queries de Rate Limit

**MySQL:**
```sql
SELECT TIMESTAMPDIFF(SECOND, data_inicio, NOW()) as diferenca
FROM _requisicoes
ORDER BY idrequisicao DESC
LIMIT 1 OFFSET 19
```

**PostgreSQL:**
```sql
SELECT EXTRACT(EPOCH FROM (NOW() - data_inicio))::integer as diferenca
FROM _requisicoes
ORDER BY idrequisicao DESC
LIMIT 1 OFFSET 19
```

---

## Tabelas Dinamicas (por Objeto)

Cada um dos 70 objetos gera uma tabela no banco. A estrutura e definida pelo arquivo YAML correspondente.

### Colunas Padrao (todas as tabelas)

| Coluna | Tipo | Constraint | Descricao |
|--------|------|-----------|-----------|
| `referencia` | VARCHAR(255) | UNIQUE | ID unico do registro na API |
| `referencia_data` | DATETIME | - | Data da ultima modificacao |
| `ativo` | VARCHAR(1) | - | "S" ou "N" |

### Colunas Especificas

Variam conforme o objeto. Tipos mapeados de YAML para SQL:

| Tipo YAML | MySQL | PostgreSQL | SQL Server |
|-----------|-------|-----------|-----------|
| string(N) | VARCHAR(N) | VARCHAR(N) | NVARCHAR(N) |
| integer | INT | INTEGER | INT |
| float | DECIMAL | DECIMAL | DECIMAL |
| datetime | DATETIME | TIMESTAMP | DATETIME2 |
| text | TEXT | TEXT | NTEXT |
| boolean | VARCHAR(5) | VARCHAR(5) | NVARCHAR(5) |

---

## Sub-tabelas

### Criacao Automatica

Quando o YAML define um campo do tipo `array` ou `object`, o sistema cria uma sub-tabela:

**Convencao de Nome:** `{tabela_pai}_sub_{nome_campo}`

**Exemplo:**
- Tabela pai: `leads`
- Campo: `campos_adicionais` (tipo array)
- Sub-tabela: `leads_sub_campos_adicionais`

### Estrutura de Sub-tabela

| Coluna | Tipo | Descricao |
|--------|------|-----------|
| `referencia` | VARCHAR(255) | FK para tabela pai |
| campos do array | variavel | Campos do objeto aninhado |

### Fluxo de INSERT em Sub-tabela

1. Registro pai e processado normalmente
2. Campo array detectado em `trataDados()`
3. Registros existentes da sub-tabela com mesma `referencia` sao deletados
4. Novos registros sao inseridos na sub-tabela
5. Campo original na tabela pai recebe `NULL` ou e ignorado

---

## Gerenciamento de Schema

### Criacao Inicial

```
cvdw configurar -> Opcao 3 (Criar tabelas)
```

1. Itera sobre todos os 70 objetos YAML
2. Para cada objeto, cria tabela com todas as colunas
3. Cria sub-tabelas quando necessario
4. Cria tabela `_requisicoes` se nao existir

### Sincronizacao de Schema

Na execucao do `configurar`, o sistema compara o schema atual com as definicoes YAML:

1. **Colunas novas** na API -> `ALTER TABLE ADD COLUMN`
2. **Colunas removidas** da API -> `ALTER TABLE DROP COLUMN`
3. **Tipo alterado** -> `ALTER TABLE MODIFY COLUMN`

### Operacoes de Manutencao

| Opcao | Comando SQL | Uso |
|-------|-------------|-----|
| Limpar data referencia | `UPDATE SET referencia_data = NULL` | Forcar re-sync completo |
| Limpar tabelas (TRUNCATE) | `TRUNCATE TABLE` | Apagar dados mantendo estrutura |
| Apagar tabelas (DROP) | `DROP TABLE` | Remover tabela completamente |

---

## Configuracao via Variaveis de Ambiente

```env
DB_CONNECTION=pdo_mysql     # Driver: pdo_mysql, pdo_pgsql, sqlsrv
DB_HOST=localhost           # Host do servidor
DB_PORT=3306                # Porta
DB_DATABASE=cvdw            # Nome do banco
DB_USERNAME=cvdw_user       # Usuario
DB_PASSWORD=cvdw_pwd        # Senha
DB_SCHEMA=public            # Schema (apenas PostgreSQL)
```
