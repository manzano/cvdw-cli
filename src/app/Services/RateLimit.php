<?php

namespace Manzano\CvdwCli\Services;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\ProgressBar;

use Manzano\CvdwCli\Inc\CvdwException;
use Manzano\CvdwCli\Services\DatabaseSetup;
use Manzano\CvdwCli\Services\Console\CvdwSymfonyStyle;

class RateLimit
{
    protected CvdwSymfonyStyle $io;
    public InputInterface $input;
    public OutputInterface $output;
    public \Doctrine\DBAL\Connection $conn;
    public DatabaseSetup $database;
    public object $resposta;
    public $logObjeto = false;
    public array $objeto;
    public $executarObj;
    public $idrequisicao;

    public $inicioExecucao;
    public $tempoLimiteExecucao;

    public function __construct(InputInterface $input, OutputInterface $output, $executarObj)
    {
        $this->input = $input;
        $this->output = $output;
        $this->executarObj = $executarObj;
        $this->conn = conectarDB($this->input, $this->output);
    }

    public function inserirRequisicao($objeto): int
    {

        $queryBuilder = $this->conn->createQueryBuilder();
        $queryBuilder
            ->insert('_requisicoes')
            ->values([
                'data_inicio' => 'NOW()',
                'objeto' => ':objeto',
            ])
            ->setParameter('objeto', $objeto);
        
        $queryBuilder->executeStatement();
        $this->idrequisicao = $this->conn->lastInsertId();
        return $this->idrequisicao;

    }

    public function concluirRequisicao($idrequisicao, $dados_retorno_qtd = null, $header_resultado = null): void
    {
        $queryBuilder = $this->conn->createQueryBuilder();
        $queryBuilder
            ->update('_requisicoes')
            ->set('data_fim', 'NOW()')
            ->set('dados_retorno_qtd', ':dados_retorno_qtd')
            ->set('header_resultado', ':header_resultado')
            ->where('referencia = :referencia')
            ->setParameter('dados_retorno_qtd', $dados_retorno_qtd)
            ->setParameter('header_resultado', $header_resultado)
            ->setParameter('referencia', $idrequisicao);
        $queryBuilder->executeStatement();
    }

    public function getDiferencaSegundosUltimaRequisicao(): ?int
    {
        $query = "SELECT TIMESTAMPDIFF(SECOND, data_inicio, NOW()) AS diferenca_segundos
                  FROM _requisicoes 
                  ORDER BY data_inicio DESC 
                  LIMIT 19,1";
    
        $stmt = $this->conn->executeQuery($query);
        $result = $stmt->fetchOne();
    
        return $result !== false ? (int) $result : null;
    }

    public function qtdRequisicoes($segundos = 60): ?int
    {
        $query = "SELECT COUNT(*) AS total_requisicoes
        FROM _requisicoes
        WHERE data_inicio >= NOW() - INTERVAL $segundos SECOND";

        $stmt = $this->conn->executeQuery($query);
        $result = $stmt->fetchOne();

        return (int) $result;
    }

    public function removerRequisicoesAntigas($dias = 30): ?int
    {
        $query = "SELECT TIMESTAMPDIFF(SECOND, data_inicio, NOW()) AS diferenca_segundos
                  FROM _requisicoes 
                  ORDER BY data_inicio DESC 
                  LIMIT 19,1";
    
        $stmt = $this->conn->executeQuery($query);
        $result = $stmt->fetchOne();
    
        return $result !== false ? (int) $result : null;
    }

    public function iniciarExecucao(): float
    {
        $this->inicioExecucao = time();
        return $this->inicioExecucao;
    }
    
    public function tempoDeExecucao(): float
    {
        $tempoAtual = time();
        return $tempoAtual - $this->inicioExecucao;
    }

    public function validarTempoExecucao(){
        if($this->tempoLimiteExecucao){
            $tempoExecucao = $this->tempoDeExecucao();
            if($tempoExecucao >= $this->tempoLimiteExecucao){

                $this->io = new CvdwSymfonyStyle($this->input, $this->output, $this->logObjeto);
                $this->io->error("Tempo de execução excedido! Limite: {$this->tempoLimiteExecucao} segundos.");
                exit;
            }
        }
    }

    public function setTempoLimiteExecucao($tempoLimiteExecucao){
        $this->tempoLimiteExecucao = $tempoLimiteExecucao;
    }


}
