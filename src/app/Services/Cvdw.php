<?php

namespace Manzano\CvdwCli\Services;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\ProgressBar;

use Doctrine\DBAL\Exception;

use DateTime;

use Manzano\CvdwCli\Services\Http;
use Manzano\CvdwCli\Services\DatabaseSetup;
use Manzano\CvdwCli\Services\Objeto;
use Manzano\CvdwCli\Services\Console\CvdwSymfonyStyle;

class Cvdw
{
    protected CvdwSymfonyStyle $io;
    public InputInterface $input;
    public OutputInterface $output;
    public \Doctrine\DBAL\Connection $conn;
    public DatabaseSetup $database;
    public object $resposta;
    public $logObjeto = false;
    public array $objeto;

    public function __construct(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;
        $this->conn = conectarDB($input, $output);
    }

    public function processar(array $objeto, $io, $inputDataReferencia = false, $logObjeto = null): bool
    {

        $this->io = $io;
        if(is_object($logObjeto)) {
            $this->logObjeto = $logObjeto;
        }

        $this->database = new DatabaseSetup($this->input, $this->output);
        $http = new Http($this->input, $this->output, $io, $this->logObjeto);

        if ($inputDataReferencia) {
            $this->io->text('Data de referência: <fg=red>Ignorada</>');
            $parametros = array(
                'pagina' => 1,
                'registros_por_pagina' => 500
            );
        } else {
            $referencia_data = $this->buscaUltimaData($objeto['tabela']);
            
            $parametros = array(
                'pagina' => 1,
                'registros_por_pagina' => 500
            );

            if ($referencia_data) {
                $referencia_data = new DateTime($referencia_data); // Cria um objeto DateTime
                $referencia_data->modify('-1 seconds');
                $referencia_data_UI = $referencia_data->format('d/m/Y H:i:s');
                $referencia_data = $referencia_data->format('Y-m-d H:i:s');
                $parametros['a_partir_data_referencia'] = $referencia_data;
            } else {
                $referencia_data_UI = 'Nenhuma data encontrada';
            }
            
            $this->io->text('Data de referência: ' . $referencia_data_UI);
        }
        $resposta = $http->requestCVDW($objeto['path'], $parametros);

        // Se não existir $resposta->total_de_registros, imprimir uma mensagem de erro;
        if (!isset($resposta->total_de_registros)) {
            $this->io->error([
                'A requisição não retornou os dados esperados!',
                'Parametros: ' . print_r($parametros, true)
            ]);
        } else {

            $paginas = $resposta->total_de_paginas;

            if ($paginas > 0) {

                $this->io->text('Total de registros encontrados: ' . $resposta->total_de_registros);
                $this->io->text('Total de páginas: ' . $resposta->total_de_paginas);

                $progressBar = new ProgressBar($this->output, $resposta->total_de_registros);
                $progressBar->setFormat('normal'); // debug
                $progressBar->setBarCharacter('<fg=green>=</>');
                $progressBar->setProgressCharacter("\xF0\x9F\x9A\x80");
                $progressBar->setFormat(" Dados processados %current% de %max% [%bar%] %percent:3s%% \n %message%");
                $progressBar->setMessage('Dados processados na página: 0');

                $dadosProcessados = 0;
                for ($pagina = 1; $pagina <= $paginas; $pagina++) {

                    if ($pagina > 1) {

                        if ($inputDataReferencia) {
                            $parametros = array(
                                'pagina' => $pagina,
                                'registros_por_pagina' => 500
                            );
                        } else {
                            $parametros = array(
                                'pagina' => $pagina,
                                'registros_por_pagina' => 500,
                                'a_partir_data_referencia' => "$referencia_data"
                            );
                        }
                        $resposta = $http->requestCVDW($objeto['path'], $parametros, $inputDataReferencia);
                    }

                    $progressBar->setMessage('Dados processados na página: ' . $dadosProcessados);
                    $progressBar->display();
                    if(!isset($resposta->dados) && is_array($resposta->dados) && count($resposta->dados) > 0) {
                        $this->io->error([
                            'A requisição não retornou os dados esperados!'
                        ]);
                    } else {

                            $dadosNoPadrao = $this->verificaPadrao($resposta->dados[0]);
                            if(!$dadosNoPadrao) {
                                $progressBar->finish();
                                $messagem = 'Os dados de ' . $objeto['path'] . ' não estão no padrão esperado!';
                                $this->io->error([
                                    $messagem,
                                ]);
                                if(isset($_ENV['CVDW_AMBIENTE']) && $_ENV['CVDW_AMBIENTE'] == 'PRD') {
                                    \Sentry\addBreadcrumb(
                                        category: 'CVDW',
                                        metadata: ['acao' => 'processar']
                                    );
                                    \Sentry\captureMessage($messagem);
                                }

                                break;
                            }

                            foreach ($resposta->dados as $linha) {
                                //print_r($linha);
                                $dadosProcessados++;

                                if ($this->processaSql($objeto, $linha)) {
                                    $progressBar->setMessage('Dados processados na página: ' . $dadosProcessados);
                                    $progressBar->advance();
                                    $progressBar->display();
                                }
                            }
                    }

                    // Precisa aguardar 3 segundos para não dar erro de limite de requisições
                    // CV Bloqueia se for feito mais que 20 requisições por minuto
                    for($i=3; $i>0; $i--) {
                        if($i == 1) {
                            $progressBar->setMessage('<fg=green>'.$i.' segundo para a próxima requisição...</>');
                        } else {
                            $progressBar->setMessage('<fg=green>'.$i.' segundos para a próxima requisição...</>');
                        }
                        $progressBar->display();
                        sleep(1);
                    }
                    $progressBar->setMessage('Executando próxima requisiçao...');
                    $progressBar->display();
                }
                $progressBar->finish();
            } else {
                $this->io->text('<fg=green>Nenhuma informação nova foi encontrada!</fg=green>');
                sleep(4);
            }
        }

        $this->io->newLine();

        return true;
    }

