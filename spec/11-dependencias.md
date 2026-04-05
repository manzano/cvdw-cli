# Dependencias

## Dependencias de Producao

### Framework e CLI

| Pacote | Versao | Descricao |
|--------|--------|-----------|
| `symfony/console` | ^6.3 | Framework CLI (comandos, input/output, progress bars) |
| `symfony/dotenv` | ^6.3 | Carregamento de arquivos `.env` |
| `symfony/yaml` | ^6.3 | Parser de YAML (definicoes de objetos) |
| `symfony/finder` | ^6.3 | Busca de arquivos no sistema de arquivos |
| `symfony/process` | ^7.0 | Execucao de processos do sistema |

### Banco de Dados

| Pacote | Versao | Descricao |
|--------|--------|-----------|
| `doctrine/dbal` | ^3.7 | Camada de abstracao de banco de dados |
| `doctrine/migrations` | ^3.6 | Gerenciamento de migracoes de schema |
| `doctrine/doctrine-migrations-bundle` | ^3.2 | Bundle de migracoes |
| `doctrine/sql-formatter` | ^1.1 | Formatacao de queries SQL |

### Monitoramento

| Pacote | Versao | Descricao |
|--------|--------|-----------|
| `sentry/sentry` | ^4.8 | Rastreamento de erros em producao |
| `monolog/monolog` | ^3.8 | Sistema de logging estruturado |
| `mixpanel/mixpanel-php` | ^2.11 | Analytics e telemetria de uso |

### Utilitarios

| Pacote | Versao | Descricao |
|--------|--------|-----------|
| `fakerphp/faker` | ^1.23 | Geracao de dados falsos (usado para anonimizacao e testes) |

---

## Dependencias de Desenvolvimento

| Pacote | Versao | Descricao |
|--------|--------|-----------|
| `phpstan/phpstan` | ^1.10 | Analise estatica de codigo |
| `phpunit/phpunit` | ^11 | Framework de testes unitarios |
| `codeception/codeception` | ^5.1 | Framework de testes BDD/API |
| `codeception/module-phpbrowser` | ^3.0 | Modulo de browser para testes API |
| `codeception/module-rest` | ^3.3 | Modulo REST para testes API |
| `codeception/module-asserts` | ^3.0 | Modulo de assercoes |
| `friendsofphp/php-cs-fixer` | ^3.75 | Formatacao e padrao de codigo |
| `phpmd/phpmd` | ^2.15 | Deteccao de problemas de codigo |
| `enlightn/security-checker` | * | Verificacao de vulnerabilidades em dependencias |

---

## Requisitos de Sistema

### PHP

| Requisito | Valor |
|-----------|-------|
| Versao minima | 8.0 (composer.json) |
| Versao recomendada | 8.2+ (README) |
| Versao Docker | 8.3 |

### Extensoes PHP Necessarias

| Extensao | Descricao |
|----------|-----------|
| `pdo_mysql` | Conexao MySQL/MariaDB |
| `pdo_pgsql` | Conexao PostgreSQL |
| `zip` | Manipulacao de arquivos ZIP |
| `curl` | Requisicoes HTTP |
| `json` | Codificacao/decodificacao JSON |
| `mbstring` | Manipulacao de strings multibyte |

### Ferramentas

| Ferramenta | Descricao |
|------------|-----------|
| Composer | Gerenciador de dependencias PHP |
| Git | Controle de versao (para instalacao) |
| cURL | Para script de atualizacao |

---

## Autoload

### PSR-4

```json
{
    "autoload": {
        "psr-4": {
            "Manzano\\CvdwCli\\": "src/app/"
        }
    }
}
```

### Files (carregados automaticamente)

```json
{
    "autoload": {
        "files": [
            "src/app/Inc/Helper.php",
            "src/app/Inc/Conexao.php",
            "src/app/Inc/Exception.php"
        ]
    }
}
```

O autoload por arquivo e usado para as classes utilitarias em `Inc/` que precisam estar disponives globalmente.

---

## Binario

```json
{
    "bin": ["app"]
}
```

O Composer registra `app` como binario executavel, disponivel via `vendor/bin/app`.

---

## Scripts

```json
{
    "scripts": {
        "test": "codecept run --coverage --coverage-xml Unit"
    }
}
```

Executavel via `composer test`.
