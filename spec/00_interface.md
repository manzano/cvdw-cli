# 00 - Especificacao de Interface (Telas)

## Objetivo

Documentar todas as telas do CVDW-CLI v2, tanto de configuracao quanto de execucao. Cada tela mostra exatamente o que o usuario vera no terminal, incluindo cores, formatacao, prompts e fluxos interativos.

---

## Identidade Visual

### Banner (presente em todas as telas principais)

```
 ╔══════════════════════════════════════════════════╗
 ║                                                  ║
 ║   CVDW-CLI  v2.0.0                              ║
 ║   CV CRM Data Warehouse                         ║
 ║                                                  ║
 ╚══════════════════════════════════════════════════╝
```

### Cores

| Elemento              | Cor         | Uso                                          |
|-----------------------|-------------|----------------------------------------------|
| Titulo/Banner         | Branco bold | Identidade do app                            |
| Sucesso / Checkmark   | Verde       | `✓` Operacao concluida com exito             |
| Erro / X              | Vermelho    | `✗` Falha, erro critico                      |
| Aviso                 | Amarelo     | `⚠` Atencao necessaria                       |
| Info / Dica           | Azul        | Informacoes contextuais, dicas               |
| Texto secundario      | Cinza       | Labels, metadata, timestamps                 |
| Input do usuario      | Ciano       | Respostas digitadas pelo usuario              |
| Progresso ativo       | Verde       | Barra de progresso preenchida                |
| Progresso pendente    | Cinza       | Barra de progresso restante                  |

### Icones

```
✓  Sucesso
✗  Falha
⚠  Aviso
→  Proximo passo / Dica
●  Item ativo
○  Item inativo
━  Barra de progresso preenchida
░  Barra de progresso vazia
```

---

# TELAS DE CONFIGURACAO

---

## 1. Menu Principal de Configuracao (`cvdw setup`)

Quando executado sem subcomando, mostra um assistente interativo.

```
 ╔══════════════════════════════════════════════════╗
 ║   CVDW-CLI  v2.0.0                              ║
 ║   CV CRM Data Warehouse                         ║
 ╚══════════════════════════════════════════════════╝

 Ambiente: padrao
 Status:   ⚠ Configuracao incompleta

 ─────────────────────────────────────────────────

 O que deseja configurar?

   [1] Credenciais da API
   [2] Conexao com Banco de Dados
   [3] Criar/Atualizar Tabelas
   [4] Anonimizacao de Dados
   [5] Verificar Ambiente
   [6] Gerenciar Ambientes
   [0] Voltar

 > _
```

---

## 2. Configurar API (`cvdw setup api`)

### 2.1 Tela Inicial — Solicitar Subdominio

```
 ╔══════════════════════════════════════════════════╗
 ║   Configurar Credenciais da API                 ║
 ╚══════════════════════════════════════════════════╝

 Passo 1/3 — Subdominio
 ──────────────────────

 O subdominio e a parte antes de ".cvcrm.com.br"
 Exemplo: se voce acessa https://suaempresa.cvcrm.com.br
          use apenas "suaempresa"

 Subdominio CV CRM: _
```

### 2.2 Subdominio Validado com Sucesso

```
 Subdominio CV CRM: acme

   Conectando a acme.cvcrm.com.br...
   ✓ Ambiente encontrado: "ACME Incorporadora"
```

### 2.3 Subdominio com Falha

```
 Subdominio CV CRM: empresaxyz

   Conectando a empresaxyz.cvcrm.com.br...
   ✗ Nao foi possivel conectar ao ambiente.

   → Verifique se o subdominio esta correto.
   → O endereco completo seria: https://empresaxyz.cvcrm.com.br

 Deseja tentar novamente? [S/n] _
```

### 2.4 Solicitar Email

```
 Passo 2/3 — Autenticacao
 ────────────────────────

 E-mail de acesso: _
```

### 2.5 Email com Formato Invalido

```
 E-mail de acesso: joao@

   ✗ E-mail invalido. Use o formato usuario@dominio.com

 E-mail de acesso: _
```

### 2.6 Solicitar Token

```
 E-mail de acesso: admin@acme.com
 ✓ E-mail registrado

 Token de acesso: ________________________________________
```

### 2.7 Validando Autenticacao — Sucesso

```
 Passo 3/3 — Validacao
 ─────────────────────

   Validando credenciais com a API...
   ✓ Autenticacao bem sucedida!

 ┌──────────────────────────────────────────────────┐
 │  Configuracao da API salva com sucesso           │
 │                                                  │
 │  Ambiente:    ACME Incorporadora                 │
 │  URL:         acme.cvcrm.com.br                  │
 │  E-mail:      admin@acme.com                     │
 │  Token:       ****************************a1b2   │
 └──────────────────────────────────────────────────┘

 → Proximo passo: cvdw setup database
```

