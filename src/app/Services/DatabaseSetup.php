<?php

namespace Manzano\CvdwCli\Services;

use Manzano\CvdwCli\Configuracoes;
use Manzano\CvdwCli\Services\Objeto;

use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\ProgressBar;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Statement;

class DatabaseSetup
{
    protected SymfonyStyle $io;
    public InputInterface $input;
    public OutputInterface $output;
    public \Doctrine\DBAL\Connection $conn;

    public Table $tabelaIO;
    public ProgressBar $progressBar;

    public function __construct(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
        $this->input = $input;
        $this->output = $output;
        $this->conn = conectarDB($input, $output);
    }

    public function listarTabelas(): void
    {
        $tabelaIO = new Table($this->output);
        $tabelaIO->setHeaders(['Tabela', 'Descrição']);
        $tabelas = $this->listarTabelasArray();
        foreach ($tabelas as $tabela => $nome) {
            $tabelaIO->addRow([$tabela, $nome]);
        }
        $tabelaIO->render();
    }

    public function executarCriarTabelas(): bool
    {

        $this->tabelaIO = new Table($this->output);
        $this->tabelaIO->setHeaders(['Tabela', 'Situação']);

        $totalObjetos = count(OBJETOS);
        $this->progressBar = new ProgressBar($this->output, $totalObjetos);
        $this->progressBar->start();

        $objetoObj = new Objeto($this->input, $this->output);
        foreach (OBJETOS as $key => $dados) {

            $objeto = $objetoObj->retornarObjeto($key);
            if (!$objeto) {
                $this->io->error("O objeto {$objeto} não existe.");
            }

            $this->criarTabela($key, $objeto["response"]["dados"]);
            // Verificar se há subtabelas no objeto
            foreach ($objeto["response"]["dados"] as $coluna => $valor) {
                $tipoDeDados = $objetoObj->identificarTipoDeDados($valor);
                if ($tipoDeDados == "TABELA") {
                    $subTabela = $key . "_sub_" . $coluna;
                    $arrayReferencia = array(
                        "referencia" => $objeto["response"]["dados"]['referencia'],
                        "referencia_data" => $objeto["response"]["dados"]['referencia_data']
                    );
                    $valor = array_merge($arrayReferencia, $valor);
                    $this->criarTabela($subTabela, $valor);
                }
            }
        }
        $this->tabelaIO->render();
        $this->progressBar->finish();

        return true;
    }

    public function listarTabelasArray(): array
    {
        $tabelas = array();
        $objetoObj = new Objeto($this->input, $this->output);
        foreach (OBJETOS as $key => $dados) {

            $objeto = $objetoObj->retornarObjeto($key);
            if (!$objeto) {
                $this->io->error("O objeto {$objeto} não existe.");
            }

            $tabelas[$key] = $objeto['nome'];
            // Verificar se há subtabelas no objeto
            foreach ($objeto["response"]["dados"] as $coluna => $valor) {
                $tipoDeDados = $objetoObj->identificarTipoDeDados($valor);
                if ($tipoDeDados == "TABELA") {
                    $subTabela = $key . "_sub_" . $coluna;
                    $tabelas[$subTabela] = $objeto['nome'] . " - " . $coluna;
                }
            }
        }
        return $tabelas;
    }