    protected function verificaPadrao(object $linha): bool
    {
        $dadosNoPadrao = true;
        if(!isset($linha->referencia) || !isset($linha->referencia_data)) {
            $dadosNoPadrao = false;
        }
        return $dadosNoPadrao;
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

    protected function executaUpdate(array $objeto, object $linha): bool
    {
        $linha = $this->trataDados($objeto, $linha);
        $queryBuilder = $this->conn->createQueryBuilder();
        $queryBuilder->update($objeto['tabela']);

        $indice = 0;
        
        foreach ($objeto['response']['dados'] as $colunaInd => $valor) {

            
            $nomeColuna = $this->database->tratarNomeColuna($colunaInd, $valor);

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

            if (!is_null($this->logObjeto) && is_object($this->logObjeto)) {
                $this->logObjeto->escreverLog("  - Atualizado: " . $linha->referencia);
            }
        } catch (Exception $e) {
            $this->io->error([
                'Erro ao tentar executar o SQL! (Update)',
                'Objeto: ' . print_r($linha, true),
                'SQL: ' . $queryBuilder->getSQL(),
                'Erro: ' . $e->getMessage()
            ]);
            if(isset($_ENV['CVDW_AMBIENTE']) && $_ENV['CVDW_AMBIENTE'] == 'PRD') {
                \Sentry\addBreadcrumb(
                    category: $objeto['tabela'],
                    metadata: ['acao' => 'update']
                );
                \Sentry\captureException($e);
            }
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
            $nomeColuna = $this->database->tratarNomeColuna($colunaInd, $valor);

            if (isset($linha->$colunaInd) && !is_array($linha->$colunaInd)) {
                $queryBuilder->setValue($nomeColuna, '?');
                $queryBuilder->setParameter($indice, $linha->$colunaInd);
                $indice++;
            }
        }

        try {
            $queryBuilder->executeStatement();

            if ($this->logObjeto) {
                $this->logObjeto->escreverLog("  - Inserido: " . $linha->referencia);
            }
        } catch (Exception $e) {
            $this->io->error([
                'Erro ao tentar executar o SQL! (Insert)',
                'Objeto: ' . print_r($linha, true),
                'SQL: ' . $queryBuilder->getSQL(),
                'Erro: ' . $e->getMessage()
            ]);
            if(isset($_ENV['CVDW_AMBIENTE']) && $_ENV['CVDW_AMBIENTE'] == 'PRD') {
                \Sentry\addBreadcrumb(
                    category: $objeto['tabela'],
                    metadata: ['acao' => 'insert']
                );
                \Sentry\captureException($e);
            }
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
                if ($valor["type"] == "number" && $linha->$coluna != null) {
                    $linha->$coluna = number_format($linha->$coluna, 2, '.', '');
                }
            }
        }
        return $linha;
    }
}
