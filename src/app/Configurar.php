<?php

namespace Manzano\CvdwCli;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Manzano\CvdwCli\Services\Console\CvdwSymfonyStyle;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputOption;

use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;

use Manzano\CvdwCli\Services\DatabaseSetup;
use Manzano\CvdwCli\Services\Http;
use Manzano\CvdwCli\Services\Objeto;
use Manzano\CvdwCli\Services\Ambientes;
use Manzano\CvdwCli\Services\Cvdw;
use Manzano\CvdwCli\Inc\CvdwException;

use Manzano\CvdwCli\Services\Monitor\Eventos;

use Symfony\Component\Process\Process;

#[AsCommand(
    name: 'configurar',
    description: 'Configure o CVDW-CLI',
    hidden: false,
    aliases: ['Configurar', 'configurar', 'configuracoes',
        'configurações', 'configuracao', 'configuração',
        'config', 'cfg']
)]
class Configurar extends Command
{
    protected static $defaultName = 'configurar';
    protected InputInterface $input;
    protected OutputInterface $output;
    protected CvdwSymfonyStyle $io;
    /**
     * @var string[]
     */
    public array $variaveisAmbiente = [];
    public bool $voltarProMenu = false;
    public \Doctrine\DBAL\Connection $conn;
    protected $eventosObj;
    protected $ambientesObj;
    protected $databaseObj;
    protected $cvdwObj;
    protected $env = null;
    protected $evento = 'Configurar';
    const VAMOS_LA = 'Vamos Lá!';
    const QUER_TENTAR_NOVAMENTE = 'Quer tentar novamente?';

