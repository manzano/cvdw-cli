<?php

namespace Manzano\CvdwCli\Services;

use Manzano\CvdwCli\Services\Console\CvdwSymfonyStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Yaml\Yaml;

class Objeto
{

    protected CvdwSymfonyStyle $io;
    public InputInterface $input;
    public OutputInterface $output;
    public array $objetos = array();

    public function __construct(InputInterface $input, OutputInterface $output)
    {
        $this->io = new CvdwSymfonyStyle($input, $output);
        $this->input = $input;
        $this->output = $output;
        $this->objetos = $this->retornarConstantesObjetos();
    }

    public function retornarObjetos(string $objeto = null): array
    {
        if ($objeto) {
            if (isset($this->objetos[$objeto])) {
                return ["$objeto" => $this->objetos[$objeto]];
            } else {
                return [];
            }
        } else {
            return $this->objetos;
        }
    }

    public function retornarObjeto(string $objeto): array
    {
        // Verifica se o objeto existe em OBJETOS
        if (!array_key_exists($objeto, $this->objetos)) {
            return [];
        } else {
            $objetoFile = __DIR__ . "/../Objetos/{$objeto}.yaml";
            $objeto = file_get_contents($objetoFile);
            return Yaml::parse($objeto);
        }
    }

    public function retornarObjetoTabelas($objeto): array
    {
        $objeto = $this->retornarObjeto($objeto);
        if (empty($objeto)) {
            return [];
        }
        $componentes = $objeto['response']['dados'];
        $tabelas = [];
        foreach ($componentes as $componente => $dados) {
            if ($this->identificarTipoDeDados($dados) == "TABELA") {
                $tabelas[$componente] = $dados;
            }
        }
        return $tabelas;
    }

    public function identificarTipoDeDados(array $dados): string
    {
        foreach ($dados as $valor) {
            if (is_array($valor) || is_object($valor)) {
                return "TABELA";
            }
        }
        return "COMPONENTE";
    }

    public function retornarConstantesObjetos(): array
    {
        return array_merge(
            $this->retornarObjetosReservas(),
            $this->retornarObjetosPessoas(),
            $this->retornarObjetosAtendimentos(),
            $this->retornarObjetosRepasses(),
            $this->retornarObjetosPrecadastros(),
            $this->retornarObjetosCadastros(),
            $this->retornarObjetosLeads(),
            $this->retornarObjetosAssistencia(),
            $this->retornarObjetosComissoes(),
            $this->retornarObjetosSimulacoes(),
            $this->retornarObjetosAgendamentos(),
            $this->retornarObjetosProcessos(),
            $this->retornarObjetosPesquisas(),
            $this->retornarObjetosDemandas()

        );
    }

    private function retornarObjetosReservas()
    {
        return [
            "reservas" => [
                "nome" => "Reservas (/reservas)",
                "arquivo" => "reservas.yaml"
            ],
            "reservas_comissoes" => [
                "nome" => "Reservas (/reservas/comissoes)",
                "arquivo" => "reservas_comissoes.yaml"
            ],
            "reservas_comissoes_programacao" => [
                "nome" => "Reservas (/reservas/comissoes/programacao)",
                "arquivo" => "reservas_comissoes_programacao.yaml"
            ],
            "reservas_coordenador" => [
                "nome" => "Reservas (/reservas/coordenador)",
                "arquivo" => "reservas_coordenador.yaml"
            ],
            "reservas_historico_situacoes" => [
                "nome" => "Reservas (/reservas/historico/situacoes)",
                "arquivo" => "reservas_historico_situacoes.yaml"
            ],
            "reservas_historico" => [
                "nome" => "Reservas (/reservas/historico)",
                "arquivo" => "reservas_historico.yaml"
            ],
            "reservas_condicoes" => [
                "nome" => "Reservas (/reservas/condicoes)",
                "arquivo" => "reservas_condicoes.yaml"
            ],
            "reservas_associados" => [
                "nome" => "Reservas (/reservas/associados)",
                "arquivo" => "reservas_associados.yaml"
            ],
            "reservas_contratos" => [
                "nome" => "Reservas (/reservas/contratos)",
                "arquivo" => "reservas_contratos.yaml"
            ],
            "reservas_workflow_tempo" => [
                "nome" => "Reservas (/reservas/workflow/tempo)",
                "arquivo" => "reservas_workflow_tempo.yaml"
            ],
            "reservas_campos_adicionais" => [
                "nome" => "Reservas (/reservas/campos-adicionais)",
                "arquivo" => "reservas_campos_adicionais.yaml"
            ],
            "reservas_sienge" => [
                "nome" => "Reservas (/reservas/sienge)",
                "arquivo" => "reservas_sienge.yaml"
            ],
            "reservas_registros_flags" => [
                "nome" => "Reservas (/reservas/registros/flags)",
                "arquivo" => "reservas_registros_flags.yaml"
            ],
            "vendas" => [
                "nome" => "Vendas (/vendas)",
                "arquivo" => "vendas.yaml"
            ],
            "distratos" => [
                "nome" => "Distratos (/distratos)",
                "arquivo" => "distratos.yaml"
            ],
        ];
    }

