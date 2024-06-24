<?php

namespace Manzano\CvdwCli\Services;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\ProgressBar;

use Manzano\CvdwCli\Services\Console\CvdwSymfonyStyle;
use Manzano\CvdwCli\Services\DatabaseSetup;
use DateTime;

use Symfony\Component\Yaml\Yaml;

class Brain
{

    protected CvdwSymfonyStyle $io;
    public InputInterface $input;
    public OutputInterface $output;
    public array $brains = array();
    public DatabaseSetup $database;
    public int $processados = 0;
    public $treinarObj;
    public $conn;

    public function __construct(InputInterface $input, OutputInterface $output, $treinarObj)
    {
        $this->io = new CvdwSymfonyStyle($input, $output);
        $this->input = $input;
        $this->output = $output;
        $this->brains = $this->retornarConstantesBrain();
        $this->treinarObj = $treinarObj;
        $this->conn = conectarDB($input, $output);
    }

    public function conectar(): void
    {
        $this->conn = conectarDB($this->input, $this->output);
    }

    public function retornarBrains(string $brain = null): array
    {
        if ($brain) {
            if (isset($this->brains[$brain])) {
                return ["$brain" => $this->brains[$brain]];
            } else {
                return [];
            }
        } else {
            return $this->brains;
        }
    }

    public function retornarBrain(string $brain): array
    {
        // Verifica se o objeto existe em OBJETOS
        if (!array_key_exists($brain, $this->brains)) {
            return [];
        } else {
            $brainFile = __DIR__ . "/../Brain/{$brain}.yaml";
            $brain = file_get_contents($brainFile);
            return Yaml::parse($brain);
        }
    }

    public function retornarConstantesBrain(): array
    {
        return [
            "pessoas" => [
                "nome" => "Pessoas",
                "arquivo" => "pessoas.yaml"
            ],
            "vendas" => [
                "nome" => "Vendas",
                "arquivo" => "vendas.yaml"
            ],
            "reservas" => [
                "nome" => "Reservas",
                "arquivo" => "reservas.yaml"
            ]
        ];
    }

    public function processar(array $brain, $io): bool
    {
        $this->io = $io;

        if ($this->output->isDebug()) {
            $io->info(" LOG: " . __FUNCTION__);
        }

        try {
            $queryBuilder = $this->conn->createQueryBuilder();
            $brain['sql']['select'] = str_replace("\\", "", $brain['sql']['select']);
            $queryBuilder
                ->select($brain['sql']['select'])
                ->from($brain['sql']['from'])
                ->orderBy($brain['sql']['order_by']);
            $stmt = $queryBuilder->executeQuery();
            $rows = $stmt->fetchAllAssociative(); // Fetch all rows as an associative array
            $total = count($rows);
        } catch (\Exception $e) {
            // Captura outras exceções genéricas
            $this->io->error("Erro inesperado: " . $e->getMessage());
            $this->io->error("SQL: " . $queryBuilder->getSQL());
            return false;
        }

        $this->io->text('Registros encontrados: ' . $total);
        $progressBar = new ProgressBar($this->output, $total);
        $this->gerarDicionario($brain);
        if ($total > 0) {
            $progressBar->setFormat('normal'); // debug
            $progressBar->setBarCharacter('<fg=green>=</>');
            $progressBar->setProgressCharacter("\xF0\x9F\x9A\x80");
            $progressBar->setFormat(" Dados processados %current% de %max% [%bar%] %percent:3s%% \n %message%");
            $progressBar->setMessage($this->getMensagem());
            for ($i = 0; $i < $total; $i++) {
                $this->salvarBrainTXT($brain,$rows[$i][$brain['agrupar_por']], $rows[$i]);
                if($i == 0) {
                    $this->salvarBrainCSV($brain,$rows[$i][$brain['agrupar_por']],array_keys($rows[$i]));
                }
                $this->salvarBrainCSV($brain,$rows[$i][$brain['agrupar_por']],$rows[$i]);
                $progressBar->setMessage($this->getMensagem());
                $progressBar->display();
                $this->processados++;
                usleep(500);
            }
            $progressBar->finish();
        } else {
            $this->io->text('<fg=green>Nenhuma informação nova foi encontrada!</fg=green>');
            $progressBar->setMessage($this->getMensagem());
        }

        $this->io->newLine();
        return true;
    }

