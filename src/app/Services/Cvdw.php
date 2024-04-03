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

    public int $processados;
    public int $erros;
    public int $inseridos = 0;
    public int $inseridos_erros = 0;
    public int $alterados = 0;
    public int $alterados_erros = 0;
    public array $execucoes = [];
    public int $qtd = 500;

    public function __construct(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;
        $this->conn = conectarDB($input, $output);
    }

    public function processar(array $objeto, int $qtd, $io, $inputDataReferencia = false, $logObjeto = null): bool
    {
        if ($this->output->isDebug()) {
            $io->info(" LOG: " . __FUNCTION__);
        }
        if($qtd > 0) {
            $this->qtd = $qtd;
            if($qtd > 500) {
                $this->io->warning('Quantidade de registros por página maior que 500!');
                $this->qtd = 500;
            }
        }
        $this->io = $io;
        if (is_object($logObjeto)) {
            $this->logObjeto = $logObjeto;
        }
        $this->database = new DatabaseSetup($this->input, $this->output);
        $http = new Http($this->input, $this->output, $io, $this->logObjeto);
        $parametros = array(
            'pagina' => 1,
            'registros_por_pagina' => $this->qtd
        );
        if ($inputDataReferencia) {
            $this->io->text('Data de referência: <fg=red>Ignorada</>');
        } else {
            $referencia_data = $this->buscaUltimaData($objeto['tabela']);
            if ($referencia_data) {
                $referencia_data = new DateTime($referencia_data); // Cria um objeto DateTime
                $referencia_data->modify('-0 seconds');
                $referencia_data_UI = $referencia_data->format('d/m/Y H:i:s');
                $referencia_data = $referencia_data->format('Y-m-d H:i:s');
                $parametros['a_partir_data_referencia'] = $referencia_data;
            } else {
                $referencia_data_UI = 'Nenhuma data encontrada';
            }
            $this->io->text('Data de referência: ' . $referencia_data_UI);
        }
        $this->processados = $this->erros = 0;
        $this->inseridos = $this->inseridos_erros = 0;
        $this->alterados = $this->alterados_erros = 0;

        $this->execucoes[] = time();
        $resposta = $http->requestCVDW($objeto['path'], $parametros);
        // Se não existir $resposta->total_de_registros, imprimir uma mensagem de erro;
        if (!isset($resposta->total_de_registros)) {
            $this->io->error([
                'A requisição não retornou os dados esperados!',
                'Parametros: ' . print_r($parametros, true)
            ]);
        } else {
            $this->io->text('Registros encontrados: ' . $resposta->total_de_registros);
            $this->io->text('Total de páginas: ' . $resposta->total_de_paginas);
            $progressBar = new ProgressBar($this->output, $resposta->total_de_registros);
            $paginas = $resposta->total_de_paginas;
            if ($paginas > 0) {
                $progressBar->setFormat('normal'); // debug
                $progressBar->setBarCharacter('<fg=green>=</>');
                $progressBar->setProgressCharacter("\xF0\x9F\x9A\x80");
                $progressBar->setFormat(" Dados processados %current% de %max% [%bar%] %percent:3s%% \n %message%");
                $progressBar->setMessage($this->getMensagem());
                $this->processados = 0;
                for ($pagina = 1; $pagina <= $paginas; $pagina++) {
                    if ($this->getLimiteErros()) {
                        break;
                    }
                    if ($pagina > 1) {
                        $parametros['pagina'] = $pagina;
                        if ($inputDataReferencia) {
                            unset($parametros['a_partir_data_referencia']);
                        } else {
                            $parametros['a_partir_data_referencia'] = $referencia_data;
                        }
                        $this->execucoes[] = time();
                        $resposta = $http->requestCVDW($objeto['path'], $parametros, $inputDataReferencia);
                    }
                    $progressBar->setMessage($this->getMensagem());
                    $progressBar->display();
                    if (!isset($resposta->dados) && is_array($resposta->dados) && count($resposta->dados) > 0) {
                        $this->io->error([
                            'A requisição não retornou os dados esperados!'
                        ]);
                    } else {
                        $dadosNoPadrao = $this->verificaPadrao($resposta->dados[0]);
                        if (!$dadosNoPadrao) {
                            $progressBar->finish();
                            $mensagem = 'Os dados de ' . $objeto['path'] . ' não estão no padrão esperado!';
                            $this->io->error([
                                $mensagem,
                            ]);
                            $metadata = [
                                'acao' => 'executar',
                                'cv_url' => $_ENV['CV_URL'],
                                'path' => $objeto['path'],
                                'tabela' => $objeto['tabela'],
                                'referencia' => null
                            ];
                            salvarEventoErro(null, 'CVDW', $metadata, $mensagem, $resposta);
                            break;
                        }
                        foreach ($resposta->dados as $linha) {
                            $this->processados++;
                            $this->processaSql($objeto, $linha);
                            $progressBar->setMessage($this->getMensagem());
                            $progressBar->display();
                            $progressBar->advance();
                            if($this->getLimiteErros()){
                                break;
                            }
                        }
                    }
                    $segundos = $this->gerenciarRateLimit();
                    $this->aguardar($progressBar, $segundos);
                    $progressBar->setMessage($this->getMensagem(' Executando próxima requisição...'));
                    $progressBar->display();
                }
                $progressBar->finish();
            } else {
                $this->io->text('<fg=green>Nenhuma informação nova foi encontrada!</fg=green>');
                $progressBar->setMessage($this->getMensagem());
                $segundos = $this->gerenciarRateLimit();
                $this->aguardar($progressBar, $segundos);
            }
        }
        $this->io->newLine();
        return true;
    }

    protected function gerenciarRateLimit(): int {

        $agora = time();
        while (!empty($this->execucoes) && $agora - $this->execucoes[0] > 60) {
            array_shift($this->execucoes);
        }

        $esperar = 0;
        if (count($this->execucoes) >= 20) {
            // Calcula o tempo a esperar: diferença para completar um minuto desde a primeira execução no array
            $esperar = 60 - ($agora - $this->execucoes[0]);
            // Após a espera, remove a execução mais antiga e permite a nova
            array_shift($this->execucoes);
        }

        return $esperar;

    }

    protected function getLimiteErros(): bool
    {
        $qtdErros =  $this->erros;
        if ($qtdErros >= 3) {
            return true;
        } else {
            return false;
        }
    }

    protected function getMensagem($info = false, $erro = false): string
    {
        
        // Se inseridos_erros for maior que 1, imprimir o (s)
        $mensagem = "";
        //$mensagem .= "Processados: " . $this->processados . "\n";
        $mensagem .= "Inseridos: <fg=green>" . $this->inseridos . " sucesso" . (($this->inseridos > 1) ? 's' : '') . "</fg=green> / ";
        $mensagem .= "<fg=red>" . $this->inseridos_erros . " erro" . (($this->inseridos_erros > 1) ? 's' : '') . "</fg=red> \n";
        $mensagem .= " Alterados: <fg=green>" . $this->alterados . " sucesso" . (($this->alterados > 1) ? 's' : '') . "</fg=green> / ";
        $mensagem .= "<fg=red>" . $this->alterados_erros . " erro" . (($this->alterados_erros > 1) ? 's' : '') . "</fg=red> \n";

        if ($info) {
            $mensagem .= "\n";
            $mensagem .= "<fg=blue>" . $info . "</fg=blue> \n";
        }

        if ($erro) {
            $mensagem .= "\n";
            $mensagem .= "<fg=red>" . $erro . "</fg=red> \n";
        }

        return $mensagem;
    }

    protected function aguardar($progressBar, int $segundos = 3): void
    {

        $mensagem = null;
        for ($i = $segundos; $i > 0; $i--) {
            if ($i == 1) {
                $mensagem = ' <fg=blue>Aguardando ' . $i . ' segundo para a próxima requisição...</>';
            } else {
                $mensagem = ' <fg=blue>Aguardando ' . $i . ' segundos para a próxima requisição...</>';
            }
            $mensagem .= "\n <fg=gray>Proteção contra o Rate Limit do servidor. (20req/min)</>";
            $mensagem = $this->getMensagem($mensagem);
            $progressBar->setMessage($mensagem);
            $progressBar->display();
            sleep(1);
        }
        $progressBar->setMessage($this->getMensagem($mensagem));
    }

    protected function verificaPadrao(object $linha): bool
    {
        
        $dadosNoPadrao = true;
        if (!isset($linha->referencia) || !isset($linha->referencia_data)) {
            $dadosNoPadrao = false;
        }
        return $dadosNoPadrao;
    }

    protected function buscaUltimaData(string $tabela): ?string
    {

        try {
            // Buscar com o Doctrine o último registro inserido pela data
            $queryBuilder = $this->conn->createQueryBuilder();
            $queryBuilder
                ->select("MAX(referencia_data) as referencia_data")
                ->from($tabela);
            $stmt = $queryBuilder->executeQuery();
            $dados = $stmt->fetchAssociative();
        } catch (\Doctrine\DBAL\Exception $e) {
            // Trata o caso em que a tabela não existe ou outro erro de banco de dados ocorre
            $this->io->error("Erro ao buscar a última data na tabela '$tabela': " . $e->getMessage());
            return null;
        } catch (\Exception $e) {
            // Captura outras exceções genéricas
            $this->io->error("Erro inesperado: " . $e->getMessage());
            return null;
        }
        return $dados['referencia_data'];
    }

    protected function verificaSeExiste(string $tabela, int $referencia): string
    {
        try {
            $queryBuilder = $this->conn->createQueryBuilder();
            $queryBuilder
                ->select("COUNT(referencia) as total")
                ->from($tabela)
                ->where("referencia = :referencia")
                ->setParameter('referencia', $referencia);
            $stmt = $queryBuilder->executeQuery();
            $row = $stmt->fetchAssociative();
            if($row['total'] > 0) {
                return 'existe';
            } else {
                return 'nao_existe';
            }
        } catch (\Doctrine\DBAL\Exception $e) {
            // Trata o caso em que a tabela não existe ou outro erro de banco de dados ocorre
            $this->io->error("Erro ao verificar se o dado existe em '$tabela': " . $e->getMessage());
            return false;
        } catch (\Exception $e) {
            // Captura outras exceções genéricas
            $this->io->error("Erro inesperado: " . $e->getMessage());
            return false;
        }
    }

    protected function processaSql(array $objeto, object $linha): bool
    {
        $tabela = $objeto['tabela'];
        $existe = $this->verificaSeExiste($tabela, $linha->referencia);
        if ($existe == 'existe') {
            $retorno = $this->executaUpdate($objeto, $linha);
            if ($retorno) {
                $this->alterados++;
            } else {
                $this->alterados_erros++;
                $this->erros++;
            }
        } elseif($existe == 'nao_existe') {
            $retorno = $this->executaInsert($objeto, $linha);
            if ($retorno) {
                $this->inseridos++;
            } else {
                $this->inseridos_erros++;
                $this->erros++;
            }
        } else {
            $this->erros++;
            $retorno = false;
        }
        $this->processados++;
        return $retorno;
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
            $retorno = true;
        } catch (Exception $e) {
            $erroMsg = [
                'Erro ao tentar executar o SQL! (Update)',
                'Objeto: ' . print_r($linha, true),
                'SQL: ' . $queryBuilder->getSQL(),
                'Erro: ' . $e->getMessage()
            ];
            $this->io->error($erroMsg);
            // Mapeando o erro
            $metadata = [
                'acao' => 'update',
                'cv_url' => $_ENV['CV_URL'],
                'path' => $objeto['path'],
                'tabela' => $objeto['tabela'],
                'referencia' => $linha->referencia
            ];
            salvarEventoErro($e, $objeto, $metadata, $erroMsg);
            $retorno = false;
        }
        return $retorno;
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
            $retorno = true;
        } catch (Exception $e) {
            $erroMsg = [
                'Erro ao tentar executar o SQL! (Insert)',
                'Objeto: ' . print_r($linha, true),
                'SQL: ' . $queryBuilder->getSQL(),
                'Erro: ' . $e->getMessage()
            ];
            $this->io->error($erroMsg);
            if ($this->logObjeto) {
                foreach ($erroMsg as $msg) {
                    $this->logObjeto->escreverLog("  - [ERRO] " . $msg);
                }
            }

            // Mapeando o erro
            $metadata = [
                'acao' => 'update',
                'cv_url' => $_ENV['CV_URL'],
                'path' => $objeto['path'],
                'tabela' => $objeto['tabela'],
                'referencia' => $linha->referencia
            ];
            salvarEventoErro($e, $objeto, $metadata, $erroMsg);

            $retorno = true;
        }

        return $retorno;
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
