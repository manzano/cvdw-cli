# 00 - Bootstrap do Projeto & Fundacao Clean Architecture

## Objetivo

Criar o projeto do zero com PHP 8.3+, Clean Architecture e stack de dependencias modernas. Este e o esqueleto sobre o qual todas as outras tasks constroem.

---

## Premissa Importante: Idioma no Codigo

- **Todo o codigo** (variaveis, funcoes, classes, metodos, namespaces) sera em **portugues**
- **Documentacao, tasks, specs e mensagens pro usuario** permanecem em **portugues**
- Os 70 arquivos YAML existentes permanecem como estao (chaves em portugues)
- O parser ira mapear internamente as chaves PT -> propriedades PT

---

## Visao da Arquitetura

```
src/
├── Dominio/                    # Logica de negocio pura, zero dependencias
│   ├── Entidade/               # Entidades (ObjetoSync, Requisicao, Ambiente)
│   ├── ObjetoValor/            # Tipos imutaveis (Referencia, Paginacao, Credenciais)
│   ├── Repositorio/            # Apenas interfaces (sem implementacoes)
│   ├── Servico/                # Servicos de dominio (PoliticaSync, PoliticaLimiteRequisicao)
│   └── Excecao/                # Excecoes especificas do dominio
│
├── Aplicacao/                  # Casos de uso / orquestracao
│   ├── CasoDeUso/              # Uma classe por caso de uso (SincronizarObjeto, ConfigurarBanco)
│   ├── Porta/                  # Interfaces de entrada/saida (observers)
│   └── DTO/                    # Objetos de transferencia entre camadas
│
├── Infraestrutura/             # Adaptadores para o mundo externo
│   ├── Api/                    # Cliente HTTP do CV CRM
│   ├── BancoDeDados/           # Repositorios Doctrine DBAL, gerenciador de schema
│   ├── Configuracao/           # Carregador de ambiente, handler de .env
│   ├── SistemaArquivos/        # Parser YAML, escritor de log
│   └── Monitoramento/          # Sentry, telemetria
│
├── Interface/                  # Camada de apresentacao (CLI)
│   ├── Comando/                # Comandos Symfony Console
│   ├── Formatador/             # Formatadores de saida, tabelas, progresso
│   └── Estilo/                 # SymfonyStyle customizado com identidade visual
│
├── bootstrap.php               # Autoload + setup do container DI
└── cvdw                        # Ponto de entrada (fino)
```

---

## Decisoes Tecnicas

| Decisao | Escolha | Justificativa |
|---------|---------|---------------|
| Versao PHP | 8.3+ | Typed properties, enums, fibers, readonly |
| Container DI | `php-di/php-di` | Leve, sem lock-in de framework |
| Banco de Dados | Doctrine DBAL 4.x | Versao mais recente, tipos melhores |
| Console | Symfony Console 7.x | Ultimo estavel |
| Cliente HTTP | Symfony HttpClient | Substitui cURL cru, testavel |
| YAML | Symfony Yaml 7.x | Ja familiar |
| Testes | PHPUnit 11 + Mockery | Mocking limpo |
| Estilo de Codigo | PHP-CS-Fixer + PHPStan level 8 | Rigoroso desde o dia 1 |
| Logging | Monolog 3.x | Padrao da industria |

---

## Tarefas

### 1. Inicializar o projeto

```bash
mkdir cvdw-cli-v2 && cd cvdw-cli-v2
composer init --name="manzano/cvdw-cli" --type="project" --license="MIT"
```

### 2. Instalar dependencias core

```json
{
    "require": {
        "php": "^8.3",
        "symfony/console": "^7.0",
        "symfony/dotenv": "^7.0",
        "symfony/yaml": "^7.0",
        "symfony/http-client": "^7.0",
        "doctrine/dbal": "^4.0",
        "monolog/monolog": "^3.8",
        "php-di/php-di": "^7.0"
    },
    "require-dev": {
        "phpunit/phpunit": "^11.0",
        "phpstan/phpstan": "^2.0",
        "friendsofphp/php-cs-fixer": "^3.75",
        "mockery/mockery": "^1.6"
    }
}
```

### 3. Criar estrutura de diretorios

Criar todos os diretorios listados na visao da arquitetura. Adicionar `.gitkeep` nos vazios.

### 4. Configurar autoload

```json
{
    "autoload": {
        "psr-4": {
            "CvdwCli\\": "src/"
        }
    },
    "autoload-dev": {
        "psr-4": {
            "CvdwCli\\Testes\\": "tests/"
        }
    },
    "bin": ["src/cvdw"]
}
```

Convencao de namespace: `CvdwCli\Dominio\...`, `CvdwCli\Aplicacao\...`, `CvdwCli\Infraestrutura\...`, `CvdwCli\Interface\...`

### 5. Criar ponto de entrada (`src/cvdw`)

Ponto de entrada fino. Tudo que faz:
1. Require autoload
2. Constroi o container DI
3. Obtem o Application do Symfony do container
4. Executa

```php
#!/usr/bin/env php
<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

$container = require __DIR__ . '/bootstrap.php';
$aplicacao = $container->get(Symfony\Component\Console\Application::class);
exit($aplicacao->run());
```

### 6. Criar `bootstrap.php`

Constroi o container DI:
- Registra todos os servicos
- Liga interfaces a implementacoes
- Configura Application com versao e comandos

### 7. Configurar PHPStan (`phpstan.neon`)

```neon
parameters:
    level: 8
    paths:
        - src
    excludePaths:
        - src/cvdw
```

### 8. Configurar PHP-CS-Fixer (`.php-cs-fixer.php`)

PSR-12 rigoroso + regras modernas:
- `declare_strict_types`
- `final_class` onde aplicavel
- `strict_comparison`
- `no_unused_imports`

### 9. Configurar PHPUnit (`phpunit.xml`)

```xml
<testsuites>
    <testsuite name="Unitario">
        <directory>tests/Unitario</directory>
    </testsuite>
    <testsuite name="Integracao">
        <directory>tests/Integracao</directory>
    </testsuite>
</testsuites>
```

### 10. Criar `.gitignore`

```
/vendor/
/.env
/envs/*.env
/logs/
/.phpunit.cache/
/.php-cs-fixer.cache
/var/
```

---

## Criterios de Aceite

- [ ] `composer install` roda sem erros
- [ ] `php src/cvdw` executa e mostra help do Symfony Console
- [ ] `vendor/bin/phpstan analyse` passa no level 8
- [ ] `vendor/bin/php-cs-fixer fix --dry-run` passa
- [ ] `vendor/bin/phpunit` roda (0 testes, 0 erros)
- [ ] Estrutura de diretorios corresponde ao diagrama da arquitetura
- [ ] Todo codigo usa `declare(strict_types=1)`
- [ ] Todo codigo em portugues (variaveis, funcoes, classes, metodos)
