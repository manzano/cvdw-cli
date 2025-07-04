<?php

namespace Manzano\CvdwCli;

use Manzano\CvdwCli\Inc\CvdwException;
use Manzano\CvdwCli\Services\Ambientes;
use Manzano\CvdwCli\Services\Console\CvdwSymfonyStyle;
use Manzano\CvdwCli\Services\Cvdw;
use Manzano\CvdwCli\Services\Log;
use Manzano\CvdwCli\Services\Objeto;
use Manzano\CvdwCli\Services\RateLimit;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'executar',
    description: 'Configure o CVDW-CLI',
    hidden: false,
    aliases: ['Executar', 'execute', 'Execute']
)]
class Executar extends Command
{
    protected static $defaultName = 'executar';
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
    protected $ambientesObj;
    public $rateLimitObj;
    protected $env = null;
    protected $evento = 'Executar';
    protected $qtd = 500;
    protected $tempoLimiteExecucao = null;
    protected $tempoExecucao = null;
    protected $apartir = null;
    protected $maxpag = null;
    public array $execucoes = [];

    public const OPCAO_SAIR = 'Sair (CTRL+C)';

    protected function configure()
    {
        $this->setName('executar')
            ->setDescription('Executar o CVDW-CLI')
            ->addArgument('objeto', InputArgument::OPTIONAL, 'Qual objeto deseja executar')
            ->addOption(
                'ignorar-data-referencia',
                'idr',
                InputOption::VALUE_NONE, // Modo: VALUE_REQUIRED, VALUE_OPTIONAL, VALUE_NONE
                'Ignorar a data de referência.',
            )
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
            )->addOption(
                'set-qtd',
                'qtd',
                InputOption::VALUE_OPTIONAL, // Modo: VALUE_REQUIRED, VALUE_OPTIONAL, VALUE_NONE
                'Quantidade de dados retornada por cada requisicao.',
            )->addOption(
                'apartir',
                'a',
                InputOption::VALUE_OPTIONAL, // Modo: VALUE_REQUIRED, VALUE_OPTIONAL, VALUE_NONE
                "Consultar a partir de uma data de referencia especifica.\n
                No formato: Y-m-d\TH:i:s ou Y-m-d.",
            )->addOption(
                'max-pag',
                'm',
                InputOption::VALUE_OPTIONAL, // Modo: VALUE_REQUIRED, VALUE_OPTIONAL, VALUE_NONE
                "Executa o número máximo de página informado.",
            )->addOption(
                'tempo-execucao',
                't',
                InputOption::VALUE_OPTIONAL, // Modo: VALUE_REQUIRED, VALUE_OPTIONAL, VALUE_NONE
                "Tempo limite de execução em segundos. (Padrão ilimitado)",
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        if ($input->getOption('set-env')) {
            $this->env = $input->getOption('set-env');
        }

        $this->rateLimitObj = new RateLimit($input, $output, $this);
        $this->rateLimitObj->iniciarExecucao();

        $this->ambientesObj = new Ambientes($this->env);
        $this->ambientesObj->retornarEnvs();

        if ($input->getOption('set-qtd')) {
            $this->qtd = $input->getOption('set-qtd');
        }

        if ($input->getOption('apartir')) {
            $this->apartir = trim($input->getOption('apartir'));
            if (empty($this->apartir)) {
                throw new CvdwException('Data de referência não pode estar vazia.');
            }
            if (! validarData($this->apartir)) {
                throw new CvdwException(
                    'Data de referência informada (' . $this->apartir . ') é inválida. ' .
                    'Use o formato Y-m-d ou Y-m-d\TH:i:s'
                );
            }
        }

        if ($input->getOption('max-pag')) {
            $this->maxpag = $input->getOption('max-pag');
        }

        $console = new CvdwSymfonyStyle($input, $output, $this->logObjeto);
        $console->title('Executando o CVDW-CLI');

        $versaoCVDW = $this->ambientesObj->retornarVersao();
        $console->text('Versão: ' . $versaoCVDW);

        $ambienteAtivo = $this->ambientesObj->ambienteAtivo();
        $console->text('Ambiente ativo: ' . $ambienteAtivo);

        // Verificar a versão do repositorio
        $cvdwObj = new Cvdw($input, $output, $this, $this->rateLimitObj);
        $cvdwObj->alertarNovaVersao($versaoCVDW, $console);

        if ($input->getOption('salvarlog')) {
            $this->arquivoLog = 'log_' . date('Y-m-d_H-i-s') . '.log';
            $this->logObjeto = new Log($this->arquivoLog);
            $this->logObjeto->criarArquivoLog();
        }

        $ignorar = ['DB_SCHEMA', 'ANONIMIZAR', 'ANONIMIZAR_TIPO'];
        $this->ambientesObj->validarConfiguracao($console, $ignorar);

        $this->input = $input;
        $this->output = $output;

        if ($input->getOption('tempo-execucao')) {
            $this->tempoLimiteExecucao = $input->getOption('tempo-execucao');
            $this->rateLimitObj->setTempoLimiteExecucao($this->tempoLimiteExecucao);
            $console->text(['Tempo limite de execução: <fg=red>' . $this->tempoLimiteExecucao . ' segundo(s)</fg=red>']);
        }

        $inputObjeto = $input->getArgument('objeto');
        $inputDataReferencia = $input->getOption('ignorar-data-referencia');
        if ($inputObjeto) {
            $this->executarObjeto($console, $inputObjeto, $inputDataReferencia);

            return Command::SUCCESS;
        }

        $this->variaveisAmbiente['executar'] = $console->choice('O que deseja fazer agora?', [
            'Listar todos os objetos disponíveis no CVDW-CLI',
            'Executar todos os objetos',
            'Executar um objeto especifico',
            'Configurar o CVDW-CLI',
            $this::OPCAO_SAIR,
        ]);

        if ($this->variaveisAmbiente['executar'] === $this::OPCAO_SAIR) {
            $console->text(['Até mais!', '']);

            return Command::SUCCESS;
        }
        $console->text(['Você escolheu: ' . $this->variaveisAmbiente['executar'], '']);
        $this->voltarProMenu = true;

        switch ($this->variaveisAmbiente['executar']) {
            case 'Listar todos os objetos disponíveis no CVDW-CLI':
                $this->exibirObjetos($console);

                break;
            case 'Executar todos os objetos':
                $this->executarObjeto($console, 'all', $inputDataReferencia);

                break;
            case 'Executar um objeto especifico':
                $this->executarObjetoOpcoes($console);

                break;
            case 'Configurar o CVDW-CLI':
                if ($console->confirm('Deseja configurar o CVDW-CLI?') == true) {
                    $console->success('Configurando o CVDW-CLI...');
                    $this->getApplication()->find('configurar')->run($input, $output);

                    return Command::SUCCESS;
                }
                // no break
            default:
                $this->execute($this->input, $this->output);

                break;
        }

        return Command::SUCCESS;
    }

