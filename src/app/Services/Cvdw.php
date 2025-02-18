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
    public int $inseridoserros = 0;
    public int $alterados = 0;
    public int $alteradoserros = 0;
    
    public int $qtd = 500;

    public $executarObj;
    

    public function __construct(InputInterface $input, OutputInterface $output, $executarObj)
    {
        $this->input = $input;
        $this->output = $output;
        $this->executarObj = $executarObj;
        
    }

    public function conectar(): void
    {
        $this->conn = conectarDB($this->input, $this->output);
    }

    public function processar(array $objeto, int $qtd, $io, $apartir = null, $inputDataReferencia = false, $logObjeto = null, $maxpag = null): bool
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
        $this->database = new DatabaseSetup($this->input, $this->output, $this);
        $http = new Http($this->input, $this->output, $io, $this->executarObj, $this->logObjeto );
        $parametros = array(
            'pagina' => 1,
            'registros_por_pagina' => $this->qtd
        );
        if ($inputDataReferencia) {
            $this->io->text('Data de referência: <fg=yellow>Ignorada</>');
        } else {
            
            if($apartir) {
                $apartir = str_replace('T', ' ', $apartir);
                $referencia_data = new DateTime($apartir);
                $parametros['a_partir_data_referencia'] = $apartir;
                $referencia_data->modify('-0 seconds');
                $referencia_data_UI = $referencia_data->format('d/m/Y H:i:s');
                $referencia_data = $referencia_data->format('Y-m-d H:i:s');
                $parametros['a_partir_data_referencia'] = $referencia_data;
                $this->io->text('Data de referência: ' . $referencia_data_UI);
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
        }
        $this->processados = $this->erros = 0;
        $this->inseridos = $this->inseridoserros = 0;
        $this->alterados = $this->alteradoserros = 0;

        $resposta = $http->requestCVDW($objeto['path'], false, $this, $parametros);
       
        // Se não existir $resposta->total_de_registros, imprimir uma mensagem de erro;
        //if (!isset($resposta->total_de_registros)) {
        if (!property_exists($resposta, 'total_de_registros')) { 
            $this->io->info([
                'A requisição não retornou os dados esperados!',
                'Parametros: ' . print_r($parametros, true),
                'Está requisição está retornando vazia' 
                
            ]);
        } else {
            $this->io->text('Registros encontrados: ' . $resposta->total_de_registros);
            $totaldepaginas = 'Total de páginas: ' . $resposta->total_de_paginas;
            if(isset($maxpag)) {
                $totaldepaginas .= '  <fg=yellow>(Será executado '.$maxpag.' página(s))</>';
            }
            $this->io->text($totaldepaginas);
            $progressBar = new ProgressBar($this->output, $resposta->total_de_registros);
            $paginas = $resposta->total_de_paginas;
            if ($paginas > 0) {
                $progressBar->setFormat('normal'); // debug
                $progressBar->setBarCharacter('<fg=green>=</>');
                $progressBar->setProgressCharacter("\xF0\x9F\x9A\x80");


                $progressBar->setFormat("\n Dados processados %current% de %max% [%bar%] %percent:3s%% \n %message%");
                $progressBar->setMessage($this->getMensagem());
                $this->processados = 0;
                for ($pagina = 1; $pagina <= $paginas; $pagina++) {
                    if ($this->getLimiteErros()) {
                        break;
                    }
                    if($maxpag && $pagina >= ($maxpag+1)){
                        break;
                    }
                    if ($pagina > 1) {
                        $parametros['pagina'] = $pagina;
                        if ($inputDataReferencia) {
                            unset($parametros['a_partir_data_referencia']);
                        } else {
                            $parametros['a_partir_data_referencia'] = $referencia_data;
                        }
                        $resposta = $http->requestCVDW($objeto['path'], $progressBar, $this, $parametros, $inputDataReferencia);
                    }
                    $progressBar->setMessage($this->getMensagem());
                    $progressBar->display();
                    if (!isset($resposta->dados) || !is_array($resposta->dados)) {
                        $this->io->warning([
                            'A requisição não retornou os dados esperados!'
                        ]);
                    } else {
                        $dadosNoPadrao = $this->verificaPadrao($resposta->dados[0]);
                        if (!$dadosNoPadrao) {
                            $progressBar->finish();
                            $mensagem = 'Os dados de ' . $objeto['path'] . ' não estão no padrão esperado!';
                            $this->io->warning([
                                $mensagem,
                            ]);
                            $metadata = [
                                'acao' => 'executar',
                                'cv_url' => $_ENV['CV_URL'],
                                'path' => $objeto['path'],
                                'tabela' => $objeto['tabela'],
                                'referencia' => null
                            ];
                            $info_adicionais = [
                                'resposta' => $resposta
                            ];
                            salvarEventoErro(null, 'CVDW', $metadata, $mensagem, $info_adicionais);
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
                    $progressBar->setMessage($this->getMensagem(' Executando próxima requisição...'));
                    $progressBar->display();
                }
                $progressBar->finish();
            } else {
                $this->io->text('<fg=green>Nenhuma informação nova foi encontrada!</fg=green>');
                $progressBar->setMessage($this->getMensagem());
            }
        }
        $this->io->newLine();
        return true;
    }

    protected function getLimiteErros(): bool
    {
        return $this->erros >= 3;
    }

    public function getMensagem($info = false, $erro = false): string
    {
        
        // Se inseridoserros for maior que 1, imprimir o (s)
        $mensagem = "";

        $tempodeexecucao = $this->executarObj->tempoDeExecucao();
        if($tempodeexecucao){
            $mensagem .= "Tempo de execução: <fg=green>" . $tempodeexecucao . " segundos.</fg=green> \n";
            $this->executarObj->validarTempoExecucao();
        }

        $mensagem .= " Inseridos: <fg=green>" . $this->inseridos . " sucesso" . (($this->inseridos > 1) ? 's' : '') . "</fg=green> / ";
        $mensagem .= "<fg=red>" . $this->inseridoserros . " erro" . (($this->inseridoserros > 1) ? 's' : '') . "</fg=red> \n";
        $mensagem .= " Alterados: <fg=green>" . $this->alterados . " sucesso" . (($this->alterados > 1) ? 's' : '') . "</fg=green> / ";
        $mensagem .= "<fg=red>" . $this->alteradoserros . " erro" . (($this->alteradoserros > 1) ? 's' : '') . "</fg=red> \n";

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
        } catch (Exception $e) {
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

    protected function verificaSeExiste(string $tabela, string $referencia): string
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
                $this->alteradoserros++;
                $this->erros++;
            }
        } elseif($existe == 'nao_existe') {
            $retorno = $this->executaInsert($objeto, $linha);
            if ($retorno) {
                $this->inseridos++;
            } else {
                $this->inseridoserros++;
                $this->erros++;
            }
        } else {
            $this->erros++;
            $retorno = false;
        }

        if($retorno){
            $subTabelas = array();
            $subTabelas = $this->retornarColunasTabelas($linha);

            foreach($subTabelas as  $subTabelaNome => $subTabelaLinhas){
                // converter objeto em um array
                foreach($subTabelaLinhas as $subTabelaLinha){
                    $subTabela = (array) $subTabelaLinha;
                    $subTabelaDados = array();
                    $subTabelaDados['referencia'] = $linha->referencia.'_'.$subTabela[array_key_first($subTabela)];
                    $subTabelaDados['referencia_data'] = $linha->referencia_data;
                    $subTabelaDados = array_merge($subTabelaDados, $subTabela);
                    

                    $subTabelaObjetoVirtual = array();
                    $subTabelaObjetoVirtual["tabela"] = $objeto["tabela"].'_sub_'.$subTabelaNome;
                    $subTabelaObjetoVirtual["path"] = $objeto["tabela"];
                    $arrayAux = array(
                        "referencia" => $objeto["response"]["dados"]['referencia'],
                        "referencia_data" => $objeto["response"]["dados"]['referencia_data'],
                    );

                    $subTabelaObjetoVirtual["response"]["dados"] = array_merge($arrayAux, $objeto["response"]["dados"][$subTabelaNome]);
                    
                    $this->processaSql($subTabelaObjetoVirtual, (object) $subTabelaDados);

                }
            }

        }

        $this->processados++;
        return $retorno;
    }

    
    protected function retornarColunasTabelas(object $linha): array
    {
        $retorno = [];
        foreach ($linha as $colunaNome => $colunaValor) {
            if ( is_object($colunaValor) && is_countable($colunaValor) && count( $colunaValor) > 0) {
                $retorno[$colunaNome] = (array) $linha->$colunaNome;
            }
            if ( is_array($colunaValor) && count( $colunaValor) > 0 ) {
                $retorno[$colunaNome] = (array) $linha->$colunaNome;
            }
        }
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
            if (isset($linha->$colunaInd) && !is_array($linha->$colunaInd) && !is_object($linha->$colunaInd)) {
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
            if (isset($linha->$colunaInd) && !is_array($linha->$colunaInd) && !is_object($linha->$colunaInd)) {
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
            $tipoLinha = $objetoObj->identificarTipoDeDados($valor);
            if (isset($linha->$coluna) && $tipoLinha  !== "TABELA") {

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
                } elseif ($valor["type"] == "number" && $linha->$coluna != null) {
                    if (is_numeric($linha->$coluna)) {
                        $linha->$coluna = number_format($linha->$coluna, 2, '.', '');
                    }
                }

                if($valor["type"] == "datetime" && $linha->$coluna != null) {
                    $linha->$coluna = date('Y-m-d H:i:s', strtotime($linha->$coluna));
                }

                if ($linha->$coluna == '') {
                    $linha->$coluna = null;
                }

                if(isset($valor['sensivel']) && $valor['sensivel'] == 1 && $_ENV['ANONIMIZAR'] == 'true'){
                        if($_ENV['ANONIMIZAR_TIPO'] == 'Asteriscos') {
                            $linha->$coluna = substituirPorAsteriscos($linha->$coluna);
                            // Converter para UTF*
                            $linha->$coluna = mb_convert_encoding($linha->$coluna, 'UTF-8');
                        }
                        if($_ENV['ANONIMIZAR_TIPO'] == 'Hash') {
                            $tamanho = 32;
                            if (strpos($coluna, "documento") !== false) {
                                $tamanho = 11;
                            }
                            $linha->$coluna = substituirPorHash($linha->$coluna, $tamanho);
                            $linha->$coluna = mb_convert_encoding($linha->$coluna, 'UTF-8');
                        }
                }

            } 
        }
        return $linha;
    }

    public function verificarNovaVersao($io): string
    {
        $http = new Http($this->input, $this->output, $io, $this);
        return $http->buscarVersaoRepositorio();
    }

    public function alertarNovaVersao($versaoCVDW, $io): void
    {
        $versaoRepositorio = $this->verificarNovaVersao($io);

        if($versaoRepositorio == 'OFF'){
            $io->warning('Não consegui acessar o repositório do CV, considere verificar a conexão com a internet.');
        } else {
            if($versaoRepositorio !== $versaoCVDW){
                $io->info('Existe uma nova versão disponível: '.$versaoRepositorio."\n".
                "Acesse https://github.com/manzano/cvdw-cli para mais informações \n".
                "Ou utilize a opção 10 para atualizar o seu CVDW.");
            }
        }
    }

    public function validarAmbiente($http): bool
    {
        $response = $http->pingAmbienteAutenticadoCVDW(
            $_ENV['CV_URL'],
            "/imobiliarias",
            $_ENV['CV_EMAIL'],
            $_ENV['CV_TOKEN']
        );
        return isset($response['registros']);
    }

}
