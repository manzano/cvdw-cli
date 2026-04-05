# 04 - Registro de Objetos YAML

## Objetivo

Construir o parser YAML e o registro de objetos que carrega todas as 70 definicoes de objetos da API. Implementa `InterfaceRepositorioObjeto` da camada de dominio.

---

## Registro de Objetos

### `RepositorioObjetoYaml`

```php
namespace CvdwCli\Infraestrutura\SistemaArquivos;

final class RepositorioObjetoYaml implements InterfaceRepositorioObjeto
{
    private ?array $cache = null;

    public function __construct(
        private readonly string $caminhoObjetos,
        private readonly AnalisadorObjetoYaml $analisador,
    ) {}

    public function buscarTodos(): array;
    public function buscarPorNome(string $nome): ?ObjetoSincronizacao;
    public function buscarPorNomes(array $nomes): array;

    /** Retorna apenas nomes + descricoes (para listagem na UI) */
    public function listarDisponiveis(): array;
}
```

### `AnalisadorObjetoYaml`

Converte YAML cru em entidades `ObjetoSincronizacao` do dominio.

```php
namespace CvdwCli\Infraestrutura\SistemaArquivos;

final class AnalisadorObjetoYaml
{
    public function analisar(string $nome, array $dadosYaml): ObjetoSincronizacao;
    public function analisarCampo(string $nome, array $especificacao): Campo;
    private function extrairSubTabelas(string $nomePai, array $campos): array;
}
```

Mapeamento de chaves PT (YAML) para propriedades PT (dominio):
- `tabela` -> `nomeTabela`
- `descricao` -> `descricao`
- `tamanho` -> `tamanho`
- `sensivel` -> `sensivel`

---

## Formato YAML

Manter o formato existente. Nao ha necessidade de reescrever 70 arquivos YAML.

```yaml
path: /reservas
tabela: reservas
metodo: GET
descricao: Reservas de unidades
parametros:
  header:
    Content-Type: application/json
    email: "{email}"
    token: "{token}"
  query:
    pagina: 1
    registros_por_pagina: 500
    a_partir_data_referencia: ""
response:
  referencia:
    type: string
    tamanho: 255
    sensivel: false
  referencia_data:
    type: datetime
  nome_cliente:
    type: string
    tamanho: 200
    sensivel: true
```

---

## Catalogo de Objetos

```php
namespace CvdwCli\Infraestrutura\SistemaArquivos;

final class CatalogoObjetos
{
    /** Retorna objetos organizados por categoria para exibicao */
    public function obterPorCategoria(): array;
}
```

Categorias derivadas do prefixo do nome do objeto:
- `reservas*` -> Reservas
- `leads*` -> Leads
- `pessoas*` -> Pessoas
- `atendimentos*` -> Atendimentos
- `assistencias*` -> Assistencias
- `comissoes*` -> Comissoes
- `corretores*` -> Corretores
- `pesquisas*` -> Pesquisas
- `repasses*` -> Repasses
- `precadastro*` -> Pre-cadastros
- Demais -> avulsos

---

## Validacao

### `ValidadorObjeto`

```php
namespace CvdwCli\Infraestrutura\SistemaArquivos;

final class ValidadorObjeto
{
    /** @throws ExcecaoObjetoInvalido */
    public function validar(string $nome, array $dadosYaml): void;
}
```

Verifica:
- Chaves obrigatorias: `path`, `tabela`, `response`
- `response` tem campos `referencia` e `referencia_data`
- Todos os campos tem um `type` valido
- `tamanho` e inteiro positivo quando presente
- Sem nomes de coluna duplicados apos sanitizacao

---

## Testes

- Parse de objeto simples com campos basicos
- Parse de objeto com sub-tabela (array aninhado)
- Mapeamento `tabela` -> `nomeTabela`, `descricao` -> `descricao`
- Enum TipoCampo resolve corretamente para todos os tipos
- Tipo desconhecido lanca ExcecaoObjetoInvalido
- buscarTodos() carrega todos os YAMLs do diretorio
- buscarPorNome() retorna null para inexistente
- buscarPorNomes() com separador "+" funciona
- Resultados cacheados apos primeiro carregamento
- YAML valido passa validacao
- YAML com `path` faltando lanca excecao

---

## Nota sobre Migracao

Os 70 arquivos YAML existentes sao reutilizados como estao. O analisador lida com as chaves em portugues (`tabela`, `descricao`, `tamanho`, `sensivel`) internamente e mapeia para propriedades do dominio.

---

## Criterios de Aceite

- [ ] Todos os 70 YAMLs existentes carregam sem erros
- [ ] Entidades `ObjetoSincronizacao` representam corretamente todos os campos
- [ ] Sub-tabelas detectadas e modeladas como `ObjetoSincronizacao` filho
- [ ] Campos sensiveis identificados
- [ ] Catalogo agrupa por categoria
- [ ] Validacao captura YAMLs malformados
- [ ] Resultados cacheados (carregados uma vez por execucao)
- [ ] Todos os testes passam