### 2.8 Validando Autenticacao — Falha

```
   Validando credenciais com a API...
   ✗ Autenticacao falhou (HTTP 401)

   → Verifique se o e-mail e token estao corretos.
   → O token pode ser obtido em: CV CRM > Configuracoes > Integracao > API

 Deseja tentar novamente? [S/n] _
```

---

## 3. Configurar Banco de Dados (`cvdw setup database`)

### 3.1 Escolher Driver

```
 ╔══════════════════════════════════════════════════╗
 ║   Configurar Banco de Dados                     ║
 ╚══════════════════════════════════════════════════╝

 Passo 1/3 — Driver
 ──────────────────

 Qual banco de dados voce utiliza?

   [1] MySQL / MariaDB
   [2] PostgreSQL
   [3] SQL Server

 > _
```

### 3.2 Dados de Conexao

```
 Passo 2/3 — Conexao
 ───────────────────

 Host [localhost]: _
 Porta [3306]: _
 Nome do Banco: _
 Usuario: _
 Senha: ________
 Schema (opcional, PostgreSQL): _
```

### 3.3 Testando Conexao — Sucesso

```
 Passo 3/3 — Teste
 ─────────────────

   Testando conexao...
   ✓ Conectado com sucesso!

 ┌──────────────────────────────────────────────────┐
 │  Configuracao do Banco salva com sucesso         │
 │                                                  │
 │  Driver:   MySQL 8.0.35                          │
 │  Host:     localhost:3306                        │
 │  Banco:    cvdw                                  │
 │  Usuario:  cvdw_user                             │
 └──────────────────────────────────────────────────┘

 → Proximo passo: cvdw setup tables
```

### 3.4 Testando Conexao — Falha

```
   Testando conexao...
   ✗ Nao foi possivel conectar ao banco de dados.

   Erro: SQLSTATE[HY000] [2002] Connection refused

   → Verifique se o servidor esta rodando em localhost:3306
   → Verifique se usuario e senha estao corretos
   → Verifique se o banco "cvdw" existe

 Deseja tentar novamente? [S/n] _
```

---

## 4. Criar/Atualizar Tabelas (`cvdw setup tables`)

### 4.1 Criacao Inicial (Nenhuma Tabela Existe)

```
 ╔══════════════════════════════════════════════════╗
 ║   Criar Tabelas                                 ║
 ╚══════════════════════════════════════════════════╝

 Banco: mysql://localhost:3306/cvdw

 Nenhuma tabela do CVDW encontrada no banco.

 Criando 70 tabelas + sub-tabelas...

 ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━░░░░░░░░░░  65%  [46/70]

   ✓ reservas (+ 4 sub-tabelas)
   ✓ leads (+ 3 sub-tabelas)
   ✓ pessoas (+ 6 sub-tabelas)
     atendimentos...
```

### 4.2 Criacao Completa

```
 ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━  100%  [70/70]

 ┌──────────────────────────────────────────────────┐
 │  Tabelas Criadas                                 │
 │                                                  │
 │  Tabelas principais:   70                        │
 │  Sub-tabelas:          23                        │
 │  Total de colunas:     1.847                     │
 │  Engine:               InnoDB                    │
 │  Duracao:              00:00:12                  │
 └──────────────────────────────────────────────────┘

 → Proximo passo: cvdw sync
```

### 4.3 Atualizacao de Schema (Tabelas Ja Existem)

```
 ╔══════════════════════════════════════════════════╗
 ║   Atualizar Tabelas                             ║
 ╚══════════════════════════════════════════════════╝

 Banco: mysql://localhost:3306/cvdw
 Tabelas existentes: 70

 Verificando alteracoes de schema...

 Alteracoes encontradas:

 ┌────────────────────────┬─────────────┬───────────────────────┐
 │ Tabela                 │ Tipo        │ Detalhe               │
 ├────────────────────────┼─────────────┼───────────────────────┤
 │ reservas               │ + coluna    │ campo_novo (varchar)  │
 │ reservas               │ + coluna    │ outro_campo (integer) │
 │ leads                  │ + coluna    │ score (decimal)       │
 │ pessoas                │ ~ tipo      │ documento (text→varchar)│
 └────────────────────────┴─────────────┴───────────────────────┘

 Aplicar 4 alteracoes? [S/n] _

   ✓ reservas: 2 colunas adicionadas
   ✓ leads: 1 coluna adicionada
   ✓ pessoas: 1 tipo alterado

 Schema atualizado com sucesso.
```

---

## 5. Configurar Anonimizacao (`cvdw setup anonymize`)

