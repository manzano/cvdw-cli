<?php

namespace Manzano\CvdwCli;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Manzano\CvdwCli\Services\Console\CvdwSymfonyStyle;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\ProgressBar;

use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;

use Manzano\CvdwCli\Services\DatabaseSetup;
use Manzano\CvdwCli\Services\Http;
use Manzano\CvdwCli\Services\Objeto;

use Manzano\CvdwCli\Services\Monitor\Eventos;

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
    protected $evento = 'Configurar';

    protected function configure()
    {
        $this->setName('configurar')
            ->setDescription('Configurações do aplicativo');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $this->limparTela();

        $this->eventosObj = new Eventos();
    
        
        $io = new CvdwSymfonyStyle($input, $output);

        $this->input = $input;
        $this->output = $output;

        $io->title('Configurando o CVDW-CLI');
        $this->eventosObj->registrarEvento($this->evento, 'Início');

        $this->variaveisAmbiente['configurar'] = $io->choice('O que deseja configurar agora?', [
            'Acesso ao CVDW API',
            'Acesso ao meu Banco de dados',
            'Criar as tabelas em meu banco de dados',
            'Verificar/Atualizar meu ambiente',
            'Limpar as tabelas do CVDW (Truncate)',
            'Apagar as tabelas do CVDW (Drop)',
            'Sair (CTRL+C)'
        ]);

        if ($this->variaveisAmbiente['configurar'] === 'Sair (CTRL+C)') {
            $io->text(['Até mais!', '']);
            return Command::SUCCESS;
            exit;
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
            case 'Limpar as tabelas do CVDW (Truncate)':
                $this->limparTabelas();
                break;
            case 'Apagar as tabelas do CVDW (Drop)':
                $this->apagarTabelas();
                break;
            case 'Verificar/Atualizar meu ambiente':
                $this->verificarInstalacao();
                break;
            default:
                //$this->execute();
                break;
        }

        return Command::SUCCESS;
    }

    private function verificarInstalacao(): bool
    {
        $objetoObj = new Objeto($this->input, $this->output);
        $io = new CvdwSymfonyStyle($this->input, $this->output);
        $http = new Http($this->input, $this->output, $io);
        $diferencasBanco = array();

        $io->text([
            'Vamos lá!',
            'Validando a instalação...',
            ''
        ]);

        // Validando conexão com a api do CVDW
        $response = $http->pingAmbienteAutenticadoCVDW(
            $_ENV['CV_URL'],
            "/imobiliarias",
            $_ENV['CV_EMAIL'],
            $_ENV['CV_TOKEN']
        );
        if (isset($response['registros'])) {
            $io->text('<bg=green>[OK]</> Conexão com o CVDW está funcionando!');
        } else {
            $io->text('<fg=white;bg=red>[PROBLEMA]</> Não consegui acessar o ambiente do CVDW!');
            $validarObjetos = false;
        }

        // Validar conexao com o banco de dados
        $validarObjetos = true;
        $this->conn = conectarDB($this->input, $this->output, false);
        if($this->conn->isConnected()) {
            $io->text('<bg=green>[OK]</> Conexão com o banco de dados está funcionando!');
            $databaseObj = new DatabaseSetup($this->input, $this->output);
        } else {
            $io->text('<fg=white;bg=red>[PROBLEMA]</> Não consegui acessar o banco de dados!');
            $validarObjetos = false;
        }
        

        // Validando objetos
        if(!$validarObjetos) {
            $io->note('Como o Banco ou Api não está acessível, não posso validar os objetos.');
        } else {

            $io->text([
                '',
                'Agora vamos validar os objetos...',
                ''
            ]);

            $bancoProblemas = false;
            foreach(OBJETOS as $key => $dados) {
                $existe = $databaseObj->verificarSeTabelaExiste($key);
                if ($existe) {
                    $estrutura = $databaseObj->retornarEstruturaTabela($key);
                    $objeto = $objetoObj->retornarObjeto($key);
                    $logDiferencas = $databaseObj->compararTabelaObjeto($estrutura, $objeto);
                    $diferencas = $logDiferencas[1];
                    $logs = $logDiferencas[0];
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
            }
        }

        if($bancoProblemas) {
            $io->text([
                '',
                'Encontrei problemas no banco de dados, vamos tentar corrigir?',
                ''
            ]);
            if ($io->confirm('Quer tentar corrigir?', true)) {
                $this->executarCorrecoes($diferencasBanco);
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

    private function executarCorrecoes($diferencasBanco) {
        $io = new CvdwSymfonyStyle($this->input, $this->output);
        $databaseObj = new DatabaseSetup($this->input, $this->output);
        $objetoObj = new Objeto($this->input, $this->output);
        $io->text([
            '',
            'Vamos lá!',
            'Corrigindo as diferenças...',
            ''
        ]);
        foreach($diferencasBanco as $tabela => $diferencas) {
            $io->text('Corrigindo a tabela ' . $tabela);
            $objeto = $objetoObj->retornarObjeto($tabela);
            if(isset($diferencas['add'])){
                foreach($diferencas['add'] as $campo => $estrutura) {
                    $adicionado = $databaseObj->inserirColuna($tabela, $estrutura['nomeTratado'], $estrutura);
                    if($adicionado){
                        $io->text('<bg=green>[OK]</> Adicionando a coluna ' . $estrutura['nomeTratado']);
                    } else {
                        $io->text('<fg=white;bg=red>[PROBLEMA]</> Não foi possível adicionar a coluna ' . $estrutura['nomeTratado']);
                    }
                }
            }
            if(isset($diferencas['remove'])){
                foreach($diferencas['remove'] as $campo => $estrutura) {
                    $coluna = $estrutura->getName();
                    $removido = $databaseObj->removerColuna($tabela, $coluna);
                    if($removido){
                        $io->text('<bg=green>[OK]</> Removendo a coluna ' . $estrutura->getName());
                    } else {
                        $io->text('<fg=white;bg=red>[PROBLEMA]</> Não foi possível remover a coluna ' . $estrutura->getName());
                    }
                }
            }
            if(isset($diferencas['change'])){
                foreach($diferencas['change'] as $coluna => $estrutura) {
                    $estrutura['opcoes']['type'] = $estrutura['type'];
                    $alterado = $databaseObj->alterarColuna($tabela, $estrutura['nomeTratado'], $estrutura['opcoes']);
                    if($alterado){
                        $io->text('<bg=green>[OK]</> Alterando a coluna ' . $estrutura['nomeTratado']);
                    } else {
                        $io->text('<fg=white;bg=red>[PROBLEMA]</> Não foi possível alterar a coluna ' . $estrutura['nomeTratado']);
                    }
                }
            }
            $io->text('');
        }

        $io->text([
            '',
            'Pronto!'
        ]);
    }

    private function configurarCV(): int
    {

        $io = new CvdwSymfonyStyle($this->input, $this->output);
        $io->text([
            'Vamos lá!',
            'Primeiro vamos configurar as variáveis do CV...'
        ]);
        $io->note([
            'Vamos começar pelo endereço de acesso ao CV,',
            'Lembre que não deve ser o endereço completo, somente o subdominio',
            'Ex.: https://SUA_INCORPORADORA.cvcrm.com.br então vamos usar "sua_incorporadora"',
        ]);

        $io->ask('Me diz o subdominio do ambiente seu CV:', $_ENV['CV_URL'], function (string $endereco_cv): string {

            $io = new CvdwSymfonyStyle($this->input, $this->output);

            $http = new \Manzano\CvdwCli\Services\Http($this->input, $this->output, $io);
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
                    throw new \RuntimeException('E-mail inválido, vamos tentar de novo?');
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

        $http = new Http($this->input, $this->output, $io);
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
            salvarEnv($newEnv);

            $io->text('Salvo!');

            retornarEnvs();
            $this->voltarProMenu = true;
            $this->voltarProMenu();

            return 0;
        } else {
            $io->error('Não consegui acessar o ambiente.');
            if ($io->confirm('Quer tentar novamente?', true)) {
                return $this->configurarCV($io);
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

        $this->variaveisAmbiente['banco'] = $io->choice('Que banco de dados você quer conectar?', ['pdo_mysql', 'pdo_pgsql', 'pdo_sqlsrv'], $_ENV['DB_CONNECTION']);
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
            'DB_PASSWORD' => $this->variaveisAmbiente['db_password']
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

        try {
            $conn = DriverManager::getConnection($connectionParams, $config);
            $conn->connect();
            if ($conn->isConnected()) {
                $io->success('Conexão bem-sucedida!');
            } else {
                $io->error('Não foi possível conectar ao banco de dados.');
                if ($io->confirm('Quer tentar novamente?', true)) {
                    return $this->configurarBanco($io);
                }
            }
        } catch (\Exception $e) {
            $io->error('Não foi possível conectar ao banco de dados.');
            $io->error('Encontrei esse erro: ' . $e->getMessage());
            if ($io->confirm('Quer tentar novamente?', true)) {
                return $this->configurarBanco($io);
            }
        }

        $io->text([
            '',
            'Deixa eu salvar as informações...'
        ]);
        salvarEnv($newEnv);

        $this->voltarProMenu = true;
        $this->voltarProMenu();

        return 1;

    }

    private function criarTabelas(): bool
    {

        $io = new CvdwSymfonyStyle($this->input, $this->output);

        $io->text('Serão criadas as tabelas abaixo:');

        $database = new DatabaseSetup($this->input, $this->output);
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

    private function limparTabelas(): bool
    {

        $io = new CvdwSymfonyStyle($this->input, $this->output);
        
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

            $database = new DatabaseSetup($this->input, $this->output);
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
                    if ($io->confirm('Quer tentar novamente?', true)) {
                        $this->limparTabelas($io);
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
                    $progressBar->setMessage('A tabela ' . $tabela . ' não existe');
                    $progressBar->advance();
                    continue;
                }
                $truncate  = $database->limparTabela($tabela);
                if ($truncate) {
                    $table->addRow([$tabela, '<info>Limpa!</info>']);
                    $progressBar->setMessage('A tabela ' . $tabela . ' foi limpa');
                    $progressBar->advance();
                } else {
                    $table->addRow([$tabela, '<error>Ocorreu algum erro!</error>']);
                    $progressBar->setMessage('A tabela ' . $tabela . ' não pode ser limpa');
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
        
        $io->warning([
            '',
            'Essa opção apaga todas as tabelas ok?'
        ]);

        if ($io->confirm('Quer continuar?', false)) {
            $tabela = $io->ask(
                'Qual tabela você quer apagar? (Use all para todas)',
                'all',
                function (string $tabela): string {
                    return $tabela;
                }
            );
            $database = new DatabaseSetup($this->input, $this->output);
            $tabelasApagar = array();
            if ($tabela === 'all') {
                $io->text([
                    'Ok! Vou apagar todas as tabelas.',
                    ''
                ]);
                $tabelasApagar = $database->listarTabelasArray();
            } else {

                if ($database->verificarSeTabelaExiste($tabela)) {
                    $tabelasApagar[$tabela] = "";
                    $io->text([
                        'Encontrei a tabela "' . $tabela . '".',
                        ''
                    ]);
                } else {
                    $io->text([
                        'Não encontrei a tabela "' . $tabela . '".',
                        ''
                    ]);
                    if ($io->confirm('Quer tentar novamente?', true)) {
                        return $this->apagarTabelas($io);
                    }
                }
            }

            $table = new Table($this->output);
            $table->setHeaders(['Tabela', 'Situação']);

            $totalObjetos = count($tabelasApagar);
            $progressBar = new ProgressBar($this->output, $totalObjetos);
            $progressBar->start();

            foreach ($tabelasApagar as $tabela => $valor) {

                $tabelaExiste = $database->verificarSeTabelaExiste($tabela);
                if (!$tabelaExiste) {
                    $table->addRow([$tabela, '<error>Não encontrada!</error>']);
                    $progressBar->setMessage('A tabela ' . $tabela . ' não existe');
                    $progressBar->advance();
                    continue;
                }
                $truncate  = $database->apagarTabela($tabela);
                if ($truncate) {
                    $table->addRow([$tabela, '<info>Apagada!</info>']);
                    $progressBar->setMessage('A tabela ' . $tabela . ' foi limpa');
                    $progressBar->advance();
                } else {
                    $table->addRow([$tabela, '<error>Ocorreu algum erro!</error>']);
                    $progressBar->setMessage('A tabela ' . $tabela . ' foi limpa');
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
