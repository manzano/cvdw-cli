<?php

include __DIR__ . '/../vendor/autoload.php';
use Symfony\Component\Yaml\Yaml;

echo "Comparador de arquivos YAML\n";
echo "===========================\n\n";


echo "Baixando arquivo cvdw.yaml do CV...\n";
$url = 'https://docs-dev.cvcrm.com.br/yaml-files/cvdw.yaml';
// Baixamos o conteúdo do arquivo e salvamos em _temp/cvdw.yaml
$down = file_put_contents(__DIR__ . '/cvdw/cvdw.yaml', file_get_contents($url));
if ($down) {
    echo "✅ Arquivo baixado com sucesso!\n";
} else {
    echo "❌ Erro ao baixar o arquivo!\n";
    exit;
}

echo "\n";
echo "Convertendo arquivo baixado...\n";

try {
    $data = Yaml::parseFile(__DIR__ . '/cvdw/cvdw.yaml');
    echo "✅ Arquivo convertido com sucesso!\n";
} catch (ParseException $e) {
    echo "❌ Erro ao converter o arquivo baixado: " . $e->getMessage() . "\n";
    exit;
}

echo "\n";
echo "Vamos higienizar os dados do YAML baixado...\n";
echo "Removendo openapi, info, servers...\n";
unset($data['openapi']);
unset($data['info']);
unset($data['servers']);
unset($data['tags']);
echo "✅ Dados removidos com sucesso!\n";


echo "\n";
echo "Varrendo paths e removendo tags, description, operationId, summary, parameters, requestBody...\n";
foreach ($data['paths'] as $path => $methods) {
    foreach ($methods as $method => $props) {
        unset($data['paths'][$path][$method]['tags']);
        unset($data['paths'][$path][$method]['description']);
        unset($data['paths'][$path][$method]['operationId']);
        unset($data['paths'][$path][$method]['summary']);
        unset($data['paths'][$path][$method]['parameters']);
        unset($data['paths'][$path][$method]['requestBody']);
        unset($data['paths'][$path]['description']);
        unset($data['paths'][$path]['content']['application/json']['examples']);
        $data['paths'][$path] = $data['paths'][$path][$method]['responses']['200']['content']['application/json']['schema'];
    }
}
echo "✅ Paths varridos com sucesso!\n";

echo "\n";
echo "Tratando os \$refs dos schemas...\n";
foreach ($data['paths'] as $path => $dados) {
    foreach ($dados['allOf'] as $key => $value) {
        if (isset($value['$ref'])) {
            $ref = explode('/', ltrim($value['$ref'], '#/'));
            $data['paths'][$path] = array_merge($data['paths'][$path], $data['components']['schemas'][$ref[2]]['properties']);
        }
    }
    unset($data['paths'][$path]['allOf']);
    unset($data['paths'][$path]['dados']['example']);
}
echo "✅ \$Refs varridos com sucesso!\n";


echo "\n";
echo "Tratando os \$refs de dados dos schemas...\n";
foreach ($data['paths'] as $path => $dados) {
    if (isset($dados['dados']['items']['$ref'])) {
        $ref = explode('/', ltrim($dados['dados']['items']['$ref'], '#/'));
        $data['paths'][$path]['dados'] = $data['components']['schemas'][$ref[2]]['properties'];
    }
    if (isset($dados['dados']['$ref'])) {
        $ref = explode('/', ltrim($dados['dados']['$ref'], '#/'));
        $data['paths'][$path]['dados'] = $data['components']['schemas'][$ref[2]]['items']['properties'];
    }
}



echo "✅ \$Refs varridos com sucesso!\n";

echo "Tratando os dados que contem itens...\n";
foreach ($data['paths'] as $path => $dados) {
    foreach ($data['paths'][$path]['dados'] as $k => $v) {
        if (isset($v['items']['$ref'])) {
            $ref = explode('/', ltrim($v['items']['$ref'], '#/'));
            //print_r($ref);
            foreach ($data['components']['schemas'][$ref[2]]['properties'] as $key => $value) {
                //echo $key . " -> ".$value['type']." \n";
                $data['paths'][$path]['dados'][$k][$key] = $value['type'];
            }
        }
    }
}

echo "✅ Exemplos e descrições removidos com sucesso!\n";