    protected function configure()
    {
        $this->setName('configurar')
            ->setDescription('Configurações do aplicativo')
        ->addOption(
            'set-env',
            'env',
            InputOption::VALUE_OPTIONAL, // Modo: VALUE_REQUIRED, VALUE_OPTIONAL, VALUE_NONE
            'Diz qual ENV usar. Exemplo: dev, homologacao, producao.',
        )->addOption(
            'set-env',
            'env',
            InputOption::VALUE_OPTIONAL, // Modo: VALUE_REQUIRED, VALUE_OPTIONAL, VALUE_NONE
            'Diz qual ENV usar. Exemplo: dev, homologacao, producao.',
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $this->limparTela();
        $this->eventosObj = new Eventos();
        
        if ($input->getOption('set-env')) {
            $this->env = $input->getOption('set-env');
        }
        $this->ambientesObj = new Ambientes($this->env, $this);
        $this->ambientesObj->retornarEnvs();

        $this->cvdwObj = new Cvdw($input, $output, $this);

        $io = new CvdwSymfonyStyle($input, $output);

        $this->input = $input;
        $this->output = $output;

        $io->title('Configurando o CVDW-CLI');

        $versaoCVDW = $this->ambientesObj->retornarVersao();
        $io->text('Versão: ' . $versaoCVDW);

        $ambienteAtivo = $this->ambientesObj->ambienteAtivo();
        $io->text('Ambiente ativo: ' . $ambienteAtivo);

        // Verificar a versão do repositorio
        $cvdwObj = new Cvdw($input, $output, $this);
        $cvdwObj->alertarNovaVersao($versaoCVDW, $io);
        
        $this->eventosObj->registrarEvento($this->evento, 'Início');

        $this->variaveisAmbiente['configurar'] = $io->choice('O que deseja configurar agora?', [
            'Acesso ao CVDW API',
            'Acesso ao meu Banco de dados',
            'Criar as tabelas em meu banco de dados',
            'Configurar anonimização de dados sensíveis',
            'Verificar/Atualizar meu ambiente',
            'Limpar as tabelas do CVDW (Truncate)',
            'Apagar as tabelas do CVDW (Drop)',
            'Configurar integração OpenAI',
            'Cadastrar novo ambiente a partir do padrão',
            'Listar e remover seus ambientes',
            'Atualizar o ambiente do CVDW-CLI',
            'Sair (CTRL+C)'
        ]);

        if ($this->variaveisAmbiente['configurar'] === 'Sair (CTRL+C)') {
            $io->text(['Até mais!', '']);
            return Command::SUCCESS;
        }
        $io->text(['Você escolheu: ' . $this->variaveisAmbiente['configurar'], '']);
        $this->eventosObj->registrarEvento($this->evento, $this->variaveisAmbiente['configurar']);

        switch ($this->variaveisAmbiente['configurar']) {
            case 'Acesso ao CVDW API':
                $this->configurarCV();
                break;
            case 'Acesso ao meu Banco de dados':
                $this->configurarBanco();
                break;
            case 'Criar as tabelas em meu banco de dados':
                $this->criarTabelas();
                break;
            case 'Configurar anonimização de dados sensíveis':
                $this->configurarAnonimizacao();
                break;
            case 'Verificar/Atualizar meu ambiente':
                $cvdwObj->conectar();
                $this->verificarInstalacao();
                break;
            case 'Limpar as tabelas do CVDW (Truncate)':
                $this->limparTabelas();
                break;
            case 'Apagar as tabelas do CVDW (Drop)':
                $this->apagarTabelas();
                break;
            case 'Configurar integração OpenAI';
                $this->configurarOpenAI();
                break;
            case 'Cadastrar novo ambiente a partir do padrão':
                $this->cadastarAmbiente();
                break;
            case 'Listar e remover seus ambientes':
                $this->listarAmbientesRemover();
                break;
            case 'Atualizar o ambiente do CVDW-CLI':
                $this->atualizarCVDW();
                break;
                
            default:
                return Command::INVALID;
        }

        return Command::SUCCESS;
    }


    private function configurarCV(): int
    {

        $io = new CvdwSymfonyStyle($this->input, $this->output);
        $io->text([
            $this::VAMOS_LA,
            'Primeiro vamos configurar as variáveis do CV...'
        ]);
        $io->note([
            'Vamos começar pelo endereço de acesso ao CV,',
            'Lembre que não deve ser o endereço completo, somente o subdominio',
            'Ex.: https://SUA_INCORPORADORA.cvcrm.com.br então vamos usar "sua_incorporadora"',
        ]);

        $io->ask('Me diz o subdominio do ambiente seu CV:', $_ENV['CV_URL'], function (string $endereco_cv): string {

            $io = new CvdwSymfonyStyle($this->input, $this->output);

            $http = new Http($this->input, $this->output, $io, $this);
            $response = $http->pingAmbienteCVDW($endereco_cv);

            if ($response['nome'] !== null) {
                $this->variaveisAmbiente = $response;
                $this->variaveisAmbiente['endereco_cv'] = $endereco_cv;
                putenv('CV_URL=' . $endereco_cv);
            } else {
                $io->error('Não consegui pegar as informações do ambiente, vamos tentar de novo?');
            }

            return $endereco_cv;
        });

        $io->text([
            'Legal, consegui encontrar o ambiente!',
            'Agora já sei que vamos trabalhar com o ambiente "' . $this->variaveisAmbiente['nome'] . '"'
        ]);

        $io->ask(
            'E-mail de acesso:',
            $_ENV['CV_EMAIL'],
            function (string $email_cv): string {
                // validar se o e-mail é válido
                if (!filter_var($email_cv, FILTER_VALIDATE_EMAIL)) {
                    throw new CvdwException('E-mail inválido, vamos tentar de novo?');
                }
                $this->variaveisAmbiente['email'] = $email_cv;
                putenv('CV_EMAIL=' . $email_cv);
                return $email_cv;
            }
        );

        $io->ask(
            'Token de acesso:',
            $_ENV['CV_TOKEN'],
            function (string $token_cv): string {
                // fazer uma re
                $this->variaveisAmbiente['token'] = $token_cv;
                putenv('CV_TOKEN=' . $token_cv);
                return $token_cv;
            }
        );

        $io->text([
            'Ok! Agora vou tentar fazer uma requisição para tentar validar os dados...'
        ]);

        $http = new Http($this->input, $this->output, $io, $this);
        $response = $http->pingAmbienteAutenticadoCVDW(
            $this->variaveisAmbiente['endereco_cv'],
            "/imobiliarias",
            $this->variaveisAmbiente['email'],
            $this->variaveisAmbiente['token']
        );

        if (isset($response['registros'])) {

            $io->success([
                'Validado com sucesso!'
            ]);

            $io->text([
                'Deixar eu salvar essas informações...'
            ]);

            $newEnv = [
                'CV_URL' => $this->variaveisAmbiente['endereco_cv'],
                'CV_TOKEN' => $this->variaveisAmbiente['token'],
                'CV_EMAIL' => $this->variaveisAmbiente['email']
            ];
        
            $this->ambientesObj->salvarEnv($newEnv);

            $io->text('Salvo!');

            $this->ambientesObj->retornarEnvs();
            $this->voltarProMenu = true;
            $this->voltarProMenu();

            return 0;
        } else {
            $io->error('Não consegui acessar o ambiente.');
            if ($io->confirm($this::QUER_TENTAR_NOVAMENTE, true)) {
                return $this->configurarCV();
            }
            return 0;
        }
    }

    private function configurarBanco(): int
    {
        $io = new CvdwSymfonyStyle($this->input, $this->output);

        $io->text([
            '',
            'Agora vamos pegar as informações de conexão do banco de dados...'
        ]);

        $this->variaveisAmbiente['banco'] = $io->choice('Que banco de dados você quer conectar?',
                                                        ['pdo_mysql', 'pdo_pgsql', 'pdo_sqlsrv'],
                                                        $_ENV['DB_CONNECTION']);
        $io->text('Você escolheu: ' . $this->variaveisAmbiente['banco']);

        $io->ask(
            'Qual o endereço do banco de dados?',
            $_ENV['DB_HOST'],
            function (string $db_host): string {
                $this->variaveisAmbiente['db_host'] = $db_host;
                return $db_host;
            }
        );

        $io->ask(
            'Qual a porta?',
            $_ENV['DB_PORT'],
            function (string $db_port): string {
                $this->variaveisAmbiente['db_port'] = $db_port;
                return $db_port;
            }
        );

        $io->ask(
            'Qual o nome da database?',
            $_ENV['DB_DATABASE'],
            function (string $db_database): string {
                $this->variaveisAmbiente['db_database'] = $db_database;
                return $db_database;
            }
        );

        if($this->variaveisAmbiente['banco'] == 'pdo_pgsql'){
            // se não tiver $_ENV['DB_SCHEMA'], $_ENV['DB_SCHEMA'] = 'public';
            if(!isset($_ENV['DB_SCHEMA']) || $_ENV['DB_SCHEMA'] == ''){
                $_ENV['DB_SCHEMA'] = 'public';
            }
            $io->ask(
                'Qual a schema?',
                $_ENV['DB_SCHEMA'],
                function (string $db_schema): string {
                    $this->variaveisAmbiente['db_schema'] = $db_schema;
                    return $db_schema;
                }
            );
        }

        $io->ask(
            'Qual o usuário?',
            $_ENV['DB_USERNAME'],
            function (string $db_username): string {
                $this->variaveisAmbiente['db_username'] = $db_username;
                return $db_username;
            }
        );

        $io->ask(
            'Qual a senha?',
            $_ENV['DB_PASSWORD'],
            function (string $db_password): string {
                $this->variaveisAmbiente['db_password'] = $db_password;
                return $db_password;
            }
        );

        $newEnv = [
            'DB_CONNECTION' => $this->variaveisAmbiente['banco'],
            'DB_HOST' => $this->variaveisAmbiente['db_host'],
            'DB_PORT' => $this->variaveisAmbiente['db_port'],
            'DB_DATABASE' => $this->variaveisAmbiente['db_database'],
            'DB_USERNAME' => $this->variaveisAmbiente['db_username'],
            'DB_PASSWORD' => $this->variaveisAmbiente['db_password'],
            'DB_SCHEMA' => $this->variaveisAmbiente['db_schema']
        ];

        $io->text([
            'Ok! testar a conexão...'
        ]);

        // Criar uma conexao com o Doctrine DBAL
        $config = new Configuration();
        $connectionParams = array(
            'dbname' => $this->variaveisAmbiente['db_database'],
            'user' => $this->variaveisAmbiente['db_username'],
            'password' => $this->variaveisAmbiente['db_password'],
            'host' => $this->variaveisAmbiente['db_host'],
            'port' => $this->variaveisAmbiente['db_port'],
            'driver' => $this->variaveisAmbiente['banco'],
        );

        if ($this->variaveisAmbiente['banco'] == 'pdo_pgsql') {
            $connectionParams['driverOptions'] = array(
                \PDO::ATTR_PERSISTENT => true,
            );
            $connectionParams['options'] = array(
                'search_path' => $this->variaveisAmbiente['db_schema'],
            );
        }

        try {
            $conn = DriverManager::getConnection($connectionParams, $config);
            $conn->connect();
            if ($conn->isConnected()) {
                
                $io->success('Conexão bem-sucedida!');
            } else {
                $io->error('Não foi possível conectar ao banco de dados.');
                if ($io->confirm($this::QUER_TENTAR_NOVAMENTE, true)) {
                    return $this->configurarBanco();
                }
            }
        } catch (\Exception $e) {
            $io->error('Não foi possível conectar ao banco de dados.');
            $io->error('Encontrei esse erro: ' . $e->getMessage());
            if ($io->confirm($this::QUER_TENTAR_NOVAMENTE, true)) {
                return $this->configurarBanco();
            }
        }

        $io->text([
            '',
            'Deixa eu salvar as informações...'
        ]);
        $this->ambientesObj->salvarEnv($newEnv);

        $this->voltarProMenu = true;
        $this->voltarProMenu();

        return 1;

    }

    private function criarTabelas(): bool
    {

        $io = new CvdwSymfonyStyle($this->input, $this->output);

        $io->text('Serão criadas as tabelas abaixo:');

        $database = new DatabaseSetup($this->input, $this->output, $this);
        $database->listarTabelas();

        if ($io->confirm('Podemos continuar?', true)) {
            $database->executarCriarTabelas();
            $io->text('Criação finalizada!');
            $io->text('');
        } else {
            $io->text('Ok, vamos parar por aqui...');
            $io->text('');
        }

        $this->voltarProMenu = true;
        $this->voltarProMenu();

        return true;

    }

    private function configurarAnonimizacao(): bool
    {
        $io = new CvdwSymfonyStyle($this->input, $this->output);

        $io->text('A anonimização dos dados sensíveis de pessoas é uma prática recomendada para proteger a privacidade dos usuários');
        $io->text('O CVDW-CLI pode ajudar você a esconder esses dados.');
        $io->text('Exemplo: Nome, E-mail, Telefone, CPF, RG, etc.');

        if(!isset($_ENV['ANONIMIZAR'])){
            $_ENV['ANONIMIZAR'] = false;
            $_ENV['ANONIMIZAR_TIPO'] = 'Asteriscos';
        } else {
            if($_ENV['ANONIMIZAR'] == 'true'){
                $_ENV['ANONIMIZAR'] = true;
            } else {
                $_ENV['ANONIMIZAR'] = false;
            }
        }
        $_ENV['ANONIMIZAR'] = $io->confirm('Você deseja anonimizar os dados sensíveis?', $_ENV['ANONIMIZAR']);

        if($_ENV['ANONIMIZAR']) {

            $io->text('Ok, vamos configurar a anonimização...');
            $io->text('Agora você pode escolher como deseja anonimizar os dados sensíveis.');
            $nomeEx = 'Gabriel Manzano';
            $io->text(' - Com asteriscos:');
            $io->text("   Ex: $nomeEx -> ".substituirPorAsteriscos($nomeEx));
            $io->text(' - Com um hash unico:');
            $io->text("   Ex: $nomeEx -> ".substituirPorHash($nomeEx, 20));

            $_ENV['ANONIMIZAR_TIPO'] = $io->choice('Como você deseja anonimizar?',
            ['Asteriscos', 'Hash'],
            $_ENV['ANONIMIZAR_TIPO']);
            $io->text('Você escolheu: ' . $_ENV['ANONIMIZAR_TIPO']);
            $_ENV['ANONIMIZAR'] = 'true';

        } else {
            $_ENV['ANONIMIZAR'] = 'false';
        }

        $this->ambientesObj->salvarEnv($_ENV);
        $io->text('Pronto, configuração salva...');
        $this->voltarProMenu = true;
        $this->voltarProMenu();

        return true;
    }

    private function verificarInstalacao(): bool
    {
        
        $io = new CvdwSymfonyStyle($this->input, $this->output);
        $http = new Http($this->input, $this->output, $io, $this);
        $diferencasBanco = array();

        $io->text([$this::VAMOS_LA, 'Validando a instalação...', '' ]);
        $validarObjetos = true;

        if ($this->cvdwObj->validarAmbiente($http)) {
            $io->text('<bg=green>[OK]</> Conexão com o CVDW está funcionando!');
        } else {
            $io->text('<fg=white;bg=red>[PROBLEMA]</> Não consegui acessar o ambiente do CVDW!');
            $validarObjetos = false;
        }

        $this->conn = conectarDB($this->input, $this->output, false);
        if($this->conn->isConnected()) {
            $io->text('<bg=green>[OK]</> Conexão com o banco de dados está funcionando!');
            $databaseObj = new DatabaseSetup($this->input, $this->output, $this);
        } else {
            $io->text('<fg=white;bg=red>[PROBLEMA]</> Não consegui acessar o banco de dados!');
            $validarObjetos = false;
        }
        
        if(!$validarObjetos) {
            $io->note('Como o Banco ou Api não está acessível, não posso validar os objetos.');
        } else {

            $io->text(['', 'Agora vamos validar os objetos...', '']);
            $bancoProblemas = false;
            $objetoObj = new Objeto($this->input, $this->output);
            $objetos = $objetoObj->retornarObjetos();
            foreach($objetos as $key => $dados) {
                $existe = $databaseObj->verificarSeTabelaExiste($key);
                if ($existe) {
                    $objeto = $objetoObj->retornarObjeto($key);
                    $estrutura = $databaseObj->retornarEstruturaTabela($key);
                    $logDiferencas = $databaseObj->compararTabelaObjeto($estrutura, $objeto);
                    $diferencas = $logDiferencas[1];
                    $logs = $logDiferencas[0];
                    $subtabelas = $logDiferencas[2];
                    if(count($diferencas) > 0) {
                        $diferencasBanco[$key] = $diferencas;
                        $bancoProblemas = true;
                        $io->text('<fg=white;bg=red>[PROBLEMA]</> Encontrei algo na tabela ' . $key . '!');
                        foreach ($logs as $log) {
                            $io->text('- '.$log);
                        }
                    } else {
                        $io->text('<bg=green>[OK]</> A tabela ' . $key . ' está atualizada!');
                    }
                } else {
                    $bancoProblemas = true;
                    $io->text('<fg=white;bg=red>[PROBLEMA]</> A tabela ' . $key . ' não foi encontrada!');
                }

                if(isset($subtabelas) && is_array($subtabelas) && count($subtabelas) > 0){
                    foreach($subtabelas as $subespecificacao){
                        $existe = $databaseObj->verificarSeTabelaExiste($subespecificacao['nome']);
                        if($existe){
                            $subestrutura = $databaseObj->retornarEstruturaTabela($subespecificacao['nome']);
                            $subobjeto = array();
                            $subobjeto['response']['dados'] = $subespecificacao['objeto']['dados'];
                            
                            $logDiferencas = $databaseObj->compararTabelaObjeto($subestrutura, $subobjeto);
                            $diferencas = $logDiferencas[1];
                            $logs = $logDiferencas[0];
                            if (count($diferencas) > 0) {
                                $diferencasBanco[$subespecificacao['nome']] = $diferencas;
                                $bancoProblemas = true;
                                $io->text('<fg=white;bg=red>[PROBLEMA]</>
                                            -> Encontrei algo na sub-tabela ' . $estrutura['nome'] . '!');
                                foreach ($logs as $log) {
                                    $io->text('-- ' . $log);
                                }
                            } else {
                                $io->text('<bg=green>[OK]</> -> A sub-tabela ' . $estrutura['nome'] . ' está atualizada!');
                            }
                        } else {
                            $bancoProblemas = true;
                            $io->text('<fg=white;bg=red>[PROBLEMA]</> -> A sub-tabela ' . $estrutura['nome'] . ' não foi encontrada!');
                        
                        }
                    }
                }

            }
        }

        if($bancoProblemas) {
            $io->text([
                '',
                'Encontrei problemas no banco de dados, vamos tentar corrigir?',
                ''
            ]);
            if ($io->confirm('Quer tentar corrigir?', true)) {
                $this->databaseObj->executarCorrecoes($diferencasBanco);
            } else {
                $io->text([
                    '',
                    'Ok, vamos parar por aqui...',
                    ''
                ]);
            }
        } else {
            $io->text([
                '',
                'Parece que esta tudo ok!',
                ''
            ]);
        }

        $this->voltarProMenu = true;
        $this->voltarProMenu();
        return true;
    }

    public function limparTabelas($tabelasLimpar = false): bool
    {

        $io = new CvdwSymfonyStyle($this->input, $this->output);
        $database = new DatabaseSetup($this->input, $this->output, $this);

        $io->warning([
            '',
            'Essa opção apaga todos os dados das tabelas ok?'
        ]);

        if ($io->confirm('Quer continuar?', false)) {
            $tabela = $io->ask(
                'Qual tabela você quer limpar? (Use all para todas)',
                'all',
                function (string $tabela): string {
                    return $tabela;
                }
            );

            if(!is_array($tabelasLimpar)){
                $tabelasLimpar = array();
                if ($tabela === 'all') {
                    $io->text([
                        'Ok! Vou limpar todas as tabelas.',
                        ''
                    ]);
                    $tabelasLimpar = $database->listarTabelasArray();
                } else {

                    if ($database->verificarSeTabelaExiste($tabela)) {
                        $tabelasLimpar[$tabela] = "";
                        $io->text([
                            'Encontrei a tabela "' . $tabela . '".',
                            ''
                        ]);
                    } else {
                        $io->text([
                            'Não encontrei a tabela "' . $tabela . '".',
                            ''
                        ]);
                        if ($io->confirm($this::QUER_TENTAR_NOVAMENTE, true)) {
                            $this->limparTabelas($io);
                        }
                    }
                }
            }

            $table = new Table($this->output);
            $table->setHeaders(['Tabela', 'Situação']);

            $totalObjetos = count($tabelasLimpar);
            $progressBar = new ProgressBar($this->output, $totalObjetos);
            $progressBar->start();
            foreach ($tabelasLimpar as $tabela => $valor) {

                $tabelaExiste = $database->verificarSeTabelaExiste($tabela);
                if (!$tabelaExiste) {
                    $table->addRow([$tabela, '<error>Não encontrada!</error>']);
                    $progressBar->setMessage("A tabela {$tabela} não existe");
                    $progressBar->advance();
                    continue;
                }
                $truncate  = $database->limparTabela($tabela);
                if ($truncate) {
                    $table->addRow([$tabela, '<info>Limpa!</info>']);
                    $progressBar->setMessage("A tabela {$tabela} foi limpa");
                    $progressBar->advance();
                } else {
                    $table->addRow([$tabela, '<error>Ocorreu algum erro!</error>']);
                    $progressBar->setMessage("A tabela {$tabela} não pode ser limpa");
                    $progressBar->advance();
                }
            }
            $progressBar->finish();
            $table->render();

            $io->text([
                '',
                'Pronto!'
            ]);
        }
        $this->voltarProMenu = true;
        $this->voltarProMenu();

        return true;

    }

    private function apagarTabelas(): bool
    {
        $io = new CvdwSymfonyStyle($this->input, $this->output);
        $io->warning(['','Essa opção apaga todas as tabelas ok?']);
        if ($io->confirm('Quer continuar?', false)) {
            $tabela = $io->ask(
                'Qual tabela você quer apagar? (Use all para todas)',
                'all',
                function (string $tabela): string {
                    return $tabela;
                }
            );
            $database = new DatabaseSetup($this->input, $this->output, $this);
            $tabelasApagar = array();
            if ($tabela === 'all') {
                $io->text([ 'Ok! Vou apagar todas as tabelas.', '' ]);
                $tabelasApagar = $database->listarTabelasArray();
            } else {
                if ($database->verificarSeTabelaExiste($tabela)) {
                    $tabelasApagar[$tabela] = "";
                    $io->text([ 'Encontrei a tabela "' . $tabela . '".', '' ]);
                } else {
                    $io->text([ 'Não encontrei a tabela "' . $tabela . '".', '' ]);
                    if ($io->confirm($this::QUER_TENTAR_NOVAMENTE, true)) {
                        return $this->apagarTabelas();
                    }
                }
            }

            $table = new Table($this->output);
            $table->setHeaders(['Tabela', 'Situação']);

            $totalObjetos = count($tabelasApagar);
            $progressBar = new ProgressBar($this->output, $totalObjetos);
            $progressBar->start();

            $database->executarApagarTabelas($tabelasApagar, $table, $progressBar);
        
            $progressBar->finish();
            $table->render();

            $io->text([ '', 'Pronto!' ]);
        }

        $this->voltarProMenu = true;
        $this->voltarProMenu();

        return true;

    }

    private function configurarOpenAI(){

        $io = new CvdwSymfonyStyle($this->input, $this->output);
        $io->text([
            $this::VAMOS_LA,
            'Agora vamos configurar a integração do CVDW-CLI com o OpenAI...'
        ]);

        $io->note([
            'Para isso você precisa ter um cadastrdo na plataforma: https://platform.openai.com/',
            'Acesse de configurações: https://platform.openai.com/settings/organization/general',
            'Busque por Organization ID...',
        ]);

        $io->ask(
            'Id da Organização:',
            $_ENV['OPENAI_ORG'],
            function (string $openai_org): string {
                // Verificar se a string começa com 'org-'
                if (substr($openai_org, 0, 4) !== 'org-') {
                    throw new CvdwException('Id da Organização inválido, vamos tentar de novo?');
                }
                $this->variaveisAmbiente['openai_org'] = $openai_org;
                putenv('OPENAI_ORG=' . $openai_org);
                return $openai_org;
            }
        );

        $io->note([
            'Agora vamos precisar do ID do seu projeto.',
            'Acesse de configurações: https://platform.openai.com/settings/organization/general',
            'Acesse: Project >> General e encontre Project ID',
        ]);

        $io->ask(
            'Id do Projeto:',
            $_ENV['OPENAI_PROJ'],
            function (string $openai_proj): string {
                // Verificar se a string começa com 'org-'
                if (substr($openai_proj, 0, 5) !== 'proj_') {
                    throw new CvdwException('Id do projeto inválido, vamos tentar de novo?');
                }
                $this->variaveisAmbiente['openai_proj'] = $openai_proj;
                putenv('OPENAI_PROJ=' . $openai_proj);
                return $openai_proj;
            }
        );

        $io->note([
            'Agora vamos precisar de um token de acesso.',
            'Acesse a opção Api Keys: https://platform.openai.com/api-keys',
            'Gere um Token para o CVDW-CLI e cole aqui.',
        ]);

        $io->ask('Informe o token da plataforma:',
        $_ENV['OPENAI_TOKEN'],
        function (string $openai_token): string {
            // Verificar se a string começa com 'org-'
            if (substr($openai_token, 0, 8) !== 'sk-proj-') {
                throw new CvdwException('Token inválido, vamos tentar de novo?');
            }
            $this->variaveisAmbiente['openai_token'] = $openai_token;
            putenv('OPENAI_TOKEN=' . $openai_token);
            return $openai_token;
        });

        $openaiObj = new \Manzano\CvdwCli\Services\OpenAi($this->input, $this->output, $io, $this);
        $response = $openaiObj->validarToken($this->variaveisAmbiente['openai_token'], $this->variaveisAmbiente['openai_org'], $this->variaveisAmbiente['openai_proj']);

        if(isset($response['object']) && $response['object'] == 'list') {
            $io->text([
                'Legal, conseguimos conectar ao OpenAI!',
                ''
            ]);
        }else {
            $io->error('Não conseguimos validar a conexão, vamos tentar de novo?');
            $this->configurarOpenAI();
        }

        $io->text([
            'Deixar eu salvar essas informações...'
        ]);

        $newEnv = [
            'OPENAI_TOKEN' => $this->variaveisAmbiente['openai_token'],
            'OPENAI_ORG' => $this->variaveisAmbiente['openai_org'],
            'OPENAI_PROJ' => $this->variaveisAmbiente['openai_proj']
        ];
    
        $this->ambientesObj->salvarEnv($newEnv);

        $io->text('Salvo!');

        $this->voltarProMenu = true;
        $this->voltarProMenu();

        return true;
    }
    private function cadastarAmbiente()
    {
        $io = new CvdwSymfonyStyle($this->input, $this->output);
        $this->ambientesObj->verificarAmbientePadrao($io);
        $io->text([
            $this::VAMOS_LA,
            'Vou cadastrar um novo ambiente a partir do seu padrão...',
            'Você precisa informar o nome para poder usar em seus comandos.',
            'O ideal é somente usar letras minúsculas e sem espaços.',
            'Depois é so usar: cvdw configurar --set-env=nome_escolhido'
        ]);

        $referencia = $io->ask(
            'Qual nome de referencia deseja usar?',
            null,
            function (string $referencia): string {
                // copiar arquivo .env
                return $referencia;
            }
        );

        if($referencia <> ''){
            copy($this->ambientesObj->getEnvPath() . "/.env", $this->ambientesObj->getEnvPath() . "/$referencia.env");
            $io->success('Ambiente clonado com sucesso.');
            $io->text([
                '',
                'Agora é so usar: cvdw configurar --set-env='. $referencia,
                'Ou: cvdw executar --set-env='. $referencia.' all',
                ''
            ]);
        }

        $this->voltarProMenu = true;
        $this->voltarProMenu();

        return true;
    }

    private function listarAmbientesRemover(): bool
    {
        $io = new CvdwSymfonyStyle($this->input, $this->output);
        $this->ambientesObj->verificarAmbientePadrao($io);

        $ambientesOpcoes = array();
        $ambientes = $this->ambientesObj->listarAmbientes();
        foreach ($ambientes as $ambiente) {
            $ambientesOpcoes[] = $ambiente['referencia']." - ". $ambiente['email']. " - ". $ambiente['nome'];
        }
        $ambientesOpcoes[] =  'Nenhum / Cancelar';
        $inputAmbiente = $io->choice('Qual objeto deseja remover?', $ambientesOpcoes);
        if($inputAmbiente === 'Nenhum / Cancelar'){
            $this->voltarProMenu = true;
            $this->voltarProMenu();
            return true;
        }
        $indiceEscolhido = array_search($inputAmbiente, $ambientesOpcoes);
        $ambiente = $ambientes[$indiceEscolhido];
        $io->text([
            '',
            'Você selecionou o ambiente: ',
            ' - Endereço: https://'. $ambiente['referencia'].'.cvcrm.com.br/',
            ' - Email: '.$ambiente['email'],
            ' - Arquivo: '. $ambiente['nome']
        ]);
        if ($io->confirm('Posso remover o ambiente?', false)) {
            // Remover arquivo
            $arquivoEnv = $this->ambientesObj->getEnvPath()."/". $ambiente['nome'];
            if(file_exists($arquivoEnv)){
                unlink($arquivoEnv);
                $io->success('Ambiente remnovido com sucesso');
            } else {
                $io->error('Arquivo não encontrado');
            }
        }
        
        $this->voltarProMenu = true;
        $this->voltarProMenu();
        return true;
    }

    private function atualizarCVDW(){

        $io = new CvdwSymfonyStyle($this->input, $this->output);
        $this->ambientesObj = new Ambientes($this->env);
        $versaoCVDW = $this->ambientesObj->retornarVersao();

        $cvdwObj = new Cvdw($this->input, $this->output, $this);
        $novaVersaoCVDW = $cvdwObj->verificarNovaVersao($io);
        
        $io->text([
            $this::VAMOS_LA,
            'Hoje o seu CVDW-CLI está na versão: ' . $versaoCVDW,
            'Vamos atualizar o CVDW-CLI para a última versão disponível, '. $novaVersaoCVDW.'.',
            '',
            'Sugerimos fazer o backup do banco antes de prosseguir.'
        ]);

        if ($io->confirm('Deseja continuar?', false)) {

            $output = $this->output;
            $shellDir = str_replace('src/app', '', __DIR__);
            $shellScript = 'install.sh';
            $process = new Process(['./'.$shellScript]);
            $process->setWorkingDirectory($shellDir);
            $process->run(function ($buffer) use ($output) {
                $output->write($buffer);
            });

            if (!$process->isSuccessful()) {
                $io->error('Aconteceu algum problema ao tentar executar o update.');
                return Command::FAILURE;
            }

        }

        $io->text('');
        $io->success('Atualização finalizada!');
        $io->text('É altamente recomendável você usar a opção 3 das configurações.');

        $this->voltarProMenu = true;
        $this->voltarProMenu();

        return true;
    }

    protected function voltarProMenu()
    {
        $io = new CvdwSymfonyStyle($this->input, $this->output);
        
        if ($this->voltarProMenu) {
            if ($io->confirm('Vamos voltar pro menu anterior?', true)) {
                $this->limparTela();
                return $this->execute($this->input, $this->output);
            } else {
                return 0;
            }
        }
    }

    protected function limparTela(): void
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            // Limpa a tela no Windows
            system('cls');
        } else {
            // Limpa a tela em sistemas Unix-like
            system('clear');
        }
    }

}
