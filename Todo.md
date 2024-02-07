
# To-Do List para o Projeto CVDW

Aqui está uma lista de tarefas pendentes para melhorar e otimizar o projeto CVDW. Estas tarefas são essenciais para garantir a eficiência, a robustez e a usabilidade da ferramenta.

## Tarefas Pendentes

- ~~**Recarregar .env no Final das Configurações**: Garantir que o arquivo `.env` seja recarregado automaticamente após quaisquer alterações nas configurações para que as novas variáveis de ambiente sejam aplicadas imediatamente.~~

- **Ignorar data de referencia**: Usar o argumento --ignorar-data-referencia.

- **Continuar após o erro**: Se der erro em algum requisição, seguir com a execução dos outros endpoints.

- **Controle de requisições vazias por minuto**: Garantir que o a quantidade de requisições por minuto não ultrapasse o limite quando o retorno for vazio.

- **Validar no Executar se Já Estamos Configurados**: Antes de executar qualquer comando, verificar se o ambiente já está configurado corretamente para evitar a repetição de configurações ou erros de execução.

- **Corrigir Erro ao Sair das Configurações**: Resolver o problema que causa um erro em `criarTabelas` quando se clica para sair das configurações. Isso melhorará a estabilidade da ferramenta.

- **Implementar `cvdw -dir` para Exibir o URL do CVDW**: Adicionar uma opção `-dir` ao comando `cvdw` que permita aos usuários visualizar facilmente o Caminho do Alias atual do CVDW.

- **Modo Detalhado `--detalhado` (Verbose)**: Introduzir um modo detalhado (ou verbose) que forneça aos usuários mais informações durante a execução dos comandos, facilitando o diagnóstico e a compreensão dos processos internos.

- **Salvar Log `--salvarlog`**: Implementar uma opção para salvar logs de execução, permitindo aos usuários armazenar e revisar logs para análise ou depuração de problemas.

---

Estas tarefas são fundamentais para o desenvolvimento contínuo do projeto CVDW e devem ser priorizadas conforme a disponibilidade dos desenvolvedores e os recursos.