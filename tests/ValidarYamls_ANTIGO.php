<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Dumper;

// Fazer o download de $urlYamlCVDQ e salvar em ../src/app/Objetos/cvdw.yaml
//$url = 'https://docs-dev.cvcrm.com.br/yaml-files/cvdw.yaml';
//$url = __DIR__ . '/../../CV-Aplicacao/docs/yaml-files/cvdw.yaml';
$url = 'https://docs-dev.cvcrm.com.br/yaml-files/cvdw.yaml';
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
// $objetoCVDWCOnteudo é um Yaml de uma documentacao de Apis no formasto OpenApi
$objetoCVDWConteudo = file_get_contents($objetoCVDWFile);
// $objetoCVDWJson é um json de $objetoCVDWCOnteudo
$objetoCVDWJson =  Yaml::parse($objetoCVDWConteudo);

// print_r($objetoCVDWJson);
// exit();

foreach ($objetoCVDWJson['paths'] as $path => $objetoCVDWDados) {
    echo $path . "\n";

    $objetoCVDWDados = tratarRefs($objetoCVDWDados['get'], $objetoCVDWJson);
    $objetoCVDWDados['path'] = $path;
    $objetoCVDWDados = tratarObjetoCVDW($objetoCVDWDados);

    $objetoCVDWBody = tratarBodyCVDW($objetoCVDWDados['get']);

    $dumper = new Dumper();
    $yaml = $dumper->dump($objetoCVDWDados, 4, 2, Yaml::DUMP_OBJECT_AS_MAP | Yaml::DUMP_EMPTY_ARRAY_AS_SEQUENCE | Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK);

    // Remove a primeira / de $path
    //$objetoCliNome = substr($path, 1);
    $objetoCliNome = $path;
    $objetoCliNome = str_replace("/", "_", substr($objetoCliNome, 1));
    $objetoCliNome = str_replace("-", "_", $objetoCliNome);

    // Verifica se o arquivo ../src/app/Objetos/$objeto.yaml existe
    $objetoCliFile = __DIR__ . "/../src/app/Objetos/$objetoCliNome.yaml";
    if (!file_exists($objetoCliFile) || !isset($objetoCVDWDados)) {
        echo "---> ERRO! Arquivo $objetoCliNome não encontrado\n";
        continue;
    }

    // Carrega o arquivo
    $objetoCliConteudo = file_get_contents($objetoCliFile);
    $objetoCliJsonCompleto = Yaml::parse($objetoCliConteudo);

    $objetoCliJson = $objetoCliJsonCompleto['response']['dados'];

    $objetoPassado = null;
    foreach ($objetoCVDWDados as $objeto => $dados) {
        if (isset($objetoCliJson[$objeto])) {
            $diferencas = compareArrays($dados, $objetoCliJson[$objeto]);
            if ($dados['type'] <> $objetoCliJson[$objeto]['type']) {
                if ($dados['type'] == 'string' && $objetoCliJson[$objeto]['type'] == 'datetime') {
                    echo " --> OK -> $objeto";
                    echo "\n";
                } else if ($dados['type'] == 'string' && $objetoCliJson[$objeto]['type'] == 'text') {
                        echo " --> OK -> $objeto"; 
                        echo "\n";   
                } else {
                    echo " --> ERRO -> $objeto -> " . $dados['type'] . " <> " . $objetoCliJson[$objeto]['type'];
                    echo " ($objetoCliFile)";
                    echo "\n";
                }
            } else {
                echo " --> OK -> $objeto";
                echo "\n";
            }
        } else {
            echo " --> ERRO -> $objeto -> Somente na Aplicação";
            echo " ($objetoCliFile)";
            $objetoCliJsonCompleto['response']['dados'] = insertAfterKey($objetoCliJsonCompleto['response']['dados'], $objetoPassado, $objeto, $dados);
            echo "\n";
        }
        $objetoPassado = $objeto;
    }

    // Remover example e description
    foreach ($objetoCliJsonCompleto['response'] as $objeto => $dados) {
        if($objeto <> 'dados') {
            unset($objetoCliJsonCompleto['response'][$objeto]['example']);
            unset($objetoCliJsonCompleto['response'][$objeto]['description']);
        }
    }
    foreach ($objetoCliJsonCompleto['response']['dados'] as $objeto => $dados) {
        unset($objetoCliJsonCompleto['response']['dados'][$objeto]['example']);
        unset($objetoCliJsonCompleto['response']['dados'][$objeto]['description']);
    }
    foreach ($objetoCliJsonCompleto['body'] as $objeto => $dados) {
        unset($objetoCliJsonCompleto['body'][$objeto]['example']);
        unset($objetoCliJsonCompleto['body'][$objeto]['description']);
    }

    foreach($objetoCliJson as $objeto => $dados){
        if (!isset($objetoCVDWDados[$objeto])) {
            echo " --> ERRO -> $objeto -> Somente no CLI\n";
            unset($objetoCliJsonCompleto['response']['dados'][$objeto]);
        } 
    }

    //body
    print_r($objetoCVDWDados);
    exit;
    foreach ($objetoCliJson as $objeto => $dados) {
        if(!isset($objetoCVDWJson['body'][$objeto])) {
            unset($objetoCliJsonCompleto['body'][$objeto]);
            continue;
        }
        $objetoCliJsonCompleto['body'][$objeto]['description'] = $objetoCVDWDados[$objeto]['description'];
        $objetoCliJsonCompleto['body'][$objeto]['example'] = $objetoCVDWDados[$objeto]['example'];
    }

    //response

    foreach ($objetoCliJson as $objeto => $dados) {
        $objetoCliJsonCompleto['response']['dados'][$objeto]['description'] = $objetoCVDWDados[$objeto]['description'];
        $objetoCliJsonCompleto['response']['dados'][$objeto]['example'] = $objetoCVDWDados[$objeto]['example'];  
    }

    $objetoCliJsonCompleto = ensureDateString($objetoCliJsonCompleto);

    $novoYaml = Yaml::dump(
        $objetoCliJsonCompleto,
        10, // Nível de profundidade para evitar expansão inline
        2,  // Indentação
        Yaml::DUMP_OBJECT_AS_MAP | Yaml::DUMP_EMPTY_ARRAY_AS_SEQUENCE |
        Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK  // Flags adicionais para tratar objetos como strings
    );
    $objetoCliFile = __DIR__ . "/../src/app/Objetos/$objetoCliNome.yaml";
    file_put_contents($objetoCliFile, $novoYaml);

    //$diferencas = compareArrays($objetoCVDWDados, $objetoCliJson);
    //print_r($diferencas);
    //exit();
}

