# Integracao com API CV CRM

## Visao Geral

O CVDW-CLI consome a API REST do CV CRM (CVDW - CV Data Warehouse) para extrair dados de um CRM imobiliario e persisti-los em banco de dados local.

---

## Endpoint Base

```
https://{subdominio}.cvcrm.com.br/api/v1/cvdw
```

O subdominio e configurado via variavel `CV_URL`.

---

## Autenticacao

Autenticacao via headers HTTP (nao usa OAuth):

| Header | Valor | Descricao |
|--------|-------|-----------|
| `Content-Type` | `application/json` | Tipo de conteudo |
| `email` | `{CV_EMAIL}` | Email do usuario admin |
| `token` | `{CV_TOKEN}` | Token de acesso (SHA1, 40 caracteres) |

### Validacao de Credenciais

O metodo `Http::pingAmbienteAutenticadoCVDW()` valida as credenciais fazendo uma requisicao de teste. Retorna `true` se autenticacao bem-sucedida.

---

## Formato de Requisicao

### Request

```
GET /api/v1/cvdw/{path}
Headers:
  Content-Type: application/json
  email: usuario@empresa.com
  token: abc123def456...

Query Parameters:
  pagina: 1                              # Numero da pagina
  registros_por_pagina: 500              # Registros por pagina (max 500)
  a_partir_data_referencia: 2024-01-01   # Filtro de data (opcional)
```

### Response (Sucesso)

```json
{
  "total_de_registros": 1500,
  "total_de_paginas": 3,
  "pagina": 1,
  "registros_por_pagina": 500,
  "dados": [
    {
      "referencia": "unique-id-123",
      "referencia_data": "2024-01-15 10:30:00",
      "ativo": "S",
      "campo1": "valor1",
      "campo2": 123,
      ...
    }
  ]
}
```

### Response (Erro)

Codigos HTTP padrao (401, 403, 404, 500, etc).

---

## Rate Limiting

A API do CV CRM impoe limite de **20 requisicoes por minuto**.

### Implementacao no CVDW-CLI

1. **Antes de cada requisicao:**
   - Consulta `RateLimit::getDiferencaSegundosUltimaRequisicao()`
   - Se 20+ requisicoes no ultimo minuto: aguarda ate completar 60s
   - Se menos de 3 segundos desde a ultima: aguarda

2. **Mecanismo de espera:**
   - Exibe contagem regressiva visual no terminal
   - Mensagem: "Aguardando X segundos para respeitar rate limit..."

3. **Rastreamento:**
   - Cada requisicao e registrada na tabela `_requisicoes`
   - Inicio e fim com timestamps
   - Dados de retorno (quantidade, headers)

---

## Sync Incremental

### Mecanismo

O sistema usa o campo `referencia_data` para buscar apenas registros alterados desde a ultima sincronizacao:

1. **Antes da execucao:** `Cvdw::buscaUltimaData()` busca `MAX(referencia_data)` da tabela
2. **Na requisicao:** Passa `a_partir_data_referencia` como parametro
3. **Resultado:** API retorna apenas registros modificados apos a data

### Opcoes de Override

| Opcao | Efeito |
|-------|--------|
| `--ignorar-data-referencia` | Ignora data, busca todos os registros |
| `--apartir=2024-01-01` | Define data especifica como ponto de partida |

---

## Paginacao

### Parametros

| Parametro | Default | Max | Descricao |
|-----------|---------|-----|-----------|
| `pagina` | 1 | - | Numero da pagina atual |
| `registros_por_pagina` | 500 | 500 | Registros por pagina |

### Fluxo de Paginacao

```
Pagina 1 -> Obtem total_de_paginas
  |
  ├── Processa registros da pagina 1
  ├── Pagina 2 -> Processa registros
  ├── Pagina 3 -> Processa registros
  ├── ...
  └── Pagina N -> Ultima pagina
```

### Controle de Paginacao

- `--max-pag=N`: Limita a N paginas (util para testes)
- `--set-qtd=N`: Define registros por pagina (max 500)

---

## Tratamento de Erros

### Erros de Requisicao

- Timeout de CURL configuravel
- Erros HTTP logados e contabilizados
- Processamento continua com proximo registro/pagina
- Limite de erros: se `erros > 10` em sequencia, interrompe

### Erros de Processamento

- Falhas de INSERT/UPDATE sao contabilizadas em `$erros`
- Nao interrompem o processamento de outros registros
- Registrados via CvdwSymfonyStyle para visualizacao

---

## Endpoints Disponiveis (70 objetos)

Cada endpoint segue o padrao: `GET /api/v1/cvdw/{path}`

Os paths sao definidos nos arquivos YAML de cada objeto. Exemplos:

| Objeto | Path da API |
|--------|-------------|
| reservas | `/reservas` |
| leads | `/leads` |
| pessoas | `/pessoas` |
| atendimentos | `/atendimentos` |
| assistencias | `/assistencias` |
| comissoes | `/comissoes` |
| corretores | `/corretores` |
| unidades | `/unidades` |
| vendas | `/vendas` |

Para a lista completa, consulte `spec/04-objetos-api.md`.

---

## Implementacao CURL

O servico `Http` usa CURL nativo do PHP:

```
CURLOPT_URL: {url_completa}
CURLOPT_RETURNTRANSFER: true
CURLOPT_HTTPHEADER: [Content-Type, email, token]
CURLOPT_CUSTOMREQUEST: GET
CURLOPT_POSTFIELDS: {parametros_json}
```

Nota: Apesar de ser GET, os parametros de filtro podem ser enviados no body da requisicao.