```
 ╔══════════════════════════════════════════════════╗
 ║   Anonimizacao de Dados Sensiveis               ║
 ╚══════════════════════════════════════════════════╝

 Campos marcados como sensiveis nos objetos YAML serao
 anonimizados antes de gravar no banco de dados.

 Exemplos de campos sensiveis:
   • nome_cliente, documento_cliente, email
   • telefone, endereco, cpf, cnpj

 Qual metodo de anonimizacao deseja usar?

   [1] Nenhum — Gravar dados originais
   [2] Asteriscos — J***o S***a (preserva inicio/fim)
   [3] Hash — a1b2c3d4e5 (deterministico, irreversivel)

 > _

 ✓ Anonimizacao configurada: Asteriscos

 Exemplo:
   "Joao Silva" → "J***o S***a"
   "123.456.789-00" → "1***********00"
```

---

## 6. Verificar Ambiente (`cvdw setup status` / `cvdw status`)

### 6.1 Tudo OK

```
 ╔══════════════════════════════════════════════════╗
 ║   CVDW-CLI  v2.0.0                              ║
 ╚══════════════════════════════════════════════════╝

 Ambiente
 ────────
   Nome:           padrao
   Anonimizacao:   asteriscos

 API
 ───
   URL:            acme.cvcrm.com.br          ✓ Conectado
   E-mail:         admin@acme.com
   Token:          ****************************a1b2

 Banco de Dados
 ──────────────
   Driver:         MySQL 8.0.35               ✓ Conectado
   Host:           localhost:3306
   Banco:          cvdw
   Tabelas:        70 principais, 23 sub-tabelas

 Sincronizacao
 ─────────────
   Ultimo sync:    2024-03-15 14:30:00 (2 horas atras)
   Requisicoes:    1.247 nas ultimas 24h
   Total registros: 125.678
```

### 6.2 Com Problemas

```
 ╔══════════════════════════════════════════════════╗
 ║   CVDW-CLI  v2.0.0                              ║
 ╚══════════════════════════════════════════════════╝

 Ambiente
 ────────
   Nome:           padrao
   Anonimizacao:   nenhuma

 API
 ───
   URL:            acme.cvcrm.com.br          ✓ Conectado
   E-mail:         admin@acme.com
   Token:          ****************************a1b2

 Banco de Dados
 ──────────────
   Driver:         MySQL                      ✗ Sem Conexao
   Host:           localhost:3306
   Banco:          cvdw

   ⚠ Nao foi possivel conectar ao banco de dados.
   → Execute: cvdw setup database

 Sincronizacao
 ─────────────
   Ultimo sync:    Nunca
   Requisicoes:    0
```

---

## 7. Gerenciar Ambientes

### 7.1 Listar Ambientes (`cvdw env:list`)

```
 Ambientes Configurados
 ──────────────────────

 ┌───────────────┬──────────────────────────┬───────────────────────┬─────────┐
 │ Nome          │ API                      │ Banco                 │ Status  │
 ├───────────────┼──────────────────────────┼───────────────────────┼─────────┤
 │ ● padrao      │ acme.cvcrm.com.br        │ mysql://localhost/cvdw│ ✓       │
 │ ○ homologacao │ acme-hml.cvcrm.com.br    │ mysql://staging/cvdw  │ ✓       │
 │ ○ dev         │ acme-dev.cvcrm.com.br    │ (nao configurado)     │ ✗       │
 └───────────────┴──────────────────────────┴───────────────────────┴─────────┘

 ● = ambiente ativo

 Dica: Use --env=<nome> em qualquer comando para escolher o ambiente.
```

### 7.2 Criar Ambiente (`cvdw env:create`)

```
 ╔══════════════════════════════════════════════════╗
 ║   Criar Novo Ambiente                           ║
 ╚══════════════════════════════════════════════════╝

 Nome do ambiente: homologacao

   Copiando configuracao do ambiente "padrao" como base...
   ✓ Arquivo envs/.env.homologacao criado

 → Agora configure o novo ambiente:
   cvdw setup api --env=homologacao
   cvdw setup database --env=homologacao
```

### 7.3 Remover Ambiente (`cvdw env:remove`)

```
 ⚠ Voce esta prestes a REMOVER o ambiente "dev"

   Arquivo: envs/.env.dev
   API:     acme-dev.cvcrm.com.br
   Banco:   (nao configurado)

   Isso nao apaga dados do banco, apenas a configuracao local.

 Tem certeza? [s/N] _

 ✓ Ambiente "dev" removido.
```

---

## 8. Assistente de Primeira Execucao

Quando nao existe nenhum arquivo `.env`, o setup completo e iniciado automaticamente.

