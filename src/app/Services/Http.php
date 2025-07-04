<?php

namespace Manzano\CvdwCli\Services;

use Manzano\CvdwCli\Services\Console\CvdwSymfonyStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Http
{
    protected CvdwSymfonyStyle $console;
    public InputInterface $input;
    public OutputInterface $output;
    public $logObjeto;
    protected $evento = 'Requisição';
    public $executarObj;
    public $ratelimitObj;
    public $tempodeexecucao = 0;
    protected EnvironmentManager $environmentManager;
    public const HEADER_CONTENT_TYPE = 'Content-Type: application/json';
    public const ERRO_REQUISICAO = 'Erro ao tentar fazer a requisição!';
    public const PROTOCOLO_HTTP = 'https://';
    public const RESPONSE_TOO_MANY_REQUESTS = 'Too many requests';
    public const ERRO_BLOQUEIO = 'O servidor bloqueia o acesso ao CVDW se forem feitas mais que 20 requisições por minuto.';
    public const TENTAR_NOVAMENTE = 'Você pode tentar novamente dentro de um minuto...';

    public function __construct(
        InputInterface $input,
        OutputInterface $output,
        CvdwSymfonyStyle $console,
        $executarObj,
        $logObjeto,
        RateLimit $rateLimitObj
    ) {
        if (is_object($logObjeto)) {
            $this->logObjeto = $logObjeto;
        }
        $this->console = $console;
        $this->input = $input;
        $this->output = $output;
        $this->executarObj = $executarObj;
        $this->ratelimitObj = $rateLimitObj;
        $this->environmentManager = new EnvironmentManager();

    }

    public function requestCVDW(string $path, $progressBar, $cvdw, array $parametros = [], bool $novaTentativa = true)
    {

        $this->ratelimitObj->validarTempoExecucao();

        $segundos = $this->gerenciarRateLimit($cvdw, $progressBar);
        if ($segundos > 0 && ! $progressBar) {
            $this->aguardarSemProgresso($segundos);
        }


        if ($progressBar) {
            $this->aguardar($cvdw, $progressBar, $segundos);
        }

        $idrequisicao = $this->ratelimitObj->inserirRequisicao($path);

        $cabecalho = [
            'email: '. $this->environmentManager->getCvEmail() .'',
            'token: ' . $this->environmentManager->getCvToken() . '',
            $this::HEADER_CONTENT_TYPE,
        ];

        $url = $this::PROTOCOLO_HTTP . $this->environmentManager->getCvUrl() . '.cvcrm.com.br/api/v1/cvdw'. $path;

        $curl = curl_init();
        $verbose = fopen('php://temp', 'w+');
        curl_setopt_array(
            $curl,
            [
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
            CURLOPT_STDERR => $verbose]
        );
        $response = curl_exec($curl);

        $responseJson = json_decode($response);

        // verifica se o cabecalho da requisicao esta entre 200 e 299
        if (! curl_errno($curl)) {
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $httpCode = intval($httpCode);
            if ($httpCode >= 200 && $httpCode <= 299) {
                return $responseJson;
            }
        }

        if ($this->output->isDebug() || $this->input->getOption('verbose')) {
            rewind($verbose);
            $verboseLog = stream_get_contents($verbose);
            $verboseLog = "Verbose information:\n". htmlspecialchars($verboseLog);
            $this->console->info([
                'URL: ' . $url,
                'Parametros: ' . json_encode($parametros),
                'Resposta: ' . $response,
                'Verbose: ' . $verboseLog,
            ]);
        }

        curl_close($curl);

        if (isset($responseJson->dados)) {
            $dadosRetornoQtd = count($responseJson->dados);
        } else {
            $dadosRetornoQtd = null;
        }
        $this->ratelimitObj->concluirRequisicao($idrequisicao, $dadosRetornoQtd, null);


        // Se nao for setado $resposta->total_de_registros,
        // imprimir uma mensagem de erro e tentar novamente em 3 segundos
        if (! isset($responseJson->pagina) && $novaTentativa) {
            $segundos = 3;
            $this->console->error([
                $this::ERRO_REQUISICAO,
                'Vamos tentar novamente em '.$segundos.' segundos...',
            ]);
            sleep($segundos);
            $this->requestCVDW($path, $progressBar, $cvdw, $parametros, false);
        }

        if (isset($responseJson->Response) && $responseJson->Response == $this::RESPONSE_TOO_MANY_REQUESTS) {
            $this->console->error([
                $this::ERRO_REQUISICAO,
                $this::ERRO_BLOQUEIO,
                $this::TENTAR_NOVAMENTE,
                $response,
            ]);

            return $this->console;
        }

        if (isset($responseJson->total_de_registros) && $responseJson->total_de_registros !== null) {
            return $responseJson;
        } else {

            $this->console->error([
                $this::ERRO_REQUISICAO,
                $response,
            ]);

            return $this->console;
        }
    }

    public function gerenciarRateLimit($cvdw, $progressBar): int
    {

        $diferenca = $this->ratelimitObj->getDiferencaSegundosUltimaRequisicao();
        $requisicoes = $this->ratelimitObj->qtdRequisicoes(60);

        if ($this->output->isDebug() || $this->input->getOption('verbose')) {
            $this->console->info(" LOG: Diferença: $diferenca");
        }

        $this->tempodeexecucao = $this->ratelimitObj->tempoDeExecucao();

        if ($progressBar && $requisicoes > 1) {
            $mensagem = null;
            $mensagem = "\n <fg=blue>Tempo de execução: ".$this->tempodeexecucao." segundos</>";
            $mensagem .= "\n <fg=blue>Você fez " . $requisicoes .  " requisições no último minuto...</>";
            $mensagem .= "\n <fg=gray>Proteção contra o Rate Limit do servidor. (20req/min)</>";
            $mensagem = $cvdw->getMensagem($mensagem);
            $progressBar->setMessage($mensagem);
            $progressBar->display();
            sleep(2);
        }

        $segundos = 60;
        $delay = 3;
        $esperar = 0;
        if ($diferenca < $segundos && $requisicoes > 19) {
            $esperar = $segundos - $diferenca + $delay;
        }

        return $esperar;

    }

    public function aguardar($cvdw, $progressBar, int $segundos = 3): void
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
        $this->console->text("");
        $this->console->text("<fg=gray>Proteção contra o Rate Limit do servidor. (20req/min)</>");
        for ($i = $segundos; $i > 0; $i--) {
            if ($i == 1) {
                $this->console->text('<fg=blue>Aguardando ' . $i . ' segundo para a próxima requisição...</>');
            } else {
                $this->console->text('<fg=blue>Aguardando ' . $i . ' segundos para a próxima requisição...</>');
            }
            sleep(1);
        }
        $this->console->text(['','']);
    }

    public function pingAmbienteCVDW(string $enderecoCv): array
    {

        $cabecalho = [
            $this::HEADER_CONTENT_TYPE,
        ];
        $url = $this::PROTOCOLO_HTTP . $enderecoCv . '.cvcrm.com.br/api/app/ambiente';
        $curl = curl_init();
        curl_setopt_array(
            $curl,
            [
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
                CURLOPT_SSL_VERIFYHOST => false,
            ]
        );
        $response = curl_exec($curl);
        curl_close($curl);

        $response = json_decode($response, true);

        if (isset($response['Response']) && $response['Response'] == $this::RESPONSE_TOO_MANY_REQUESTS) {
            $this->console->error([
                $this::ERRO_REQUISICAO,
                $this::ERRO_BLOQUEIO,
                $this::TENTAR_NOVAMENTE,
                $response,
            ]);

            return [];
        }

        if (isset($response['nome'])) {
            return $response;
        } else {
            $this->console->error([
                $this::ERRO_REQUISICAO,
                $response,
            ]);

            return [];
        }
    }

    public function pingAmbienteAutenticadoCVDW(string $ambienteCv, string $path, string $email, string $token)
    {

        $cabecalho = [
            'email: ' . $email . '',
            'token: ' . $token . '',
            $this::HEADER_CONTENT_TYPE,
        ];
        $parametros = [
            "pagina" => "1",
        ];
        $url = $this::PROTOCOLO_HTTP . $ambienteCv . '.cvcrm.com.br/api/v1/cvdw' . $path;

        $curl = curl_init();
        curl_setopt_array(
            $curl,
            [
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
                CURLOPT_SSL_VERIFYHOST => false,
            ]
        );

        $response = curl_exec($curl);
        curl_close($curl);

        $responseJson = json_decode($response, true);

        if (isset($responseJson->Response) && $responseJson->Response == $this::RESPONSE_TOO_MANY_REQUESTS) {
            $this->console->error([
                $this::ERRO_REQUISICAO,
                $this::ERRO_BLOQUEIO,
                $this,
                $response,
            ]);

            return false;
        }

        if (isset($responseJson['registros']) && $responseJson['registros'] !== null) {
            return $responseJson;
        } else {

            $this->console->error([
                'Erro ao tentar fazer a requisição.',
                $response,
            ]);

            return false;
        }
    }

    public function buscarVersaoRepositorio()
    {
        $repo = 'manzano/cvdw-cli'; // Altere para o usuário/repositorio desejado
        $url = $this::PROTOCOLO_HTTP."api.github.com/repos/$repo/releases/latest";
        $curl = curl_init();
        $cabecalho = ['User-Agent: Github / CVDW-CLI', 'Accept: application/json'];
        $verbose = fopen('php://temp', 'w+');
        curl_setopt_array(
            $curl,
            [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_TIMEOUT => 20,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HTTPHEADER => $cabecalho,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_VERBOSE => true,
                CURLOPT_STDERR => $verbose,
            ]
        );
        $response = curl_exec($curl);
        curl_close($curl);

        if ($response) {
            $data = json_decode($response, true);
            if (! isset($data['tag_name'])) {
                return "OFF";
            }

            return $data['tag_name'];
        } else {
            return "OFF";
        }

    }

}
