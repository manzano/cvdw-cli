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
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\ProgressIndicator;

use Doctrine\DBAL\Schema\Table as SchemaTable;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Statement;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Exception;

use DateTime;

use Manzano\CvdwCli\Services\Http;
use Manzano\CvdwCli\Services\DatabaseSetup;
use Manzano\CvdwCli\Services\Objeto;

class Cvdw
{
    protected SymfonyStyle $io;
    public InputInterface $input;
    public OutputInterface $output;
    public \Doctrine\DBAL\Connection $conn;
    public DatabaseSetup $database;
    public object $resposta;
    public array $objeto;

    public function __construct(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
        $this->input = $input;
        $this->output = $output;
        $this->conn = conectarDB($input, $output);
    }

    public function processar(array $objeto): bool
    {

        $this->database = new DatabaseSetup($this->input, $this->output);

        $http = new Http($this->input, $this->output);
        $referencia_data = $this->buscaUltimaData($objeto['tabela']);
        if ($referencia_data) {
            $referencia_data = new DateTime($referencia_data); // Cria um objeto DateTime a partir da sua data de referência
            $referencia_data->modify('+1 seconds'); // Subtrai X segundo(s)
            // Formata a data para o formato desejado e atribui de volta à variável
            $referencia_data = $referencia_data->format('d/m/Y H:i:s');
        }

        $this->io->text('Data de referência: ' . $referencia_data);

        $parametros = array(
            'pagina' => 1,
            'registros_por_pagina' => 500,
            'a_partir_data_referencia' => "$referencia_data"
        );
        $resposta = $http->requestCVDW($objeto['path'], $parametros);

        // Se não existir $resposta->total_de_registros, imprimir uma mensagem de erro;
        if (!isset($resposta->total_de_registros)) {
            $this->io->error([
                'A requisição não retornou os dados esperados!',
                'Parametros: ' . print_r($parametros, true)
            ]);
            exit;
        }

        $paginas = $resposta->total_de_paginas;

        if ($paginas > 0) {

            $this->io->text('Total de registros encontrados: ' . $resposta->total_de_registros);
            $this->io->text('Total de páginas: ' . $resposta->total_de_paginas);

            $progressBar = new ProgressBar($this->output, $resposta->total_de_paginas);
            $progressBar->setFormat('normal'); // debug
            $progressBar->setBarCharacter('<fg=green>=</>');
            $progressBar->setProgressCharacter("\xF0\x9F\x9A\x80");
            $progressBar->setFormat(" Página %current%/%max% [%bar%] %percent:3s%% \n %message%");
            $progressBar->setMessage('Dados processados: 0');

            $dadosProcessados = 0;
            for ($pagina = 1; $pagina <= $paginas; $pagina++) {

                if ($pagina > 1) {
                    $parametros = array(
                        'pagina' => $pagina,
                        'registros_por_pagina' => 500,
                        'a_partir_data_referencia' => "$referencia_data"
                    );
                    $resposta = $http->requestCVDW($objeto['path'], $parametros);
                }

                $progressBar->setMessage('Dados processados: XXX ' . $dadosProcessados);
                foreach ($resposta->dados as $linha) {
                    //print_r($linha);
                    $dadosProcessados++;
                    if($this->processaSql($objeto, $linha))
                    {
                        $progressBar->setMessage('Dados processados: ' . $dadosProcessados);
                    }
                }
                $progressBar->advance();
                // Precisa aguardar 4 segundos para não dar erro de limite de requisições
                // CV Bloqueia se for feito mais que 20 requisições por minuto
                sleep(4);
            }
            $progressBar->finish();

        } else {
            $this->io->text('<fg=green>Nenhuma informação nova foi encontrada!</fg=green>');
            sleep(4);
        }

        $this->io->newLine();

        return true;
    }

    protected function buscaUltimaData(string $tabela): ?string
    {
        // Buscar com o Doctrine o último registro inserido pela data
        $queryBuilder = $this->conn->createQueryBuilder();
        $queryBuilder
            ->select("MAX(referencia_data) as referencia_data")
            ->from($tabela);
        $stmt = $queryBuilder->executeQuery();
        $dados = $stmt->fetchAssociative();
        
        return $dados['referencia_data'];
    }