echo "\n";
echo "Limpando exemplos e descrições...\n";
foreach ($data['paths'] as $path => $dados) {

    foreach ($dados as $key => $value) {
        if (isset($value['example'])) {
            unset($data['paths'][$path][$key]['example']);
        }
        if (isset($value['description'])) {
            unset($data['paths'][$path][$key]['description']);
        }
        if (isset($value['type'])) {
            $data['paths'][$path][$key] = $value['type'];
        }
        if ($key == 'dados') {

            foreach ($data['paths'][$path]['dados'] as $k => $v) {
                if (isset($data['paths'][$path]['dados'][$k]['example'])) {
                    unset($data['paths'][$path]['dados'][$k]['example']);
                }
                if (isset($data['paths'][$path]['dados'][$k]['description'])) {
                    unset($data['paths'][$path]['dados'][$k]['description']);
                }
                if (isset($data['paths'][$path]['dados'][$k]['type'])) {
                    if ($data['paths'][$path]['dados'][$k]['type'] == 'object') {
                        unset($data['paths'][$path]['dados'][$k]['type']);
                        if (isset($data['paths'][$path]['dados'][$k]['items']) &&
                            is_array($data['paths'][$path]['dados'][$k]['items'])) {
                            foreach ($data['paths'][$path]['dados'][$k]['items'] as $key => $value) {
                                $data['paths'][$path]['dados'][$k][$key] = $value['type'];
                            }
                            unset($data['paths'][$path]['dados'][$k]['items']);
                        }
                        if (isset($data['paths'][$path]['dados'][$k]['properties']) &&
                            is_array($data['paths'][$path]['dados'][$k]['properties'])) {
                            foreach ($data['paths'][$path]['dados'][$k]['properties'] as $key => $value) {
                                $data['paths'][$path]['dados'][$k][$key] = $value['type'];
                            }
                            unset($data['paths'][$path]['dados'][$k]['properties']);
                        }
                    } else {
                        $data['paths'][$path]['dados'][$k] = $data['paths'][$path]['dados'][$k]['type'];
                    }
                }

            }
        } else {
            //$data['paths'][$path] = $data['paths'][$path]['dados'];
        }
    }

}
echo "✅ Exemplos e descrições removidos com sucesso!\n";

echo "\n";
echo "Removendo components...\n";
unset($data['components']);
echo "✅ Components removidos com sucesso!\n";

echo "\n";
echo "Salvamos JSON com o Yaml convertido...\n";
file_put_contents(__DIR__ . '/cvdw/cvdw.json', json_encode($data, JSON_PRETTY_PRINT));
echo "✅ Json do CVDW salvo com sucesso!\n";

echo "\n";
echo "Lista arquivos de objetos do CLI\n";
// Lista os arquivos .yaml da pasta ../src/app/Objetos
$Objetos = __DIR__ . '/../src/app/Objetos';
$locals = glob($Objetos . '/*.yaml');
foreach ($locals as $local) {
    echo "-> " . basename($local) . "\n";
    $dataLocal = Yaml::parseFile($local);
    unset($dataLocal['tabela']);
    unset($dataLocal['metodo']);
    unset($dataLocal['descricao']);
    unset($dataLocal['parametros']);
    unset($dataLocal['schema']);
    unset($dataLocal['subschema']);
    unset($dataLocal['body']);
    $path = $dataLocal['path'];
    foreach ($dataLocal['response'] as $campo => $valor) {
        if ($campo <> 'dados') {
            $dataLocal['response'][$campo] = $valor['type'];
        }
    }


    foreach ($dataLocal['response']['dados'] as $campo => $valor) {
        if (isset($valor['type'])) {
            $dataLocal['response']['dados'][$campo] = $valor['type'];
        }
    }

    foreach ($dataLocal['response']['dados'] as $campo => $valor) {
        if (is_array($valor)) {
            foreach ($dataLocal['response']['dados'][$campo] as $campo2 => $valor2) {
                $dataLocal['response']['dados'][$campo][$campo2] = $valor2['type'];
            }
        }
    }

    //echo "\n";

    echo "Salvamos JSON convertido em cvdw/objetos...\n";
    file_put_contents(__DIR__ . '/cvdw/objetos/' . basename($local, '.yaml') . '.json', json_encode($dataLocal, JSON_PRETTY_PRINT));
    echo "✅ Json do Objeto salvo com sucesso!\n";
    echo "\n";
    echo "\n";

}
