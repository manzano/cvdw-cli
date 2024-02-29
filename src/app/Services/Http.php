<?php

namespace Manzano\CvdwCli\Services;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Manzano\CvdwCli\Services\Objeto;
use Manzano\CvdwCli\Services\Console\CvdwSymfonyStyle;

class Http
{

    protected CvdwSymfonyStyle $io;
    public InputInterface $input;
    public OutputInterface $output;
    public $logObjeto;

    public function __construct(InputInterface $input, OutputInterface $output, $io = null, $logObjeto = false)
    {
        if(is_object($logObjeto)) {
            $this->logObjeto = $logObjeto;
        }
        $this->io = $io;
        $this->input = $input;
        $this->output = $output;
    }

    public function requestCVDW(string $path, array $parametros = [], bool $novaTentativa = true) : object
    {

        $cabecalho = array(
            'email: '. $_ENV['CV_EMAIL'] .'',
            'token: ' . $_ENV['CV_TOKEN'] . '',
            'Content-Type: application/json'
        );
        // Converter o array de parametros em string
        $parametrosUrl = http_build_query($parametros);

        $url = 'https://' . $_ENV['CV_URL'] . '.cvcrm.com.br/api/v1/cvdw'. $path;
        //$url = $url . '?' . $parametrosUrl;
        
        $curl = curl_init();
        $verbose = fopen('php://temp', 'w+');
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 40,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_POSTFIELDS => json_encode($parametros),
            CURLOPT_HTTPHEADER => $cabecalho,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_VERBOSE => true,
            CURLOPT_STDERR => $verbose)
        );
        $response = curl_exec($curl);

        if ($this->output->isDebug()) {
            rewind($verbose);
            $verboseLog = stream_get_contents($verbose);
            $verboseLog = "Verbose information:\n". htmlspecialchars($verboseLog);
            $this->io->info([
                'URL: ' . $url,
                'Parametros: ' . json_encode($parametros),
                'Resposta: ' . $response,
                'Verbose: ' . $verboseLog
            ]);
        }

        curl_close($curl);
        
        $responseJson = json_decode($response);

        // Se nao for setado $resposta->total_de_registros,
        // imprimir uma mensagem de erro e tentar novamente em 3 segundos
        if(!isset($responseJson->total_de_registros) && $novaTentativa){
            $segundos = 3;
            $this->io->error([
                'Erro ao tentar fazer a requisição!',
                'Vamos tentar novamente em '.$segundos.' segundos...',
            ]);
            sleep($segundos);
            $this->requestCVDW($path, $parametros, false);
        }
         
        if(isset($responseJson->Response) && $responseJson->Response == "Too many requests"){
            $this->io->error([
                'Erro ao tentar fazer a requisição!',
                'O servidor bloqueia o acesso ao CVDW se forem feitas mais que 20 requisições por minuto.',
                'Você pode tentar novamente dentro de um minuto...',
                'Retorno: '. $response
            ]);
            return $this->io;
            exit;
        }

        if (isset($responseJson->total_de_registros) && $responseJson->total_de_registros !== null) {
            return $responseJson;
        } else {
            
            $this->io->error([
                'Erro ao tentar fazer a requisição!',
                'Retorno: '. $response
            ]);
            return $this->io;
        }
    }

    public function pingAmbienteCVDW(string $endereco_cv) : array
    {

        $cabecalho = array(
            'Content-Type: application/json'
        );
        $url = 'https://' . $endereco_cv . '.cvcrm.com.br/api/app/ambiente';
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
        
        $response = json_decode($response, true);

        if (isset($response['Response']) && $response['Response'] == "Too many requests") {
            $this->io->error([
                'Erro ao tentar fazer a requisição!',
                'O servidor bloqueia o acesso ao CVDW se forem feitas mais que 20 requisições por minuto.',
                'Você pode tentar novamente dentro de um minuto...',
                'Retorno: ' . $response
            ]);
            exit;
            return false;
        }

        if ( isset($response['nome']) ) {
            return $response;
        } else {
            $this->io->error([
                'Erro ao tentar fazer a requisição!',
                'Retorno: ' . $response
            ]);
            return false;
        }
    }

    public function pingAmbienteAutenticadoCVDW(string $ambiente_cv, string $path, string $email, string $token)
    {

        $cabecalho = array(
            'email: ' . $email . '',
            'token: ' . $token . '',
            'Content-Type: application/json'
        );
        $parametros = array(
            "pagina" => "1"
        );
        $url = 'https://' . $ambiente_cv . '.cvcrm.com.br/api/v1/cvdw' . $path;

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
                CURLOPT_POSTFIELDS => json_encode($parametros),
                CURLOPT_HTTPHEADER => $cabecalho,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false
            )
        );

        $response = curl_exec($curl);
        curl_close($curl);

        $responseJson = json_decode($response, true);

        if (isset($responseJson->Response) && $responseJson->Response == "Too many requests") {
            $this->io->error([
                'Erro ao tentar fazer a requisição!',
                'O servidor bloqueia o acesso ao CVDW se forem feitas mais que 20 requisições por minuto.',
                'Você pode tentar novamente dentro de um minuto...',
                'Retorno: ' . $response
            ]);
            return false;
        }

        if (isset($responseJson['registros']) && $responseJson['registros'] !== null) {
            return $responseJson;
        } else {

            $this->io->error([
                'Erro ao tentar fazer a requisição.',
                'Retorno: ' . $response
            ]);
            return false;
        }
    }
}
