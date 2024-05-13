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

function salvarEventoErro($e, $objeto, $metadata = array(), $mensagem = null, $info_adicionais = []){
    
    if ($_ENV['CVDW_AMBIENTE'] <> 'DEV') {
        // Define o contexto com informações adicionais
        \Sentry\configureScope(function (\Sentry\State\Scope $scope) use ($info_adicionais) {
            $scope->setContext('informacoes_adicionais', $info_adicionais);
        });
        \Sentry\addBreadcrumb(
            category: $objeto,
            metadata: $metadata
        );
        if($mensagem <> null){
            \Sentry\captureMessage($mensagem);
        }
        if($e instanceof Exception){
            \Sentry\captureException($e);
        }
    }
    
}
