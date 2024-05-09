<?php

namespace Manzano\CvdwCli\Services;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Manzano\CvdwCli\Services\Console\CvdwSymfonyStyle;
use Manzano\CvdwCli\Services\Monitor\Eventos;

class Http
{

    protected CvdwSymfonyStyle $io;
    public InputInterface $input;
    public OutputInterface $output;
    public $logObjeto;
    protected $eventosObj;
    protected $evento = 'Requisição';
    public $executarObj;

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

    public function requestCVDW(string $path, $progressBar, $cvdw, array $parametros = [], bool $novaTentativa = true) : object
    {

        $this->executarObj->salvarExecucao();
        $segundos = $this->gerenciarRateLimit();

        if ($segundos > 0 && !$progressBar) {
            $this->aguardarSemProgresso($segundos);
        }

        if($progressBar) {
            $this->aguardar($cvdw, $progressBar, $segundos);
        }

        $this->eventosObj->registrarEvento($this->evento, $path);
        
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
            $this->requestCVDW($path, $progressBar, $cvdw, $parametros, false);
        }
         
        if(isset($responseJson->Response) && $responseJson->Response == "Too many requests"){
            $this->io->error([
                'Erro ao tentar fazer a requisição!',
                'O servidor bloqueia o acesso ao CVDW se forem feitas mais que 20 requisições por minuto.',
                'Você pode tentar novamente dentro de um minuto...',
                'Retorno: '. $response
            ]);
            return $this->io;
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

    protected function gerenciarRateLimit(): int {

        $espacodetempo = 60;
        $agora = time();
        $execucoes = $this->executarObj->retornarExecucoes();
        if(!empty($execucoes)) {
            foreach($execucoes as $ind => $time) {
                $tempo_atras = $agora - $execucoes[$ind];
                if($tempo_atras > $espacodetempo) {
                    $this->executarObj->removerExecucao($ind);
                }
            }
        } else {
            return 0;
        }

        $delay = 3;
        $esperar = 0;
        reset($execucoes);
        $primeiro = current($execucoes);
        end($execucoes);
        $ultimo = current($execucoes);
        $diferenca = $ultimo - $primeiro;
        if ($this->output->isDebug()) {
            $this->io->info(" LOG: primeiro: $primeiro, ultimo: $ultimo, diferença: $diferenca");
        }

        if(count($execucoes) >= 20) {
            $esperar = ($espacodetempo - $diferenca) + $delay;
        }

        if ($diferenca > $espacodetempo) {
            $esperar = ($espacodetempo - $diferenca) + $delay;
        }

        return $esperar;

    }

    protected function aguardar($cvdw, $progressBar, int $segundos = 3): void
    {

        $mensagem = null;
        for ($i = $segundos; $i > 0; $i--) {
            if ($i == 1) {
                $mensagem = ' <fg=blue>Aguardando ' . $i . ' segundo para a próxima requisição...</>';
            } else {
                $mensagem = ' <fg=blue>Aguardando ' . $i . ' segundos para a próxima requisição...</>';
            }
            $mensagem .= "\n <fg=gray>Proteção contra o Rate Limit do servidor. (20req/min)</>";
            $mensagem = $cvdw->getMensagem($mensagem);
            $progressBar->setMessage($mensagem);
            $progressBar->display();
            sleep(1);
        }
        $progressBar->setMessage($cvdw->getMensagem($mensagem));
    }

    protected function aguardarSemProgresso(int $segundos): void
    {
        $this->io->text("");
        $this->io->text("<fg=gray>Proteção contra o Rate Limit do servidor. (20req/min)</>");
        for ($i = $segundos; $i > 0; $i--) {
            if ($i == 1) {
                $this->io->text('<fg=blue>Aguardando ' . $i . ' segundo para a próxima requisição...</>');
            } else {
                $this->io->text('<fg=blue>Aguardando ' . $i . ' segundos para a próxima requisição...</>');
            }
            sleep(1);
        }
        $this->io->text(['','']);
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
