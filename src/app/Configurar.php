<?php

namespace Manzano\CvdwCli;

use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;
use Manzano\CvdwCli\Inc\CvdwException;
use Manzano\CvdwCli\Services\Ambientes;
use Manzano\CvdwCli\Services\Console\CvdwSymfonyStyle;
use Manzano\CvdwCli\Services\Cvdw;
use Manzano\CvdwCli\Services\DatabaseSetup;
use Manzano\CvdwCli\Services\EnvironmentManager;
use Manzano\CvdwCli\Services\Http;
use Manzano\CvdwCli\Services\Objeto;
use Manzano\CvdwCli\Services\RateLimit;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
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
    protected CvdwSymfonyStyle $console;
    /**
     * @var string[]
     */
    public array $variaveisAmbiente = [];
    public bool $voltarProMenu = true;
    public \Doctrine\DBAL\Connection $conn;
    protected $ambientesObj;
    protected $databaseObj;
    public $rateLimitObj;
    protected $cvdwObj;
    protected $env = null;
    protected $force = false;
    protected $evento = 'Configurar';
    protected EnvironmentManager $environmentManager;
    public const VAMOS_LA = 'Vamos Lá!';
    public const QUER_TENTAR_NOVAMENTE = 'Quer tentar novamente?';

    protected function configure()
    {
        $this->setName('configurar')
        ->setDescription('Configurações do aplicativo')
        ->addArgument('opcao', InputArgument::OPTIONAL, 'Atalhos para a configuração')
        ->addOption(
            'set-env',
            'env',
            InputOption::VALUE_OPTIONAL, // Modo: VALUE_REQUIRED, VALUE_OPTIONAL, VALUE_NONE
            'Diz qual ENV usar. Exemplo: dev, homologacao, producao.',
        )->addOption(
            'force',
            'f',
            InputOption::VALUE_OPTIONAL, // Modo: VALUE_REQUIRED, VALUE_OPTIONAL, VALUE_NONE
            'Força positivo nas confirmações do terminal.',
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $this->limparTela();

        $this->environmentManager = new EnvironmentManager();
        $this->rateLimitObj = new RateLimit($input, $output, $this);
        $this->rateLimitObj->iniciarExecucao();

        if ($input->getOption('set-env')) {
            $this->env = $input->getOption('set-env');
        }

        if ($input->getOption('force')) {
            $this->force = true;
        }

        $this->ambientesObj = new Ambientes($this->env, $this);
        $this->ambientesObj->retornarEnvs();

        $this->cvdwObj = new Cvdw($input, $output, $this, rateLimitObj: $this->rateLimitObj);

        $this->input = $input;
        $this->output = $output;

        $console = new CvdwSymfonyStyle($input, $output);

        $console->title('Configurando o CVDW-CLI');

        $versaoCVDW = $this->ambientesObj->retornarVersao();
        $console->text('Versão: ' . $versaoCVDW);

        $ambienteAtivo = $this->ambientesObj->ambienteAtivo();
        $console->text('Ambiente ativo: ' . $ambienteAtivo);


        $inputOpcao = $input->getArgument('opcao');
        if ($inputOpcao == 'autoupdate') {
            $console = new CvdwSymfonyStyle($this->input, $this->output);
            $database = new DatabaseSetup($this->input, $this->output, $this);
            $database->executarCriarTabelas();
            $this->verificarInstalacao();

            return Command::SUCCESS;
        }

        $this->cvdwObj->alertarNovaVersao($versaoCVDW, $console);



        $this->variaveisAmbiente['configurar'] = $console->choice('O que deseja configurar agora?', [
            'Acesso ao CVDW API',
            'Acesso ao meu Banco de dados',
            'Criar as tabelas em meu banco de dados',
            'Configurar anonimização de dados sensíveis',
            'Verificar/Atualizar meu ambiente',
            'Limpar datas de referências das tabelas',
            'Limpar as tabelas do CVDW (Truncate)',
            'Apagar as tabelas do CVDW (Drop)',
            'Cadastrar novo ambiente a partir do padrão',
            'Listar e remover seus ambientes',
            'Atualizar o ambiente do CVDW-CLI',
            'Executar o CVDW-CLI',
            'Sair (CTRL+C)',
        ]);

        if ($this->variaveisAmbiente['configurar'] === 'Sair (CTRL+C)') {
            $console->text(['Até mais!', '']);
            exit;

            return Command::SUCCESS;

        }
        $console->text(['Você escolheu: ' . $this->variaveisAmbiente['configurar'], '']);

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
                $this->cvdwObj->conectar();
                $this->verificarInstalacao();

                break;
            case 'Limpar datas de referências das tabelas':
                $this->limparDataReferenciaTabelas();

                break;
            case 'Limpar as tabelas do CVDW (Truncate)':
                $this->limparTabelas();

                break;
            case 'Apagar as tabelas do CVDW (Drop)':
                $this->apagarTabelas();

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
            case 'Executar o CVDW-CLI':
                if ($console->confirm('Deseja executar o CVDW-CLI?') == true) {
                    $console->success('Executando o CVDW-CLI...');
                    $this->getApplication()->find('executar')->run($input, $output);

                    return Command::SUCCESS;
                }

                break;
            default:

                return Command::INVALID;
        }

        return Command::SUCCESS;
    }


    private function configurarCV(): int
    {

        $console = new CvdwSymfonyStyle($this->input, $this->output);
        $console->text([
            $this::VAMOS_LA,
            'Primeiro vamos configurar as variáveis do CV...',
        ]);
        $console->note([
            'Vamos começar pelo endereço de acesso ao CV,',
            'Lembre que não deve ser o endereço completo, somente o subdominio',
            'Ex.: https://SUA_INCORPORADORA.cvcrm.com.br então vamos usar "sua_incorporadora"',
        ]);

        $console->ask('Me diz o subdominio do ambiente seu CV:', $this->environmentManager->getCvUrl(), function (string $enderecoCv): string {

            $console = new CvdwSymfonyStyle($this->input, $this->output);

            $http = new Http($this->input, $this->output, $console, $this, null, rateLimitObj: $this->rateLimitObj);
            $response = $http->pingAmbienteCVDW($enderecoCv);

            if ($response['nome'] !== null) {
                $this->variaveisAmbiente = $response;
                $this->variaveisAmbiente['endereco_cv'] = $enderecoCv;
                putenv('CV_URL=' . $enderecoCv);
            } else {
                $console->error('Não consegui pegar as informações do ambiente, vamos tentar de novo?');
            }

            return $enderecoCv;
        });

        $console->text([
            'Legal, consegui encontrar o ambiente!',
            'Agora já sei que vamos trabalhar com o ambiente "' . $this->variaveisAmbiente['nome'] . '"',
        ]);

        $console->ask(
            'E-mail de acesso:',
            $this->environmentManager->getCvEmail(),
            function (string $emailCv): string {
                // validar se o e-mail é válido
                if (! filter_var($emailCv, FILTER_VALIDATE_EMAIL)) {
                    throw new CvdwException('E-mail inválido, vamos tentar de novo?');
                }
                $this->variaveisAmbiente['email'] = $emailCv;
                putenv('CV_EMAIL=' . $emailCv);

                return $emailCv;
            }
        );

        $console->ask(
            'Token de acesso:',
            $this->environmentManager->getCvToken(),
            function (string $tokenCv): string {
                // fazer uma re
                $this->variaveisAmbiente['token'] = $tokenCv;
                putenv('CV_TOKEN=' . $tokenCv);

                return $tokenCv;
            }
        );

        $console->text([
            'Ok! Agora vou tentar fazer uma requisição para tentar validar os dados...',
        ]);

        $http = new Http($this->input, $this->output, $console, $this, null, rateLimitObj: $this->rateLimitObj);
        $response = $http->pingAmbienteAutenticadoCVDW(
            $this->variaveisAmbiente['endereco_cv'],
            "/imobiliarias",
            $this->variaveisAmbiente['email'],
            $this->variaveisAmbiente['token']
        );

        if (isset($response['registros'])) {

            $console->success([
                'Validado com sucesso!',
            ]);

            $console->text([
                'Deixar eu salvar essas informações...',
            ]);

            $newEnv = [
                'CV_URL' => $this->variaveisAmbiente['endereco_cv'],
                'CV_TOKEN' => $this->variaveisAmbiente['token'],
                'CV_EMAIL' => $this->variaveisAmbiente['email'],
            ];

            $this->ambientesObj->salvarEnv($newEnv);

            $console->text('Salvo!');

            $this->ambientesObj->retornarEnvs();
            $this->voltarProMenu = true;
            $this->voltarProMenu();

            return 0;
        } else {
            $console->error('Não consegui acessar o ambiente.');
            if ($console->confirm($this::QUER_TENTAR_NOVAMENTE, true)) {
                return $this->configurarCV();
            }

            return 0;
        }
    }

    private function configurarBanco(): int
    {
        $console = new CvdwSymfonyStyle($this->input, $this->output);

        $console->text([
            '',
            'Agora vamos pegar as informações de conexão do banco de dados...',
        ]);

        $this->variaveisAmbiente['banco'] = $console->choice(
            'Que banco de dados você quer conectar?',
            ['pdo_mysql', 'pdo_pgsql', 'pdo_sqlsrv'],
            $this->environmentManager->getDbConnection()
        );
        $console->text('Você escolheu: ' . $this->variaveisAmbiente['banco']);

        $console->ask(
            'Qual o endereço do banco de dados?',
            $this->environmentManager->getDbHost(),
            function (string $dbHost): string {
                $this->variaveisAmbiente['db_host'] = $dbHost;

                return $dbHost;
            }
        );

        $console->ask(
            'Qual a porta?',
            $this->environmentManager->getDbPort(),
            function (string $dbPort): string {
                $this->variaveisAmbiente['db_port'] = $dbPort;

                return $dbPort;
            }
        );

        $console->ask(
            'Qual o nome da database?',
            $this->environmentManager->getDbDatabase(),
            function (string $dbDatabase): string {
                $this->variaveisAmbiente['db_database'] = $dbDatabase;

                return $dbDatabase;
            }
        );

        if ($this->variaveisAmbiente['banco'] == 'pdo_pgsql') {
            // se não tiver DB_SCHEMA, usar 'public'
            if (! $this->environmentManager->has('DB_SCHEMA')) {
                $this->environmentManager->setDbSchema('public');
            }
            $console->ask(
                'Qual a schema?',
                $this->environmentManager->getDbSchema(),
                function (string $dbSchema): string {
                    $this->variaveisAmbiente['db_schema'] = $dbSchema;

                    return $dbSchema;
                }
            );
        } else {
            $this->variaveisAmbiente['db_schema'] = null;
        }

        $console->ask(
            'Qual o usuário?',
            $this->environmentManager->getDbUsername(),
            function (string $dbUsername): string {
                $this->variaveisAmbiente['db_username'] = $dbUsername;

                return $dbUsername;
            }
        );

        $console->ask(
            'Qual a senha?',
            $this->environmentManager->getDbPassword(),
            function (string $dbPassword): string {
                $this->variaveisAmbiente['db_password'] = $dbPassword;

                return $dbPassword;
            }
        );

        $newEnv = [
            'DB_CONNECTION' => $this->variaveisAmbiente['banco'],
            'DB_HOST' => $this->variaveisAmbiente['db_host'],
            'DB_PORT' => $this->variaveisAmbiente['db_port'],
            'DB_DATABASE' => $this->variaveisAmbiente['db_database'],
            'DB_USERNAME' => $this->variaveisAmbiente['db_username'],
            'DB_PASSWORD' => $this->variaveisAmbiente['db_password'],
            'DB_SCHEMA' => $this->variaveisAmbiente['db_schema'],
        ];

        $console->text([
            'Ok! testar a conexão...',
        ]);

        // Criar uma conexao com o Doctrine DBAL
        $config = new Configuration();

        // Se nao tiver $this->variaveisAmbiente['banco'], adicionamos o valor pdo_mysql
        if (! isset($this->variaveisAmbiente['banco'])) {
            $this->variaveisAmbiente['banco'] = 'pdo_mysql';
        }

        $connectionParams = [
            'dbname' => $this->variaveisAmbiente['db_database'],
            'user' => $this->variaveisAmbiente['db_username'],
            'password' => $this->variaveisAmbiente['db_password'],
            'host' => $this->variaveisAmbiente['db_host'],
            'port' => $this->variaveisAmbiente['db_port'],
            'driver' => $this->variaveisAmbiente['banco'],
        ];

        if ($this->variaveisAmbiente['banco'] == 'pdo_pgsql') {
            $connectionParams['driverOptions'] = [
                \PDO::ATTR_PERSISTENT => true,
            ];
            $connectionParams['options'] = [
                'search_path' => $this->variaveisAmbiente['db_schema'],
            ];
        }

        try {
            $conn = DriverManager::getConnection($connectionParams, $config);
            $conn->connect();
            if ($conn->isConnected()) {

                $console->success('Conexão bem-sucedida!');
            } else {
                $console->error('Não foi possível conectar ao banco de dados (1)');
                if ($console->confirm($this::QUER_TENTAR_NOVAMENTE, true)) {
                    return $this->configurarBanco();
                }
            }
        } catch (\Exception $e) {
            $console->error('Não foi possível conectar ao banco de dados. (2)');
            $console->error('Encontrei esse erro: ' . $e->getMessage());
            if ($console->confirm($this::QUER_TENTAR_NOVAMENTE, true)) {
                return $this->configurarBanco();
            }
        }

        $console->text([
            '',
            'Deixa eu salvar as informações...',
        ]);
        $this->ambientesObj->salvarEnv($newEnv);

        $this->voltarProMenu = true;
        $this->voltarProMenu();

        return 1;

    }

    private function criarTabelas(): bool
    {

        $console = new CvdwSymfonyStyle($this->input, $this->output);

        $console->text('Serão criadas as tabelas abaixo:');

        $database = new DatabaseSetup($this->input, $this->output, $this);
        $database->listarTabelas();

        if ($console->confirm('Podemos continuar?', true)) {
            $database->executarCriarTabelas();
            $console->text('Criação finalizada!');
            $console->text('');
        } else {
            $console->text('Ok, vamos parar por aqui...');
            $console->text('');
        }

        $database->fecharConexao();

        $this->voltarProMenu = true;
        $this->voltarProMenu();

        return true;

    }

    private function configurarAnonimizacao(): bool
    {
        $console = new CvdwSymfonyStyle($this->input, $this->output);

        $console->text('A anonimização dos dados sensíveis de pessoas é uma prática recomendada para proteger a privacidade dos usuários');
        $console->text('O CVDW-CLI pode ajudar você a esconder esses dados.');
        $console->text('Exemplo: Nome, E-mail, Telefone, CPF, RG, etc.');

        if (! $this->environmentManager->has('ANONIMIZAR')) {
            $this->environmentManager->setAnonimizar(false);
            $this->environmentManager->setAnonimizarTipo('Asteriscos');
        }

        $anonimizar = $console->confirm('Você deseja anonimizar os dados sensíveis?', $this->environmentManager->getAnonimizar());
        $this->environmentManager->setAnonimizar($anonimizar);

        if ($this->environmentManager->getAnonimizar()) {

            $console->text('Ok, vamos configurar a anonimização...');
            $console->text('Agora você pode escolher como deseja anonimizar os dados sensíveis.');
            $nomeEx = 'Gabriel Manzano';
            $console->text(' - Com asteriscos:');
            $console->text("   Ex: $nomeEx -> ".\Manzano\CvdwCli\Inc\Helper::substituirPorAsteriscos($nomeEx));
            $console->text(' - Com um hash unico:');
            $console->text("   Ex: $nomeEx -> ".\Manzano\CvdwCli\Inc\Helper::substituirPorHash($nomeEx, 20));

            $anonimizarTipo = $console->choice(
                'Como você deseja anonimizar?',
                ['Asteriscos', 'Hash'],
                $this->environmentManager->getAnonimizarTipo()
            );
            $console->text('Você escolheu: ' . $anonimizarTipo);
            $this->environmentManager->setAnonimizarTipo($anonimizarTipo);
            $this->environmentManager->setAnonimizar(true);

        } else {
            $this->environmentManager->setAnonimizar(false);
        }

        $this->ambientesObj->salvarEnv($this->environmentManager->getAll());
        $console->text('Pronto, configuração salva...');
        $this->voltarProMenu = true;
        $this->voltarProMenu();

        return true;
    }

    private function verificarInstalacao(): bool
    {

        $console = new CvdwSymfonyStyle($this->input, $this->output);
        $http = new Http($this->input, $this->output, $console, $this, null, rateLimitObj: $this->rateLimitObj);
        $diferencasBanco = [];

        $console->text([$this::VAMOS_LA, 'Validando a instalação...', '' ]);
        $validarObjetos = true;

        if ($this->cvdwObj->validarAmbiente($http)) {
            $console->text('<bg=green>[OK]</> Conexão com o CVDW está funcionando!');
        } else {
            $console->text('<fg=white;bg=red>[PROBLEMA]</> Não consegui acessar o ambiente do CVDW!');
            $validarObjetos = false;
        }

        $this->conn = \Manzano\CvdwCli\Inc\Conexao::conectarDB($this->input, $this->output, false);
        $databaseObj = null;
        if ($this->conn->isConnected()) {
            $console->text('<bg=green>[OK]</> Conexão com o banco de dados está funcionando!');
            $databaseObj = new DatabaseSetup($this->input, $this->output, $this);
        } else {
            $console->text('<fg=white;bg=red>[PROBLEMA]</> Não consegui acessar o banco de dados!');
            $validarObjetos = false;
        }

        if (! $validarObjetos) {
            $console->note('Como o Banco ou Api não está acessível, não posso validar os objetos.');
        } else {

            $console->text(['', 'Agora vamos validar os objetos...', '']);
            $bancoProblemas = false;
            $objetoObj = new Objeto($this->input, $this->output);

            $objetos = $objetoObj->retornarObjetos('all');
            if ($databaseObj === null) {
                $console->text('<fg=white;bg=red>[PROBLEMA]</> DatabaseObj não foi inicializado!');

                return false;
            }
            foreach ($objetos as $key => $dados) {
                $existe = $databaseObj->verificarSeTabelaExiste($key);
                if ($existe) {
                    $objeto = $objetoObj->retornarObjeto($key);
                    $estrutura = $databaseObj->retornarEstruturaTabela($key);
                    $logDiferencas = $databaseObj->compararTabelaObjeto($estrutura, $objeto);

                    $diferencas = $logDiferencas[1];
                    $logs = $logDiferencas[0];
                    $subtabelas = $logDiferencas[2];
                    if (count($diferencas) > 0) {
                        $diferencasBanco[$key] = $diferencas;
                        $bancoProblemas = true;
                        $console->text('<fg=white;bg=red>[PROBLEMA]</> Encontrei algo na tabela ' . $key . '!');
                        foreach ($logs as $log) {
                            $console->text('- '.$log);
                        }
                    } else {
                        $console->text('<bg=green>[OK]</> A tabela ' . $key . ' está atualizada!');
                    }
                } else {
                    $bancoProblemas = true;
                    $console->text('<fg=white;bg=red>[PROBLEMA]</> A tabela ' . $key . ' não foi encontrada!');
                }

                if (isset($subtabelas) && is_array($subtabelas) && count($subtabelas) > 0) {
                    foreach ($subtabelas as $subespecificacao) {
                        $existe = $databaseObj->verificarSeTabelaExiste($subespecificacao['nome']);
                        if ($existe) {
                            $subestrutura = $databaseObj->retornarEstruturaTabela($subespecificacao['nome']);
                            $subobjeto = [];
                            $subobjeto['response']['dados'] = $subespecificacao['objeto']['dados'];

                            $logDiferencas = $databaseObj->compararTabelaObjeto($subestrutura, $subobjeto);
                            $diferencas = $logDiferencas[1];
                            $logs = $logDiferencas[0];
                            if (count($diferencas) > 0) {
                                $diferencasBanco[$subespecificacao['nome']] = $diferencas;
                                $bancoProblemas = true;
                                $console->text('<fg=white;bg=red>[PROBLEMA]</>
                                        -> Encontrei algo na sub-tabela ' . ($estrutura['nome'] ?? 'desconhecida') . '!');
                                foreach ($logs as $log) {
                                    $console->text('-- ' . $log);
                                }
                            } else {
                                $console->text('<bg=green>[OK]</> -> A sub-tabela ' . ($estrutura['nome'] ?? 'desconhecida') . ' está atualizada!');
                            }
                        } else {
                            $bancoProblemas = true;
                            $console->text('<fg=white;bg=red>[PROBLEMA]</> -> A sub-tabela ' . ($estrutura['nome'] ?? 'desconhecida') . ' não foi encontrada!');

                        }
                    }
                }

            }

            if ($databaseObj !== null) {
                $databaseObj->verificaTabelaRequisicoes();
            }

        }

        if (isset($bancoProblemas) && $bancoProblemas) {
            $console->text([
                '',
                'Encontrei problemas no banco de dados, vamos tentar corrigir?',
                '',
            ]);

            if ($this->force) {
                if ($databaseObj !== null) {
                    $databaseObj->executarCorrecoes($diferencasBanco, false);
                    $console->text('Concluindo operação...');
                    $databaseObj->fecharConexao();
                }
                exit;
            }

            if ($console->confirm('Quer tentar corrigir?', true)) {
                if ($databaseObj !== null) {
                    $databaseObj->executarCorrecoes($diferencasBanco, true);
                }

            } else {
                $console->text([
                    '',
                    'Ok, vamos parar por aqui...',
                    '',
                ]);
            }
        } else {
            $console->text([
                '',
                'Parece que esta tudo ok!',
                '',
            ]);
            if ($this->force) {
                $console->text('Concluindo operação...');
                if ($databaseObj !== null) {
                    $databaseObj->fecharConexao();
                }
                exit;
            }
        }

        if ($databaseObj !== null) {
            $databaseObj->fecharConexao();
        }

        // Só volta ao menu se não estiver sendo chamado do atualizarCVDW
        if ($this->voltarProMenu) {
            $this->voltarProMenu();
        }

        return true;
    }

    public function limparDataReferenciaTabelas(array $tabelasLimpar = []): bool
    {
        $console = new CvdwSymfonyStyle($this->input, $this->output);
        $database = new DatabaseSetup($this->input, $this->output, $this);

        if (empty($tabelasLimpar)) {
            $console->warning([
                '',
                'Essa opção apaga as datas de referência mas mantém os dados das tabelas.',
                'Mas isso vai forçar o plugin a baixar todos os dados novamente ok?',
                'Mas o seu BI vai continuar trabalhando normalmente.',
            ]);

            if ($console->confirm('Quer continuar?', false)) {
                $tabela = $console->ask(
                    'Qual tabela você quer limpar? (Use all para todas)',
                    'all',
                    function (string $tabela): string {
                        return $tabela;
                    }
                );

                if ($tabela === 'all') {
                    $console->text([
                        'Ok! Vou limpar todas as tabelas.',
                        '',
                    ]);
                    $tabelasLimpar = $database->listarTabelasArray();
                } else {
                    if ($database->verificarSeTabelaExiste($tabela)) {
                        $tabelasLimpar[$tabela] = "";
                        $console->text([
                            'Encontrei a tabela "' . $tabela . '".',
                            '',
                        ]);
                    } else {
                        $console->text([
                            'Não encontrei a tabela "' . $tabela . '".',
                            '',
                        ]);
                        if ($console->confirm($this::QUER_TENTAR_NOVAMENTE, true)) {
                            $this->limparDataReferenciaTabelas();
                        }
                    }
                }
            } else {
                return false;
            }
        }

        $table = new Table($this->output);
        $table->setHeaders(['Tabela', 'Situação']);

        $totalObjetos = count($tabelasLimpar);
        $progressBar = new ProgressBar($this->output, $totalObjetos);
        $progressBar->setFormat('normal'); // debug
        $progressBar->setBarCharacter('<fg=green>=</>');
        $progressBar->setProgressCharacter("\xF0\x9F\x9A\x80");
        $progressBar->setFormat("  \_ Dados processados %current% de %max% [%bar%] %percent:3s%% \n %message%");
        $progressBar->start();
        foreach ($tabelasLimpar as $tabela => $valor) {
            $progressBar->setMessage(" Tabela {$tabela} encontrada.");
            $dataReferenciaNull = $database->limparDataReferenciaTabela($tabela);
            /** @phpstan-ignore-next-line */
            if ($dataReferenciaNull) {
                $table->addRow([$tabela, ' <info>Data de Referencia limpa!</info>']);
                $progressBar->setMessage(" A tabela {$tabela} teve sua data de referencia apagada.");
                $progressBar->advance();
            } else {
                $table->addRow([$tabela, ' <error>Ocorreu algum erro!</error>']);
                $progressBar->setMessage("A tabela {$tabela} não pode ter a data apagada!");
                $progressBar->advance();
            }
        }
        $progressBar->finish();
        $table->render();

        $console->text([
            '',
            'Pronto!',
        ]);

        $database->fecharConexao();
        $this->voltarProMenu = true;
        $this->voltarProMenu();

        return true;
    }

    public function limparTabelas(array $tabelasLimpar = []): bool
    {
        $console = new CvdwSymfonyStyle($this->input, $this->output);
        $database = new DatabaseSetup($this->input, $this->output, $this);

        if (empty($tabelasLimpar)) {
            $console->warning([
                '',
                'Essa opção apaga todos os dados das tabelas ok?',
            ]);

            if ($console->confirm('Quer continuar?', false)) {
                $tabela = $console->ask(
                    'Qual tabela você quer limpar? (Use all para todas)',
                    'all',
                    function (string $tabela): string {
                        return $tabela;
                    }
                );

                if ($tabela === 'all') {
                    $console->text([
                        'Ok! Vou limpar todas as tabelas.',
                        '',
                    ]);
                    $tabelasLimpar = $database->listarTabelasArray();
                } else {
                    if ($database->verificarSeTabelaExiste($tabela)) {
                        $tabelasLimpar[$tabela] = "";
                        $console->text([
                            'Encontrei a tabela "' . $tabela . '".',
                            '',
                        ]);
                    } else {
                        $console->text([
                            'Não encontrei a tabela "' . $tabela . '".',
                            '',
                        ]);
                        if ($console->confirm($this::QUER_TENTAR_NOVAMENTE, true)) {
                            $this->limparTabelas();
                        }
                    }
                }
            } else {
                return false;
            }
        }

        $table = new Table($this->output);
        $table->setHeaders(['Tabela', 'Situação']);

        $totalObjetos = count($tabelasLimpar);
        $progressBar = new ProgressBar($this->output, $totalObjetos);
        $progressBar->start();
        foreach ($tabelasLimpar as $tabela => $valor) {
            $tabelaExiste = $database->verificarSeTabelaExiste($tabela);
            if (! $tabelaExiste) {
                $table->addRow([$tabela, '<error>Não encontrada!</error>']);
                $progressBar->setMessage("A tabela {$tabela} não existe");
                $progressBar->advance();

                continue;
            }
            $truncate = $database->limparTabela($tabela);
            /** @phpstan-ignore-next-line */
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

        $console->text([
            '',
            'Pronto!',
        ]);

        $database->fecharConexao();
        $this->voltarProMenu = true;
        $this->voltarProMenu();

        return true;
    }

    private function apagarTabelas(): bool
    {
        $console = new CvdwSymfonyStyle($this->input, $this->output);
        $console->warning(['','Essa opção apaga todas as tabelas ok?']);
        if ($console->confirm('Quer continuar?', false)) {
            $tabela = $console->ask(
                'Qual tabela você quer apagar? (Use all para todas)',
                'all',
                function (string $tabela): string {
                    return $tabela;
                }
            );
            $database = new DatabaseSetup($this->input, $this->output, $this);
            $tabelasApagar = [];
            if ($tabela === 'all') {
                $console->text([ 'Ok! Vou apagar todas as tabelas.', '' ]);
                $tabelasApagar = $database->listarTabelasArray();
            } else {
                if ($database->verificarSeTabelaExiste($tabela)) {
                    $tabelasApagar[$tabela] = "";
                    $console->text([ 'Encontrei a tabela "' . $tabela . '".', '' ]);
                } else {
                    $console->text([ 'Não encontrei a tabela "' . $tabela . '".', '' ]);
                    if ($console->confirm($this::QUER_TENTAR_NOVAMENTE, true)) {
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
            $database->fecharConexao();

            $progressBar->finish();
            $table->render();

            $console->text([ '', 'Pronto!' ]);
        }


        $this->voltarProMenu = true;
        $this->voltarProMenu();

        return true;

    }
    private function cadastarAmbiente()
    {
        $console = new CvdwSymfonyStyle($this->input, $this->output);
        $this->ambientesObj->verificarAmbientePadrao($console);
        $console->text([
            $this::VAMOS_LA,
            'Vou cadastrar um novo ambiente a partir do seu padrão...',
            'Você precisa informar o nome para poder usar em seus comandos.',
            'O ideal é somente usar letras minúsculas e sem espaços.',
            'Depois é so usar: cvdw configurar --set-env=nome_escolhido',
        ]);

        $referencia = $console->ask(
            'Qual nome de referencia deseja usar?',
            null,
            function (string $referencia): string {
                // copiar arquivo .env
                return $referencia;
            }
        );

        if ($referencia <> '') {
            copy($this->ambientesObj->getEnvPath() . "/.env", $this->ambientesObj->getEnvPath() . "/$referencia.env");
            $console->success('Ambiente clonado com sucesso.');
            $console->text([
                '',
                'Agora é so usar: cvdw configurar --set-env='. $referencia,
                'Ou: cvdw executar --set-env='. $referencia.' all',
                '',
            ]);
        }

        $this->voltarProMenu = true;
        $this->voltarProMenu();

        return true;
    }

    private function listarAmbientesRemover(): bool
    {
        $console = new CvdwSymfonyStyle($this->input, $this->output);
        $this->ambientesObj->verificarAmbientePadrao($console);

        $ambientesOpcoes = [];
        $ambientes = $this->ambientesObj->listarAmbientes();
        foreach ($ambientes as $ambiente) {
            $ambientesOpcoes[] = $ambiente['referencia']." - ". $ambiente['email']. " - ". $ambiente['nome'];
        }
        $ambientesOpcoes[] = 'Nenhum / Cancelar';
        $inputAmbiente = $console->choice('Qual objeto deseja remover?', $ambientesOpcoes);
        if ($inputAmbiente === 'Nenhum / Cancelar') {
            $this->voltarProMenu = true;
            $this->voltarProMenu();

            return true;
        }
        $indiceEscolhido = array_search($inputAmbiente, $ambientesOpcoes);
        $ambiente = $ambientes[$indiceEscolhido];
        $console->text([
            '',
            'Você selecionou o ambiente: ',
            ' - Endereço: https://'. $ambiente['referencia'].'.cvcrm.com.br/',
            ' - Email: '.$ambiente['email'],
            ' - Arquivo: '. $ambiente['nome'],
        ]);
        if ($console->confirm('Posso remover o ambiente?', false)) {
            // Remover arquivo
            $arquivoEnv = $this->ambientesObj->getEnvPath()."/". $ambiente['nome'];
            if (file_exists($arquivoEnv)) {
                unlink($arquivoEnv);
                $console->success('Ambiente remnovido com sucesso');
            } else {
                $console->error('Arquivo não encontrado');
            }
        }

        $this->voltarProMenu = true;
        $this->voltarProMenu();

        return true;
    }

    private function atualizarCVDW()
    {

        $console = new CvdwSymfonyStyle($this->input, $this->output);
        $this->ambientesObj = new Ambientes($this->env);
        $versaoCVDW = $this->ambientesObj->retornarVersao();


        $cvdwObj = new Cvdw($this->input, $this->output, $this, $this->rateLimitObj);
        $novaVersaoCVDW = $cvdwObj->verificarNovaVersao($console);

        $console->text([
            $this::VAMOS_LA,
            'Hoje o seu CVDW-CLI está na versão: ' . $versaoCVDW,
            'Vamos atualizar o CVDW-CLI para a última versão disponível, '. $novaVersaoCVDW.'.',
            '',
            'Sugerimos fazer o backup do banco antes de prosseguir.',
        ]);

        if ($console->confirm('Deseja continuar?', true)) {

            $output = $this->output;
            $shellDir = str_replace('src/app', '', __DIR__);
            $shellScript = 'install.sh';
            $process = new Process(['./'.$shellScript]);
            $process->setWorkingDirectory($shellDir);
            $process->run(function ($buffer) use ($output) {
                $output->write($buffer);
            });

            if (! $process->isSuccessful()) {
                $console->error('Aconteceu algum problema ao tentar executar o update.');

                return Command::FAILURE;
            }

        }

        $console->text('');
        $console->success('Atualização finalizada!');
        $console->text('Seu CVDW-CLI está atualizado na versão: ' . $versaoCVDW);
        $console->text('É altamente recomendável você usar a opção 4 das configurações.');

        $this->voltarProMenu = false;
        if ($console->confirm('Podemos verificar o banco de dados?', true)) {
            $cvdwObj->conectar();
            $this->verificarInstalacao();
        }

        return true;
    }

    protected function voltarProMenu()
    {

        $console = new CvdwSymfonyStyle($this->input, $this->output);

        if ($this->voltarProMenu) {
            if ($console->confirm('Vamos voltar pro menu anterior?', true)) {
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