```
 ╔══════════════════════════════════════════════════╗
 ║                                                  ║
 ║   CVDW-CLI  v2.0.0                              ║
 ║   CV CRM Data Warehouse                         ║
 ║                                                  ║
 ╚══════════════════════════════════════════════════╝

 Bem-vindo ao CVDW-CLI!

 Nenhuma configuracao encontrada.
 Vou te guiar pelo processo de configuracao inicial.

 ─────────────────────────────────────────────────

 Passo 1/4 — Credenciais da API
 ──────────────────────────────

 Subdominio CV CRM: acme

   Conectando a acme.cvcrm.com.br...
   ✓ Ambiente encontrado: "ACME Incorporadora"

 E-mail de acesso: admin@acme.com
 Token de acesso: ****************************************

   Validando credenciais...
   ✓ Autenticacao bem sucedida!

 ─────────────────────────────────────────────────

 Passo 2/4 — Banco de Dados
 ──────────────────────────

 Qual banco de dados voce utiliza?

   [1] MySQL / MariaDB
   [2] PostgreSQL
   [3] SQL Server

 > 1

 Host [localhost]:
 Porta [3306]:
 Nome do Banco: cvdw
 Usuario: cvdw_user
 Senha: ********

   Testando conexao...
   ✓ Conectado com sucesso! (MySQL 8.0.35)

 ─────────────────────────────────────────────────

 Passo 3/4 — Anonimizacao
 ────────────────────────

 Deseja anonimizar dados sensiveis (nomes, documentos, etc)?

   [1] Nao — Gravar dados originais
   [2] Asteriscos — J***o S***a
   [3] Hash — a1b2c3d4e5

 > 1

 ✓ Sem anonimizacao.

 ─────────────────────────────────────────────────

 Passo 4/4 — Criar Tabelas
 ─────────────────────────

 Criando 70 tabelas + sub-tabelas...
 ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━  100%

 ✓ 70 tabelas criadas, 23 sub-tabelas

 ╔══════════════════════════════════════════════════╗
 ║                                                  ║
 ║   Configuracao completa!                        ║
 ║                                                  ║
 ║   Ambiente:   ACME Incorporadora                ║
 ║   Banco:      mysql://localhost:3306/cvdw       ║
 ║   Tabelas:    70 + 23 sub-tabelas               ║
 ║                                                  ║
 ║   → Execute agora: cvdw sync                    ║
 ║                                                  ║
 ╚══════════════════════════════════════════════════╝
```

---

# TELAS DE EXECUCAO

---

## 9. Menu Principal de Execucao (`cvdw sync`)

Quando executado sem argumentos, mostra modo interativo.

```
 ╔══════════════════════════════════════════════════╗
 ║   CVDW-CLI  v2.0.0                              ║
 ║   CV CRM Data Warehouse                         ║
 ╚══════════════════════════════════════════════════╝

 Ambiente: padrao (acme.cvcrm.com.br)
 Banco:    mysql://localhost:3306/cvdw

 ─────────────────────────────────────────────────

 O que voce gostaria de sincronizar?

   [1] Todos os objetos (70)
   [2] Escolher por categoria
   [3] Escolher objetos especificos
   [0] Sair

 > _
```

---

## 10. Selecao por Categoria

```
 Escolha as categorias:
 (use espaco para marcar, enter para confirmar)

   [ ] Reservas (13 objetos)
   [x] Leads (11 objetos)
   [ ] Pessoas (7 objetos)
   [x] Atendimentos (7 objetos)
   [ ] Assistencias (6 objetos)
   [ ] Comissoes (3 objetos)
   [ ] Corretores (2 objetos)
   [ ] Pesquisas (3 objetos)
   [ ] Repasses (3 objetos)
   [ ] Pre-cadastros (3 objetos)
   [ ] Unidades (2 objetos)
   [ ] Outros (10 objetos)

 ─────────────────────────────────────────────────

 Selecionados: 18 objetos de 2 categorias

   Leads:
     leads, leads_conversoes, leads_corretores, leads_ganhos,
     leads_historico_situacoes, leads_infos, leads_interacoes,
     leads_momentos, leads_perdas, leads_tarefas, leads_visitas

   Atendimentos:
     atendimentos, atendimentos_interacoes, atendimentos_respostas,
     atendimentos_tarefas, atendimentos_times,
     atendimentos_times_integrantes, atendimentos_workflow_tempo

 Iniciar sincronizacao? [S/n] _
```

---

## 11. Selecao de Objetos Especificos

```
 Escolha os objetos:
 (use espaco para marcar, enter para confirmar)

 Reservas
   [ ] reservas                      Reservas de unidades
   [ ] reservas_associados           Associados das reservas
   [ ] reservas_campos_adicionais    Campos adicionais
   [ ] reservas_comissoes            Comissoes vinculadas
   [ ] reservas_comissoes_programacao Programacao de comissoes
   [ ] reservas_condicoes            Condicoes comerciais
   [ ] reservas_contratos            Contratos
   ...

 Leads
   [x] leads                         Leads (prospects)
   [x] leads_conversoes              Conversoes de leads
   [ ] leads_corretores              Corretores do lead
   ...

 Selecionados: 2 objetos
```

---

