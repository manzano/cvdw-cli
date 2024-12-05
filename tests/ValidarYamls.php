<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

// Configuração do logger
$log = new Logger('CVDW-Logger');
$log->pushHandler(new StreamHandler(__DIR__ . '/../tests/log/cvdwComparador.log', Logger::DEBUG));

try {
    // Leitura do YAML remoto
    $url = 'https://docs-dev.cvcrm.com.br/yaml-files/cvdw.yaml';
    $convert = file_get_contents($url);
    if ($convert === false) {
        die('Erro ao baixar o arquivo remoto.');
    }
    $objetoUrl = Yaml::parse($convert);

    // Leitura do YAML local
    $localPaths = __DIR__ . '/../src/app/Objetos/cvdw.yaml';
    if (!file_exists($localPaths)) {
        die('Erro: O arquivo local não foi encontrado.');
    }
    $contents = Yaml::parseFile($localPaths);

} catch (ParseException $e) {
    $log->error('Erro ao analisar o arquivo YAML.', ['message' => $e->getMessage()]);
    die("Erro ao processar arquivos YAML: " . $e->getMessage());
}

// Função para comparar arrays recursivamente
function compararArrays(array $array1, array $array2, string $path = ''): array {
    $differences = [];

    foreach ($array1 as $key => $value) {
        $currentPath = $path ? "$path.$key" : $key;

        if (array_key_exists($key, $array2)) {
            if (is_array($value) && is_array($array2[$key])) {
                $subDiff = compararArrays($value, $array2[$key], $currentPath);
                $differences = array_merge($differences, $subDiff);
            } elseif ($value !== $array2[$key]) {
                $differences[] = "Diferença em '$currentPath': '" . print_r($value, true) . "' != '" . print_r($array2[$key], true) . "'";
            }
        } else {
            $differences[] = "Chave '$currentPath' está ausente no segundo array.";
        }
    }

    foreach ($array2 as $key => $value) {
        $currentPath = $path ? "$path.$key" : $key;

        if (!array_key_exists($key, $array1)) {
            $differences[] = "Chave '$currentPath' está ausente no primeiro array.";
        }
    }

    return $differences;
}

// Comparar os arquivos YAML
$differences = compararArrays($objetoUrl, $contents);

if (empty($differences)) {
    echo "Os arquivos YAML são iguais!" . PHP_EOL;
    return true; // Retorno explícito para indicar que os arquivos são iguais
} else {
    echo "Diferenças encontradas:" . PHP_EOL;
    foreach ($differences as $difference) {
        echo $difference . PHP_EOL;
    }
    return false; // Retorno explícito para indicar diferenças
}
