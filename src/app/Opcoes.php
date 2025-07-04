<?php

namespace Manzano\CvdwCli;

use Manzano\CvdwCli\Services\Ambientes;
use Manzano\CvdwCli\Services\Console\CvdwSymfonyStyle;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

// Classe de exemplo de comando para listar comandos
class Opcoes extends Command
{
    protected static $defaultName = 'opcoes';

    /**
     * @var string[]
     */
    public array $opcoes = [];

    protected function configure()
    {
        $this->setDescription('Lista os comandos disponíveis.')
            ->setHelp('Este comando lista os comandos disponíveis no aplicativo CVDW-CLI.')
            ->addOption(
                'dir',
                'diretorio',
                InputOption::VALUE_NONE, // Modo: VALUE_REQUIRED, VALUE_OPTIONAL, VALUE_NONE
                'Imprime o diretorio onde o CVDW-CLI está instalado.',
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $console = new CvdwSymfonyStyle($input, $output);

        $ambientesObj = new Ambientes();
        $versaoCVDW = $ambientesObj->retornarVersao();

        // Aqui você pode personalizar a saída do comando de ajuda
        $console->title('Manzano // CVDW CLI '.$versaoCVDW);

        if ($input->getOption('dir')) {
            $dir = __DIR__;
            // Remover /src/app de $dir
            $dir = str_replace('/src/app', '', $dir);
            $console->section('Instalação:');
            $console->text([
                'Diretório: ' . $dir,
            ]);
        }

        $console->section('Como usar:');
        $console->text([
            'cvdw [comando] [opcao] [argumento]',
        ]);

        $console->section('Opções:');
        $console->listing([
            '-h, --help                   - Mostra uma ajuda para o comando selecionado.',
            '-q, --quiet                  - Não mostra nenhum mensagem no output.',
            '-V, --version                - Exibe a versão da aplicação.',
            '--ansi|--no-ansi             - Força (ou desabilita --no-ansi) ANSI output.',
            '-n, --no-interaction         - Não faz nenhuma pergunta na execução do comando.',
        ]);

        $console->section('Comandos disponíveis:');
        $console->listing([
            'cvdw configurar              - Configurações do aplicativo',
            'cvdw executar                - Executar o CVDW-CLI',
        ]);

        $console->section('Exemplos do Executar:');
        $console->listing([
            'cvdw executar listar-opcoes  - Lista todos os objetos disponíveis no CVDW-CLI',
            'cvdw executar all            - Executa todos os objetos disponíveis no CVDW-CLI',
            'cvdw executar -q all         - Executa todos os objetos disponíveis sem mostrar mensagens no output',
            'cvdw executar [objeto]       - Executa um objeto especifico no CVDW-CLI',
        ]);

        return Command::SUCCESS;
    }

}
