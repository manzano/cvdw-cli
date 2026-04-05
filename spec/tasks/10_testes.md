# 10 - Estrategia de Testes & Cobertura

## Objetivo

Estabelecer cobertura abrangente de testes em todas as camadas. A meta e ter confianca em cada mudanca - testes unitarios para logica, testes de integracao para banco, e testes funcionais para comandos CLI.

---

## Estrutura de Testes

```
tests/
├── Unitario/
│   ├── Dominio/
│   │   ├── Entidade/
│   │   │   ├── ObjetoSyncTest.php
│   │   │   ├── CampoTest.php
│   │   │   └── RequisicaoApiTest.php
│   │   ├── ObjetoValor/
│   │   │   ├── PaginacaoTest.php
│   │   │   ├── CredenciaisTest.php
│   │   │   ├── ResultadoSyncTest.php
│   │   │   └── TipoCampoTest.php
│   │   └── Servico/
│   │       ├── PoliticaRateLimitTest.php
│   │       ├── SanitizadorDadosTest.php
│   │       └── ResolvedorNomeColunaTest.php
│   ├── Aplicacao/
│   │   ├── CasoUso/
│   │   │   ├── CasoUsoSincronizarObjetoTest.php
│   │   │   ├── CasoUsoSincronizarTodosTest.php
│   │   │   ├── CasoUsoConfigurarBancoTest.php
│   │   │   └── CasoUsoConfigurarTest.php
│   │   └── Servico/
│   │       └── TemporizadorExecucaoTest.php
│   └── Infraestrutura/
│       ├── Api/
│       │   ├── ClienteCvCrmTest.php
│       │   └── ClienteComRateLimitTest.php
│       ├── Config/
│       │   ├── CarregadorAmbienteTest.php
│       │   └── ConfiguracaoAppTest.php
│       └── SistemaArquivos/
│           ├── ParserObjetoYamlTest.php
│           └── ValidadorObjetoTest.php
│
├── Integracao/
│   ├── BancoDados/
│   │   ├── FabricaConexaoTest.php
│   │   ├── GerenciadorEsquemaDoctrineTest.php
│   │   ├── RepositorioRegistroDoctrineTest.php
│   │   └── LimitadorRateBancoTest.php
│   └── SistemaArquivos/
│       └── RepositorioObjetoYamlTest.php
│
├── Funcional/
│   ├── ComandoSyncTest.php
│   ├── ComandoSetupTest.php
│   ├── ComandoStatusTest.php
│   └── ComandoTabelaTest.php
│
└── Fixtures/
    ├── yaml/
    │   ├── valid_simple.yaml
    │   ├── valid_with_subtable.yaml
    │   └── invalid_missing_path.yaml
    └── api/
        ├── success_response.json
        ├── empty_response.json
        └── error_429.json
```

---

## Principios de Teste

1. **Testes unitarios sao rapidos**: sem I/O, sem banco, sem HTTP. Mockar todas as interfaces.
2. **Testes de integracao usam banco real**: SQLite in-memory ou Docker MySQL/PG.
3. **Testes funcionais usam CommandTester**: simulam execucao completa do CLI.
4. **Fixtures ao inves de mocks para dados**: arquivos JSON/YAML para respostas da API e definicoes de objetos.
5. **Cada camada testada independentemente**: Domain nunca importa Infrastructure nos testes.

---

## Cenarios Chave de Teste

### Camada de Dominio (logica pura, sem mocks necessarios)

**PoliticaRateLimit:**
```php
public function testSemEsperaQuandoAbaixoDoLimite(): void
{
    $politica = new PoliticaRateLimit();
    $this->assertSame(0, $politica->calcularSegundosEspera(15, null));
}

public function testEsperaQuandoNoLimite(): void
{
    $politica = new PoliticaRateLimit();
    // 20 req, 20a foi 30s atras -> espera 33s (60-30+3)
    $this->assertSame(33, $politica->calcularSegundosEspera(20, 30));
}
```

**SanitizadorDados:**
- Anonimizar com asteriscos preserva inicio/fim
- Anonimizar com hash e deterministico
- Datas invalidas viram null
- Strings vazias viram null
- Trunca ao tamanho maximo

### Camada de Aplicacao (repositorios mockados)

**CasoUsoSincronizarObjeto:**
- Sincroniza registros: insere novos, atualiza existentes
- Respeita maximoPaginas
- Respeita limite de tempo
- Usa dataDesde quando fornecido
- Aborta apos 3 erros consecutivos
- Observador notificado em cada etapa

### Camada de Infraestrutura (integracao com banco real)

**RepositorioRegistroDoctrine:**
- inserir + existe retorna true
- atualizar modifica registro
- upsert insere novo / atualiza existente
- obterUltimaDataReferencia retorna max / null

### Funcional (comandos CLI)

**ComandoSync:**
- Sincronizar objeto unico
- Objeto invalido mostra erro
- Sincronizar todos os objetos
- Limite de tempo para graciosamente

---

## Requisitos de Cobertura

| Camada | Cobertura Minima | Meta |
|--------|-----------------|------|
| Domain | 95% | 100% |
| Application | 90% | 95% |
| Infrastructure | 80% | 90% |
| UI/Commands | 70% | 80% |
| **Geral** | **85%** | **90%** |

---

## Scripts de CI

```json
{
    "scripts": {
        "test": "phpunit",
        "test:unitario": "phpunit --testsuite=Unitario",
        "test:integracao": "phpunit --testsuite=Integracao",
        "test:funcional": "phpunit --testsuite=Funcional",
        "test:cobertura": "phpunit --coverage-html=var/coverage",
        "analyse": "phpstan analyse",
        "cs": "php-cs-fixer fix --dry-run --diff",
        "cs:fix": "php-cs-fixer fix",
        "qa": ["@cs", "@analyse", "@test"]
    }
}
```

`composer qa` roda todas as verificacoes: estilo, analise estatica e testes.

---

## Criterios de Aceite

- [ ] Estrutura de testes espelha as camadas da arquitetura
- [ ] Testes unitarios cobrem Domain e Application sem I/O
- [ ] Testes de integracao rodam contra banco real
- [ ] Testes funcionais cobrem todos os comandos CLI
- [ ] Fixtures para objetos YAML e respostas da API
- [ ] Cobertura >= 85% geral
- [ ] Cobertura do Domain >= 95%
- [ ] `composer qa` roda todas as verificacoes
- [ ] Configuracao pronta para CI
