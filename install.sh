#!/bin/bash

set -e

echo "Instalando do CVDW-CLI"
echo "============================"
echo ""
echo "🔍 Verificando os pre-requisitos..."
echo ""

# Função para capturar saída e erros
execute() {
    cmd_output=$($1 2>&1)
    exit_code=$?
    if [ $exit_code -ne 0 ]; then
        echo "Erro ao executar: $1"
        echo "Saída: $cmd_output"
        exit $exit_code
    else
        echo "Saída: $cmd_output"
    fi
}

# Checando se o Git está instalado
execute "command -v git"
echo "✅ Git encontrado."

# Checando se o PHP está instalado
execute "command -v php"
echo "✅ PHP encontrado."

# Checando se o Composer está instalado
execute "command -v composer"
echo "✅ Composer encontrado."

echo ""
echo "🚀 Iniciando a instalação do CVDW-CLI."

REPO_DIR="$HOME/cvdw-cli"

# Verificar se o repositório já existe
if [ -d "$REPO_DIR" ]; then
  echo "🔄 O CVDW-CLI já está instalado em $REPO_DIR - Iniciando o Update."
  echo ""
  cd "$REPO_DIR"
  execute "git checkout main"
  execute "git pull"
else
  echo "📦 Clonando o repositório para $REPO_DIR..."
  execute "git clone https://github.com/manzano/cvdw-cli.git $REPO_DIR"
  cd "$REPO_DIR"
  execute "git checkout main"
fi

chmod u+w $REPO_DIR

echo ""
echo "Instalando as dependências do Composer..."
execute "composer install"
execute "composer dump-autoload --optimize"
echo "✅ Dependências do Composer instaladas."


# Define o comando do alias
alias_command="alias cvdw='php $REPO_DIR/src/cvdw'"

# Função para adicionar alias ao Bash
add_alias_bash() {
    local profile_file="$HOME/.bashrc"
    if [ ! -f "$profile_file" ]; then
        profile_file="$HOME/.bash_profile"
    fi

    if ! grep -qF -- "$alias_command" "$profile_file"; then
        echo "$alias_command" >> "$profile_file"
        echo "Alias adicionado ao $profile_file para Bash."
        echo ""
    else
        echo "Alias já existe no $profile_file para Bash."
        echo ""
    fi
}

# Função para adicionar alias ao Zsh
add_alias_zsh() {
    local profile_file="$HOME/.zshrc"

    if ! grep -qF -- "$alias_command" "$profile_file"; then
        echo "$alias_command" >> "$profile_file"
        echo "Alias adicionado ao $profile_file para Zsh."
        echo ""
    else
        echo "Alias já existe no $profile_file para Zsh."
        echo ""
    fi
}

echo "📁 Salvando o alias em seu terminal..."
echo ""

# Detecta o shell atual e aplica a configuração apropriada
if [[ "$SHELL" == */bash ]]; then
    add_alias_bash
elif [[ "$SHELL" == */zsh ]]; then
    add_alias_zsh
else
    echo "Shell não suportado. Alias não adicionado."
fi

echo ""
echo "Alias instalado. Por favor, execute 'source ~/.bashrc' (para Bash) ou 'source ~/.zshrc' (para Zsh), ou reinicie seu terminal para aplicar as alterações."
echo ""
echo "✅ Instalação do CVDW-CLI concluída com sucesso!"