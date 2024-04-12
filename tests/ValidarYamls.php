<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Dumper;

$objetoCVDWFile = __DIR__ . "/../../CV-Aplicacao/docs/yaml-files/cvdw.yaml";
// $objetoCVDWCOnteudo é um Yaml de uma documentacao de Apis no formasto OpenApi
$objetoCVDWConteudo = file_get_contents($objetoCVDWFile);
// $objetoCVDWJson é um json de $objetoCVDWCOnteudo
$objetoCVDWJson =  Yaml::parse($objetoCVDWConteudo);

foreach($objetoCVDWJson['paths'] as $path => $objetoCVDWDados){
    echo $path . "\n";

    echo json_encode($objetoCVDWDados, JSON_PRETTY_PRINT) . "\n";
    exit;

    $objetoCVDWDados = tratarRefs($objetoCVDWDados['get'], $objetoCVDWJson);
    $objetoCVDWDados['path'] = $path;
    $objetoCVDWDadosAdaptado = tratarObjetoCVDW($objetoCVDWDados);

    $dumper = new Dumper();
    echo $yaml = $dumper->dump($objetoCVDWDadosAdaptado, 4, 2, Yaml::DUMP_OBJECT_AS_MAP | Yaml::DUMP_EMPTY_ARRAY_AS_SEQUENCE | Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK);
    

    exit();

    // Remove a primeira / de $path
    //$objetoCliNome = substr($path, 1);
    $objetoCliNome = $path;
    $objetoCliNome = str_replace("/", "_", substr($objetoCliNome, 1));
    $objetoCliNome = str_replace("-", "_", $objetoCliNome);

    // Verifica se o arquivo ../src/app/Objetos/$objeto.yaml existe
    $objetoCliFile = __DIR__ . "/../src/app/Objetos/$objetoCliNome.yaml";
    if(!file_exists($objetoCliFile) || !isset($objetoCVDWDados)){
        echo "---> ERRO! Arquivo $objetoCliNome não encontrado\n";
        continue;
    }

    // Carrega o arquivo
    $objetoCliConteudo = file_get_contents($objetoCliFile);
    $objetoCliJson = Yaml::parse($objetoCliConteudo);

    //print_r($objetoCVDWDados);
    //print_r($objetoCliJson);
    exit();
}

echo "Validando os arquivos YAMLs\n";

function tratarRefs($objetoCVDWDados, $objetoCVDWJson){

        foreach($objetoCVDWDados['parameters'] as $key => $parametro){
            if(isset($parametro['$ref'])){
                // $parametro['$ref'] = "#/components/parameters/Id";
                $parametro['$ref'] = str_replace("#/components/parameters/", "", $parametro['$ref']);
                //echo "Ref: " . $parametro['$ref'] . "\n";
                $objetoCVDWDados['parameters'][$key] = $objetoCVDWJson['components']['parameters'][$parametro['$ref']];
            }
        }

        $body = $objetoCVDWDados['requestBody']['content']['application/json']['schema'];
        if(isset($body['$ref'])){
            $body['$ref'] = str_replace("#/components/schemas/", "", $body['$ref']);
            $objetoCVDWDados['requestBody']['content']['application/json']['schema'] = $objetoCVDWJson['components']['schemas'][$body['$ref']]['properties'];
        }

        $response = $objetoCVDWDados['responses']['200']['content']['application/json']['schema']['allOf'];
        $schemasAux = [];
        foreach($response as $key => $resposta){
            if(isset($resposta['$ref'])){
                $resposta['$ref'] = str_replace("#/components/schemas/", "", $resposta['$ref']);
                $dadosAux = $objetoCVDWJson['components']['schemas'][$resposta['$ref']]['properties'];
                $schemasAux = array_merge($schemasAux, $dadosAux);
            }
        }
        unset($objetoCVDWDados['responses']['200']['content']['application/json']['schema']['allOf']);
        $objetoCVDWDados['responses']['200']['content']['application/json']['schema'] = $schemasAux;

        $dados = $objetoCVDWDados['responses']['200']['content']['application/json']['schema']['dados']['items'];
        $dados['$ref'] = str_replace("#/components/schemas/", "", $dados['$ref']);
        $schemasAux['dados'] = $objetoCVDWJson['components']['schemas'][$dados['$ref']]['properties'];
        
        $objetoCVDWDados['responses']['200']['content']['application/json']['schema'] = $schemasAux;
        print_r($objetoCVDWDados);

    return $objetoCVDWDados;
}

function tratarObjetoCVDW($objetoCVDWDados){
    $objetoCVDWDadosAdaptado = [];
    $objetoCVDWDadosAdaptado['path'] = $objetoCVDWDados['path'];
    $objetoCVDWDadosAdaptado['tabela'] = 'tabela';
    $objetoCVDWDadosAdaptado['metodo'] = 'metodo';
    $objetoCVDWDadosAdaptado['descricao'] = 'descricao';
    $objetoCVDWDadosAdaptado['nome'] = 'nome';

    $objetoCVDWDadosAdaptado['parametros'] = array(
        'header' => [],
        'query' => [],
        'path' => [],
        'cookie' => []
    );
    foreach($objetoCVDWDados['parameters'] as $tipo => $parametro){
        $objetoCVDWDadosAdaptado['parametros'][$parametro['in']][$tipo] = $parametro;
    }
    $objetoCVDWDadosAdaptado['parametros']['query'];;
    $objetoCVDWDadosAdaptado['parametros']['path'];
    $objetoCVDWDadosAdaptado['parametros']['cookie'];

    return $objetoCVDWDadosAdaptado;
}
