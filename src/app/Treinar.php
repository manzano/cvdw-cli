<?php

namespace Manzano\CvdwCli;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputOption;

use Symfony\Component\Console\Completion\CompletionInput;
use Symfony\Component\Console\Completion\CompletionSuggestions;

use Manzano\CvdwCli\Services\Brain;
use Manzano\CvdwCli\Services\Log;
use Manzano\CvdwCli\Services\Cvdw;
use Manzano\CvdwCli\Services\Console\CvdwSymfonyStyle;
use Manzano\CvdwCli\Services\Monitor\Eventos;
use Manzano\CvdwCli\Services\Ambientes;
use Manzano\CvdwCli\Inc\CvdwException;

#[AsCommand(
    name: 'treinar',
    description: 'Treinar a IA com o CVDW-CLI',
    hidden: false,
    aliases: ['Treinar', 'training', 'Training']
)]
class Treinar extends Command
{
    protected static $defaultName = 'treinar';
    protected $dirLog = null;
    protected $arquivoLog = null;
    protected $logObjeto = false;
    protected InputInterface $input;
    protected OutputInterface $output;
    /**
     * @var string[]
     */
    public array $variaveisAmbiente = [];
    public bool $voltarProMenu = false;
    protected $eventosObj;
    protected $ambientesObj;
    protected $env = null;
    protected $evento = 'Treinar';
    public array $execucoes = [];

    const OPCAO_SAIR = 'Sair (CTRL+C)';

