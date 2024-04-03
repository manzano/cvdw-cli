<?php
/*
function ambienteAtivo($env, $io){
    $ambienteAtivo = strtoupper($_ENV['CV_URL']);
    if ($ambienteAtivo == '') {
        $ambienteAtivo = 'Nenhum ambiente padrão configurado';
    } else {
        if($env == null){
            $ambienteAtivo .= " (Padrão)";
        }
    }
    $io->text('Ambiente ativo: ' . $ambienteAtivo);
}

function retornarEnvs($env = null): void
{
    $envVars = getEnvDir($env);
    if (!file_exists($envVars)) {
        if($env <> null){
            echo "Ambiente informado não foi encontrado.";
            exit;
        } else {
            file_put_contents($envVars, '');
            chmod($envVars, 0755);
        }
    }
    $dotenv = new \Symfony\Component\Dotenv\Dotenv();
    $dotenv->load($envVars);
}

function salvarEnv(array $newEnv = [], $env = null): void
{
    $envVars = getEnvDir($env);
    retornarEnvs();
    $novoEnv = array_merge($_ENV, $newEnv);
    $envContent = '';
    $escopo = getEnvEscope();
    $envContent = '';
    foreach ($escopo as $key => $valor) {
        if (!isset($novoEnv[$key])) {
            $novoEnv[$key] = $valor;
        }
        $envContent .= "$key=" . $novoEnv[$key] . "\n";
    }
    file_put_contents($envVars, $envContent);
    retornarEnvs();
}

function getEnvEscope(): array
{
    return [
            'CV_URL' => null,
            'CV_EMAIL' => null,
            'CV_TOKEN' => null,
            'DB_CONNECTION' => null,
            'DB_HOST' => null,
            'DB_PORT' => null,
            'DB_DATABASE' => null,
            'DB_USERNAME' => null,
            'DB_PASSWORD' => null,
            'CVDW_AMBIENTE' => 'PRD'
        ];
}

function getEnvDir($env = null): string
{
    alterarCaminhoEnv();
    $envPath = __DIR__ . '/../../envs';
    if(!is_null($env)){
        $envFile = $envPath . '/'.$env.'.env';
    } else {
        $envFile = $envPath . '/.env';
    }
    return $envFile;
}

function alterarCaminhoEnv(){
    $envPath = __DIR__ . '/../..';
    $envFile = $envPath . '/.env';
    if(file_exists($envFile)){
        $envName = basename($envFile);
        $newEnv = $envPath . '/envs/' . $envName;
        rename($envFile, $newEnv);
    }
}
*/

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