    private function retornarObjetosPessoas()
    {
        return [
            "pessoas" => [
                "nome" => "Pessoas (/pessoas)",
                "arquivo" => "pessoas.yaml"
            ],
            "pessoas_contatos" => [
                "nome" => "Pessoas (/pessoas/contatos)",
                "arquivo" => "pessoas_contatos.yaml"
            ],
            "pessoas_profissional" => [
                "nome" => "Pessoas (/pessoas/profissional)",
                "arquivo" => "pessoas_profissional.yaml"
            ],
            "pessoas_patrimoniais" => [
                "nome" => "Pessoas (/pessoas/patrimoniais)",
                "arquivo" => "pessoas_patrimoniais.yaml"
            ],
            "pessoas_bancarios" => [
                "nome" => "Pessoas (/pessoas/bancarios)",
                "arquivo" => "pessoas_bancarios.yaml"
            ],
            "pessoas_bens_empresa" => [
                "nome" => "Pessoas (/pessoas/bens-empresa)",
                "arquivo" => "pessoas_bens_empresa.yaml"
            ],
            "pessoas_financeiros" => [
                "nome" => "Pessoas (/pessoas/financeiros)",
                "arquivo" => "pessoas_financeiros.yaml"
            ]
        ];
    }

    private function retornarObjetosAtendimentos()
    {
        return [
            "atendimentos" => [
                "nome" => "Atendimentos (/atendimentos)",
                "arquivo" => "atendimentos.yaml"
            ],
            "atendimentos_workflow_tempo" => [
                "nome" => "Atendimentos (/atendimentos/workflow/tempo)",
                "arquivo" => "atendimentos_workflow_tempo.yaml"
            ],
            "atendimentos_interacoes" => [
                "nome" => "Atendimentos (/atendimentos/interacoes)",
                "arquivo" => "atendimentos_interacoes.yaml"
            ],
            "atendimentos_respostas" => [
                "nome" => "Atendimentos (/atendimentos/respostas)",
                "arquivo" => "atendimentos_respostas.yaml"
            ],
            "atendimentos_times" => [
                "nome" => "Atendimentos (/atendimentos/times)",
                "arquivo" => "atendimentos_times.yaml"
            ],
            "atendimentos_times_integrantes" => [
                "nome" => "Atendimentos (/atendimentos/times/integrantes)",
                "arquivo" => "atendimentos_times_integrantes.yaml"
            ],
            "atendimentos_tarefas" => [
                "nome" => "Atendimentos (/atendimentos/tarefas)",
                "arquivo" => "atendimentos_tarefas.yaml"
            ],
        ];
    }

    private function retornarObjetosRepasses()
    {
        return [
            "repasses" => [
                "nome" => "Repasses (/repasses)",
                "arquivo" => "repasses.yaml"
            ],
            "repasses_workflow_tempo" => [
                "nome" => "Repasses (/repasses/workflow/tempo)",
                "arquivo" => "repasses_workflow_tempo.yaml"
            ],
            "repasses_historico_situacoes" => [
                "nome" => "Repasses (/repasses/historico/situacoes)",
                "arquivo" => "repasses_historico_situacoes.yaml"
            ]
        ];
    }

    private function retornarObjetosPrecadastros()
    {
        return [
            "precadastros" => [
                "nome" => "Pre-cadastro (/precadastros)",
                "arquivo" => "precadastros.yaml"
            ],
            "precadastro_workflow_tempo" => [
                "nome" => "Pre-cadastro (/precadastro/workflow/tempo)",
                "arquivo" => "precadastro_workflow_tempo.yaml"
            ],
            "precadastro_historico_situacoes" => [
                "nome" => "Pre-cadastro (/precadastro/historico/situacoes)",
                "arquivo" => "precadastro_historico_situacoes.yaml"
            ]
        ];
    }

    private function retornarObjetosCadastros()
    {
        return [
            "usuarios_administrativos" => [
                "nome" => "Usuários Administrativos (/usuarios_administrativos)",
                "arquivo" => "usuarios_administrativos.yaml"
            ],
            "imobiliarias" => [
                "nome" => "Imobiliarias (/imobiliarias)",
                "arquivo" => "imobiliarias.yaml"
            ],
            "corretores" => [
                "nome" => "Corretores (/corretores)",
                "arquivo" => "corretores.yaml"
            ],
            "unidades_precos" => [
                "nome" => "Unidades (/unidades/precos)",
                "arquivo" => "unidades_precos.yaml"
            ],
            "unidades" => [
                "nome" => "Unidades (/unidades)",
                "arquivo" => "unidades.yaml"
            ],
            "campos_adicionais" => [
                "nome" => "Campos Adicionais (/campos_adicionais)",
                "arquivo" => "campos_adicionais.yaml"
            ]
        ];
    }

