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
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\ColumnDiff;
use Doctrine\DBAL\Schema\TableDiff;
use Doctrine\DBAL\Types\Type;

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
                    $nomeIndice = $this->criarNomeIndice($nomeColuna);
                    $tabelaObj->addIndex(array("{$nomeColuna}"), "{$nomeIndice}_idx");
                }
                if (strpos($coluna, "data") !== false) {
                    $nomeIndice = $this->criarNomeIndice($nomeColuna);
                    $tabelaObj->addIndex(array("{$nomeColuna}"), "{$nomeIndice}_idx");
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

    private function criarNomeIndice(string $nomeCompleto) : string
    {
        // Trunca o nome para 30 caracteres
        $nomeTruncado = substr($nomeCompleto, 0, 30);

        // Gera um hash único de 6 caracteres
        // Usando mt_rand para maior variedade e convertendo para base 36 para encurtar
        $hashUnico = substr(base_convert(mt_rand(0, 1679615), 10, 36), 0, 6);

        // Combina o nome truncado com o hash único
        $nomeIndice = $nomeTruncado . '_' . $hashUnico;

        return $nomeIndice;
    }

    public function tratarColuna(string $coluna, array $especificacao): array
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

        $nomeColuna = strtolower($nomeColuna);
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

    public function retornarEstruturaTabela(string $nomeTabela): array
    {
        $estrutura = array();
        $schemaManager = $this->conn->createSchemaManager();
        $estrutura['nome'] = $nomeTabela;
        // Obtenha detalhes da tabela
        $estrutura['colunas'] = $schemaManager->listTableColumns($nomeTabela);
        $estrutura['indices'] = $schemaManager->listTableIndexes($nomeTabela);
        return $estrutura;
    }

    public function compararTabelaObjeto(array $tabela, array $objeto)
    {

        // Criar um progress bar

        $objetoObj = new Objeto($this->input, $this->output);

        $colunasTabela = $tabela['colunas'];
        $colunasObjeto = $objeto['response']['dados'];
        $colunasObjetoTratado = array();

        $logs = array();
        $diferencas = array();

        foreach($colunasObjeto as $coluna => $especificacao){
            $tipoDeDados = $objetoObj->identificarTipoDeDados($especificacao);
            if($tipoDeDados == "TABELA"){
                continue;
            }
            $especificacao['nomeTratado'] = $this->tratarNomeColuna($coluna);
            $colunasObjetoTratado[$especificacao['nomeTratado']] = strtolower($coluna);
            
            // Verificar se a coluna nao existe na tabela
            $colunaBanco = strtolower($especificacao['nomeTratado']);
            if(!array_key_exists($colunaBanco, $colunasTabela)){
                $diferencas['add'][] = $especificacao;
                $logs[] = "A coluna {$especificacao['nomeTratado']} não existe na tabela\n";
            } else {
                
                $especificacaoAux = $this->tratarColuna($coluna, $especificacao);
                $especificacao = $especificacaoAux[0];
                $especificacao['opcoes'] = $especificacaoAux[1];
                
                // Verificar se a coluna tem o mesmo tipo
                $tipoBanco = $colunasTabela[$colunaBanco]->getType()->getName();
                $tipoObjeto = $especificacao['type'];
                if($tipoBanco != $tipoObjeto){
                    $logs[] = "A coluna {$especificacao['nomeTratado']} tem tipo diferente ($tipoObjeto|$tipoBanco)\n";
                    $diferencas['change'][] = $especificacao;
                }
            }
        }
        foreach($colunasTabela as $coluna => $especificacao){
            // Verificar se a coluna nao existe no objeto
            if(!array_key_exists($coluna, $colunasObjetoTratado) ){
                $diferencas['remove'][] = $especificacao;
                $logs[] = "A coluna {$coluna} não existe no objeto\n";
            }
        }

        return array($logs, $diferencas);
    }

    protected function compararColunaObjeto(array $colunaTabela, array $colunaObjeto): array
    {
        $diferencas = array();
        // Verificar se a coluna tem o mesmo tipo
        if ($colunaTabela['type'] != $colunaObjeto['especificacao']['type']) 
        {
            $diferencas[$colunaObjeto['nomeTratado']]['de'] = $colunaTabela['type'];
            $diferencas[$colunaObjeto['nomeTratado']]['para'] = $colunaObjeto['especificacao']['type'];
        }
        return $diferencas;
    }

    public function limparTabela(string $tabela): \Doctrine\DBAL\Result
    {
        return $this->conn->executeQuery("TRUNCATE TABLE {$tabela}");
    }

    public function apagarTabela(string $tabela): \Doctrine\DBAL\Result
    {
        return $this->conn->executeQuery("DROP TABLE {$tabela}");
    }

    public function inserirColuna(string $tabela, string $coluna, array $especificacao):bool
    {
        $especificacao = $this->tratarColuna($coluna, $especificacao);
        $schemaManager = $this->conn->createSchemaManager();
        $newColumnType = Type::getType($especificacao[0]['type']); // ou use o tipo de dado desejado, e.g., 'integer'
        $newColumnOptions = $especificacao[1];
        $newColumn = new Column($coluna, $newColumnType, $newColumnOptions);
        $tableDiff = new TableDiff($tabela);
        $tableDiff->addedColumns[$coluna] = $newColumn;
        try {
            $schemaManager->alterTable($tableDiff);
            return true;
        } catch (\Doctrine\DBAL\Exception $e) {
            echo "Erro ao tentar inserir a coluna: " . $e->getMessage();
            return false;
        }
    }

    public function removerColuna(string $tabela, string $coluna): bool
    {
        $schemaManager = $this->conn->createSchemaManager();
        // Prepare a coluna a ser removida (o tipo não é estritamente necessário aqui, mas é requerido pela assinatura do método)
        $columnToRemove = new Column($coluna, Type::getType('string'));
        // Crie um objeto TableDiff para a tabela existente
        $tableDiff = new TableDiff($tabela, [], [], [$columnToRemove]);
        // Aplique as mudanças
        try {
            $schemaManager->alterTable($tableDiff);
            return true;
        } catch (\Doctrine\DBAL\Exception $e) {
            echo "Erro ao tentar remover a coluna: " . $e->getMessage();
            return false;
        }
    }

    public function alterarColuna(string $tabela, string $coluna, array $especificacao): bool
    {
        $schemaManager = $this->conn->createSchemaManager();
        $newColumnType = Type::getType($especificacao['type']);
        unset($especificacao['type']);
        $newColumn = new Column($coluna, $newColumnType, $especificacao);
        $columnDiff = new ColumnDiff($coluna, $newColumn, []);
        $tableDiff = new TableDiff($tabela, [], [$columnDiff], [], [], []);
        try {
            $schemaManager->alterTable($tableDiff);
            return true;
        } catch (\Doctrine\DBAL\Exception $e) {
            echo "Erro ao tentar alterar a coluna: " . $e->getMessage();
            return false;
        }
    }
}
