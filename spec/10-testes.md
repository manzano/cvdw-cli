# Testes

## Visao Geral

O CVDW-CLI possui tres suites de teste usando Codeception (para API e BDD) e PHPUnit (para testes unitarios).

---

## Frameworks de Teste

| Framework | Versao | Uso |
|-----------|--------|-----|
| Codeception | ^5.1 | Testes de API e funcionais |
| PHPUnit | ^11 | Testes unitarios |
| PHP CS Fixer | ^3.75 | Padrao de codigo |
| PHPStan | ^1.10 | Analise estatica |
| PHPMD | ^2.15 | Deteccao de problemas |

---

## Estrutura de Testes

```
tests/
├── ApiCVDW/                    # Testes de integracao API
│   ├── ApiCVDW.suite.yml       # Configuracao da suite
│   ├── Common.php              # Classe base compartilhada
│   ├── LeadsCest.php           # Teste de leads
│   ├── ReservasCest.php        # Teste de reservas
│   ├── ... (56 classes Cest)
│   └── _bootstrap.php
├── Unit/                       # Testes unitarios
│   ├── Unit.suite.yml          # Configuracao da suite
│   ├── AmbientesCvdwTest.php   # Teste do servico Ambientes
│   ├── ConexaoTest.php         # Teste de conexao
│   ├── ... (12 classes Test)
│   └── _bootstrap.php
├── Functional/                 # Testes funcionais
│   ├── Functional.suite.yml    # Configuracao da suite
│   └── CvdwTest.php
├── Support/
│   ├── ApiTester.php           # Actor API
│   ├── UnitTester.php          # Actor Unit
│   ├── FunctionalTester.php    # Actor Functional
│   ├── Helper/
│   │   └── CvdwHelper.php     # Helper customizado
│   └── Data/                   # Dados de teste
├── ValidarYamls.php            # Validador de YAMLs
├── 00_tratarYamls.php          # Processador de YAMLs
├── 01_compararJsons.php        # Comparador de JSONs
├── _gerar.php                  # Gerador de testes
├── _template.tmp               # Template de teste
└── _output/                    # Saida dos testes
```

---

## Suite: ApiCVDW (Testes de API)

### Configuracao (`ApiCVDW.suite.yml`)

```yaml
actor: ApiTester
modules:
  enabled:
    - PhpBrowser:
        url: https://dev.cvcrm.com.br/api/v1/cvdw
        timeout: 60
    - REST
    - CvdwHelper
```

### Classe Base: `Common.php`

Setup compartilhado para todos os testes API:

1. Carrega ambiente via `Ambientes::retornarEnvs()`
2. Define headers HTTP:
   - `Content-Type: application/json`
   - `email: {CV_EMAIL}`
   - `token: {CV_TOKEN}`
3. Armazena configuracao para acesso nos testes

### Padrao de Teste API (Cest)

Cada objeto tem uma classe Cest com o seguinte padrao:

```php
class ObjetoCest extends Common
{
    private array $responseContent = [
        'pagina' => 'integer',
        'registros_por_pagina' => 'integer',
        'total_de_registros' => 'integer',
        'total_de_paginas' => 'integer',
        'dados' => 'array',
    ];

    public function testEndpoint(ApiTester $I)
    {
        // 1. Mede tempo de resposta
        $start = microtime(true);

        // 2. Envia requisicao GET com paginacao
        $I->sendGet('/endpoint', [
            'pagina' => 1,
            'registros_por_pagina' => 5
        ]);

        // 3. Verifica tempo < 5 segundos
        $elapsed = microtime(true) - $start;
        $I->assertLessThan(5, $elapsed);

        // 4. Valida status HTTP 200
        $I->seeResponseCodeIs(200);

        // 5. Valida estrutura JSON
        $I->seeResponseMatchesJsonType($this->responseContent);

        // 6. Valida dados retornados
        $response = json_decode($I->grabResponse(), true);
        $I->assertGreaterThanOrEqual(0, count($response['dados']));
    }

    public function testWithReferenceDate(ApiTester $I)
    {
        usleep(3000000); // Aguarda 3s (rate limit)

        $I->sendGet('/endpoint', [
            'pagina' => 1,
            'registros_por_pagina' => 5,
            'a_partir_data_referencia' => '2024-01-01'
        ]);

        $I->seeResponseCodeIs(200);
    }
}
```

### Objetos Testados (56 testes)