    protected function voltarProMenu($console): int
    {
        if ($this->voltarProMenu) {
            if ($console->confirm('Vamos voltar pro menu anterior?', true)) {
                $this->limparTela();

                return $this->execute($this->input, $this->output);
            } else {
                return Command::SUCCESS;
            }
        }

        return Command::SUCCESS;
    }

    public function exibirObjetos($console)
    {

        $objetos = new Objeto($this->input, $this->output);
        $objetosArray = $objetos->retornarObjetos('all');
        $console->section('Objetos disponíveis: ');
        $table = new Table($this->output);
        $table->setHeaders(['Objeto', 'nome']);
        foreach ($objetosArray as $objeto => $dados) {
            $table->addRow([$objeto, $dados['nome']]);
        }
        $table->render();
        $this->voltarProMenu($console);
    }

    public function executarObjeto($console, $inputObjeto, $inputDataReferencia = false)
    {
        if ($this->output->isDebug() || $this->input->getOption('verbose')) {
            $console->info('## Função: ' . __FUNCTION__);
        }

        if ($this->input->getOption('salvarlog')) {
            $console->text(['Salvando o Log em: ' . $this->arquivoLog]);
            $console->text(['']);
        }

        if ($inputObjeto) {
            if ($inputObjeto == 'all') {
                $console->text(['Executando todos os objetos...']);
                $objetos = new Objeto($this->input, $this->output);
                $objetosArray = $objetos->retornarObjetos('all');
            } else {
                $objetos = new Objeto($this->input, $this->output);
                $objetosArray = $objetos->retornarObjetos($inputObjeto);
                if (count($objetosArray) > 0) {
                    $console->text(['Executando objeto: ' . $inputObjeto]);
                } else {
                    $console->error('Objeto não encontrado.');

                    return Command::FAILURE;
                }
            }

            $objetoObj = new Objeto($this->input, $this->output);
            $cvdw = new Cvdw($this->input, $this->output, $this, $this->rateLimitObj);
            $cvdw->conectar();

            foreach ($objetosArray as $objeto => $dados) {
                $objeto = $objetoObj->retornarObjeto($objeto);
                $console->section($dados['nome']);
                $console->text('Executando objeto: ' . $dados['nome'] . '');

                $cvdw->processar($objeto, $this->qtd, $console, $this->apartir, $inputDataReferencia, $this->logObjeto, $this->maxpag);

            }
        } else {
            $console->error('Objeto não especificado.');
        }
        $this->voltarProMenu($console);

        return Command::SUCCESS;
    }

    public function executarObjetoOpcoes($console)
    {
        $objetos = new Objeto($this->input, $this->output);
        $objetosArray = $objetos->retornarObjetos('all');
        $objetosOpcoes = [];
        foreach ($objetosArray as $objeto => $dados) {
            $objetosOpcoes[] = $dados['nome'];
        }
        $objetosOpcoes[] = $this::OPCAO_SAIR;
        $inputObjeto = $console->choice('Qual objeto deseja executar?', $objetosOpcoes);

        foreach ($objetosArray as $objeto => $dados) {
            if ($dados['nome'] == $inputObjeto) {
                $inputObjeto = $objeto;
            }
        }

        if ($inputObjeto == $this::OPCAO_SAIR) {
            exit;
        }

        $this->limparTela();

        $this->executarObjeto($console, $inputObjeto);
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
