#!/bin/bash

set -e

echo "Instalando do CVDW-CLI"
echo "============================"

echo "🔍 Checando pré-requisitos..."

echo "🚀 Iniciando a instalação do CVDW-CLI..."

REPO_DIR="$HOME/.cvdw-cli"

mkdir $REPO_DIR
cd $REPO_DIR
wget -O cvdw-cli.zip https://github.com/manzano/cvdw-cli/archive/refs/tags/v0.8.0-alpha.zip
unzip cvdw-cli.zip
mv cvdw-cli-0.8.0-alpha/build/cvdw.phar ./cvdw.phar
rm -rf cvdw-cli-0.8.0-alpha/
rm -rf cvdw-cli.zip
chmod +x cvdw.phar

echo "🚀 Instalação concluida com sucesso..."