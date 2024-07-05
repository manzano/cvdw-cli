
# Guia de Ambiente de Desenvolvimento para CVDW-CLI

Este documento orienta a preparação do ambiente de desenvolvimento e as melhores práticas para contribuir com o projeto CVDW-CLI.

## Configuração Inicial

### Atualizar o Sistema

Garanta que seu sistema está atualizado:

```console
sudo yum update -y
```

### Instalar Ferramentas Essenciais

Instale o git, PHP 8.2, e outras ferramentas necessárias:

```console
sudo yum install -y git tmux php8.2 php8.2-cli php8.2-pdo
```

## Clonar o Repositório

Clone o repositório do projeto para sua máquina local:

```console
git clone https://github.com/manzano/cvdw-cli.git
cd cvdw-cli
```

## Criar uma Nova Branch

Antes de iniciar o desenvolvimento, crie uma nova branch para suas alterações:

```console
git checkout -b nome-da-sua-branch
```

## Desenvolvimento

Faça suas alterações no código localmente. Teste suas alterações rigorosamente para garantir qualidade e compatibilidade.

## Enviar Mudanças para Revisão

Após completar suas alterações, envie-as para o repositório remoto:

```console
git add .
git commit -m "Descrição das suas alterações"
git push origin nome-da-sua-branch
```

## Criar um Pull Request

Vá até o repositório no GitHub e crie um Pull Request da sua branch para a branch principal. Assegure-se de detalhar suas mudanças e a razão por trás delas para facilitar a revisão.

## Conclusão

Este guia destina-se a facilitar o processo de desenvolvimento e contribuição para o projeto CVDW-CLI. A adesão a estas práticas garante uma colaboração eficaz e o avanço contínuo do projeto.
