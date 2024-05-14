#!/bin/bash

# Encontra todos os IDs de processos do PHP em execução
PIDS=$(pgrep php)

# Verifica se a variável PIDS não está vazia
if [ -z "$PIDS" ]; then
  echo "Nenhum processo do PHP encontrado."
else
  # Mata cada processo encontrado
  echo "Matando os seguintes processos do PHP: $PIDS"
  kill $PIDS

  # Verifica se os processos foram terminados com sucesso
  if [ $? -eq 0 ]; then
    echo "Todos os processos do PHP foram terminados com sucesso."
  else
    echo "Houve um erro ao tentar terminar os processos do PHP."
  fi
fi

bash -ic "cvdw executar all"