    private function gerarDicionario($brain){
        $arquivo = __DIR__ . "/../../../data/brain/dicionario/dicionario.txt";
        $fp = fopen($arquivo, 'a');

        $texto = "Dicionário de dados do CSV de ".$brain['objeto']."\n";
        foreach($brain['dicionario'] as $key => $value){
            $texto .= " ".$key.": ".$value['description']."\n";
        }
        $texto .= "\n\n";
        fwrite($fp, $texto);
        fclose($fp);
    }

    private function salvarBrainCSV($brain, $agrupador ,$dados) : void
    {
        // Abre o arquivo em modo de escrita
        $arquivo = __DIR__ . "/../../../data/brain/csv/{$brain['objeto']}_{$agrupador}.csv";
        $fp = fopen($arquivo, 'a');
        $dados = $this->tratarDados($dados);
        fputcsv($fp, $dados);
        fclose($fp);
    }

    private function salvarBrainTXT($brain, $agrupador, $dados): void
    {
        // Verifica se ../../data/brain/$objeto_$agrupamento.txt existe, se não existir, cria o arquivo.
        $arquivo = __DIR__ . "/../../../data/brain/txt/{$brain['objeto']}_{$agrupador}.txt";
        if(!file_exists($arquivo)){
            $fp = fopen($arquivo, "w");
            fclose($fp);
        }
        $texto = $brain['texto'];
        $texto = $this->processaVariaveis($texto, $dados);
        $texto = $texto."\n".$brain['delimitador_individual']."\n";

        // Adiciona os dados no arquivo.txt
        $fp = fopen($arquivo, "a");
        fwrite($fp, $texto);
        fclose($fp);
        
    }

    private function processaVariaveis($texto, $dados): string 
    {
        // Substituir as variáveis do texto pelos valores dos dados
        foreach ($dados as $key => $value) {
            if(is_null($value)) {
                $value = "NULL";
            }
            $value = $this->processaData($value);
            $texto = str_replace("{{" . $key . "}}", $value, $texto);
        }
        return $texto;
    }

    private function tratarDados($dados): array
    {
        $dadosTratados = [];
        foreach ($dados as $key => $value) {
            if(is_null($value)) {
                $value = "NULL";
            }
            $value = trim($value);
            $value = $this->processaData($value);
            $dadosTratados[$key] = $value;
        }
        return $dadosTratados;
    }

    private function processaData($data): string
    {
        // Tenta criar um objeto DateTime a partir da string fornecida
        $dateTime = DateTime::createFromFormat('Y-m-d H:i:s', $data);
        // Verifica se a string é uma data válida e bem formatada
        if ($dateTime && $dateTime->format('Y-m-d H:i:s') === $data) {
            $dataFormatada = $dateTime->format('d/m/Y');
            $horaFormatada = $dateTime->format('H:i:s');
            return $dataFormatada . " às " . $horaFormatada;
        } else {
            // Retorna a string original se não for uma data válida
            return $data;
        }
    }

    public function getMensagem($info = false, $erro = false): string
    {

        // Se inseridoserros for maior que 1, imprimir o (s)
        $mensagem = "";
        $mensagem .= "Processados: <fg=green>" . $this->processados . " sucesso" . (($this->processados > 1) ? 's' : '') . "</fg=green> / ";
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

    public function apagarArquivosBrain(): void{
        // Listar todos os arquivos .csv e .txt da pasta ../../data/brain
        $arquivos = glob(__DIR__ . '/../../../data/brain/*.{csv,txt}', GLOB_BRACE);
        foreach ($arquivos as $arquivo) {
            unlink($arquivo);
        }
        $arquivos = glob(__DIR__ . '/../../../data/brain/csv/*.{csv,txt}', GLOB_BRACE);
        foreach ($arquivos as $arquivo) {
            unlink($arquivo);
        }
        $arquivos = glob(__DIR__ . '/../../../data/brain/txt/*.{csv,txt}', GLOB_BRACE);
        foreach ($arquivos as $arquivo) {
            unlink($arquivo);
        }
        $arquivos = glob(__DIR__ . '/../../../data/brain/dicionario/*.{csv,txt}', GLOB_BRACE);
        foreach ($arquivos as $arquivo) {
            unlink($arquivo);
        }
    }
}
