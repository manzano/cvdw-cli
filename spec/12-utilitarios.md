# Utilitarios e Infraestrutura

## Inc/Helper.php

Funcoes auxiliares globais carregadas via autoload.

### `validarData(string $data): bool`

Valida formato de data aceito pelo sistema.

**Formatos aceitos:**
- `Y-m-d` (ex: `2024-01-15`)
- `Y-m-dTH:i:s` (ex: `2024-01-15T10:30:00`)

**Regras de validacao:**
- Ano entre 1900 e 2100
- Mes entre 01 e 12
- Dia entre 01 e 31
- Hora entre 00 e 23
- Minuto e segundo entre 00 e 59

**Uso:** Validacao do parametro `--apartir` no comando `executar`.

### `substituirPorAsteriscos(string $texto): string`

Substitui a metade central do texto por asteriscos.

**Algoritmo:**
1. Calcula comprimento da string
2. Define inicio = 25% do comprimento
3. Define fim = 75% do comprimento
4. Substitui caracteres entre inicio e fim por `*`

**Exemplos:**
```
"Gabriel" -> "Ga***el"
"12345678901" -> "123*****901"
```

### `substituirPorHash(string $texto, int $tamanho = 32): string`

Gera hash SHA256 truncado do texto.

**Algoritmo:**
1. Aplica `hash('sha256', $texto)`
2. Trunca resultado para `$tamanho` caracteres

**Exemplo:**
```
"Gabriel" -> "a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6"
```

---

## Inc/Conexao.php

Fabrica de conexoes de banco de dados.

### `conectarDB(EnvironmentManager $env): Connection`

**Parametros da conexao:**
```php
[
    'dbname'   => $env->getDbDatabase(),
    'user'     => $env->getDbUsername(),
    'password' => $env->getDbPassword(),
    'host'     => $env->getDbHost(),
    'port'     => $env->getDbPort(),
    'driver'   => $env->getDbConnection(),
]
```

**Tratamento especial PostgreSQL:**
- Cria schema se `DB_SCHEMA` definido
- Define `search_path` para o schema

**Tratamento de erros:**
- Captura `Exception`
- Exibe mensagem amigavel com detalhes da conexao
- Re-lanca excecao

---

## Inc/Exception.php

### Classe `CvdwException`

```php
class CvdwException extends \RuntimeException
{
    public function __construct(
        string $message = "",
        int $code = 0,
        ?\Throwable $previous = null
    )
}
```

Excecao customizada para erros especificos do CVDW-CLI. Permite identificar erros da aplicacao vs erros de framework/sistema.

---

## Console/CvdwSymfonyStyle.php

Extensao do `SymfonyStyle` do Symfony Console.

### Funcionalidades Adicionais

| Metodo | Descricao |
|--------|-----------|
| `titulo(string)` | Exibe titulo estilizado |
| `subtitulo(string)` | Exibe subtitulo |
| `sucesso(string)` | Mensagem verde de sucesso |
| `erro(string)` | Mensagem vermelha de erro |
| `aviso(string)` | Mensagem amarela de aviso |
| `info(string)` | Mensagem azul informativa |

### Integracao com Log

Se servico de Log ativo (`--salvarlog`):
- Todas as saidas sao simultaneamente escritas no log
- Formato: `[Y-m-d H:i:s] {mensagem}`

### Perguntas Estilizadas

Metodos para input interativo com estilo visual do CVDW:
- `pergunta(string): string` - Pergunta aberta
- `confirmar(string): bool` - Sim/Nao
- `escolher(string, array): string` - Menu de opcoes