## 12. Sincronizacao em Andamento — Objeto Unico

```
 ╔══════════════════════════════════════════════════╗
 ║   Sincronizando                                 ║
 ╚══════════════════════════════════════════════════╝

 Ambiente: padrao (acme.cvcrm.com.br)
 Objetos:  1 selecionado

 ─────────────────────────────────────────────────

 reservas
 ────────

   Buscando desde: 2024-03-15 14:30:00
   Pagina 3 de 12 (500 registros/pagina)

   ━━━━━━━━━━━━━━━━━━░░░░░░░░░░░░  25%  [1.247 / 4.891]

   Inseridos:  892       Alterados:  341       Erros:  0
   Tempo:      00:01:34  Velocidade: ~14 reg/seg
```

---

## 13. Sincronizacao — Multiplos Objetos

```
 ╔══════════════════════════════════════════════════╗
 ║   Sincronizando                                 ║
 ╚══════════════════════════════════════════════════╝

 Ambiente: padrao (acme.cvcrm.com.br)
 Objetos:  70 selecionados
 Limite:   3600 segundos

 ─────────────────────────────────────────────────

 Progresso geral: 5/70 objetos
 ━━━━━━━░░░░░░░░░░░░░░░░░░░░░░░  7%   [00:08:32 / ~02:01:00]

 ─────────────────────────────────────────────────

 ✓ reservas                   4.891 registros    (892 novos, 341 alterados)
 ✓ reservas_associados          782 registros    (12 novos, 0 alterados)
 ✓ reservas_campos_adicionais 1.203 registros    (45 novos, 89 alterados)
 ✓ reservas_comissoes           341 registros    (0 novos, 8 alterados)

 ● leads                      pagina 7/23...
   ━━━━━━━━━━━━━━━░░░░░░░░░░░  30%  [3.412 / 11.234]

   Inseridos: 2.891     Alterados: 487     Erros: 0
   Tempo: 00:02:12      Velocidade: ~26 reg/seg

 ○ leads_conversoes           pendente
 ○ leads_corretores           pendente
 ○ ... (mais 63 objetos)
```

---

## 14. Espera de Rate Limit

Quando o limite de 20 requisicoes/minuto e atingido:

```
 ● leads                      pagina 7/23...

   ⚠ Rate limit atingido (20 requisicoes/minuto)
     Aguardando: 33 segundos...

     ██████████████░░░░░░░░░░░░░░░  47%  [00:17 restantes]
```

A contagem regressiva atualiza em tempo real, segundo a segundo.

---

## 15. Erro em Registro Individual

```
 ● leads                      pagina 7/23...
   ━━━━━━━━━━━━━━━░░░░░░░░░░░  30%  [3.412 / 11.234]

   Inseridos: 2.891     Alterados: 487     Erros: 2

   Ultimos erros:
     → ref#12345: Valor excede tamanho maximo da coluna "observacao"
     → ref#12389: Tipo incompativel para coluna "valor" (expected int, got string)
```

---

## 16. Erro de Conexao Durante Sync

```
 ● leads                      pagina 7/23...

   ✗ Erro de conexao com a API (HTTP 500)

   Tentativa 1/3... aguardando 5 segundos
   Tentativa 2/3... aguardando 10 segundos
   ✓ Conexao restabelecida

   Continuando de onde parou...
```

### Falha apos todas as tentativas:

```
 ● leads                      pagina 7/23...

   ✗ Erro de conexao com a API (HTTP 500)

   Tentativa 1/3... aguardando 5 segundos
   Tentativa 2/3... aguardando 10 segundos
   Tentativa 3/3... aguardando 20 segundos

   ✗ Nao foi possivel conectar apos 3 tentativas.
     Pulando objeto "leads" e continuando com o proximo...

   → Os dados parciais foram salvos. Na proxima execucao,
     o sync continuara de onde parou.
```

---

## 17. Limite de Tempo Atingido

```
 ● atendimentos               pagina 3/15...

   ⚠ Limite de tempo atingido (3600 segundos)

   Finalizando objeto atual graciosamente...
   ✓ Pagina 3 salva com sucesso.

 ─────────────────────────────────────────────────

 Sincronizacao interrompida por limite de tempo.

 Objetos completados:  12/70
 Objetos parciais:     1 (atendimentos: 3/15 paginas)
 Objetos pendentes:    57

 → Na proxima execucao, o sync continuara de onde parou.
 → Dica: aumente o limite com --time-limit=7200
```

---

## 18. Resumo Final — Sync Completo

