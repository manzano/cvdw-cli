<?php
require  '../src/vendor/autoload.php';

use Symfony\Component\Yaml\Parser;

$yaml = new Parser();

// Listar todos os arquivos .json da pasta /src/app/Objetos
$files = glob('../src/app/Objetos/*.json');
// Fazer um foreach em $files e carregar o conteúdo de cada arquivo em $objeto
foreach($files as $file) {
    echo $file . PHP_EOL;
    $objeto = file_get_contents($file);
    $objeto = json_decode($objeto, true);
    // Fazer um foreach em $objeto['response']['dados'] e imprimir o que contem data no indice
    foreach($objeto['response']['dados'] as $indice => $valor) {
        if (strpos($indice, 'data') !== false) {
            echo ' ---> ' . $indice . PHP_EOL;
            // Substituir o valor de $objeto['response']['dados'][$indice] por 'datetime'
            $objeto['response']['dados'][$indice]['type'] = 'datetime';
        }
    }
    // Apagar o conteudo de file e Salvar o conteúdo de $objeto em $file
    file_put_contents($file, json_encode($objeto, JSON_PRETTY_PRINT));
}
