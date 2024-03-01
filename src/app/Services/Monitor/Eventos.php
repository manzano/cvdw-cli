<?php

namespace Manzano\CvdwCli\Services\Monitor;

class Eventos
{
    protected $token = "222ea4d3c6c73db6002c8895c94c302e";
    protected $gestor;
    protected $usuario = false;
    protected $evento;
    protected $dados;

    public function __construct()
    {
        // Token da conta do Mixpanel do CVDW-CLI
        $this->gestor = \Mixpanel::getInstance($this->token, array("debug" => true));
        $this->registraAmbiente();
    }

    public function registraAmbiente()
    {
        if(isset($_ENV['CV_URL']) && $_ENV['CV_URL'] != ''){
            $this->usuario = $this->gestor->people->set($_ENV['CV_URL'], array(
                '$first_name'       => $_ENV['CV_URL'],
                '$last_name'        => "",
                '$email'            => $_ENV['CV_EMAIL'],
                "ultima_execucao"    => date('Y-m-d H:i:s')
            ));
        }
        //echo "Ambiente registrado no Mixpanel\n";
    }
    public function registrarEvento(string $evento, string $acao, $subacao = null, $dados = null)
    {
        if($this->usuario){
            $this->evento = $evento;
            $this->dados = array(
                "acao" => $acao,
                "subacao" => $subacao,
                "dados" => $dados
            );
            $this->gestor->track($this->evento, $this->dados);
        }
        //echo "Evento registrado no Mixpanel\n";
    }
}
