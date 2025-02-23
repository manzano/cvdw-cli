<?php 

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

require_once __DIR__ . '/../vendor/autoload.php';

function printError($message)
{
    echo "\033[31m$message\033[0m" . PHP_EOL; // Vermelho
}

function printSuccess($message)
{
    echo "\033[32m$message\033[0m" . PHP_EOL; // Verde
}

function printWarning($message)
{
    echo "\033[33m$message\033[0m" . PHP_EOL; // Amarelo
}
$url = 'https://docs-dev.cvcrm.com.br/yaml-files/cvdw.yaml';
$BrainObjetos = __DIR__ . '/../src/app/Brain/';
$LocalsObjetos = $BrainObjetos . '/cvdw.yaml';
$Objetos = __DIR__ . '/../src/app/Objetos';


try {
    // Baixa o arquivo remoto
    $down = file_put_contents($LocalsObjetos, file_get_contents($url));
    if ($down) {
        printSuccess("✅ Arquivo baixado com sucesso!");
    } else {
        $message = "Erro ao baixar o arquivo!";
        printError("❌ $message");
        exit;
    }
} catch (ParseException $e) {
    $message = "Erro ao analisar o arquivo YAML: " . $e->getMessage();
    printError("❌ $message");
    exit;
}

try {
    $data = Yaml::parseFile($LocalsObjetos);
} catch (ParseException $e) {
    $message = "Erro ao converter o arquivo baixado: " . $e->getMessage();
    printError("❌ $message");
    exit;
}
//Aqui é passado os caminhos para comparação
//paths cvdw 
$refs = findRefs($data['paths']['/repasses']);
//paths objetos
$locals = glob($Objetos . '/repasses.yaml');

//Unica tabela com problema de comparação é a Unidades

// !-- Tratamento de Dados Remotos --!

// Função para buscar e resolver referências recursivamente
function resolveRef(string $ref, array $yamlData) {
    // Remove o prefixo "#/" e converte em um caminho de array
    $path = explode('/', ltrim($ref, '#/'));
    $current = $yamlData;

    foreach ($path as $key) {
        if (!isset($current[$key])) {
            throw new Exception("❌ Caminho '$ref' não encontrado no arquivo YAML.");
        }
        $current = $current[$key];
    }

    // Verifica se o resultado contém outro $ref
    if (is_array($current) && isset($current['$ref'])) {
        return resolveRef($current['$ref'], $yamlData); // Resolve o próximo $ref
    }

    return $current; // Retorna os dados finais resolvidos
}

// Função para resolver todos os $refs recursivamente em um array ou objeto
function resolveAllRefs(array $data, array $yamlData): array {
    foreach ($data as $key => &$value) {
        if (is_array($value)) {
            // Processa recursivamente arrays ou objetos
            $value = resolveAllRefs($value, $yamlData);
        } elseif ($key === '$ref' && is_string($value)) {
            // Resolve a referência e substitui pelo conteúdo
            $value = resolveRef($value, $yamlData);
        }
    }
    return $data;
}

// Função recursiva para encontrar todos os $refs, incluindo os aninhados
function findRefs(array $array): array {
    $refs = [];

    foreach ($array as $key => $value) {
        if ($key === '$ref') {
            $refs[] = $value;
        } elseif (is_array($value)) {
            $refs = array_merge($refs, findRefs($value));
        }
    }

    return $refs;
}

// Função para filtrar as referências com base na lista de exclusão
function filterRefs(array $refs, array $ignoreList): array {
    return array_filter($refs, function ($ref) use ($ignoreList) {
        return !in_array($ref, $ignoreList, true); // Exclui as referências da lista de exclusão
    });
}

// Lista de prefixos ou palavras-chave para ignorar dinamicamente
$ignoreKeywords = ['#/components/examples/', '#/components/parameters/'];

// Lista de referências específicas para ignorar
$ignoreRefs = [
    '#/components/schemas/BodyCvdw',
    '#/components/schemas/RetornoCVDW',
];

