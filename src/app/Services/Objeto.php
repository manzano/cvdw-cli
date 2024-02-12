<?php

namespace Manzano\CvdwCli\Services;

use Manzano\CvdwCli\Configuracoes;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Helper\Table;

use Symfony\Component\Yaml\Yaml;

class Objeto
{

    protected SymfonyStyle $io;
    public InputInterface $input;
    public OutputInterface $output;

    public function __construct(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
        $this->input = $input;
        $this->output = $output;
    }

    public function retornarObjetos(string $objeto = null) : array
    {
        if ($objeto) {
            if (isset(OBJETOS[$objeto])) {
                return ["$objeto" => OBJETOS[$objeto]];
            } else {
                return [];
            }
        } else {
            return OBJETOS;
        }
    }

    public function retornarObjeto(string $objeto, string $formato = 'json') : array
    {
        // Verifica se o objeto existe em OBJETOS
        if (!array_key_exists($objeto, OBJETOS)) {
            return [];
        } else {
            $objetoFile = __DIR__ . "/../Objetos/{$objeto}.yaml";
            $objeto = file_get_contents($objetoFile);
            return Yaml::parse($objeto);
        }
    }

    public function identificarTipoDeDados(array $dados) : string
    {
        foreach ($dados as $valor) {
            if (is_array($valor)) {
                return "TABELA";
            }
        }
        return "COMPONENTE";
    }


}