Cada um dos 70 objetos possui um teste API correspondente verificando:
- Tempo de resposta aceitavel (< 5 segundos)
- Status HTTP 200
- Estrutura JSON correta
- Campos obrigatorios presentes
- Filtro por data de referencia funcional

---

## Suite: Unit (Testes Unitarios)

### Configuracao (`Unit.suite.yml`)

```yaml
actor: UnitTester
modules:
  enabled:
    - Asserts
    - CvdwHelper
```

### Testes Disponiveis (12 classes)

| Classe | Servico Testado | Foco |
|--------|----------------|------|
| `AmbientesCvdwTest` | Ambientes | Carregamento de envs, criacao de ambientes |
| `ConexaoTest` | Conexao | Conexao com banco de dados |
| `ExceptionTest` | CvdwException | Excecoes customizadas |
| `HelperTest` | Helper | Validacao de datas, anonimizacao |
| `DatabaseSetupTest` | DatabaseSetup | Criacao de tabelas |
| `EnvironmentManagerTest` | EnvironmentManager | Getters/setters |
| `ObjetoTest` | Objeto | Carregamento de YAMLs |
| `RateLimitTest` | RateLimit | Controle de taxa |
| ... | ... | ... |

### Padrao de Teste Unitario

```php
class AmbientesCvdwTest extends TestCase
{
    private Ambientes $ambientes;
    private array $envBackup;

    protected function setUp(): void
    {
        // Backup de $_ENV
        $this->envBackup = $_ENV;
        $this->ambientes = new Ambientes();
    }

    protected function tearDown(): void
    {
        // Restaura $_ENV
        $_ENV = $this->envBackup;
        // Remove arquivos de teste
    }

    public function testRetornarEnvs(): void
    {
        $envs = $this->ambientes->retornarEnvs();
        $this->assertArrayHasKey('CV_URL', $envs);
        $this->assertArrayHasKey('DB_CONNECTION', $envs);
    }

    public function testCriarAmbiente(): void
    {
        $this->ambientes->criarAmbiente('TEST_UNIT');
        $this->assertFileExists($this->ambientes->getEnvDir('TEST_UNIT'));
    }
}
```

---

## Suite: Functional (Testes Funcionais)

### Configuracao (`Functional.suite.yml`)

```yaml
actor: FunctionalTester
modules:
  enabled:
    - Symfony:
        app_path: src/app/cvdw
    - CvdwHelper
```

Testes de integracao end-to-end que verificam o fluxo completo do aplicativo.

---

## Ferramentas Auxiliares

### ValidarYamls.php

Valida todos os arquivos YAML em `src/app/Objetos/`:
- Verifica sintaxe YAML valida
- Valida campos obrigatorios (path, tabela, metodo, response)
- Verifica tipos de dados reconhecidos
- Reporta inconsistencias

### 00_tratarYamls.php

Processa e normaliza arquivos YAML:
- Formata campos
- Padroniza tipos
- Gera saida limpa

### 01_compararJsons.php

Compara respostas JSON da API com definicoes YAML:
- Detecta campos novos na API nao mapeados no YAML
- Detecta campos removidos da API
- Gera relatorio de diferencas

### _gerar.php + _template.tmp

Gerador automatico de testes:
- Le definicoes YAML
- Gera classes Cest a partir do template
- Cria testes padronizados para novos objetos

---

## Execucao de Testes

### Rodar todos os testes
```bash
vendor/bin/codecept run
```

### Rodar suite especifica
```bash
vendor/bin/codecept run ApiCVDW
vendor/bin/codecept run Unit
vendor/bin/codecept run Functional
```

### Com cobertura
```bash
vendor/bin/codecept run --coverage --coverage-xml Unit
```

### PHPUnit direto
```bash
vendor/bin/phpunit
```

### Analise estatica
```bash
vendor/bin/phpstan analyse
```

### Padrao de codigo
```bash
vendor/bin/php-cs-fixer fix --dry-run
```

---

## Cobertura de Codigo

- Configurada via `phpunit.xml` e `codeception.yml`
- Diretorio fonte: `src/`
- Saida: XML e HTML em `tests/_output/`
- Integrado com SonarCloud para CI/CD

---

## CI/CD

### SonarCloud

Badges no README indicam:
- Quality Gate Status
- Bugs
- Code Smells
- Coverage
- Duplicated Lines

Metricas coletadas automaticamente em cada push/PR.
