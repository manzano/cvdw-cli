<?php

namespace Manzano\CvdwCli\Services;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Manzano\CvdwCli\Services\Console\CvdwSymfonyStyle;
use Manzano\CvdwCli\Services\Monitor\Eventos;

class OpenAi
{

    protected CvdwSymfonyStyle $io;
    public InputInterface $input;
    public OutputInterface $output;
    public $logObjeto;
    protected $eventosObj;
    protected $evento = 'Requisição';
    public $executarObj;
    const HEADER_CONTENT_TYPE = 'Content-Type: application/json';
    const ERRO_REQUISICAO = 'Erro ao tentar fazer a requisição!';
    const PROTOCOLO_HTTP = 'https://';

    public function __construct(InputInterface $input, OutputInterface $output,
                                    CvdwSymfonyStyle $io, $executarObj, $logObjeto = false)
    {
        if(is_object($logObjeto)) {
            $this->logObjeto = $logObjeto;
        }
        $this->io = $io;
        $this->input = $input;
        $this->output = $output;
        $this->executarObj = $executarObj;

        $this->eventosObj = new Eventos();

    }
    
    public function validarToken(string $token, string $organizacao, string $projeto)
    {

        $cabecalho = array(
            "Authorization: Bearer ".trim($token),
            "OpenAI-Organization: $organizacao",
            "OpenAI-Project: $projeto"
        );

        $url = "https://api.openai.com/v1/models";

        $curl = curl_init();
        curl_setopt_array(
            $curl,
            array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_TIMEOUT => 60,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HTTPHEADER => $cabecalho,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false
            )
        );

        $response = curl_exec($curl);
        curl_close($curl);

        $responseJson = json_decode($response, true);

        if (isset($responseJson['object']) && $responseJson['object'] !== null) {
            return $responseJson;
        } else {
            $this->io->error([
                'Erro ao tentar fazer a requisição.',
                $response
            ]);
            return false;
        }
    }

}