    private function criarTabela(string $tabela, array $colunas): void
    {

        $seExiste = $this->verificarSeTabelaExiste($tabela);
        if ($seExiste) {
            $this->tabelaIO->addRow([$tabela, '<error>Tabela já existe!</error>']);
            $this->progressBar->setMessage('A tabela ' . $tabela . ' já existe');
            $this->progressBar->advance();
        } else {
            $objetoObj = new Objeto($this->input, $this->output);
            $schema = new Schema();
            $tabelaObj = $schema->createTable("{$tabela}");
            foreach ($colunas as $coluna => $especificacao) {

                $tipoDeDados = $objetoObj->identificarTipoDeDados($especificacao);
                if ($tipoDeDados == "TABELA") {
                    continue;
                }

                $colunaTratada = $this->tratarColuna($coluna, $especificacao);
                $especificacao = $colunaTratada[0];
                $opcoes = $colunaTratada[1];

                $nomeColuna = $this->tratarNomeColuna($coluna);

                $tabelaObj->addColumn("{$nomeColuna}", $especificacao["type"], $opcoes);
                if (
                    "referencia" == $coluna ||
                    (strpos($coluna, "id") !== false &&
                        substr($coluna, 0, 2) == "id" &&
                        $especificacao["type"] == "integer")
                ) {
                    $tabelaObj->addIndex(array("{$nomeColuna}"), "{$nomeColuna}_idx");
                }
                if (strpos($coluna, "data") !== false) {
                    $tabelaObj->addIndex(array("{$nomeColuna}"), "{$nomeColuna}_idx");
                }
            }

            $platform = $this->conn->getDatabasePlatform();
            $queries = $schema->toSql($platform);

            foreach ($queries as $query) {
                try {
                    $this->conn->executeQuery($query);
                    if (is_object($this->tabelaIO)) {
                        $this->tabelaIO->addRow([$tabela, '<info>Tabela criada!</info>']);
                    }
                    if (is_object($this->progressBar)) {
                        $this->progressBar->setMessage('Criando a tabela ' . $tabela);
                        $this->progressBar->advance();
                    }
                } catch (Exception $e) {
                    echo $query . "\n\n";
                    echo $e->getMessage();
                }
            }
        }
        
    }

    private function tratarColuna(string $coluna, array $especificacao): array
    {

        $opcoes = array();
        $opcoes["notnull"] = false;
        $opcoes["default"] = null;

        if (isset($especificacao['description'])) {
            $opcoes["comment"] = $especificacao['description'];
        }
        if ($especificacao["type"] == "int") {
            $especificacao["type"] = "integer";
        }
        if ($especificacao["type"] == "string") {
            $opcoes["length"] = 255;
        }
        if ($especificacao["type"] == "number") {
            $especificacao["type"] = "decimal";
            $opcoes["precision"] = 14;
            $opcoes["scale"] = 2;
        }
        return array($especificacao, $opcoes);
    }

    public function tratarNomeColuna(string $nomeColuna): string
    {

        $nomeColuna = str_replace(' ', '_', $nomeColuna);
        $nomeColuna = str_replace('-', '_', $nomeColuna);
        $caracteresNaoPermitidos = array('(', ')', '/', '?', '!', ';', ':', '.', ',', '\'', '"', '`', '´', '=', '+', '*', '&', '%', '$', '#', '@', '§', 'ª', 'º', '°', '¨', '~', '^', '>');
        $nomeColuna = str_replace($caracteresNaoPermitidos, '', $nomeColuna);

        // Se a coluna tiver mais que 60 caracteres
        if (strlen($nomeColuna) > 60) {
            // Criar um hash do nome da coluna
            $hash = md5($nomeColuna);
            // Corta o hash para 10 caracteres
            $hash = substr($hash, 0, 10);
            // Corta o nome da coluna para 40 caracteres
            $nomeColuna = substr($nomeColuna, 0, 40);
            // Junta o nome da coluna com o hash
            $nomeColuna = $nomeColuna . '_' . $hash;
        }

        return $nomeColuna;
    }

    public function verificarSeTabelaExiste(string $tabela): bool
    {
        // Acessa o gerenciador de esquema usando createSchemaManager() em vez de getSchemaManager()
        $schemaManager = $this->conn->createSchemaManager();
        $tabelas = $schemaManager->listTableNames();
        return in_array($tabela, $tabelas);
    }


    public function limparTabela(string $tabela): \Doctrine\DBAL\Result
    {
        return $this->conn->executeQuery("TRUNCATE TABLE {$tabela}");
    }

    public function apagarTabela(string $tabela): \Doctrine\DBAL\Result
    {
        return $this->conn->executeQuery("DROP TABLE {$tabela}");
    }
}