```
 ─────────────────────────────────────────────────

 ╔══════════════════════════════════════════════════╗
 ║   Sincronizacao Completa                        ║
 ╚══════════════════════════════════════════════════╝

 ┌──────────────────┬──────────────────┬──────────────────┐
 │  Inseridos       │  Alterados       │  Erros           │
 │  12.456          │  3.891           │  7               │
 └──────────────────┴──────────────────┴──────────────────┘

 Detalhes por objeto:

 ┌──────────────────────────┬──────────┬──────────┬──────────┬────────┐
 │ Objeto                   │ Total    │ Novos    │ Alterados│ Erros  │
 ├──────────────────────────┼──────────┼──────────┼──────────┼────────┤
 │ reservas                 │ 4.891    │ 892      │ 341      │ 0      │
 │ leads                    │ 11.234   │ 2.891    │ 487      │ 2      │
 │ pessoas                  │ 8.901    │ 1.203    │ 456      │ 0      │
 │ atendimentos             │ 3.456    │ 891      │ 234      │ 5      │
 │ ...                      │ ...      │ ...      │ ...      │ ...    │
 ├──────────────────────────┼──────────┼──────────┼──────────┼────────┤
 │ Total                    │ 45.678   │ 12.456   │ 3.891    │ 7      │
 └──────────────────────────┴──────────┴──────────┴──────────┴────────┘

 Tempo total:      00:45:12
 Paginas:          234
 Requisicoes API:  234
 Velocidade media: ~17 reg/seg
```

---

## 19. Resumo Final — Sync Parcial (com limite de tempo)

```
 ╔══════════════════════════════════════════════════╗
 ║   Sincronizacao Parcial                         ║
 ║   (limite de tempo: 1500 segundos)              ║
 ╚══════════════════════════════════════════════════╝

 ┌──────────────────┬──────────────────┬──────────────────┐
 │  Inseridos       │  Alterados       │  Erros           │
 │  5.678           │  1.234           │  3               │
 └──────────────────┴──────────────────┴──────────────────┘

 ┌──────────────────────────┬───────────┬───────────────────────┐
 │ Objeto                   │ Status    │ Detalhes              │
 ├──────────────────────────┼───────────┼───────────────────────┤
 │ reservas                 │ ✓ completo│ 4.891 registros       │
 │ leads                    │ ✓ completo│ 11.234 registros      │
 │ pessoas                  │ ◐ parcial │ 3/8 paginas (1.500)   │
 │ atendimentos             │ ○ pendente│                       │
 │ ...                      │ ○ pendente│                       │
 └──────────────────────────┴───────────┴───────────────────────┘

 Tempo: 00:25:00    Proxima execucao: continuara de onde parou
```

---

## 20. Listar Objetos (`cvdw list:objects`)

```
 Objetos Disponiveis (70)
 ────────────────────────

 Reservas (13)
 ├── reservas                          Reservas de unidades
 ├── reservas_associados               Associados das reservas
 ├── reservas_campos_adicionais        Campos adicionais
 ├── reservas_comissoes                Comissoes vinculadas
 ├── reservas_comissoes_programacao    Programacao de comissoes
 ├── reservas_condicoes                Condicoes comerciais
 ├── reservas_contratos                Contratos
 ├── reservas_coordenador              Coordenador de vendas
 ├── reservas_historico                Historico de alteracoes
 ├── reservas_historico_situacoes      Historico de situacoes
 ├── reservas_registros_flags          Flags de registro
 ├── reservas_sienge                   Integracao Sienge
 └── reservas_workflow_tempo           Tempo em cada etapa

 Leads (11)
 ├── leads                             Leads (prospects)
 ├── leads_conversoes                  Conversoes de leads
 ├── leads_corretores                  Corretores do lead
 ├── leads_ganhos                      Leads ganhos
 ├── leads_historico_situacoes         Historico de situacoes
 ├── leads_infos                       Informacoes do lead
 ├── leads_interacoes                  Interacoes do lead
 ├── leads_momentos                    Momentos do lead
 ├── leads_perdas                      Leads perdidos
 ├── leads_tarefas                     Tarefas do lead
 └── leads_visitas                     Visitas do lead

 Pessoas (7)
 ├── pessoas                           Cadastro de pessoas
 ├── pessoas_bancarios                 Dados bancarios
 ├── pessoas_bens_empresa              Bens da empresa
 ├── pessoas_contatos                  Contatos
 ├── pessoas_financeiros               Dados financeiros
 ├── pessoas_patrimoniais              Dados patrimoniais
 └── pessoas_profissional              Dados profissionais

 Atendimentos (7)
 ├── atendimentos                      Atendimentos
 ├── atendimentos_interacoes           Interacoes
 ├── atendimentos_respostas            Respostas
 ├── atendimentos_tarefas              Tarefas
 ├── atendimentos_times                Times
 ├── atendimentos_times_integrantes    Integrantes dos times
 └── atendimentos_workflow_tempo       Tempo em cada etapa

 Assistencias (6)
 ├── assistencias                      Assistencias tecnicas
 ├── assistencias_itens                Itens da assistencia
 ├── assistencias_itens_workflow_tempo  Tempo dos itens
 ├── assistencias_visitas_workflow_tempo Tempo das visitas
 └── assistencias_workflow_tempo       Tempo em cada etapa

 Comissoes (3)
 ├── comissoes                         Comissoes
 ├── comissoes_pagamentos              Pagamentos de comissoes
 └── comissoes_workflow_tempo          Tempo em cada etapa

 Corretores (2)
 ├── corretores                        Cadastro de corretores
 └── corretores_profissional           Dados profissionais

 Pesquisas (3)
 ├── pesquisas                         Pesquisas de satisfacao
 ├── pesquisas_perguntas               Perguntas
 └── pesquisas_respostas               Respostas

 Repasses (3)
 ├── repasses                          Repasses
 ├── repasses_historico_situacoes      Historico de situacoes
 └── repasses_workflow_tempo           Tempo em cada etapa

 Pre-cadastros (3)
 ├── precadastros                      Pre-cadastros
 ├── precadastro_historico_situacoes   Historico de situacoes
 └── precadastro_workflow_tempo        Tempo em cada etapa

 Unidades (2)
 ├── unidades                          Unidades imobiliarias
 └── unidades_precos                   Precos das unidades

 Outros (10)
 ├── agendamentos_vistorias            Agendamentos de vistoria
 ├── campanhas_ativacao                Campanhas de ativacao
 ├── campos_adicionais                 Campos adicionais globais
 ├── demandas                          Demandas
 ├── distratos                         Distratos
 ├── imobiliarias                      Imobiliarias
 ├── processos                         Processos
 ├── simulacoes                        Simulacoes
 ├── usuarios_administrativos          Usuarios administrativos
 └── vendas                            Vendas
```

