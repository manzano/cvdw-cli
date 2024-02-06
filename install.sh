#!/bin/bash

set -e

echo "Instalando do CVDW-CLI"
echo "============================"
echo ""
echo "🔍 Verificando os pre-requisitos..."
echo ""

# Checando se o Git está instalado
if ! command -v git >/dev/null 2>&1; then
  echo "❌ Git não encontrado. Por favor instale o Git e tente novamente."
  exit 1
fi
echo "✅ Git encontrado."

# Checando se o PHP está instalado
if ! command -v php >/dev/null 2>&1; then
  echo "❌ PHP não encontrado. Por favor instale o PHP e tente novamente."
  exit 1
fi
echo "✅ PHP encontrado."

# Checando se o Composer está instalado
if ! command -v composer >/dev/null 2>&1; then
  echo "❌ Composer não encontrado. Por favor instale o Composer e tente novamente."
  exit 1
fi
echo "✅ Composer encontrado."

echo ""
echo "🚀 Iniciando a instalação do CVDW-CLI."

REPO_DIR="$HOME/cvdw-cli"

# Check if repository already exists
if [ -d "$REPO_DIR" ]; then
  echo "🔄 O CVDW-CLI já está instalado em $REPO_DIR - Iniciciando o Update."
  echo ""
  cd "$REPO_DIR"
  git checkout main
  git pull 2>&1 || {
    read -p "⚠️ Erro ao tentar fazer o Pull. Limpar e atualizar? [Y/n]: " answer
    answer=${answer:-Y}
    if [[ $answer =~ ^[Yy]$ ]]; then
      git reset --hard >/dev/null 2>&1
      git clean -fd >/dev/null 2>&1
      git pull >/dev/null 2>&1
    fi
  }
else
  echo "📦 Clonando o repositório para $REPO_DIR..."
  git clone https://github.com/manzano/cvdw-cli.git "$REPO_DIR" >/dev/null 2>&1
  cd "$REPO_DIR"
  git checkout main >/dev/null 2>&1
fi

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