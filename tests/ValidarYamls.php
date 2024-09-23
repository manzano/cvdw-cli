<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Dumper;

// Fazer o download de $urlYamlCVDQ e salvar em ../src/app/Objetos/cvdw.yaml
//$url = 'https://docs-dev.cvcrm.com.br/yaml-files/cvdw.yaml';
$url = __DIR__ . '/../../CV-Aplicacao/docs/yaml-files/cvdw.yaml';
// Caminho local onde o arquivo será salvo
$localPath = '../src/app/Objetos/cvdw.yaml';
// Usa file_get_contents para pegar o arquivo do URL
$contents = file_get_contents($url);
if ($contents !== false) {
    unlink($localPath);
    // Salva o conteúdo no arquivo local especificado
    $saveFile = file_put_contents($localPath, $contents);
    if ($saveFile !== false) {
        echo "Arquivo baixado e salvo com sucesso!";
    } else {
        echo "Erro ao salvar o arquivo.";
    }
} else {
    echo "Erro ao baixar o arquivo.";
}
echo "\n";
echo "Validando os arquivos YAMLs\n";
echo "\n";

$objetoCVDWFile = __DIR__ . "/$localPath";
// $objetoCVDWCOnteudo é um Yaml de uma documentacao de Apis no formato OpenApi
$objetoCVDWConteudo = file_get_contents($objetoCVDWFile);
// $objetoCVDWJson é um json de $objetoCVDWCOnteudo
$objetoCVDWJson =  Yaml::parse($objetoCVDWConteudo);

//print_r($objetoCVDWJson);
//exit();

foreach ($objetoCVDWJson['paths'] as $path => $objetoCVDWDados) {
    echo $path . "\n";

}

echo "Validando os arquivos YAMLs\n";