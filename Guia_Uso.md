# Guia de Uso do CVDW-CLI

Siga as instruções para configurar e executar o CVDW-CLI em seu ambiente.

---

## Configurar o CVDW-CLI

Após a instalação do CVDW-CLI, será necessário realizar a configuração inicial para que ele funcione corretamente. Utilize o comando abaixo:

```console
cvdw configurar
```

Após executar o comando, será exibido o painel de configuração:

![cvdw configurar](imgs/configurar.png "cvdw configurar")

Na tela de configuração, você encontrará informações sobre a versão do CVDW-CLI e o ambiente ativo. As opções para configurar seu ambiente estão listadas a seguir:

### 0. Acesso ao CVDW API
Preencha os seguintes campos:
- Subdomínio do ambiente CV
- E-mail de acesso
- Token de acesso

Essa é a primeira etapa da configuração inicial.

### 1. Acesso ao Banco de Dados
Informe as credenciais do banco de dados onde deseja criar e alimentar as tabelas. Essa é a segunda etapa da configuração inicial.

### 2. Criar Tabelas no Banco de Dados
Com o ambiente devidamente configurado, selecione esta opção para criar automaticamente as tabelas no banco de dados. Essa é a terceira etapa da configuração inicial.

### 3. Configurar Anonimização de Dados Sensíveis
Escolha como os dados sensíveis do banco de dados serão tratados e anonimizados. Este passo é optativo.

### 4. Verificar/Atualizar o Ambiente
Esta opção deverá ser utilizada para atualizar o ambiente com novas colunas/tabelas ou alterar o domínio apontado. Realize essa verificação após configurar a anonimização dos dados.

### 5. Limpar Datas de Referência das Tabelas
Essa opção limpa as datas de referência das tabelas do banco de dados e permite que a inserção seja atualizada do primeiro registro da tabela.

### 6. Limpar Tabelas do CVDW (Truncate)
Utilize esta opção para limpar todos os dados das tabelas ou de uma tabela específica, fornecendo o nome correto. Use com cautela. Somente os dados contidos na tabela

### 7. Apagar Tabelas do CVDW (Drop)
Essa opção permite apagar todas as tabelas ou uma tabela específica do banco de dados. Tenha cuidado, pois essa ação é irreversível. Faz-se necessário usar a opção 2 após essa ação.

### 8. Cadastrar Novo Ambiente a Partir do Padrão
Permite cadastrar um novo ambiente baseado em um padrão já existente.

### 9. Listar e Remover Ambientes
Use esta opção para listar ou remover ambientes configurados.

### 10. Atualizar o CVDW-CLI
Atualize o CVDW-CLI para a versão mais recente. Caso encontre problemas, utilize o comando alternativo:

```console
curl -sSL https://raw.githubusercontent.com/manzano/cvdw-cli/main/install.sh | bash
```

### 11. Executar o CVDW-CLI
Essa opção redireciona para a tela de execução do CVDW-CLI.

---

## Executar o CVDW-CLI

Após configurar o ambiente, inicie a execução das tabelas para alimentá-las com dados.

![cvdw executar](imgs/executar.png "cvdw executar")

Na tela de execução, você encontrará as seguintes opções:

### 0. Listar Todos os Objetos Disponíveis
Exibe uma lista de todas as tabelas disponíveis no ambiente configurado.

### 1. Executar Todos os Objetos
Processa e alimenta todas as tabelas de uma vez. A primeira execução pode demorar mais tempo, mas as próximas serão mais rápidas.

### 2. Executar um Objeto Específico
Permite selecionar e processar uma tabela específica. Após escolher esta opção, será exibida uma lista de objetos para você selecionar a tabela desejada.

### 3. Configurar o CVDW-CLI
Redireciona diretamente para a tela de configuração do CVDW-CLI.

---

### Selecionando objetos
Você pode selecionar quais objetos quer executar.
Use "+" para informa-los. 

```console
cvdw executar reservas+leads+atendimentos
```

### Relação de objetos disponíveis

