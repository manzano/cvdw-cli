<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

// Configurar o logger
$log = new Logger('CVDW-Logger');
$log->pushHandler(new StreamHandler(__DIR__ . '/../tests/log/cvdw.log', Logger::DEBUG));

// URL do arquivo YAML
$url = 'https://docs-dev.cvcrm.com.br/yaml-files/cvdw.yaml';

// Caminhos para salvar os arquivos localmente
$localPath = __DIR__ . '/../src/app/Objetos/cvdw.yaml';
$jsonPath = __DIR__ . '/../src/app/Objetos/cvdw.json';

$log->info('Iniciando o processo de download e validação do arquivo YAML.', [
    'url' => $url,
    'local_path' => $localPath,
    'json_path' => $jsonPath,
]);

try {
    // Baixar o arquivo YAML/JSON
    $log->info('Baixando o arquivo YAML...');
    $contents = file_get_contents($url);

    if ($contents === false) {
        $log->error('Erro ao baixar o arquivo da URL.', ['url' => $url]);
        throw new Exception("Erro ao baixar o arquivo da URL: $url");
    }

    // Excluir arquivo existente antes de salvar o novo
    if (file_exists($localPath)) {
        unlink($localPath);
        $log->info('Arquivo YAML anterior excluído.', ['local_path' => $localPath]);
    }

    // Salvar o arquivo no caminho especificado
    $saveFile = file_put_contents($localPath, $contents);
    if ($saveFile === false) {
        $log->error('Erro ao salvar o arquivo YAML.', ['local_path' => $localPath]);
        throw new Exception("Erro ao salvar o arquivo em: $localPath");
    }

    $log->info('Arquivo YAML baixado e salvo com sucesso.', ['local_path' => $localPath]);

    // Validar e converter YAML para JSON
    $log->info('Validando e convertendo o arquivo YAML para JSON...');
    $objetoCVDWConteudo = file_get_contents($localPath);
    $objetoCVDWArray = Yaml::parse($objetoCVDWConteudo);

    // Validar estrutura básica do YAML
    if (!isset($objetoCVDWArray['paths']) || !is_array($objetoCVDWArray['paths'])) {
        $log->warning('Estrutura YAML inválida: "paths" não encontrada ou mal formatada.', ['local_path' => $localPath]);
        throw new Exception("Estrutura inválida no arquivo YAML: 'paths' não encontrada ou mal formatada.");
    }

    // Converter para JSON
    $objetoCVDWJson = json_encode($objetoCVDWArray, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    $jsonSave = file_put_contents($jsonPath, $objetoCVDWJson);
    if ($jsonSave === false) {
        $log->error('Erro ao salvar o arquivo JSON.', ['json_path' => $jsonPath]);
        throw new Exception("Erro ao salvar o arquivo JSON em: $jsonPath");
    }

    $log->info('Arquivo JSON gerado e salvo com sucesso.', ['json_path' => $jsonPath]);

    
    $objetoCliJson = $objetoCVDWArray['get']['dados'];
    // Exibir e registrar os paths encontrados
    foreach ($objetoCVDWArray['paths'] as $objeto => $dados) {
        $log->info('Path encontrado na documentação do CVDW.', ['path' => $objeto]);
        echo "Path '$objeto' encontrado na documentação do CVDW.\n";
        foreach ($dados as $path => $metodos) {
            echo " $path\n";
        
            foreach ($metodos as $metodo => $detalhe) {
                echo "  Dados: $metodo\n";
        
                if (isset($detalhe['tags'])) {
                    echo "  Tags: " . implode(', ', $detalhe['tags']) . "\n";
                }
        
                if (isset($detalhe['description'])) {
                    echo "  Descrição: " . $detalhe['description'] . "\n";
                }
        
                if (isset($detalhe['parameters'])) {
                    echo "  Parâmetros:\n";
                    foreach ($detalhe['parameters'] as $parameter) {
                        echo "    - $parameter\n";
                    }
                }
        
                if (isset($detalhe['requestBody']['content']['application/json']['schema']['$ref'])) {
                    echo "  Request Body Schema: " . $detalhe['requestBody']['content']['application/json']['schema']['$ref'] . "\n";
                }
        
                if (isset($detalhe['responses'])) {
                    echo "  Respostas:\n";
                    foreach ($detalhe['responses'] as $status => $responseDetails) {
                        echo "    Status: $status\n";
                        echo "    Descrição: " . ($responseDetails['description'] ?? 'Sem descrição') . "\n";
        
                        if (isset($responseDetails['content']['application/json']['schema']['allOf'])) {
                            echo "    Schemas combinados:\n";
                            foreach ($responseDetails['content']['application/json']['schema']['allOf'] as $schema) {
                                echo "      - " . $schema['$ref'] . "\n";
                            }
                        }
                    }
                }
            }
        }
        
        

        // foreach ($dados as $cvdwMetodo => $detalhes) {
        //     $log->info('Metodo encontrado na documentação do CVDW.', ['metodo' => $cvdwMetodo]);
        //     echo "Método '$cvdwMetodo' encontrado na documentação do CVDW.\n";
        // }

        // foreach ($detalhes as $detalhe => $descricao) {
        //     if($dados == 'description') {
        //         $log->info('Detalhe encontrado na documentação do CVDW.', ['dados' => $dados]);
        //         echo "Detalhe '$detalhe' encontrado na documentação do CVDW.\n";
        //     } else if($detalhe == 'tags') {
        //         $log->info('Detalhe encontrado na documentação do CVDW.', ['detalhe' => $detalhe]);
        //         echo "Detalhe '$detalhe' encontrado na documentação do CVDW.\n";
        //     } else if($detalhe == 'parameters') {
        //         $log->info('Detalhe encontrado na documentação do CVDW.', ['detalhe' => $detalhe]);
        //         echo "Detalhe '$detalhe' encontrado na documentação do CVDW.\n";    
        //     } else if($detalhe == 'requestBody') {
        //         $log->info('Detalhe encontrado na documentação do CVDW.', ['detalhe' => $detalhe]);
        //         echo "Detalhe '$detalhe' encontrado na documentação do CVDW.\n";
        //     } else if($detalhe == 'responses') {
        //         $log->info('Detalhe encontrado na documentação do CVDW.', ['detalhe' => $detalhe]);
        //         echo "Detalhe '$detalhe' encontrado na documentação do CVDW.\n";
        //     } else {
        //     $log->info('Detalhe encontrado na documentação do CVDW.', ['detalhe' => $detalhe]);
        //     echo "Detalhe '$detalhe' encontrado na documentação do CVDW.\n";
        //     }
        // }

        // foreach ($descricao as $response => $campos) {
        //     $log->info('Responses encontrado na documentação do CVDW.', ['responses' => $response]);
        //     echo "Responses '$response' encontrado na documentação do CVDW.\n";
        // }
    }
    

} catch (ParseException $e) {
    $log->error('Erro ao analisar o arquivo YAML.', ['message' => $e->getMessage()]);
    echo "Erro ao analisar o arquivo YAML: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    $log->error('Erro durante o processo.', ['message' => $e->getMessage()]);
    echo "Erro: " . $e->getMessage() . "\n";
    exit(1);
}

$log->info('Processo concluído com sucesso.');
echo "\nProcesso concluído com sucesso.\n";