    private function retornarObjetosLeads()
    {
        return [
            "leads_corretores" => [
                "nome" => "Leads (/leads/corretores)",
                "arquivo" => "leads_corretores.yaml"
            ],
            "leads" => [
                "nome" => "Leads (/leads)",
                "arquivo" => "leads.yaml"
            ],
            "leads_conversoes" => [
                "nome" => "Leads (/leads/conversoes)",
                "arquivo" => "leads_conversoes.yaml"
            ],
            "leads_ganhos" => [
                "nome" => "Leads (/leads/ganhos)",
                "arquivo" => "leads_ganhos.yaml"
            ],
            "leads_historico_situacoes" => [
                "nome" => "Leads (/leads/historico/situacoes)",
                "arquivo" => "leads_historico_situacoes.yaml"
            ],
            "leads_infos" => [
                "nome" => "Leads (/leads/infos)",
                "arquivo" => "leads_infos.yaml"
            ],
            "leads_interacoes" => [
                "nome" => "Leads (/leads/interacoes)",
                "arquivo" => "leads_interacoes.yaml"
            ],
            "leads_momentos" => [
                "nome" => "Leads (/leads/momentos)",
                "arquivo" => "leads_momentos.yaml"
            ],
            "leads_perdas" => [
                "nome" => "Leads (/leads/perdas)",
                "arquivo" => "leads_perdas.yaml"
            ],
            "leads_tarefas" => [
                "nome" => "Leads (/leads/tarefas)",
                "arquivo" => "leads_tarefas.yaml"
            ],
            "leads_visitas" => [
                "nome" => "Leads (/leads/visitas)",
                "arquivo" => "leads_visitas.yaml"
            ],
            "leads_workflow_tempo" => [
                "nome" => "Leads (/leads/workflow/tempo)",
                "arquivo" => "leads_workflow_tempo.yaml"
            ]
        ];
    }

    private function retornarObjetosAssistencia()
    {
        return [
            "assistencias" => [
                "nome" => "Assistências (/assistencias)",
                "arquivo" => "assistencias.yaml"
            ],
            "assistencias_workflow_tempo" => [
                "nome" => "Assistências (/assistencias/workflow/tempo)",
                "arquivo" => "assistencias_workflow_tempo.yaml"
            ],
            "assistencias_itens" => [
                "nome" => "Assistências (/assistencias/itens)",
                "arquivo" => "assistencias_itens.yaml"
            ],
            "assistencias_itens_workflow_tempo" => [
                "nome" => "Assistências (/assistencias/itens/workflow/tempo)",
                "arquivo" => "assistencias_itens_workflow_tempo.yaml"
            ],
            "assistencias_visitas_workflow_tempo" => [
                "nome" => "Assistências (/assistencias/visitas/workflow/tempo)",
                "arquivo" => "assistencias_visitas_workflow_tempo.yaml"
            ]
        ];
    }

    private function retornarObjetosComissoes()
    {
        return [
            "comissoes" => [
                "nome" => "Comissões (/comissoes)",
                "arquivo" => "comissoes.yaml"
            ],
            "comissoes_pagamentos" => [
                "nome" => "Comissões (/comissoes/pagamentos)",
                "arquivo" => "comissoes_pagamentos.yaml"
            ],
            "comissoes_workflow_tempo" => [
                "nome" => "Comissões (/comissoes/workflow/tempo)",
                "arquivo" => "comissoes_workflow_tempo.yaml"
            ]
        ];
    }

    private function retornarObjetosSimulacoes()
    {
        return [
            "simulacoes" => [
                "nome" => "Simulações (/simulacoes)",
                "arquivo" => "simulacoes.yaml"
            ]
        ];
    }

    private function retornarObjetosAgendamentos()
    {
        return [
            "agendamentos_vistorias" => [
                "nome" => "Agendamentos (/agendamentos/vistorias)",
                "arquivo" => "agendamentos_vistorias.yaml"
            ]
        ];
    }

    private function retornarObjetosProcessos()
    {
        return [
            "processos" => [
                "nome" => "Processos (/processos)",
                "arquivo" => "processos.yaml"
            ],
        ];
    }

    private function retornarObjetosPesquisas()
    {
        return [
            "pesquisas" => [
                "nome" => "Pesquisas (/pesquisas)",
                "arquivo" => "pesquisas.yaml"
            ],
            "pesquisas_perguntas" => [
                "nome" => "Pesquisas (/pesquisas/perguntas)",
                "arquivo" => "pesquisas_perguntas.yaml"
            ]
            ,
            "pesquisas_respostas" => [
                "nome" => "Pesquisas (/pesquisas/respostas)",
                "arquivo" => "pesquisas_respostas.yaml"
            ]
        ];
    }

    private function retornarObjetosDemandas()
    {
        return [
            "demandas" => [
                "nome" => "Demandas (/demandas)",
                "arquivo" => "demandas.yaml"
            ],
        ];
    }

}