// Função para verificar se um $ref deve ser ignorado
function shouldIgnoreRef(string $ref, array $ignoreRefs, array $ignoreKeywords): bool {
    // Verifica se o $ref está na lista de exclusões específicas
    if (in_array($ref, $ignoreRefs, true)) {
        return true;
    }
    // Verifica se o $ref contém qualquer palavra-chave da lista
    foreach ($ignoreKeywords as $keyword) {
        if (strpos($ref, $keyword) !== false) {
            return true;
        }
    }
    return false;
}
// Acessa os dados e encontra as referências & Busca todos os arquivos YAML no diretório local
try { 
    // Filtra as referências para ignorar
    $filteredRefs = array_filter($refs, function ($ref) use ($ignoreRefs, $ignoreKeywords) {
        return !shouldIgnoreRef($ref, $ignoreRefs, $ignoreKeywords);
    });

    foreach ($filteredRefs as $ref ) {
        try {
            $resolvedData = resolveRef($ref, $data);

            // Resolve todas as referências dentro do resolvedData
            $resolvedData = resolveAllRefs($resolvedData, $data);

            printSuccess("Dados da referência dentro do '$ref':\n");
            
        //    $resultado = ($resolvedData['properties']['dados']['items']);
             $resultado = ($resolvedData['properties']['dados']['items']['$ref']['properties']);
            unset($resultado['example']);
            // $demandas = print_r($resultado);
             
            // echo "\n";
        } catch (Exception $e) {
            printError("❌ Erro ao resolver referência '$ref': " . $e->getMessage() . "\n");
        }
    }
} catch (Exception $e) {
    printError("❌ Erro ao encontrar referências: " . $e->getMessage() . "\n");
}

// !-- Tratamento de Arquivos Locais --!

// Variavel vazia para armazenar os dados extraídos
$ResultObjetos = []; 

// Percorre todos os arquivos YAML na pasta local
foreach ($locals as $local) {
    echo "Processando arquivo: " . basename($local) . "\n";

    try {
        // Converse o arquivo YAML em um array
        $data = Yaml::parseFile($local);

        // Verifica se o arquivo contém os campos necessários
        if (isset($data['response']['dados'])) {
            $dados = $data['response']['dados'];

            // Adiciona os dados extraídos diretamente no array final
            $ResultObjetos[] = $dados; 
        } else {
            echo "O arquivo '" . basename($local) . "' não possui os campos necessários.\n";
        }
    } catch (Exception $e) {
        echo "Erro ao processar o arquivo '" . basename($local) . "': " . $e->getMessage() . "\n";
    }
}

// Verifica o resultado final e extrai o array [0] para pode fazer a comparação com a remota
$extraidos = $ResultObjetos[0];


echo "\n=== Comparando os dados ===\n";

// Função para comparar arrays recursivamente
function array_diff_recursive($array1, $array2) {
    $difference = [];
    foreach ($array1 as $key => $value) {
        if (!array_key_exists($key, $array2)) {
            $difference[$key] = $value;
        } elseif (is_array($value)) {
            $new_diff = array_diff_recursive($value, $array2[$key]);
            if (!empty($new_diff)) {
                $difference[$key] = $new_diff;
            }
        } elseif ($value !== $array2[$key]) {
            $difference[$key] = $value;
        }
    }
    return $difference;
}

// Função para exibir as diferenças encontradas
function displayDifferences($differences) {
    foreach ($differences as $key => $value) {
        if (is_array($value)) {
            echo "\033[33m$key:\033[0m\n"; // Amarelo
            displayDifferences($value); // Exibe diferenças internas
        } else {
            echo "\033[31m$key => $value\033[0m\n"; // Vermelho
        }
    }
}
// Verifica as diferenças entre os arquivos
$differences = array_diff_recursive($resultado,  $extraidos);
if (empty($differences)) {
    printSuccess("✅ Os arquivos estão atualizados!");
} else {
    printWarning("❌ Diferenças encontradas entre os arquivos!");
    displayDifferences($differences);
}