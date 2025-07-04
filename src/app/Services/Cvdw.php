<?php

namespace Manzano\CvdwCli\Services;

use DateTime;
use Doctrine\DBAL\Exception;
use Manzano\CvdwCli\Services\Console\CvdwSymfonyStyle;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Cvdw
{
    protected CvdwSymfonyStyle $console;
    public InputInterface $input;
    public OutputInterface $output;
    public \Doctrine\DBAL\Connection $conn;
    public DatabaseSetup $database;
    public object $resposta;
    public $logObjeto;
    public array $objeto;

    public int $processados;
    public int $erros;
    public int $inseridos = 0;
    public int $inseridoserros = 0;
    public int $alterados = 0;
    public int $alteradoserros = 0;
    public int $paginasExecutadas = 0;
    public int $paginasEncontradas = 0;

    public int $qtd = 500;

    public $executarObj;
    public RateLimit $rateLimitObj;
    protected EnvironmentManager $environmentManager;


    public function __construct(InputInterface $input, OutputInterface $output, $executarObj, $rateLimitObj)
    {
        $this->input = $input;
        $this->output = $output;
        $this->executarObj = $executarObj;
        $this->rateLimitObj = $rateLimitObj;
        $this->environmentManager = new EnvironmentManager();

    }

    public function conectar(): void
    {
        $this->conn = \Manzano\CvdwCli\Inc\Conexao::conectarDB($this->input, $this->output);
    }

    public function processar(array $objeto, int $qtd, $console, $apartir = null, $inputDataReferencia = false, $logObjeto = null, $maxpag = null): bool
    {

        if ($this->output->isDebug() || $this->input->getOption('verbose')) {
            $console->info(" LOG: " . __FUNCTION__);
        }

        if ($qtd > 0) {
            $this->qtd = $qtd;
            if ($qtd > 500) {
                $console->warning('Quantidade de registros por página maior que 500!');
                $this->qtd = 500;
            }
        }
        $this->console = $console;
        if (is_object($logObjeto)) {
            $this->logObjeto = $logObjeto;
        }
        $this->database = new DatabaseSetup($this->input, $this->output, $this);
        $http = new Http($this->input, $this->output, $console, $this->executarObj, $this->logObjeto, rateLimitObj: $this->rateLimitObj);
        $this->paginasExecutadas = 1;
        $parametros = [
            'pagina' => $this->paginasExecutadas,
            'registros_por_pagina' => $this->qtd,
        ];
        if ($inputDataReferencia) {
            $console->text('Data de referência: <fg=red>Ignorada</>');
        } else {

            if ($apartir) {
                $apartir = str_replace('T', ' ', $apartir);
                $referenciaData = new DateTime($apartir);
                $parametros['a_partir_data_referencia'] = $apartir;
                $referenciaData->modify('-0 seconds');
                $referenciaDataUI = $referenciaData->format('d/m/Y H:i:s');
                $referenciaData = $referenciaData->format('Y-m-d H:i:s');
                $parametros['a_partir_data_referencia'] = $referenciaData;
                $console->text('Data de referência: <fg=yellow>' . $referenciaDataUI.'</fg=yellow>');
            } else {
                $referenciaData = $this->buscaUltimaData($objeto['tabela']);
                if ($referenciaData) {
                    $referenciaData = new DateTime($referenciaData); // Cria um objeto DateTime
                    $referenciaData->modify('-0 seconds');
                    $referenciaDataUI = $referenciaData->format('d/m/Y H:i:s');
                    $referenciaData = $referenciaData->format('Y-m-d H:i:s');
                    $parametros['a_partir_data_referencia'] = $referenciaData;
                } else {
                    $referenciaDataUI = 'Nenhuma data encontrada';
                }

                $console->text('Data de referência: <fg=yellow>' . $referenciaDataUI.'</fg=yellow>');
            }
        }
        $this->processados = $this->erros = 0;
        $this->inseridos = $this->inseridoserros = 0;
        $this->alterados = $this->alteradoserros = 0;

        $resposta = $http->requestCVDW($objeto['path'], false, $this, $parametros);
        $resposta = $this->corrigeRetornoJson($resposta);

        // Se não existir $resposta->total_de_registros, imprimir uma mensagem de erro;
        //if (!isset($resposta->total_de_registros)) {
        if (! property_exists($resposta, 'total_de_registros')) {
            $console->info([
                'A requisição não retornou os dados esperados!',
                'Parametros: ' . print_r($parametros, true),
                'Está requisição está retornando vazia',

            ]);
        } else {
            $console->text('Registros encontrados: ' . $resposta->total_de_registros);
            $totaldepaginas = 'Total de páginas: ' . (property_exists($resposta, 'total_de_paginas') ? $resposta->total_de_paginas : 0);
            $this->paginasEncontradas = property_exists($resposta, 'total_de_paginas') ? $resposta->total_de_paginas : 0;
            if (isset($maxpag)) {
                $totaldepaginas .= '  <fg=yellow>(Será executado '.$maxpag.' página(s))</>';
            }
            $console->text($totaldepaginas);
            $progressBar = new ProgressBar($this->output, $resposta->total_de_registros);
            $paginas = property_exists($resposta, 'total_de_paginas') ? $resposta->total_de_paginas : 0;
            if ($paginas > 0) {
                $progressBar->setFormat('normal'); // debug
                $progressBar->setBarCharacter('<fg=green>=</>');
                $progressBar->setProgressCharacter("\xF0\x9F\x9A\x80");


                $progressBar->setFormat("\n  \_ Dados processados %current% de %max% [%bar%] %percent:3s%% \n %message%");
                $progressBar->setMessage($this->getMensagem());
                $this->processados = 0;
                for ($pagina = 1; $pagina <= $paginas; $pagina++) {

                    if ($this->getLimiteErros()) {
                        break;
                    }
                    if ($maxpag && $pagina >= ($maxpag + 1)) {
                        break;
                    }
                    if ($pagina > 1) {
                        $parametros['pagina'] = $pagina;
                        if ($inputDataReferencia) {
                            unset($parametros['a_partir_data_referencia']);
                        } else {
                            $parametros['a_partir_data_referencia'] = $referenciaData;
                        }
                        $resposta = $http->requestCVDW($objeto['path'], $progressBar, $this, $parametros, $inputDataReferencia);
                        if (isset($resposta->total_de_paginas)) {
                            $this->paginasEncontradas = $resposta->total_de_paginas;
                        }
                        if (isset($resposta->total_de_registros) && $resposta->total_de_registros == 0) {
                            continue;
                        }
                        $this->paginasExecutadas = $pagina;
                    }
                    $progressBar->setMessage($this->getMensagem());
                    $progressBar->display();
                    if (! isset($resposta->dados) || ! is_array($resposta->dados)) {
                        $console->warning([
                            'A requisição não retornou os dados esperados!',
                        ]);
                    } else {
                        $dadosNoPadrao = $this->verificaPadrao($resposta->dados[0]);
                        if (! $dadosNoPadrao) {
                            $progressBar->finish();
                            $mensagem = 'Os dados de ' . $objeto['path'] . ' não estão no padrão esperado!';
                            $console->warning([
                                $mensagem,
                            ]);

                            break;
                        }
                        foreach ($resposta->dados as $linha) {
                            $this->processados++;
                            $this->processaSql($objeto, $linha);
                            $progressBar->setMessage($this->getMensagem());
                            $progressBar->display();
                            $progressBar->advance();
                            if ($this->getLimiteErros()) {
                                break;
                            }
                        }
                    }
                    $progressBar->setMessage($this->getMensagem(' Executando próxima requisição...'));
                    $progressBar->display();
                }
                $progressBar->finish();
            } else {
                $console->text('<fg=green>Nenhuma informação nova foi encontrada!</fg=green>');
                $progressBar->setMessage($this->getMensagem());
            }
        }
        $console->newLine();

        return true;
    }

    protected function corrigeRetornoJson($resposta): object
    {

        // Se resposta não for um objeto, retornar um objeto vazio
        if (! is_object($resposta)) {
            $resposta = (object) [];
        }

        // Se nao tiver a chave página, adicionar
        if (! isset($resposta->pagina)) {
            $resposta->pagina = 1;
        }
        // Se nao tiver a chave registros, adicionar
        if (! isset($resposta->registros)) {
            $resposta->registros = 500;
        }
        // Se nao tiver a chave total_de_registros, adicionar
        if (! isset($resposta->total_de_registros)) {
            $resposta->total_de_registros = 0;
        }
        // Se nao tiver a chave total_de_paginas, adicionar
        if (! isset($resposta->total_de_paginas)) {
            $resposta->total_de_paginas = 0;
        }
        // Se nao tiver a chave dados, adicionar
        if (! isset($resposta->dados)) {
            $resposta->dados = [];
        }

        return $resposta;
    }

    protected function getLimiteErros(): bool
    {
        return $this->erros >= 3;
    }

    public function getMensagem($info = false, $erro = false): string
    {

        // Se inseridoserros for maior que 1, imprimir o (s)
        $mensagem = "";

        $tempodeexecucao = $this->rateLimitObj->tempoDeExecucao();
        if ($tempodeexecucao) {
            $mensagem .= "    Página ".$this->paginasExecutadas." de ".$this->paginasEncontradas." \n";
            $this->rateLimitObj->validarTempoExecucao();
        }

        $mensagem .= "     Inseridos: <fg=green>" . $this->inseridos . " sucesso" . (($this->inseridos > 1) ? 's' : '') . "</fg=green> / ";
        $mensagem .= "  <fg=red>" . $this->inseridoserros . " erro" . (($this->inseridoserros > 1) ? 's' : '') . "</fg=red> \n";
        $mensagem .= "     Alterados: <fg=green>" . $this->alterados . " sucesso" . (($this->alterados > 1) ? 's' : '') . "</fg=green> / ";
        $mensagem .= "  <fg=red>" . $this->alteradoserros . " erro" . (($this->alteradoserros > 1) ? 's' : '') . "</fg=red> \n";

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
        if (! isset($linha->referencia) || ! isset($linha->referencia_data)) {
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
            $this->console->error("Erro ao buscar a última data na tabela '$tabela': " . $e->getMessage());

            return null;
        } catch (\Exception $e) {
            // Captura outras exceções genéricas
            $this->console->error("Erro inesperado: " . $e->getMessage());

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
            if ($row['total'] > 0) {
                return 'existe';
            } else {
                return 'nao_existe';
            }
        } catch (\Exception $e) {
            // Captura outras exceções genéricas
            $this->console->error("Erro inesperado: " . $e->getMessage());

            return 'erro';
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
        } elseif ($existe == 'nao_existe') {
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

        if ($retorno) {
            $subTabelas = [];
            $subTabelas = $this->retornarColunasTabelas($linha);

            foreach ($subTabelas as $subTabelaNome => $subTabelaLinhas) {
                // converter objeto em um array
                foreach ($subTabelaLinhas as $subTabelaLinha) {
                    $subTabela = (array) $subTabelaLinha;
                    $subTabelaDados = [];
                    $subTabelaDados['referencia'] = $linha->referencia.'_'.$subTabela[array_key_first($subTabela)];
                    $subTabelaDados['referencia_data'] = $linha->referencia_data;
                    $subTabelaDados = array_merge($subTabelaDados, $subTabela);


                    $subTabelaObjetoVirtual = [];
                    $subTabelaObjetoVirtual["tabela"] = $objeto["tabela"].'_sub_'.$subTabelaNome;
                    $subTabelaObjetoVirtual["path"] = $objeto["tabela"];
                    $arrayAux = [
                        "referencia" => $objeto["response"]["dados"]['referencia'],
                        "referencia_data" => $objeto["response"]["dados"]['referencia_data'],
                    ];

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
            if (is_object($colunaValor) && is_countable($colunaValor) && count($colunaValor) > 0) {
                $retorno[$colunaNome] = (array) $linha->$colunaNome;
            }
            if (is_array($colunaValor) && count($colunaValor) > 0) {
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
            if (isset($linha->$colunaInd) && ! is_array($linha->$colunaInd) && ! is_object($linha->$colunaInd)) {
                $queryBuilder->set($nomeColuna, ':valor' . $indice);
                $queryBuilder->setParameter('valor' . $indice, $linha->$colunaInd);
                $indice++;
            }
        }

        $queryBuilder->where('referencia = :referencia');
        $queryBuilder->setParameter('referencia', $linha->referencia);

        try {
            $queryBuilder->executeStatement();
            if (! is_null($this->logObjeto) && is_object($this->logObjeto)) {
                $this->logObjeto->escreverLog("  - Atualizado: " . $linha->referencia);
            }
            $retorno = true;
        } catch (Exception $e) {

            $erroMsg = [
                'Erro ao tentar executar o SQL! (Update)',
                'Objeto: ' . print_r($linha, true),
                'SQL: ' . $queryBuilder->getSQL(),
                'Erro MySQL: ' . $e->getMessage(),
                'Código do Erro: ' . $e->getCode(),
                'Arquivo: ' . $e->getFile() . ':' . $e->getLine(),
            ];
            $this->console->error($erroMsg);
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
            if (isset($linha->$colunaInd) && ! is_array($linha->$colunaInd) && ! is_object($linha->$colunaInd)) {
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
                'Erro MySQL: ' . $e->getMessage(),
                'Código do Erro: ' . $e->getCode(),
                'Arquivo: ' . $e->getFile() . ':' . $e->getLine(),
            ];
            $this->console->error($erroMsg);
            if ($this->logObjeto) {
                foreach ($erroMsg as $msg) {
                    $this->logObjeto->escreverLog("  - [ERRO] " . $msg);
                }
            }

            $retorno = true;
        }

        return $retorno;
    }

    protected function trataDados(array $objeto, object $linha): object
    {

        $objetoObj = new Objeto($this->input, $this->output);

        foreach ($objeto['response']['dados'] as $coluna => $valor) {
            $tipoLinha = $objetoObj->identificarTipoDeDados($valor);

            if (isset($linha->$coluna) && $tipoLinha !== "TABELA") {
                $linha->$coluna = trim($linha->$coluna);
                if (strpos($coluna, "data") !== false) {
                    if ($linha->$coluna == "0000-00-00 00:00:00") {
                        $linha->$coluna = null;
                    }
                    if ($linha->$coluna == "0000-00-00") {
                        $linha->$coluna = null;
                    }
                }
                if ($coluna == 'referencia_data' && ! isset($linha->$coluna)) {
                    $linha->$coluna = date('Y-m-d H:i:s');
                }

                if ($valor["type"] == "int") {
                    $linha->$coluna = substr($linha->$coluna, 0, 11);
                    $linha->$coluna = intval($linha->$coluna);
                } elseif ($valor["type"] == "number" && $linha->$coluna != null) {
                    if (is_numeric($linha->$coluna)) {
                        $linha->$coluna = number_format((float)$linha->$coluna, 2, '.', '');
                    }
                }

                if ($valor["type"] == "datetime" && $linha->$coluna != null) {
                    // Verificar se a data é válida
                    if (! \Manzano\CvdwCli\Inc\Helper::validarData($linha->$coluna)) {
                        $linha->$coluna = null;
                    } else {
                        $linha->$coluna = date('Y-m-d H:i:s', strtotime($linha->$coluna));
                    }
                }

                // Se valor tiver tamanho, verificar se o tamanho é maior que o tamanho da coluna
                if (isset($valor['tamanho']) && $linha->$coluna != null && $valor['type'] != 'string') {
                    if (strlen($linha->$coluna) > $valor['tamanho']) {

                        // exibir uma mensagem de erro
                        // O valor do campo e maior que o tamanho da coluna, ele foi truncado
                        $this->console->error("O valor do campo " . $coluna . " e maior que o tamanho da coluna (" . $valor['tamanho'] . " > " . strlen($linha->$coluna) . "), ele foi truncado");
                        $linha->$coluna = substr($linha->$coluna, 0, $valor['tamanho']);
                    }
                }

                if ($linha->$coluna == '') {
                    $linha->$coluna = null;
                }

                if (isset($valor['sensivel']) && $valor['sensivel'] == 1 && $this->environmentManager->getAnonimizar()) {
                    if ($this->environmentManager->getAnonimizarTipo() == 'Asteriscos') {
                        $linha->$coluna = \Manzano\CvdwCli\Inc\Helper::substituirPorAsteriscos($linha->$coluna);
                        // Converter para UTF*
                        $linha->$coluna = mb_convert_encoding($linha->$coluna, 'UTF-8');
                    }
                    if ($this->environmentManager->getAnonimizarTipo() == 'Hash') {
                        $tamanho = 32;
                        if (strpos($coluna, "documento") !== false) {
                            $tamanho = 11;
                        }
                        $linha->$coluna = \Manzano\CvdwCli\Inc\Helper::substituirPorHash($linha->$coluna, $tamanho);
                        $linha->$coluna = mb_convert_encoding($linha->$coluna, 'UTF-8');
                    }
                }

            }
        }

        return $linha;
    }

    public function verificarNovaVersao($console): string
    {
        $http = new Http($this->input, $this->output, $console, $this, null, rateLimitObj: $this->rateLimitObj);

        return $http->buscarVersaoRepositorio();
    }

    public function alertarNovaVersao($versaoCVDW, $console): void
    {
        $versaoRepositorio = $this->verificarNovaVersao($console);
        if ($versaoRepositorio == 'OFF') {
            $console->warning('Não consegui acessar o repositório do CV, considere verificar a conexão com a internet.');
        } else {
            if ($versaoRepositorio !== $versaoCVDW) {
                $console->info('Existe uma nova versão disponível: '.$versaoRepositorio."\n".
                "Acesse https://github.com/manzano/cvdw-cli para mais informações \n".
                "Ou utilize a opção 10 para atualizar o seu CVDW.");
            }
        }
    }

    public function validarAmbiente($http): bool
    {
        $response = $http->pingAmbienteAutenticadoCVDW(
            $this->environmentManager->getCvUrl(),
            "/imobiliarias",
            $this->environmentManager->getCvEmail(),
            $this->environmentManager->getCvToken()
        );

        return isset($response['registros']);
    }

}