| Nome                                      | Comando                             |
|-------------------------------------------|-------------------------------------|
| Agendamentos (/agendamentos/vistorias)    | agendamentos_vistorias              |
| Assistências (/assistencias)              | assistencias                        |
| Assistências (/assistencias/itens)        | assistencias_itens                  |
| Assistências (/assistencias/itens/workflow/tempo)| assistencias_itens_workflow_tempo|
| Assistências (/assistencias/visitas/workflow/tempo)| assistencias_visitas_workflow_tempo|
| Assistências (/assistencias/workflow/tempo)| assistencias_workflow_tempo        |
| Atendimentos (/atendimentos)              | atendimentos                        |
| Atendimentos (/atendimentos/interacoes)   | atendimentos_interacoes             |
| Atendimentos (/atendimentos/respostas)    | atendimentos_respostas              |
| Atendimentos (/atendimentos/tarefas)      | atendimentos_tarefas                |
| Atendimentos (/atendimentos/times)        | atendimentos_times                  |
| Atendimentos (/atendimentos/times/integrantes)| atendimentos_times_integrantes   |
| Atendimentos (/atendimentos/workflow/tempo)| atendimentos_workflow_tempo         |
| Campos Adicionais (/campos_adicionais)    | campos_adicionais                   |
| Campanhas de Ativação (/campanhas_ativacao)| campanhas_ativacao                 |
| Comissões (/comissoes)                    | comissoes                           |
| Comissões (/comissoes/pagamentos)         | comissoes_pagamentos                |
| Comissões (/comissoes/workflow/tempo)     | comissoes_workflow_tempo            |
| Corretores (/corretores)                  | corretores                          |
| Corretores Profissional (/corretores_profissional)| corretores_profissional        |
| Demandas (/demandas)                      | demandas                            |
| Distratos (/distratos)                    | distratos                           |
| Imobiliarias (/imobiliarias)              | imobiliarias                        |
| Leads (/leads)                            | leads                               |
| Leads (/leads/conversoes)                 | leads_conversoes                    |
| Leads (/leads/corretores)                 | leads_corretores                    |
| Leads (/leads/ganhos)                     | leads_ganhos                        |
| Leads (/leads/historico/situacoes)        | leads_historico_situacoes           |
| Leads (/leads/infos)                      | leads_infos                         |
| Leads (/leads/interacoes)                 | leads_interacoes                    |
| Leads (/leads/momentos)                   | leads_momentos                      |
| Leads (/leads/perdas)                     | leads_perdas                        |
| Leads (/leads/tarefas)                    | leads_tarefas                       |
| Leads (/leads/visitas)                    | leads_visitas                       |
| Leads (/leads/workflow/tempo)             | leads_workflow_tempo                |
| Pessoas (/pessoas)                        | pessoas                             |
| Pessoas (/pessoas/bancarios)              | pessoas_bancarios                   |
| Pessoas (/pessoas/bens-empresa)           | pessoas_bens_empresa                |
| Pessoas (/pessoas/contatos)               | pessoas_contatos                    |
| Pessoas (/pessoas/financeiros)            | pessoas_financeiros                 |
| Pessoas (/pessoas/patrimoniais)           | pessoas_patrimoniais                |
| Pessoas (/pessoas/profissional)           | pessoas_profissional                |
| Pesquisas (/pesquisas)                    | pesquisas                           |
| Pesquisas (/pesquisas/perguntas)          | pesquisas_perguntas                 |
| Pesquisas (/pesquisas/respostas)          | pesquisas_respostas                 |
| Pre-cadastro (/precadastros)              | precadastros                        |
| Pre-cadastro (/precadastro/historico/situacoes)| precadastro_historico_situacoes |
| Pre-cadastro (/precadastro/workflow/tempo)| precadastro_workflow_tempo          |
| Processos (/processos)                    | processos                           |
| Reservas (/reservas)                      | reservas                            |
| Reservas (/reservas/associados)           | reservas_associados                 |
| Reservas (/reservas/campos-adicionais)    | reservas_campos_adicionais          |
| Reservas (/reservas/comissoes)            | reservas_comissoes                  |
| Reservas (/reservas/comissoes/programacao)| reservas_comissoes_programacao      |
| Reservas (/reservas/condicoes)            | reservas_condicoes                  |
| Reservas (/reservas/contratos)            | reservas_contratos                  |
| Reservas (/reservas/coordenador)          | reservas_coordenador                |
| Reservas (/reservas/historico)            | reservas_historico                  |
| Reservas (/reservas/historico/situacoes)  | reservas_historico_situacoes        |
| Reservas (/reservas/registros/flags)      | reservas_registros_flags            |
| Reservas (/reservas/sienge)               | reservas_sienge                     |
| Reservas (/reservas/workflow/tempo)       | reservas_workflow_tempo             |
| Repasses (/repasses)                      | repasses                            |
| Repasses (/repasses/historico/situacoes)  | repasses_historico_situacoes        |
| Repasses (/repasses/workflow/tempo)       | repasses_workflow_tempo             |
| Simulações (/simulacoes)                  | simulacoes                          |
| Unidades (/unidades)                      | unidades                            |
| Unidades (/unidades/precos)               | unidades_precos                     |
| Usuários Administrativos (/usuarios_administrativos)| usuarios_administrativos    |
| Vendas (/vendas)                          | vendas                              |
