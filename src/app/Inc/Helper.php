<?php

function retornarEnvs(): void
{
    $envVars = getEnvDir();
    if (!file_exists($envVars)) {
        file_put_contents($envVars, '');
        chmod($envVars, 0755);
    }
    $dotenv = new \Symfony\Component\Dotenv\Dotenv();
    $dotenv->load($envVars);
}

/**
 * @param string[] $newEnv Array de novas variáveis de ambiente.
 */
function salvarEnv(array $newEnv = []): void
{
    $envVars = getEnvDir();
    retornarEnvs();
    $novoEnv = array_merge($_ENV, $newEnv);
    $envContent = '';
    $escopo = getEnvEscope();
    $envContent = '';
    foreach ($escopo as $key) {
        if (!isset($novoEnv[$key])) {
            $novoEnv[$key] = "";
        }
        $envContent .= "$key=" . $novoEnv[$key] . "\n";
    }
    file_put_contents($envVars, $envContent);
    retornarEnvs();
}

/**
 * Retorna um array de strings representando o escopo das variáveis de ambiente.
 *
 * @return string[] Array de escopos de variáveis de ambiente.
 */
function getEnvEscope(): array
{
    return ['CV_URL', 'CV_EMAIL', 'CV_TOKEN', 'DB_CONNECTION', 'DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD'];
}

function getEnvDir(): string
{
    $envPath = __DIR__ . '/../..';
    $envFile = $envPath . '/.env';
    return $envFile;
}
