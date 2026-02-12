<?php

namespace Manzano\CvdwCli\Services;

require_once __DIR__ . '/../Inc/Conexao.php';

use Manzano\CvdwCli\Services\Console\CvdwSymfonyStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RateLimit
{
    protected CvdwSymfonyStyle $console;
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
        $this->conn = \Manzano\CvdwCli\Inc\Conexao::conectarDB($this->input, $this->output);
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

    public function concluirRequisicao($idrequisicao, $dadosRetornoQtd = null, $headerResultado = null): void
    {
        $queryBuilder = $this->conn->createQueryBuilder();
        $queryBuilder
            ->update('_requisicoes')
            ->set('data_fim', 'NOW()')
            ->set('dados_retorno_qtd', ':dados_retorno_qtd')
            ->set('header_resultado', ':header_resultado')
            ->where('idrequisicao = :idrequisicao')
            ->setParameter('dados_retorno_qtd', $dadosRetornoQtd)
            ->setParameter('header_resultado', $headerResultado)
            ->setParameter('idrequisicao', $idrequisicao);
        $queryBuilder->executeStatement();
    }

    public function getDiferencaSegundosUltimaRequisicao(): ?int
    {
        $queryBuilder = $this->conn->createQueryBuilder();
        // EXTRACT(EPOCH FROM (NOW() - data_inicio)) funciona em PostgreSQL e MySQL 5.6+
        $queryBuilder
            ->select("EXTRACT(EPOCH FROM (NOW() - data_inicio)) AS diferenca_segundos")
            ->from('_requisicoes')
            ->orderBy('data_inicio', 'DESC')
            ->setFirstResult(19)
            ->setMaxResults(1);

        $stmt = $queryBuilder->executeQuery();
        $result = $stmt->fetchOne();

        return $result !== false ? (int) $result : null;
    }

    public function qtdRequisicoes($segundos = 60): ?int
    {
        $platform = $this->conn->getDatabasePlatform()->getName();
        if (stripos($platform, 'postgres') !== false) {
            $interval = sprintf("INTERVAL '%d seconds'", (int)$segundos);
        } else {
            $interval = sprintf("INTERVAL %d SECOND", (int)$segundos);
        }

        $queryBuilder = $this->conn->createQueryBuilder();
        $queryBuilder
            ->select('COUNT(*) AS total_requisicoes')
            ->from('_requisicoes')
            ->where("data_inicio >= (NOW() - $interval)");

        $stmt = $queryBuilder->executeQuery();
        $result = $stmt->fetchOne();

        return (int) $result;
    }

    public function removerRequisicoesAntigas($dias = 30): ?int
    {
        $platform = $this->conn->getDatabasePlatform()->getName();
        if (stripos($platform, 'postgres') !== false) {
            $interval = sprintf("INTERVAL '%d days'", (int)$dias);
        } else {
            $interval = sprintf("INTERVAL %d DAY", (int)$dias);
        }

        $queryBuilder = $this->conn->createQueryBuilder();
        $queryBuilder
            ->delete('_requisicoes')
            ->where("data_inicio < (NOW() - $interval)");

        $result = $queryBuilder->executeStatement();

        return $result;
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

    public function validarTempoExecucao()
    {
        if ($this->tempoLimiteExecucao) {
            $tempoExecucao = $this->tempoDeExecucao();
            if ($tempoExecucao >= $this->tempoLimiteExecucao) {

                $this->console = new CvdwSymfonyStyle($this->input, $this->output, $this->logObjeto);
                $this->console->error("Tempo de execução excedido! Limite: {$this->tempoLimiteExecucao} segundos.");
                exit;
            }
        }
    }

    public function setTempoLimiteExecucao($tempoLimiteExecucao)
    {
        $this->tempoLimiteExecucao = $tempoLimiteExecucao;
    }


}
