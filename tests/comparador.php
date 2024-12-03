<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Dumper;
use Symfony\Component\Yaml\Exception\ParseException;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$log = new Logger('CVDW-Logger');
$log->pushHandler(new StreamHandler(__DIR__ . '/../tests/log/cvdwComparador.log', Logger::DEBUG));


try {
$url = 'https://docs-dev.cvcrm.com.br/yaml-files/cvdw.yaml';
$convert = file_get_contents($url);
$objetoUrl = Yaml::parse($convert);

// var_dump($objetoUrl);
// die();

$localPaths = __DIR__ . '/../src/app/Objetos/cvdw.yaml';
$contents = Yaml::parseFile($localPaths);
 
} catch (ParseException $e) {
    $log->error('Erro ao analisar o arquivo YAML.', ['message' => $e->getMessage()]);   
}


function compararArrays($array1, $array2, $path = '') {
    $differences = [];

    // Iterar sobre o primeiro array
    foreach ($array1 as $key => $value) {
        $currentPath = $path ? "$path.$key" : $key;

        if (array_key_exists($key, $array2)) {
            if (is_array($value) && is_array($array2[$key])) {
                // Recursão para subarrays
                $subDiff = compararArrays($value, $array2[$key], $currentPath);
                $differences = array_merge($differences, $subDiff);
            } elseif ($value !== $array2[$key]) {
                // Valores diferentes
                $differences[] = "Diferença em '$currentPath': '" . print_r($value, true) . "' != '" . print_r($array2[$key], true) . "'";
            }
        } else {
            // Chave ausente no segundo array
            $differences[] = "Chave '$currentPath' está ausente no segundo array.";
        }
    }

    // Verificar chaves presentes no segundo array, mas ausentes no primeiro
    foreach ($array2 as $key => $value) {
        $currentPath = $path ? "$path.$key" : $key;

        if (!array_key_exists($key, $array1)) {
            $differences[] = "Chave '$currentPath' está ausente no primeiro array.";
        }
    }

    return $differences;
}


$differences = compararArrays($objetoUrl, $contents);

if (empty($differences)) {
    echo "Os arquivos YAML são iguais!" . PHP_EOL;
} else {
    echo "Diferenças encontradas:" . PHP_EOL;
    foreach ($differences as $difference) {
        echo $difference . PHP_EOL;
    }
}