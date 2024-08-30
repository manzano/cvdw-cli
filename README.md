
# CVDW-CLI

[![Quality Gate Status](https://sonarcloud.io/api/project_badges/measure?project=manzano_cvdw-cli&metric=alert_status)](https://sonarcloud.io/summary/new_code?id=manzano_cvdw-cli)

[![Bugs](https://sonarcloud.io/api/project_badges/measure?project=manzano_cvdw-cli&metric=bugs)](https://sonarcloud.io/summary/new_code?id=manzano_cvdw-cli)

[![Code Smells](https://sonarcloud.io/api/project_badges/measure?project=manzano_cvdw-cli&metric=code_smells)](https://sonarcloud.io/summary/new_code?id=manzano_cvdw-cli)

[![Coverage](https://sonarcloud.io/api/project_badges/measure?project=manzano_cvdw-cli&metric=coverage)](https://sonarcloud.io/summary/new_code?id=manzano_cvdw-cli)

[![Duplicated Lines (%)](https://sonarcloud.io/api/project_badges/measure?project=manzano_cvdw-cli&metric=duplicated_lines_density)](https://sonarcloud.io/summary/new_code?id=manzano_cvdw-cli)

O **CVDW Command-line Interface (cvdw-cli)** é uma ferramenta poderosa projetada para facilitar a busca de informações nas APIs do [CV CRM](https://www.cvcrm.com.br) e salvar em um banco de dados, seja local ou remoto. Isso torna a ferramenta extremamente útil para a criação de dashboards e análise de dados.

![CVDW-cli Terminal](imgs/terminal.gif "CVDW-cli")

## Banco de Dados Compatíveis

- MySQL
- MariaDB
- PostgreSQL
- SQL Server

## Pré-requisitos

- PHP >= 8.2
- Composer

## Depois de instalado, atualizando

Depois de instalado, use sempre esse comando para atualizar o CVDW.

```console
curl -sSL https://raw.githubusercontent.com/manzano/cvdw-cli/main/install.sh | bash
```

## Instalação

Para instalar o CVDW-cli, siga as [instruções de instalação](Install.md).

## Configurando

```console
cvdw configurar [-env|--set-env [SET-ENV]]
```

* `--set-env` `-env` — Diz qual ENV usar. Exemplo: dev, homologacao, producao.

![cvdw configurar](imgs/configurar.png "cvdw configurar")

## Executando
Agora sim, podemos executar...

```console
cvdw executar [-idr|--ignorar-data-referencia] [-log|--salvarlog] [-env|--set-env [SET-ENV]] [-qtd|--set-qtd [SET-QTD]] [-a|--apartir [APARTIR]] [-m|--max-pag [MAX-PAG]] [--] [<objeto>]
```

* `--ignorar-data-referencia` `-idr` — Ignorar a data de referência.
* `--salvarlog` `-log` — Salvar Log da execução no diretorio de instalação.
* `--set-env` `-env` — Diz qual ENV usar. Exemplo: dev, homologacao, producao.
* `--set-qtd` `-qtd` — Quantidade de dados retornada por cada requisicao.
* `--apartir` `-a` — Consultar a partir de uma data de referencia especifica.

                No formato: Y-m-d\TH:i:s ou Y-m-d.
* `--max-pag` `-m` — Executa o número máximo de página informado.
* `<objeto>` Qual objeto deseja executar

![cvdw executar](imgs/executar.png "cvdw executar")

## Contribuição

Contribuições para o projeto são bem-vindas! Se você deseja contribuir, por favor, siga as [instruções de contribuição](Developer.md).

## Licença

Este projeto está licenciado sob a Licença MIT - veja o arquivo [LICENSE](LICENSE) para detalhes.

