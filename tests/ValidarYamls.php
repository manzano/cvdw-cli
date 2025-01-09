<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

// Configuração do logger
$log = new Logger('cvdw');
$log->pushHandler(new StreamHandler(__DIR__ . '/log/cvdw.log', Logger::DEBUG));

// Funções de formatação
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

// URLs e arquivos locais
// Arquivo remoto 
$url = 'https://docs-dev.cvcrm.com.br/yaml-files/cvdw.yaml';
// Arquivo local atualizado
$local = __DIR__ . '/../src/app/Brain/cvdw2.yaml';
// Arquivo local para comparação e possivel atualização
$local2 = __DIR__ . '/../src/app/Brain/cvdw.yaml';

try {
    // Baixa o arquivo remoto 
    $down = file_put_contents($local, file_get_contents($url));
    if ($down) {
        printSuccess("✅ Arquivo baixado com sucesso!");
        $log->info("Arquivo baixado com sucesso: $local");
    } elseif (file_exists($local) && file_get_contents($local) === file_get_contents($url)) {
        printSuccess("✅ Arquivo já baixado com sucesso!");    
        $log->info("Arquivo já baixado com sucesso: $local");
    } else {
        $message = "Erro ao baixar o arquivo!";
        printError("❌ $message");
        $log->error($message);
        exit;
    }
} catch (ParseException $e) {
    $message = "Erro ao analisar o arquivo YAML: " . $e->getMessage();
    printError("❌ $message");
    $log->error($message);
    exit;
}

// Converte o arquivo baixado
try {
    $objetoCVDW = Yaml::parseFile($local);
} catch (ParseException $e) {
    $message = "Erro ao converter o arquivo baixado: " . $e->getMessage();
    printError("❌ $message");
    $log->error($message);
    exit;
}

$objetoCVDWLocal = Yaml::parseFile($local2);

// Função para comparar arrays recursivamente
function array_diff_recursive($array1, $array2)
{
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

// Função para exibir as diferenças
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
$differences = array_diff_recursive($objetoCVDW, $objetoCVDWLocal);
if (empty($differences)) {
    printSuccess("✅ Os arquivos estão atualizados!");
    $log->info("Os arquivos estão atualizados.");
} else {
    printWarning("❌ Diferenças encontradas entre os arquivos!");
    $log->warning("Diferenças encontradas entre os arquivos.", ['differences' => $differences]);
    displayDifferences($differences);
}
