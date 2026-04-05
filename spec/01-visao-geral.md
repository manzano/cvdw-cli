# CVDW-CLI - Visao Geral

## Descricao

CVDW-CLI (CV CRM Data Warehouse Command-Line Interface) e uma ferramenta CLI em PHP que sincroniza dados da API do CV CRM para um banco de dados local ou remoto. Projetada para facilitar a criacao de dashboards e analise de dados do ecossistema imobiliario CV CRM.

## Informacoes do Projeto

| Campo | Valor |
|-------|-------|
| **Nome** | cvdw-cli |
| **Autor** | Gabriel Manzano (gabriel@cvcrm.com.br) |
| **Licenca** | MIT |
| **Linguagem** | PHP 8.0+ (recomendado 8.2+) |
| **Framework CLI** | Symfony Console 6.3 |
| **ORM/DBAL** | Doctrine DBAL 3.7 |
| **Versao Atual** | v1.10.3 |
| **Repositorio** | manzano/cvdw-cli (GitHub) |

## Arquitetura de Alto Nivel

```
┌─────────────────────────────────────────────────┐
│                  CVDW-CLI                        │
│                                                  │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐      │
│  │configurar│  │ executar  │  │  opcoes   │      │
│  │ Command  │  │ Command   │  │ Command   │      │
│  └────┬─────┘  └────┬──────┘  └──────────┘      │
│       │              │                            │
│  ┌────┴──────────────┴─────────────────────┐     │
│  │            Services Layer               │     │
│  │                                         │     │
│  │  ┌─────────┐ ┌──────┐ ┌─────────────┐  │     │
│  │  │Ambientes│ │ Http │ │DatabaseSetup│  │     │
│  │  └─────────┘ └──────┘ └─────────────┘  │     │
│  │  ┌──────┐ ┌─────────┐ ┌──────────┐     │     │
│  │  │ Cvdw │ │RateLimit│ │  Objeto  │     │     │
│  │  └──────┘ └─────────┘ └──────────┘     │     │
│  │  ┌──────┐ ┌───────────────────────┐     │     │
│  │  │ Log  │ │EnvironmentManager    │     │     │
│  │  └──────┘ └───────────────────────┘     │     │
│  └─────────────────────────────────────────┘     │
│                                                  │
│  ┌─────────────────────────────────────────┐     │
│  │          Infrastructure Layer           │     │
│  │  ┌────────┐ ┌────────┐ ┌───────────┐   │     │
│  │  │Conexao │ │Helper  │ │Exception  │   │     │
│  │  └────────┘ └────────┘ └───────────┘   │     │
│  └─────────────────────────────────────────┘     │
└─────────────────────────────────────────────────┘
         │                         │
         ▼                         ▼
┌─────────────────┐     ┌──────────────────┐
│   CV CRM API    │     │  Banco de Dados  │
│  (REST/HTTPS)   │     │ MySQL/PgSQL/MSSQL│
└─────────────────┘     └──────────────────┘
```

## Fluxo Principal de Execucao

```
1. Usuario executa: cvdw executar [objeto]
2. Ambientes carrega configuracao (.env)
3. Conexao estabelece conexao com banco de dados
4. Objeto carrega definicoes YAML do objeto selecionado
5. Http faz requisicao paginada a API do CV CRM
6. RateLimit controla taxa de requisicoes (max 20/min)
7. Cvdw processa cada registro:
   a. Verifica se registro existe (via campo 'referencia')
   b. INSERT se novo, UPDATE se existente
   c. Aplica anonimizacao se configurado
   d. Trata sub-tabelas para dados aninhados
8. Progresso exibido via barra de progresso
9. Estatisticas finais: inseridos, alterados, erros
```

## Estrutura de Diretorios

```
cvdw-cli/
├── src/
│   ├── cvdw                      # Entry point principal
│   ├── app/
│   │   ├── Configurar.php        # Comando de configuracao
│   │   ├── Executar.php          # Comando de execucao
│   │   ├── Opcoes.php            # Comando de ajuda
│   │   ├── Inc/                  # Utilitarios core
│   │   │   ├── Conexao.php       # Conexao com banco
│   │   │   ├── Helper.php        # Funcoes auxiliares
│   │   │   └── Exception.php     # Excecoes customizadas
│   │   ├── Objetos/              # 70 definicoes YAML
│   │   └── Services/             # Logica de negocio
│   │       ├── Ambientes.php
│   │       ├── Cvdw.php
│   │       ├── DatabaseSetup.php
│   │       ├── EnvironmentManager.php
│   │       ├── Http.php
│   │       ├── Log.php
│   │       ├── Objeto.php
│   │       ├── RateLimit.php
│   │       └── Console/
│   │           └── CvdwSymfonyStyle.php
│   └── envs/                     # Arquivos de ambiente
├── tests/                        # Suite de testes
│   ├── ApiCVDW/                  # Testes de API (56 classes)
│   ├── Unit/                     # Testes unitarios (12 classes)
│   ├── Functional/               # Testes funcionais
│   └── Support/                  # Helpers de teste
├── docker/                       # Configuracao Docker
├── data/                         # Arquivos de dados
├── logs/                         # Logs de execucao
├── composer.json                 # Dependencias PHP
├── docker-compose.yaml           # Orquestracao Docker
├── codeception.yml               # Config testes Codeception
└── phpunit.xml                   # Config testes PHPUnit
```

## Funcionalidades Principais

1. **Sincronizacao de 70 objetos** da API CV CRM para banco local
2. **Sync incremental** via datas de referencia (busca apenas alteracoes)
3. **Multi-ambiente** (dev, homologacao, producao)
4. **Multi-banco** (MySQL, MariaDB, PostgreSQL, SQL Server)
5. **Rate limiting** automatico (20 req/min)
6. **Anonimizacao de dados** sensiveis (asteriscos ou hash)
7. **Sub-tabelas automaticas** para dados aninhados da API
8. **Sincronizacao de schema** (detecta e aplica alteracoes de colunas)
9. **Interface interativa** com menus e barras de progresso
10. **Suporte Docker** para execucao containerizada com cron
