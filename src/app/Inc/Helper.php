<?php

function validarData($dataInput)
{
    // Primeiro, tenta validar o formato completo com hora
    $data = DateTime::createFromFormat('Y-m-d\TH:i:s', $dataInput);
    if ($data && $data->format('Y-m-d\TH:i:s') === $dataInput) {
        echo 'Data válida com hora: ' . $dataInput . PHP_EOL;
        return true;
    }
    // Se não for válido, tenta validar apenas a data sem a hora
    $data = DateTime::createFromFormat('Y-m-d', $dataInput);
    if ($data && $data->format('Y-m-d') === $dataInput) {
        echo 'Data válida sem hora: ' . $dataInput . PHP_EOL;
        return true;
    }
    // Se nenhum dos formatos for válido, retorna falso
    return false;
}


function substituirPorAsteriscos($texto)
{
    
    if(is_null($texto)){
        return null;
    }

    $texto = trim($texto);

    $tamanho = strlen($texto);
    $metade = floor($tamanho / 2); // Encontra o índice da metade da string

    // Verifica se o tamanho da string é ímpar para determinar o número de asteriscos
    if ($tamanho % 2 !== 0) {
        $asteriscos = str_repeat('*', $metade + 1);
    } else {
        $asteriscos = str_repeat('*', $metade);
    }

    // Substitui a metade do meio da string pelos asteriscos
    $texto = substr_replace($texto, $asteriscos, intval($metade / 2), $metade);
    
    return $texto;
}

function substituirPorHash($texto, $caracteres = 32) {
    
    if(is_null($texto)){
        return null;
    }

    $hash = hash('sha256', $texto);
    return substr($hash, 0, $caracteres);
}
