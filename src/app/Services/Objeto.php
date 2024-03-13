<?php

namespace Manzano\CvdwCli\Services;

use Manzano\CvdwCli\Services\Console\CvdwSymfonyStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Yaml\Yaml;

class Objeto
{

    protected CvdwSymfonyStyle $io;
    public InputInterface $input;
    public OutputInterface $output;

    public function __construct(InputInterface $input, OutputInterface $output)
    {
        $this->io = new CvdwSymfonyStyle($input, $output);
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

    public function retornarObjetoTabelas($objeto) : array
    {
        $objeto = $this->retornarObjeto($objeto);
        $componentes = $objeto['response']['dados'];
        $tabelas = [];
        foreach ($componentes['body']['response']['dados'] as $componente => $dados) {
            if ($this->identificarTipoDeDados($dados) == "TABELA") {
                $tabelas[$componente] = $dados;
            }
        }
        return $tabelas;
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
