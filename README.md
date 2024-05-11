
# CVDW-CLI

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

    curl -sSL https://raw.githubusercontent.com/manzano/cvdw-cli/main/install.sh | bash

## Instalação

Para instalar o CVDW-cli, siga as [instruções de instalação](Install.md).

## Configurando

    cvdw configurar

![cvdw configurar](imgs/configurar.png "cvdw configurar")

## Executando
Agora sim, podemos executar...

    $ cvdw executar

![cvdw executar](imgs/executar.png "cvdw executar")

## Contribuição

Contribuições para o projeto são bem-vindas! Se você deseja contribuir, por favor, siga as [instruções de contribuição](Developer.md).

## Licença

Este projeto está licenciado sob a Licença MIT - veja o arquivo [LICENSE](LICENSE) para detalhes.