---

## 21. Status das Tabelas (`cvdw table:status`)

```
 Tabelas do Banco
 ────────────────

 ┌───────────────────────────────┬──────────┬────────────┬─────────────────────┐
 │ Tabela                        │ Registros│ Sub-tabelas│ Ultimo Sync         │
 ├───────────────────────────────┼──────────┼────────────┼─────────────────────┤
 │ reservas                      │ 12.345   │ 4          │ 2024-03-15 14:30    │
 │ leads                         │ 45.678   │ 3          │ 2024-03-15 14:28    │
 │ pessoas                       │ 8.901    │ 6          │ 2024-03-15 14:25    │
 │ atendimentos                  │ 3.456    │ 6          │ 2024-03-15 14:20    │
 │ assistencias                  │ 1.234    │ 4          │ 2024-03-15 14:15    │
 │ comissoes                     │ 5.678    │ 2          │ 2024-03-15 14:10    │
 │ corretores                    │ 234      │ 1          │ 2024-03-15 14:08    │
 │ pesquisas                     │ 567      │ 2          │ 2024-03-15 14:05    │
 │ repasses                      │ 1.890    │ 2          │ 2024-03-15 14:00    │
 │ precadastros                  │ 345      │ 2          │ 2024-03-15 13:55    │
 │ unidades                      │ 2.345    │ 1          │ 2024-03-15 13:50    │
 │ outros (10)                   │ 43.005   │ 0          │ 2024-03-15 13:45    │
 ├───────────────────────────────┼──────────┼────────────┼─────────────────────┤
 │ Total                         │ 125.678  │ 33         │                     │
 └───────────────────────────────┴──────────┴────────────┴─────────────────────┘

 Tabelas de sistema:
   _requests: 1.247 registros (mais antigo: 30 dias)
```

---

## 22. Operacoes Destrutivas — Confirmacao

### Truncate (`cvdw table:truncate`)

```
 ⚠ Voce esta prestes a LIMPAR os dados das seguintes tabelas:

   reservas                     12.345 registros
   reservas_associados             782 registros
   reservas_campos_adicionais    1.203 registros
   reservas_comissoes              341 registros
                               ────────
                                14.671 registros no total

 ⚠ Esta acao NAO pode ser desfeita.
   As tabelas serao mantidas, apenas os dados serao apagados.
   Na proxima execucao, todos os registros serao re-sincronizados.

 Digite "LIMPAR" para confirmar: _

 ✓ reservas — limpa
 ✓ reservas_associados — limpa
 ✓ reservas_campos_adicionais — limpa
 ✓ reservas_comissoes — limpa

 4 tabelas limpas. 14.671 registros removidos.
```

### Drop (`cvdw table:drop`)

```
 ⚠ Voce esta prestes a APAGAR as seguintes tabelas:

   reservas                     12.345 registros
   reservas_associados             782 registros
   reservas_campos_adicionais    1.203 registros
   reservas_comissoes              341 registros

 ⚠ Esta acao NAO pode ser desfeita.
   As tabelas e todos os dados serao removidos permanentemente.
   Sera necessario executar "cvdw setup tables" para recria-las.

 Digite "APAGAR" para confirmar: _

 ✓ reservas_comissoes — apagada
 ✓ reservas_campos_adicionais — apagada
 ✓ reservas_associados — apagada
 ✓ reservas — apagada

 4 tabelas apagadas.
```

