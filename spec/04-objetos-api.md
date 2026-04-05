# Objetos da API

O CVDW-CLI sincroniza 70 objetos da API do CV CRM, cada um definido por um arquivo YAML em `src/app/Objetos/`.

---

## Estrutura de um Arquivo YAML

Cada arquivo YAML segue esta estrutura:

```yaml
path: /endpoint/api           # Caminho do endpoint na API
tabela: nome_tabela           # Nome da tabela no banco de dados
metodo: GET                   # Metodo HTTP
descricao: Descricao breve    # Descricao do objeto
parametros:
  header:
    Content-Type: application/json
    email: "{email}"
    token: "{token}"
  query:
    pagina: 1
    registros_por_pagina: 500
    a_partir_data_referencia: ""
  body: {}
response:
  referencia:
    tipo: string
    tamanho: 255
    descricao: Identificador unico
    sensivel: false
  referencia_data:
    tipo: datetime
    descricao: Data de referencia para sync
  campo_exemplo:
    tipo: string
    tamanho: 100
    descricao: Descricao do campo
    sensivel: true            # Campo sensivel (anonimizavel)
```

---

## Catalogo Completo de Objetos

### Reservas (13 objetos)

| Arquivo | Tabela | Descricao |
|---------|--------|-----------|
| `reservas.yaml` | reservas | Reservas de unidades imobiliarias |
| `reservas_associados.yaml` | reservas_associados | Associados das reservas |
| `reservas_campos_adicionais.yaml` | reservas_campos_adicionais | Campos adicionais de reservas |
| `reservas_comissoes.yaml` | reservas_comissoes | Comissoes vinculadas a reservas |
| `reservas_comissoes_programacao.yaml` | reservas_comissoes_programacao | Programacao de comissoes |
| `reservas_condicoes.yaml` | reservas_condicoes | Condicoes comerciais das reservas |
| `reservas_contratos.yaml` | reservas_contratos | Contratos gerados |
| `reservas_coordenador.yaml` | reservas_coordenador | Coordenadores responsaveis |
| `reservas_historico.yaml` | reservas_historico | Historico geral de reservas |
| `reservas_historico_situacoes.yaml` | reservas_historico_situacoes | Historico de mudancas de situacao |
| `reservas_registros_flags.yaml` | reservas_registros_flags | Flags e marcadores |
| `reservas_sienge.yaml` | reservas_sienge | Integracao com Sienge ERP |
| `reservas_workflow_tempo.yaml` | reservas_workflow_tempo | Tempos de workflow |

### Leads (11 objetos)

| Arquivo | Tabela | Descricao |
|---------|--------|-----------|
| `leads.yaml` | leads | Leads (prospects) |
| `leads_conversoes.yaml` | leads_conversoes | Conversoes de leads |
| `leads_corretores.yaml` | leads_corretores | Corretores atribuidos a leads |
| `leads_ganhos.yaml` | leads_ganhos | Leads convertidos (ganhos) |
| `leads_historico_situacoes.yaml` | leads_historico_situacoes | Historico de situacoes |
| `leads_infos.yaml` | leads_infos | Informacoes adicionais de leads |
| `leads_interacoes.yaml` | leads_interacoes | Interacoes com leads |
| `leads_momentos.yaml` | leads_momentos | Momentos/estapas do lead |
| `leads_perdas.yaml` | leads_perdas | Leads perdidos |
| `leads_tarefas.yaml` | leads_tarefas | Tarefas vinculadas a leads |
| `leads_visitas.yaml` | leads_visitas | Visitas agendadas |
| `leads_workflow_tempo.yaml` | leads_workflow_tempo | Tempos de workflow |

### Atendimentos (7 objetos)

| Arquivo | Tabela | Descricao |
|---------|--------|-----------|
| `atendimentos.yaml` | atendimentos | Atendimentos ao cliente |
| `atendimentos_interacoes.yaml` | atendimentos_interacoes | Interacoes de atendimento |
| `atendimentos_respostas.yaml` | atendimentos_respostas | Respostas de atendimento |
| `atendimentos_tarefas.yaml` | atendimentos_tarefas | Tarefas de atendimento |
| `atendimentos_times.yaml` | atendimentos_times | Times de atendimento |
| `atendimentos_times_integrantes.yaml` | atendimentos_times_integrantes | Integrantes dos times |
| `atendimentos_workflow_tempo.yaml` | atendimentos_workflow_tempo | Tempos de workflow |

### Assistencias (5 objetos)

| Arquivo | Tabela | Descricao |
|---------|--------|-----------|
| `assistencias.yaml` | assistencias | Assistencias tecnicas |
| `assistencias_itens.yaml` | assistencias_itens | Itens de assistencia |
| `assistencias_itens_workflow_tempo.yaml` | assistencias_itens_workflow_tempo | Tempos de workflow dos itens |
| `assistencias_visitas_workflow_tempo.yaml` | assistencias_visitas_workflow_tempo | Tempos de workflow das visitas |
| `assistencias_workflow_tempo.yaml` | assistencias_workflow_tempo | Tempos de workflow geral |

### Pessoas (7 objetos)

| Arquivo | Tabela | Descricao |
|---------|--------|-----------|
| `pessoas.yaml` | pessoas | Cadastro de pessoas |
| `pessoas_bancarios.yaml` | pessoas_bancarios | Dados bancarios |
| `pessoas_bens_empresa.yaml` | pessoas_bens_empresa | Bens e empresas |
| `pessoas_contatos.yaml` | pessoas_contatos | Contatos (telefone, email) |
| `pessoas_financeiros.yaml` | pessoas_financeiros | Dados financeiros |
| `pessoas_patrimoniais.yaml` | pessoas_patrimoniais | Patrimonio |
| `pessoas_profissional.yaml` | pessoas_profissional | Dados profissionais |

