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
}
echo "✅ \$Refs varridos com sucesso!\n";

echo "Tratando os dados que contem itens...\n";
foreach ($data['paths'] as $path => $dados) {
    foreach($data['paths'][$path]['dados'] as $k => $v) {
        if (isset($v['items']['$ref'])) {
            $ref = explode('/', ltrim($v['items']['$ref'], '#/'));
            $data['paths'][$path]['dados'][$k] = $data['components']['schemas'][$ref[2]]['properties'];
        }
    }
}
echo "✅ Exemplos e descrições removidos com sucesso!\n";

echo "\n";
echo "Limpando exemplos e descrições...\n";
foreach ($data['paths'] as $path => $dados) {
    foreach($dados as $key => $value) {
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
            foreach($data['paths'][$path]['dados'] as $k => $v) {
                if (isset($data['paths'][$path]['dados'][$k]['example'])) {
                    unset($data['paths'][$path]['dados'][$k]['example']);
                }
                if (isset($data['paths'][$path]['dados'][$k]['description'])) {
                    unset($data['paths'][$path]['dados'][$k]['description']);
                }
                if (isset($data['paths'][$path]['dados'][$k]['type'])) {
                    $data['paths'][$path]['dados'][$k] = $data['paths'][$path]['dados'][$k]['type'];
                }
            }
        } else {
            //$data['paths'][$path] = $data['paths'][$path]['dados'];
        }
    }
}
echo "✅ Exemplos e descrições removidos com sucesso!\n";

echo "\n";
echo "Limpando exemplos e descrições dos subdados...\n";
foreach ($data['paths'] as $path => $dados) {
    foreach($dados['dados'] as $key => $value) {
        if(is_array($value)){
            foreach($value as $key2 => $value2) {
                $data['paths'][$path]['dados'][$key][$key2] = $value2['type'];
            }
            
        }
    }
}
echo "✅ Exemplos e descrições removidos com sucesso!\n";

echo "\n";

echo "\n";
echo "Salvamos JSON com o Yaml convertido...\n";
file_put_contents(__DIR__ . '/cvdw/cvdw.json', json_encode($data, JSON_PRETTY_PRINT));