<?php 
require_once __DIR__ . '/../vendor/autoload.php';
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Dumper;
use Symfony\Component\Yaml\Exception\ParseException;

$localPaths = __DIR__ . '/../src/app/Objetos/precadastros.yaml';
$json = file_get_contents($localPaths);

$jsonYaml = Yaml::parse($json);

$remote = 'https://docs-dev.cvcrm.com.br/yaml-files/cvdw.yaml/campos_adicionais';

$convert = file_get_contents($remote);
$objetoUrl = Yaml::parse($convert);
var_dump($jsonYaml);
die();