echo "Validando os arquivos YAMLs\n";

function tratarRefs($objetoCVDWDados, $objetoCVDWJson)
{

    foreach ($objetoCVDWDados['parameters'] as $key => $parametro) {
        if (isset($parametro['$ref'])) {
            // $parametro['$ref'] = "#/components/parameters/Id";
            $parametro['$ref'] = str_replace("#/components/parameters/", "", $parametro['$ref']);
            //echo "Ref: " . $parametro['$ref'] . "\n";
            $objetoCVDWDados['parameters'][$key] = $objetoCVDWJson['components']['parameters'][$parametro['$ref']];
        }
    }

    $body = $objetoCVDWDados['requestBody']['content']['application/json']['schema'];
    if (isset($body['$ref'])) {
        $body['$ref'] = str_replace("#/components/schemas/", "", $body['$ref']);
        $objetoCVDWDados['requestBody']['content']['application/json']['schema'] = $objetoCVDWJson['components']['schemas'][$body['$ref']]['properties'];
    }

    $response = $objetoCVDWDados['responses']['200']['content']['application/json']['schema']['allOf'];
    $schemasAux = [];
    foreach ($response as $key => $resposta) {
        if (isset($resposta['$ref'])) {
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
    //print_r($objetoCVDWDados);

    return $objetoCVDWDados;
}

function tratarObjetoCVDW($objetoCVDWDados)
{
    $objetoCVDWDadosAdaptado = [];
    $objetoCVDWDadosAdaptado = $objetoCVDWDados['responses']['200']['content']['application/json']['schema']['dados'];
    return $objetoCVDWDadosAdaptado;
}

function tratarBodyCVDW($objetoCVDWDados)
{
    print_r($objetoCVDWDados); exit;
    $objetoCVDWBodyAdaptado = [];
    $objetoCVDWBodyAdaptado = $objetoCVDWDados['responses']['content']['application/json']['schema'];
    return $objetoCVDWBodyAdaptado;
}

function compareArrays($array1, $array2)
{
    $result = [];

    foreach ($array1 as $key => $value) {
        if (!array_key_exists($key, $array2)) {
            // A chave não existe no segundo array, mostra apenas o valor do primeiro
            $result[$key] = ['de' => $value];
        } else {
            if (is_array($value)) {
                // Se é um array, chama a função recursivamente
                $diff = compareArrays($value, $array2[$key]);
                if (!empty($diff)) {
                    $result[$key] = $diff;
                }
            } else {
                if ($value !== $array2[$key]) {
                    // Diferença encontrada, armazena os valores "de" e "para"
                    $result[$key] = ['de' => $value, 'para' => $array2[$key]];
                }
            }
        }
    }

    // Verifica se há chaves no segundo array que não estão no primeiro
    foreach ($array2 as $key => $value) {
        if (!array_key_exists($key, $array1)) {
            // A chave não existe no primeiro array, mostra apenas o valor do segundo
            $result[$key] = ['para' => $value];
        }
    }

    return $result;
}

function insertAfterKey($array, $key, $newKey, $newValue)
{
    $newArray = [];
    $inserted = false;

    foreach ($array as $currentKey => $currentValue) {
        // Copia o elemento atual para o novo array
        $newArray[$currentKey] = $currentValue;

        // Se o elemento atual é a chave após a qual queremos inserir, faça a inserção
        if ($currentKey === $key) {
            $newArray[$newKey] = $newValue;
            $inserted = true;
        }
    }

    // Se a chave após a qual o valor deveria ser inserido não foi encontrada, você pode decidir o que fazer:
    // Opção 1: Adicionar no final se não encontrou a chave
    if (!$inserted) {
        $newArray[$newKey] = $newValue;
    }

    // Opção 2: Lançar um erro ou simplesmente não fazer nada (dependendo de sua lógica de negócio)

    return $newArray;
}

function ensureDateString($array)
{
    foreach ($array as $key => &$value) {
        if (is_array($value)) {
            // Chamada recursiva para subarrays
            $value = ensureDateString($value);
        } elseif (is_string($value) && preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $value)) {
            // Verifica se o valor é uma data no formato 'YYYY-MM-DD HH:MM:SS'
            // E adiciona aspas apenas se o valor for uma string que corresponde ao formato de data
            $value = "'$value'";
        }
    }
    return $array;
}