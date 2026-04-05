# 08 - Comandos de Manutencao & Gerenciamento de Tabelas

## Objetivo

Construir os comandos de manutencao para gerenciar tabelas, ambientes e atualizacoes. Cobrem operacoes menos frequentes mas importantes.

---

## Comandos

### `ComandoTabela` (gerenciamento de tabelas)

```
Uso:
  cvdw table:status              # Visao geral das tabelas
  cvdw table:sync                # Sincronizar schema (add/modify/remove colunas)
  cvdw table:truncate [tabelas]  # Limpar dados das tabelas
  cvdw table:drop [tabelas]      # Apagar tabelas completamente
  cvdw table:reset-dates [tabelas]  # Limpar datas de referencia (forcar re-sync)

Opcoes:
  -e, --env            Ambiente
      --all            Aplicar em todas as tabelas
      --yes            Pular prompts de confirmacao
```

#### Saida de `table:status`

```
 Tabelas do Banco
 ────────────────

 ┌───────────────────────────┬──────────┬────────────┬─────────────────────┐
 │ Tabela                    │ Registros│ Sub-tabelas│ Ultimo Sync         │
 ├───────────────────────────┼──────────┼────────────┼─────────────────────┤
 │ reservas                  │ 12.345   │ 3          │ 2024-03-15 14:30    │
 │ leads                     │ 45.678   │ 2          │ 2024-03-15 14:28    │
 │ pessoas                   │ 8.901    │ 5          │ 2024-03-15 14:25    │
 │ atendimentos              │ 3.456    │ 4          │ 2024-03-15 14:20    │
 ├───────────────────────────┼──────────┼────────────┼─────────────────────┤
 │ Total                     │ 125.678  │ 23         │                     │
 └───────────────────────────┴──────────┴────────────┴─────────────────────┘

 Sistema: _requests (1.247 registros, mais antigo: 30 dias)
```

#### `table:truncate` com confirmacao

```
 ⚠ Voce esta prestes a LIMPAR as seguintes tabelas:

   reservas (12.345 registros)
   reservas_sub_campos_adicionais (34.567 registros)

 Esta acao nao pode ser desfeita.

 Tem certeza? [s/N] s

 ✓ reservas limpa
 ✓ reservas_sub_campos_adicionais limpa
```

---

### `ComandoAmbiente` (gerenciamento de ambientes)

```
Uso:
  cvdw env:list                  # Listar todos os ambientes
  cvdw env:create <nome>         # Criar novo ambiente
  cvdw env:remove <nome>         # Remover ambiente
  cvdw env:show [nome]           # Mostrar detalhes do ambiente
```

#### Saida de `env:list`

```
 Ambientes
 ─────────

 ┌──────────────┬─────────────────────────┬──────────────────────┬─────────┐
 │ Nome         │ API                     │ Banco                │ Status  │
 ├──────────────┼─────────────────────────┼──────────────────────┼─────────┤
 │ * padrao     │ acme.cvcrm.com.br       │ mysql://localhost/cvdw│ ✓      │
 │   homologacao│ acme-hml.cvcrm.com.br   │ mysql://staging/cvdw │ ✓      │
 │   dev        │ acme-dev.cvcrm.com.br   │ (nao configurado)    │ ✗      │
 └──────────────┴─────────────────────────┴──────────────────────┴─────────┘
```

---

### `ComandoAtualizar`

```
Uso:
  cvdw self-update               # Atualizar CVDW-CLI para ultima versao
  cvdw version                   # Mostrar info de versao
```

#### Saida de version

```
 CVDW-CLI v2.0.0
 PHP 8.3.4
 Doctrine DBAL 4.2.1
 Symfony Console 7.2.3

 Ultima versao: v2.0.0 ✓ Voce esta atualizado
```

---

## Casos de Uso de Manutencao

### `CasoUsoGerenciarTabelas`

```php
final class CasoUsoGerenciarTabelas
{
    public function obterStatusTabelas(): array;
    public function sincronizarTodosSchemas(): RelatorioSchema;
    public function limparTabelas(array $tabelas): void;
    public function excluirTabelas(array $tabelas): void;
    public function resetarDatasReferencia(array $tabelas): void;
}
```

### `CasoUsoGerenciarAmbientes`

```php
final class CasoUsoGerenciarAmbientes
{
    public function listar(): array;
    public function criar(string $nome): void;
    public function remover(string $nome): void;
    public function obterDetalhes(?string $nome = null): DetalhesAmbiente;
}
```

---

## Criterios de Aceite

- [ ] `cvdw table:status` mostra visao geral abrangente
- [ ] `cvdw table:sync` detecta e aplica mudancas de schema
- [ ] `cvdw table:truncate` exige confirmacao
- [ ] `cvdw table:drop` exige confirmacao
- [ ] `cvdw env:list` mostra todos os ambientes com status
- [ ] `cvdw self-update` funciona
- [ ] Todas as operacoes destrutivas exigem `--yes` ou confirmacao interativa
- [ ] Todos os comandos testados