    protected function configure()
    {
        $this->setName('treinar')
            ->setDescription('Treinar a IA com o CVDW-CLI')
            ->addArgument('objeto', InputArgument::OPTIONAL, 'Qual objeto deseja gerar o arquivo de treinamento?')
            ->addOption(
                'salvarlog',
                'log',
                InputOption::VALUE_NONE, // Modo: VALUE_REQUIRED, VALUE_OPTIONAL, VALUE_NONE
                'Salvar Log da execução no diretorio de instalação.',
            )
            ->addOption(
                'set-env',
                'env',
                InputOption::VALUE_OPTIONAL, // Modo: VALUE_REQUIRED, VALUE_OPTIONAL, VALUE_NONE
                'Diz qual ENV usar. Exemplo: dev, homologacao, producao.',
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $this->eventosObj = new Eventos();
        $this->ambientesObj = new Ambientes($this->env);
        $this->ambientesObj->retornarEnvs();

        if ($input->getOption('set-env')) {
            $this->env = $input->getOption('set-env');
        }

        $io = new CvdwSymfonyStyle($input, $output, $this->logObjeto);
        $io->title('Treinando a IA com o CVDW-CLI');

        $versaoCVDW = $this->ambientesObj->retornarVersao();
        $io->text('Versão: ' . $versaoCVDW);

        $ambienteAtivo = $this->ambientesObj->ambienteAtivo();
        $io->text('Ambiente ativo: ' . $ambienteAtivo);

        // Verificar a versão do repositorio
        $cvdwObj = new Cvdw($input, $output, $this);
        $cvdwObj->alertarNovaVersao($versaoCVDW, $io);

        if ($input->getOption('salvarlog')) {
            $this->arquivoLog = 'log_' . date('Y-m-d_H-i-s') . '.log';
            $this->logObjeto = new Log($this->arquivoLog);
            $this->logObjeto->criarArquivoLog();
        }
        
        $this->limparTela();
        $this->validarConfiguracao($io);

        $this->input = $input;
        $this->output = $output;

        $inputBrain = $input->getArgument('objeto');
        if ($inputBrain) {
            $this->executarBrain($io, $inputBrain, false);
            return Command::SUCCESS;
        }

        $io->title('Treinando a IA com o CVDW-CLI');
        $ambienteAtivo = $this->ambientesObj->ambienteAtivo();
        $io->text('Ambiente ativo: ' . $ambienteAtivo);

        $this->eventosObj->registrarEvento($this->evento, 'Início');

        $this->variaveisAmbiente['executar'] = $io->choice('O que deseja fazer agora?', [
            'Listar todos os objetos disponíveis para o treinamento',
            'Executar todos os objetos',
            'Executar um objeto especifico',
            $this::OPCAO_SAIR
        ]);

        if ($this->variaveisAmbiente['executar'] === $this::OPCAO_SAIR) {
            $io->text(['Até mais!', '']);
            return Command::SUCCESS;
        }
        $io->text(['Você escolheu: ' . $this->variaveisAmbiente['executar'], '']);
        $this->eventosObj->registrarEvento($this->evento, $this->variaveisAmbiente['executar']);
        $this->voltarProMenu = true;

        switch ($this->variaveisAmbiente['executar']) {
            case 'Listar todos os objetos disponíveis para o treinamento':
                $this->exibirBrains($io);
                break;
            case 'Executar todos os objetos':
                $this->executarBrain($io, 'all', false);
                break;
            case 'Executar um objeto especifico':
                $this->executarBrainOpcoes($io);
                break;
            default:
                $this->execute($this->input, $this->output);
                break;
        }

        return Command::SUCCESS;
    }

    protected function voltarProMenu($io)
    {
        if ($this->voltarProMenu) {
            if ($io->confirm('Vamos voltar pro menu anterior?', true)) {
                $this->limparTela();
                return $this->execute($this->input, $this->output);
            } else {
                return 0;
            }
        }
    }

    public function validarConfiguracao($io): void
    {
        $envVars = $this->ambientesObj->getEnvEscope();
        // Listar todas as variáveis de $envVars e verificar se todas tem valor
        foreach ($envVars as $envVar => $value) {
            if (!isset($_ENV[$envVar]) || $_ENV[$envVar] == '') {
                $io->error('Configuração não encontrada, invalida ou incompleta. (' . $envVar . ')');
                $io->text(['Por favor use o comando "cvdw configurar" para configurar o CVDW-CLI.']);
                $io->text(['']);
                exit;
            }
        }
    }

    public function exibirBrains($io)
    {

        $brains = new Brain($this->input, $this->output, $this);
        $brainsArray = $brains->retornarBrains();
        $io->section('Objetos disponíveis: ');
        $table = new Table($this->output);
        $table->setHeaders(['Objeto', 'nome']);
        foreach ($brainsArray as $brain => $dados) {
            $table->addRow([$brain, $dados['nome']]);
        }
        $table->render();
        $this->voltarProMenu($io);
    }

    public function executarBrain($io, $inputObjeto, $inputDataReferencia = false)
    {
        if ($this->output->isDebug()) {
            $io->info('## Função: ' . __FUNCTION__);
        }

        if ($this->input->getOption('salvarlog')) {
            $io->text(['Salvando o Log em: ' . $this->arquivoLog]);
            $io->text(['']);
        }

        if ($inputObjeto) {
            if ($inputObjeto == 'all') {
                $io->text(['Executando todos os objetos...']);
                $brains = new Brain($this->input, $this->output, $this);
                $brainsArray = $brains->retornarBrains();
            } else {
                $brains = new Brain($this->input, $this->output, $this);
                $brainsArray = $brains->retornarBrains($inputObjeto);
                if (count($brainsArray) > 0) {
                    $io->text(['Executando objeto: ' . $inputObjeto]);
                } else {
                    $io->error('Objeto não encontrado.');
                    return Command::FAILURE;
                }
            }

            $brainObj = new Brain($this->input, $this->output, $this);
            $brainObj->conectar();
            $brainObj->apagarArquivosBrain();

            foreach ($brainsArray as $brain=> $dados) {
                $brain = $brainObj->retornarBrain($brain);
                $io->section($dados['nome']);
                $io->text('Executando objeto: ' . $dados['nome'] . '');
                $this->eventosObj->registrarEvento($this->evento, 'treinar', $dados['nome']);

                $brainObj->processar($brain, $io);
            }
        } else {
            $io->error('Objeto não especificado.');
        }
        $this->voltarProMenu($io);
        return Command::SUCCESS;
    }

    public function executarBrainOpcoes($io)
    {
        $brains = new Brain($this->input, $this->output, $this);
        $brainsArray = $brains->retornarBrains();

        $brainsOpcoes = array();
        foreach ($brainsArray as $brain=> $dados) {
            $brainsOpcoes[] = $dados['nome'];
        }
        $brainsOpcoes[] =  $this::OPCAO_SAIR;
        $inputBrain = $io->choice('Qual objeto deseja executar?', $brainsOpcoes);

        foreach ($brainsArray as $brain=> $dados) {
            if ($dados['nome'] == $inputBrain) {
                $inputBrain = $brain;
            }
        }

        if ($inputBrain == $this::OPCAO_SAIR) {
            exit;
        }

        $this->limparTela();

        $this->executarBrain($io, $inputBrain);
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
