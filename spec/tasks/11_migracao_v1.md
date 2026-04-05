# 11 - Migracao da v1

## Objetivo

Garantir que usuarios da v1 possam atualizar para v2 sem problemas. Tratar migracao de config, compatibilidade de banco, e fornecer um comando de migracao.

---

## O que Muda

### Configuracao (.env)

| Chave v1 | Chave v2 | Observacao |
|----------|----------|------------|
| `CV_URL` | `CV_URL` | Igual |
| `CV_EMAIL` | `CV_EMAIL` | Igual |
| `CV_TOKEN` | `CV_TOKEN` | Igual |
| `DB_CONNECTION` | `DB_CONNECTION` | Igual |
| `DB_HOST` | `DB_HOST` | Igual |
| `DB_PORT` | `DB_PORT` | Igual |
| `DB_DATABASE` | `DB_DATABASE` | Igual |
| `DB_USERNAME` | `DB_USERNAME` | Igual |
| `DB_PASSWORD` | `DB_PASSWORD` | Igual |
| `DB_SCHEMA` | `DB_SCHEMA` | Igual |
| `ANONIMIZAR` + `ANONIMIZAR_TIPO` | `ANONYMIZE` | Unificado: `none`, `asterisks`, `hash` |
| `CVDW_AMBIENTE` | `ENVIRONMENT` | Renomeado |

### Tabelas do Banco

- **Tabelas de dados**: inalteradas (mesmas definicoes YAML, mesmos nomes)
- **`_requisicoes`** -> **`_requests`**: colunas renomeadas, dados sao efemeros (podem ser recriados)
- **Engine**: MyISAM -> InnoDB

### Comandos

| v1 | v2 | Nota |
|----|----|----|
| `cvdw executar` | `cvdw sync` | Alias mantido |
| `cvdw configurar` | `cvdw setup` | Alias mantido |
| `cvdw opcoes` | `cvdw --help` | Help padrao do Symfony |

### Objetos YAML

Sem mudancas. A v2 le o mesmo formato YAML da v1.

---

## Comando de Migracao

```
Uso:
  cvdw migrate                   # Auto-detectar e migrar da v1
```

### Fluxo

```
 ╔══════════════════════════════════════╗
 ║  Migracao CVDW-CLI v1 -> v2         ║
 ╚══════════════════════════════════════╝

 Verificando instalacao v1...

 ✓ Config v1 encontrada em envs/.env
 ✓ Tabelas v1 encontradas (70 tabelas)

 Etapas da migracao:

   [1/4] Migrar configuracao .env
         ANONIMIZAR=true + ANONIMIZAR_TIPO=Hash -> ANONYMIZE=hash
         CVDW_AMBIENTE=PRD -> ENVIRONMENT=PRD
         ✓ Configuracao migrada

   [2/4] Migrar _requisicoes -> _requests
         Renomeando colunas...
         ✓ Tabela migrada (1.247 registros preservados)

   [3/4] Converter tabelas para InnoDB
         Convertendo 70 tabelas...
         ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━  100%
         ✓ Todas as tabelas convertidas

   [4/4] Sincronizar schema
         Verificando novas colunas...
         + reservas: 2 colunas adicionadas
         ✓ Schema atualizado

 ╔══════════════════════════════════════╗
 ║  Migracao completa!                 ║
 ║  Seus dados estao preservados.      ║
 ║  Execute: cvdw sync                 ║
 ╚══════════════════════════════════════╝
```

---

## Compatibilidade Retroativa

### Aliases de Comando

```php
// Aliases de comando para compatibilidade com v1
#[AsCommand(name: 'sync', aliases: ['executar', 'execute'])]
#[AsCommand(name: 'setup', aliases: ['configurar', 'config', 'cfg'])]
```

### Carregador de Config

O `CarregadorAmbiente` detecta chaves v1 e mapeia silenciosamente:

```php
// Mapeia chaves legadas da v1 para o formato v2
private function mapearChavesLegado(array $variaveis): array
{
    // Verifica se a chave de anonimizacao v1 existe
    if (isset($variaveis['ANONIMIZAR'])) {
        if ($variaveis['ANONIMIZAR'] === 'true') {
            // Converte o tipo de anonimizacao para o formato v2
            $tipo = $variaveis['ANONIMIZAR_TIPO'] ?? 'Asteriscos';
            $variaveis['ANONYMIZE'] = match ($tipo) {
                'Hash' => 'hash',
                default => 'asterisks',
            };
        } else {
            $variaveis['ANONYMIZE'] = 'none';
        }
    }
    // Mapeia a chave de ambiente legada, se nao houver a nova
    if (isset($variaveis['CVDW_AMBIENTE']) && !isset($variaveis['ENVIRONMENT'])) {
        $variaveis['ENVIRONMENT'] = $variaveis['CVDW_AMBIENTE'];
    }
    return $variaveis;
}
```

---

## Testes

### Cenarios
- Migrar config v1 completa
- Migrar com anonimizacao Hash
- Migrar com anonimizacao Asteriscos
- Migrar sem anonimizacao
- Migrar com chaves opcionais faltando
- Aliases de comando legado funcionam
- Chaves de config legado auto-mapeadas
- Conversao InnoDB pulada para PostgreSQL
- Migracao e idempotente (rodar duas vezes e seguro)

---

## Criterios de Aceite

- [ ] `cvdw migrate` detecta e migra instalacoes v1
- [ ] Chaves de config mapeadas corretamente (todas as combinacoes)
- [ ] Tabelas de dados preservadas (sem perda de dados)
- [ ] `_requisicoes` -> `_requests` tratado
- [ ] Conversao MyISAM -> InnoDB para MySQL
- [ ] Aliases de comando funcionam (`executar`, `configurar`)
- [ ] Chaves legadas do .env auto-mapeadas
- [ ] Migracao e idempotente
- [ ] Todos os testes passam
