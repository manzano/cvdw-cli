# Seguranca e Anonimizacao

## Autenticacao da API

### Credenciais

| Variavel | Descricao | Formato |
|----------|-----------|---------|
| `CV_URL` | Subdominio do CV CRM | String alfanumerica |
| `CV_EMAIL` | Email do usuario admin | Email valido |
| `CV_TOKEN` | Token de acesso | SHA1 (40 caracteres hex) |

### Armazenamento

- Credenciais armazenadas em arquivos `.env` no diretorio `src/envs/`
- Formato texto plano (nao criptografado)
- Arquivo `.env` deve ter permissoes restritas no servidor

### Transmissao

- Headers HTTP sobre HTTPS
- Nenhum mecanismo de refresh token
- Sem OAuth ou certificados client-side

---

## Anonimizacao de Dados

O sistema oferece anonimizacao de dados sensiveis para conformidade com LGPD/GDPR.

### Configuracao

```env
ANONIMIZAR=true              # Ativar anonimizacao
ANONIMIZAR_TIPO=Asteriscos   # Metodo: Asteriscos ou Hash
```

### Campos Sensiveis

Campos marcados com `sensivel: true` nos arquivos YAML sao candidatos a anonimizacao.

Exemplos tipicos:
- Nomes de pessoas (`nome`, `nome_completo`)
- Documentos (`cpf`, `cnpj`, `rg`)
- Contatos (`email`, `telefone`, `celular`)
- Enderecos (`endereco`, `cep`, `bairro`)

### Metodos de Anonimizacao

#### 1. Asteriscos (`substituirPorAsteriscos`)

Substitui a metade central do texto por asteriscos:

```
"Gabriel Manzano" -> "Gab*********ano"
"gabriel@email.com" -> "gab**********com"
"11999887766" -> "119*****766"
```

**Logica:**
- Calcula metade do comprimento da string
- Substitui caracteres centrais por `*`
- Mantém inicio e fim visiveis

#### 2. Hash (`substituirPorHash`)

Gera hash SHA256 do texto original:

```
"Gabriel Manzano" -> "a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6"
"gabriel@email.com" -> "x1y2z3a4b5c6d7e8f9g0h1i2j3k4l5m6"
```

**Logica:**
- Aplica `hash('sha256', $texto)`
- Trunca para 32 caracteres (por padrao)
- Resultado irreversivel

### Fluxo de Anonimizacao

```
Registro recebido da API
  ↓
Para cada campo do registro:
  ↓
Campo marcado como 'sensivel' no YAML?
  ├── Nao -> Valor mantido original
  └── Sim -> Anonimizacao ativada?
        ├── Nao -> Valor mantido original
        └── Sim -> Aplica metodo configurado
              ├── Asteriscos -> substituirPorAsteriscos()
              └── Hash -> substituirPorHash()
  ↓
Valor (original ou anonimizado) salvo no banco
```

---

## Seguranca do Banco de Dados

### Conexao

- Usa Doctrine DBAL (prepared statements por padrao)
- Protecao contra SQL injection via parametros vinculados
- Suporte a schemas separados (PostgreSQL)

### Credenciais de Banco

```env
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=cvdw
DB_USERNAME=cvdw_user
DB_PASSWORD=cvdw_pwd
```

- Armazenadas em `.env` (texto plano)
- Recomendado: permissoes restritas no arquivo
- Recomendado: usuario com permissoes minimas necessarias

---

## Monitoramento e Rastreamento

### Sentry (Error Tracking)

- Dependencia: `sentry/sentry ^4.8`
- Captura excecoes e erros nao tratados
- Envia para dashboard Sentry configurado

### Mixpanel (Analytics)

- Dependencia: `mixpanel/mixpanel-php ^2.11`
- Rastreia eventos de uso da ferramenta
- Dados de telemetria anonimizados

### Logs Locais

- Arquivos em `logs/log_Y-m-d_H-i-s.log`
- Registram operacoes de sync e erros
- Ativados via `--salvarlog`

---

## Boas Praticas de Seguranca

1. **Proteger arquivos `.env`** com permissoes `600` ou `640`
2. **Nao versionar** arquivos `.env` (ja incluido no `.gitignore`)
3. **Usar anonimizacao** quando dados PII nao sao necessarios
4. **Preferir Hash** sobre Asteriscos para maior protecao
5. **Limitar usuario do banco** a permissoes estritamente necessarias
6. **Usar HTTPS** (garantido pela URL do CV CRM)
7. **Rotacionar tokens** periodicamente no CV CRM
