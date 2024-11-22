<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Dumper;
use Symfony\Component\Yaml\Exception\ParseException;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

// Configurar o logger
$log = new Logger('CVDW-Logger');
$log->pushHandler(new StreamHandler(__DIR__ . '/../tests/log/cvdwComparador.log', Logger::DEBUG));
// Fazer o download de $urlYamlCVDQ e salvar em ../src/app/Objetos/cvdw.yaml
$url = 'https://docs-dev.cvcrm.com.br/yaml-files/cvdw.yaml';
//$url = __DIR__ . '/../../CV-Aplicacao/docs/yaml-files/cvdw.yaml';
//$url = __DIR__ . 'https://dev.cvcrm.com.br/api/v1/cvdw';
// Caminho local onde o arquivo será salvo
$localPath = '../src/app/Objetos/cvdw.yaml';
// Caminho onde o arquivo JSON convertido será salvo
$jsonPath = __DIR__ . '/../src/app/Objetos/cvdw.json';


//Usa file_get_contents para pegar o arquivo do URL
echo "Baixando o arquivo...\n";
$contents = file_get_contents($url);

if ($contents !== false) {
    // Verifica se o arquivo já existe e exclui antes de salvar
    if (file_exists($localPath)) {
        unlink($localPath);
    }

    // Salvar o arquivo no caminho especificado
    $saveFile = file_put_contents($localPath, $contents);
    if ($saveFile !== false) {
        echo "Arquivo YAML baixado e salvo com sucesso em '$localPath'!\n";
    } else {
        echo "Erro ao salvar o arquivo YAML.\n";
        exit(1);
    }
} else {
    echo "Erro ao baixar o arquivo.\n";
    exit(1);
}

echo "\nValidando e convertendo o arquivo YAML para JSON...\n";

try {
    // Carregar e analisar o arquivo YAML
    $objetoCVDWConteudo = file_get_contents($localPath);
    $objetoCVDWArray = Yaml::parseFile($objetoCVDWConteudo);

    // Validar a estrutura básica do YAML
    if (!isset($objetoCVDWArray['paths']) || !is_array($objetoCVDWArray['paths'])) {
        throw new Exception("Estrutura inválida: 'paths' não encontrada ou mal formatada.");
    }

    // Converter o array do YAML para JSON
    $objetoCVDWJson = json_encode($objetoCVDWArray, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

    // Salvar o JSON no caminho especificado
    $jsonSave = file_put_contents($jsonPath, $objetoCVDWJson);
    if ($jsonSave !== false) {
        echo "Arquivo convertido e salvo como JSON em '$jsonPath'.\n";
    } else {
        echo "Erro ao salvar o arquivo JSON.\n";
        exit(1);
    }

    // Exemplo de validação e exibição de paths do JSON
    foreach ($objetoCVDWArray['paths'] as $objeto => $dados) {
        echo "Path '$objeto' encontrado na documentação do CVDW.\n";
    }
} catch (ParseException $e) {
    echo "Erro ao analisar o YAML: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\nProcesso concluído com sucesso.\n";

