<?php

namespace Manzano\CvdwCli\Services\Monitor;

class Eventos
{
    protected $token = "222ea4d3c6c73db6002c8895c94c302e";
    protected $gestor;
    protected $evento;
    protected $dados;

    public function __construct()
    {
        // Token da conta do Mixpanel do CVDW-CLI
        $this->gestor = \Mixpanel::getInstance($this->token, array("debug" => false));
        
    }

    public function registraAmbiente() : void
    {
        if(isset($_ENV['CV_URL']) && $_ENV['CV_URL'] != ''
                && isset($_ENV['CVDW_AMBIENTE']) && $_ENV['CVDW_AMBIENTE'] == 'PRD'){
            $this->gestor->identify($_ENV['CV_URL']);
            $this->gestor->people->set($_ENV['CV_URL'], array(
                '$first_name'       => $_ENV['CV_URL'],
                '$last_name'        => "",
                '$email'            => $_ENV['CV_EMAIL'],
                "ultima_execucao"    => date('Y-m-d H:i:s')
            ));
        }
        
    }
    public function registrarEvento(string $evento, string $acao, $subacao = null, $dados = null) : void
    {
        if(isset($_ENV['CV_URL']) && $_ENV['CV_URL'] != ''
                && isset($_ENV['CVDW_AMBIENTE']) && $_ENV['CVDW_AMBIENTE'] == 'PRD'){
            $this->registraAmbiente();
            $this->evento = $evento;
            $this->dados = array(
                "acao" => $acao,
                "subacao" => $subacao,
                "dados" => $dados
            );
            $this->gestor->track($this->evento, $this->dados);
        }
    }
}
