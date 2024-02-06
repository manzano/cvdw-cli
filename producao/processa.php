<?php
require __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Yaml\Parser;

$yaml = new Parser();

$dir = '../../CV-Aplicacao/docs/yaml-files/cvdw.yaml';
// remover cvcdw.json
unlink('cvdw.yaml');
copy($dir, 'cvdw.yaml');

$yamlContent = $yaml->parse(file_get_contents('cvdw.yaml'));
file_put_contents('cvdw.json', json_encode($yamlContent, JSON_PRETTY_PRINT));

$objetos = [];
$objetosArquivos = '<?php

    define("OBJETOS", [
';


$files = glob('../src/objetos/*'); // get all file names
foreach ($files as $file) { // iterate files
    if (is_file($file))
        unlink($file); // delete file
}

foreach ($yamlContent["paths"] as $caminho => $value) {

    $key_nomalizada = substr($caminho, 1);
    $key_nomalizada = str_replace('/', '_', $key_nomalizada);
    $key_nomalizada = str_replace('-', '_', $key_nomalizada);

    $parametros = [
        'header' => array(),
        'query' => array(),
        'path' => array(),
        'cookie' => array()
    ];

    foreach ($value["get"]['parameters'] as $tipo => $parametro) {
        if ($parametro['$ref']) {
            $parametro = explode('/', $parametro['$ref']);
            $parametro = end($parametro);
            $parametro = $yamlContent["components"]["parameters"][$parametro];
            $parametros[$parametro['in']][] = $parametro;
        } else {
            $parametros[$parametro['in']][] = $parametro;
        }
    }

    $schemas = [];
    $schemasAux = $value["get"]['responses']['200']['content']['application/json']['schema'];
    if(is_array($schemasAux)){
        foreach($schemasAux['allOf'] as $dadosAux){
            $schemas[] = $dadosAux['$ref'];
        }
    }else{
        $schemas[] = $schemasAux;
    }

    //print_r($schemas);

    $response = array();
    $qtdSchemas = count($schemas);
    for ($i = 0; $i < $qtdSchemas; $i++) {
        $schema = $schemas[$i];
        $schema = explode('/', $schema);
        $schema = end($schema);
        echo $schema . "\n";

        $responseYaml = $yamlContent["components"]["schemas"][$schema]["properties"];
        
        if(isset($yamlContent["components"]["schemas"][$schema]["properties"]["dados"]["items"]['$ref'])){
            $subschema = $yamlContent["components"]["schemas"][$schema]["properties"]["dados"]["items"]['$ref'];
            if($subschema){
                $subschema = explode('/', $subschema);
                $subschema = end($subschema);
                $responseYaml['dados'] = $yamlContent["components"]["schemas"][$subschema]["properties"];
            
                // Lista $responseYaml['dados'] e verifica se algum campo é um array
                foreach($responseYaml['dados'] as $key => $subValue){
                    if($subValue['type'] == 'array'){
                        $ref = $responseYaml['dados'][$key]['items']['$ref'];
                        // Se existe $ref, da o explode e pega o nome do schema
                        if($ref){
                            $ref = explode('/', $ref);
                            $ref = end($ref);
                            echo "---> SUB: " . $ref . "\n";
                            $responseYaml['dados'][$key] = $yamlContent["components"]["schemas"][$ref]["properties"];
                        }
                    } elseif(isset($subValue['type']) && $subValue['type'] == 'object'){
                        $responseYaml['dados'][$key] = $subValue['properties'];
                    }
                }
                
            }
        }
        
        $response = $response + $responseYaml;

        $body = $value["get"]['requestBody']['content']['application/json']['schema']['$ref'];
        $body = explode('/', $body);
        $body = end($body);
        $body = $yamlContent["components"]["schemas"][$body]["properties"];

        $objetos[$key_nomalizada] = [
            'path' => $caminho,
            'tabela' => $key_nomalizada,
            'metodo' => 'get',
            'descricao' => $value["get"]['description'],
            'nome' => $value["get"]['tags'][0] . " ($key_nomalizada)",
            'parametros' => $parametros,
            'schema' => $schema,
            'subschema' => $subschema,
            'body' => $body,
            'response' => $response

        ];
    }

        $objetosArquivos .= '
        "' . $key_nomalizada . '" => [
            "nome" => "' . $value["get"]['tags'][0] . ' (' . $caminho . ')",
            "arquivo" => "' . $key_nomalizada . '.json"
            
        ],
        ';
        
    file_put_contents('../src/objetos/' . $key_nomalizada . '.json', json_encode($objetos[$key_nomalizada], JSON_PRETTY_PRINT));
}

$objetosArquivos .= ' ]
);';
//echo $objetosArquivos;
echo 'Concluído!';
//print_r(json_encode($objetos['processos'], JSON_PRETTY_PRINT));
file_put_contents('cvdw.json', json_encode($objetos, JSON_PRETTY_PRINT));
file_put_contents('../src/Objetos.php', $objetosArquivos);
