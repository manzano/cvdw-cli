<?php
require __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Yaml\Parser;

$yaml = new Parser();

$dir = '../../CV-Aplicacao/docs/yaml-files/cvdw.yaml';
$yamlContent = $yaml->parse(file_get_contents($dir));

// Varrer $yamlContent["components"]["schemas"] e depois as propriedades de cada um
foreach($yamlContent["components"]["schemas"] as $key => $value){
    // Listar propriedades, verificar se o exemplo é uma string e se type é string tambem
    foreach($value['properties'] as $key2 => $value2){
        if(isset($value2['example']) && is_string($value2['example']) && $value2['type'] !== 'string'){
            echo $key . ' - ' . $key2 . ' - ' . strlen($value2['example']) . "\n";
        }
    }
}