### Comissoes (3 objetos)

| Arquivo | Tabela | Descricao |
|---------|--------|-----------|
| `comissoes.yaml` | comissoes | Comissoes de venda |
| `comissoes_pagamentos.yaml` | comissoes_pagamentos | Pagamentos de comissoes |
| `comissoes_workflow_tempo.yaml` | comissoes_workflow_tempo | Tempos de workflow |

### Corretores (2 objetos)

| Arquivo | Tabela | Descricao |
|---------|--------|-----------|
| `corretores.yaml` | corretores | Cadastro de corretores |
| `corretores_profissional.yaml` | corretores_profissional | Dados profissionais |

### Pesquisas (3 objetos)

| Arquivo | Tabela | Descricao |
|---------|--------|-----------|
| `pesquisas.yaml` | pesquisas | Pesquisas de satisfacao |
| `pesquisas_perguntas.yaml` | pesquisas_perguntas | Perguntas das pesquisas |
| `pesquisas_respostas.yaml` | pesquisas_respostas | Respostas das pesquisas |

### Repasses (3 objetos)

| Arquivo | Tabela | Descricao |
|---------|--------|-----------|
| `repasses.yaml` | repasses | Repasses financeiros |
| `repasses_historico_situacoes.yaml` | repasses_historico_situacoes | Historico de situacoes |
| `repasses_workflow_tempo.yaml` | repasses_workflow_tempo | Tempos de workflow |

### Pre-cadastros (3 objetos)

| Arquivo | Tabela | Descricao |
|---------|--------|-----------|
| `precadastros.yaml` | precadastros | Pre-cadastros de clientes |
| `precadastro_historico_situacoes.yaml` | precadastro_historico_situacoes | Historico de situacoes |
| `precadastro_workflow_tempo.yaml` | precadastro_workflow_tempo | Tempos de workflow |

### Objetos Individuais

| Arquivo | Tabela | Descricao |
|---------|--------|-----------|
| `agendamentos_vistorias.yaml` | agendamentos_vistorias | Agendamentos de vistorias |
| `campanhas_ativacao.yaml` | campanhas_ativacao | Campanhas de ativacao |
| `campos_adicionais.yaml` | campos_adicionais | Definicoes de campos adicionais |
| `demandas.yaml` | demandas | Demandas diversas |
| `distratos.yaml` | distratos | Distratos de reservas |
| `imobiliarias.yaml` | imobiliarias | Cadastro de imobiliarias |
| `processos.yaml` | processos | Processos gerais |
| `simulacoes.yaml` | simulacoes | Simulacoes financeiras |
| `unidades.yaml` | unidades | Unidades imobiliarias |
| `unidades_precos.yaml` | unidades_precos | Precos de unidades |
| `usuarios_administrativos.yaml` | usuarios_administrativos | Usuarios administrativos |
| `vendas.yaml` | vendas | Registros de vendas |

---

## Tipos de Dados Suportados

| Tipo YAML | Tipo Doctrine | Tipo SQL |
|-----------|---------------|----------|
| `string` | `string` | `VARCHAR(tamanho)` |
| `integer` | `integer` | `INTEGER` |
| `float` / `number` | `decimal` | `DECIMAL` |
| `date` / `datetime` | `datetime` | `DATETIME` |
| `boolean` | `string` | `VARCHAR(5)` |
| `text` | `text` | `TEXT` |
| `array` / `object` | - | Sub-tabela criada |

---

## Campos Comuns a Todos os Objetos

| Campo | Tipo | Descricao |
|-------|------|-----------|
| `referencia` | string (255) | Identificador unico do registro na API |
| `referencia_data` | datetime | Data/hora da ultima alteracao (usado para sync incremental) |
| `ativo` | string (1) | Status ativo: "S" ou "N" |

---

## Campos Sensiveis

Campos marcados com `sensivel: true` nos YAMLs sao candidatos a anonimizacao. Exemplos tipicos:
- `nome` - Nome da pessoa
- `email` - Endereco de email
- `telefone` - Numero de telefone
- `cpf` / `documento_cliente` - Documento de identificacao
- `endereco` - Endereco completo

---

## Sub-tabelas

Quando um campo da API retorna um array ou objeto aninhado, o sistema cria automaticamente uma sub-tabela:

- **Nomenclatura:** `{tabela_pai}_sub_{nome_campo}`
- **Exemplo:** Campo `campos_adicionais` em `leads` gera tabela `leads_sub_campos_adicionais`
- **Relacao:** Vinculada pelo campo `referencia` do registro pai
- **Criacao:** Automatica durante `DatabaseSetup::criarTabelaSchema()`

---

## Paginacao da API

Todos os endpoints seguem o mesmo padrao de paginacao:

**Request:**
```
GET /api/v1/cvdw/{path}
?pagina=1
&registros_por_pagina=500
&a_partir_data_referencia=2024-01-01T00:00:00
```

**Response:**
```json
{
  "total_de_registros": 1500,
  "total_de_paginas": 3,
  "pagina": 1,
  "registros_por_pagina": 500,
  "dados": [
    { "referencia": "abc123", "referencia_data": "2024-01-15 10:30:00", ... },
    ...
  ]
}
```
