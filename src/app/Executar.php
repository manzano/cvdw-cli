<?php

namespace Manzano\CvdwCli;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Style\SymfonyStyle;

use Symfony\Component\Console\Completion\CompletionInput;
use Symfony\Component\Console\Completion\CompletionSuggestions;

use Manzano\CvdwCli\Services\Objeto;
use Manzano\CvdwCli\Services\CVDW;

#[AsCommand(
    name: 'executar',
    description: 'Configure o CVDW-CLI',
    hidden: false,
    aliases: ['Executar', 'execute', 'Execute']
)]
class Executar extends Command
{
    protected static $defaultName = 'executar';
    protected InputInterface $input;
    protected OutputInterface $output;
    /**
     * @var string[]
     */
    public array $variaveisAmbiente = [];
    public bool $voltarProMenu = false;

    protected function configure()
    {
        $this->setName('executar')
            ->setDescription('Executar o CVDW-CLI')
            ->addArgument('objeto', InputArgument::OPTIONAL, 'Qual objeto deseja executar');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
    
        $this->limparTela();
        
        $io = new SymfonyStyle($input, $output);
        $this->input = $input;
        $this->output = $output;

        $inputObjeto = $input->getArgument('objeto');
        if ($inputObjeto) {
            $this->executarObjeto($io, $inputObjeto);
            return Command::SUCCESS;
        }

        $this->variaveisAmbiente['executar'] = $io->choice('O que deseja fazer agora?', [
            'Listar todos os objetos disponíveis no CVDW-CLI',
            'Executar todos os objetos',
            'Executar um objeto especifico',
            'Sair (CTRL+C)'
        ]);

        if ($this->variaveisAmbiente['executar'] === 'Sair (CTRL+C)') {
            $io->text(['Até mais!', '']);
            return Command::SUCCESS;
        }
        $io->text(['Você escolheu: ' . $this->variaveisAmbiente['executar'], '']);
        $this->voltarProMenu = true;

        switch ($this->variaveisAmbiente['executar']) {
            case 'Listar todos os objetos disponíveis no CVDW-CLI':
                $this->exibirObjetos($io);
                break;
            case 'Executar todos os objetos':
                $this->executarObjeto($io, 'all');
                break;
            case 'Executar um objeto especifico':
                $this->executarObjetoOpcoes($io);
                break;
            default:
                $this->execute($this->input, $this->output);
                break;
        }

        return Command::SUCCESS;
    }

    protected function voltarProMenu($io)
    {
        if($this->voltarProMenu){
            if ($io->confirm('Vamos voltar pro menu anterior?', true)) {
                $this->limparTela();
                return $this->execute($this->input, $this->output);

            } else {
                return 0;
            }
        }
    }

    public function exibirObjetos($io)
    {
        $objetos = new Objeto($this->input, $this->output);
        $objetosArray = $objetos->retornarObjetos();
        $io->section('Objetos disponíveis: ');
        $table = new Table($this->output);
        $table->setHeaders(['Objeto', 'nome']);
        foreach ($objetosArray as $objeto => $dados) {
            $table->addRow([$objeto, $dados['nome']]);
        }
        $table->render();
        $this->voltarProMenu($io);
    }

    public function executarObjeto($io, $inputObjeto)
    {

        if ($inputObjeto) {
            if ($inputObjeto == 'all') {
                $io->text(['Executando todos os objetos...']);
                $objetos = new Objeto($this->input, $this->output);
                $objetosArray = $objetos->retornarObjetos();
            } else {
                $objetos = new Objeto($this->input, $this->output);
                $objetosArray = $objetos->retornarObjetos($inputObjeto);
                if (is_array($objetosArray) && count($objetosArray) > 0) {
                    $io->text(['Executando objeto: ' . $inputObjeto]);
                } else {
                    $io->error('Objeto não encontrado.');
                    return Command::FAILURE;
                }
            }

            $objetoObj = new Objeto($this->input, $this->output);
            $cvdw = new \Manzano\CvdwCli\Services\Cvdw($this->input, $this->output);

            foreach ($objetosArray as $objeto => $dados) {
                $objeto = $objetoObj->retornarObjeto($objeto);
                $io->section($dados['nome']);
                $io->text('Executando objeto: ' . $dados['nome'] . ' (' . $objeto['subschema'] . ')');
                $cvdw->processar($objeto);
                //$this->limparTela();
            }
        } else {
            $io->error('Objeto não especificado.');
        }

        $this->voltarProMenu($io);

        return Command::SUCCESS;
    }

    public function executarObjetoOpcoes($io)
    {
        $objetos = new Objeto($this->input, $this->output);
        $objetosArray = $objetos->retornarObjetos();

        $objetosOpcoes = array();
        foreach ($objetosArray as $objeto => $dados) {
            $objetosOpcoes[] = $dados['nome'];
        }
        $objetosOpcoes[] =  'Sair (CTRL+C)';
        $inputObjeto = $io->choice('Qual objeto deseja executar?', $objetosOpcoes);

        foreach ($objetosArray as $objeto => $dados) {
            if($dados['nome'] == $inputObjeto){
                $inputObjeto = $objeto;
            }
        }

        $this->limparTela();

        $this->executarObjeto($io, $inputObjeto);

    }

    public function complete(CompletionInput $input, CompletionSuggestions $suggestions): void
    {

        if ($input->mustSuggestOptionValuesFor('executar')) {
            // Sugestões para a opção 'role'
            $suggestions->suggestValues(['ROLE_USER', 'ROLE_ADMIN']);
        }
    }

    protected function limparTela() : void
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