---

## 23. Execucao Nao-Interativa (Modo Cron)

Saida simplificada para execucoes automatizadas (sem banner, sem cores, sem prompts).

### `cvdw sync all --no-interaction --time-limit=1500`

```
[2024-03-15 14:00:00] Sincronizacao iniciada (70 objetos, limite: 1500s)
[2024-03-15 14:00:01] reservas: 4.891 registros (892 novos, 341 alterados, 0 erros) [00:02:15]
[2024-03-15 14:02:16] reservas_associados: 782 registros (12 novos, 0 alterados, 0 erros) [00:00:18]
[2024-03-15 14:02:34] reservas_campos_adicionais: 1.203 registros (45 novos, 89 alterados, 0 erros) [00:00:32]
...
[2024-03-15 14:24:59] RATE LIMIT: aguardando 33 segundos
[2024-03-15 14:25:32] leads: 11.234 registros (2.891 novos, 487 alterados, 2 erros) [00:05:12]
...
[2024-03-15 14:25:00] LIMITE DE TEMPO: 1500s atingido. Finalizando...
[2024-03-15 14:25:01] Concluido: 45/70 objetos, 89.234 registros (12.456 novos, 3.891 alterados, 7 erros) [00:25:00]
```

### `cvdw sync reservas --no-interaction --log`

```
[2024-03-15 14:00:00] Sincronizacao iniciada (1 objeto)
[2024-03-15 14:02:15] reservas: 4.891 registros (892 novos, 341 alterados, 0 erros) [00:02:15]
[2024-03-15 14:02:15] Concluido: 1/1 objetos, 4.891 registros [00:02:15]
[2024-03-15 14:02:15] Log salvo em: logs/log_2024-03-15_14-00-00.log
```

---

## 24. Mensagens de Erro Globais

### Banco nao configurado

```
 ✗ Banco de dados nao configurado.

 → Execute: cvdw setup database
```

### API nao configurada

```
 ✗ Credenciais da API nao configuradas.

 → Execute: cvdw setup api
```

### Tabelas nao existem

```
 ✗ Tabelas nao encontradas no banco de dados.

 → Execute: cvdw setup tables
```

### Ambiente nao encontrado

```
 ✗ Ambiente "producao" nao encontrado.

 Ambientes disponiveis: padrao, homologacao, dev

 → Use: cvdw sync --env=padrao
 → Ou crie: cvdw env:create producao
```

### Versao desatualizada

```
 ⚠ Nova versao disponivel: v2.1.0 (voce esta na v2.0.0)

 → Execute: cvdw self-update
```

---

## 25. Ajuda (`cvdw --help`)

```
 CVDW-CLI v2.0.0 — CV CRM Data Warehouse

 Uso:
   cvdw <comando> [opcoes] [argumentos]

 Comandos:
   sync                Sincronizar dados da API CV CRM
   setup               Configurar API, banco e tabelas
   status              Mostrar status do ambiente e conexao
   list:objects        Listar objetos disponiveis da API
   table:status        Visao geral das tabelas do banco
   table:sync          Sincronizar schema com definicoes da API
   table:truncate      Limpar dados das tabelas
   table:drop          Apagar tabelas
   table:reset-dates   Limpar datas de referencia (forcar re-sync)
   env:list            Listar ambientes configurados
   env:create          Criar novo ambiente
   env:remove          Remover ambiente
   migrate             Migrar do CVDW-CLI v1
   self-update         Atualizar para ultima versao
   version             Mostrar informacoes de versao

 Opcoes globais:
   -e, --env           Selecionar ambiente
   -h, --help          Exibir ajuda do comando
   -q, --quiet         Nao exibir nenhuma saida
   -v, --verbose       Aumentar nivel de detalhamento
       --no-interaction Nao fazer perguntas interativas

 Execute "cvdw <comando> --help" para uso detalhado.
```

---

## Resumo dos Modos de Saida

| Modo            | Banner | Cores | Prompts | Barra Progresso | Quando Usar                |
|-----------------|--------|-------|---------|-----------------|----------------------------|
| Interativo      | Sim    | Sim   | Sim     | Animada         | Terminal humano             |
| Nao-interativo  | Nao    | Nao   | Nao     | Nao             | Cron, scripts, CI          |
| Verbose (-v)    | Sim    | Sim   | Sim     | Animada         | Debug leve                 |
| Debug (-vvv)    | Sim    | Sim   | Sim     | Animada         | Debug completo (HTTP, SQL) |
| Quiet (-q)      | Nao    | Nao   | Nao     | Nao             | Apenas erros               |