    protected function verificaSeExiste(string $tabela, int $referencia): bool
    {
        $queryBuilder = $this->conn->createQueryBuilder();
        $queryBuilder
            ->select("COUNT(referencia) as total")
            ->from($tabela)
            ->where("referencia = :referencia")
            ->setParameter('referencia', $referencia);
        //echo $queryBuilder->getSQL();
        $stmt = $queryBuilder->executeQuery();
        $row = $stmt->fetchAssociative();
        if ($row['total'] > 0) {
            return true;
        }
        return false;
    }

    protected function processaSql(array $objeto, object $linha): bool
    {
        $tabela = $objeto['tabela'];
        $existe = $this->verificaSeExiste($tabela, $linha->referencia);
        if ($existe) {
            $this->executaUpdate($objeto, $linha);
        } else {
            $this->executaInsert($objeto, $linha);
        }
        return true;
    }

    protected function executaUpdate(array $objeto, object $linha): bool {
        $linha = $this->trataDados($objeto, $linha);
        $queryBuilder = $this->conn->createQueryBuilder();
        $queryBuilder->update($objeto['tabela']);

        $indice = 0;

        foreach ($objeto['response']['dados'] as $colunaInd => $valor) {

            $nomeColuna = $this->database->tratarNomeColuna($colunaInd);

            if (isset($linha->$colunaInd) && !is_array($linha->$colunaInd)) {

                $queryBuilder->set($nomeColuna, ':valor' . $indice);
                $queryBuilder->setParameter('valor' . $indice, $linha->$colunaInd);
                $indice++;
            }
        }

        $queryBuilder->where('referencia = :referencia');
        $queryBuilder->setParameter('referencia', $linha->referencia);

        try {
            $queryBuilder->executeStatement();
        } catch (Exception $e) {
            $this->io->error([
                'Erro ao tentar executar o SQL! (Update)',
                'Objeto: ' . print_r($linha, true),
                'SQL: ' . $queryBuilder->getSQL(),
                'Erro: ' . $e->getMessage()
            ]);
            exit;
        }
        return true;
    }

    protected function executaInsert(array $objeto, object $linha): bool
    {
        $linha = $this->trataDados($objeto, $linha);
        $queryBuilder = $this->conn->createQueryBuilder();
        $queryBuilder->insert($objeto['tabela']);

        $indice = 0;
        foreach ($objeto['response']['dados'] as $colunaInd => $valor) {
            $nomeColuna = $this->database->tratarNomeColuna($colunaInd);

            if (isset($linha->$colunaInd) && !is_array($linha->$colunaInd)) {
                $queryBuilder->setValue($nomeColuna, '?');
                $queryBuilder->setParameter($indice, $linha->$colunaInd);
                $indice++;
            }
        }

        try {
            $queryBuilder->executeStatement();
        } catch (Exception $e) {
            $this->io->error([
                'Erro ao tentar executar o SQL! (Insert)',
                'Objeto: ' . print_r($linha, true),
                'SQL: ' . $queryBuilder->getSQL(),
                'Erro: ' . $e->getMessage()
            ]);
            exit;
        }

        return true;
    }

    protected function trataDados(array $objeto, object $linha): object
    {
        $objetoObj = new Objeto($this->input, $this->output);
        foreach ($objeto['response']['dados'] as $coluna => $valor) {
            if (isset($linha->$coluna) && $objetoObj->identificarTipoDeDados($valor) !== "TABELA") {

                if (strpos($coluna, "data") !== false) {
                    if ($linha->$coluna == "0000-00-00 00:00:00") {
                        $linha->$coluna = null;
                    }
                    if ($linha->$coluna == "0000-00-00") {
                        $linha->$coluna = null;
                    }
                }
                if ($coluna == 'referencia_data' && !isset($linha->$coluna)) {
                    $linha->$coluna = date('Y-m-d H:i:s');
                }
                if ($valor["type"] == "int") {
                    $linha->$coluna = substr($linha->$coluna, 0, 11);
                    $linha->$coluna = intval($linha->$coluna);
                }
                if ($linha->$coluna == '') {
                    $linha->$coluna = null;
                }
                if ($valor["type"] == "number") {
                    $linha->$coluna = number_format($linha->$coluna, 2, '.', '');
                }
            }
        }
        return $linha;
    }
}
