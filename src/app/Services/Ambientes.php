<?php

namespace Manzano\CvdwCli\Services;

class Ambientes
{
    private $envVars;

    public function __construct()
    {
        $this->envVars = $this->getEnvEscope();
    }

    public function ambienteAtivo($env, $io)
    {
        $ambienteAtivo = strtoupper($_ENV['CV_URL'] ?? '');
        if ($ambienteAtivo == '') {
            $ambienteAtivo = 'Nenhum ambiente padrão configurado';
        } else {
            if ($env == null) {
                $ambienteAtivo .= " (Arquivo: .env)";
            } else {
                $ambienteAtivo .= " (Arquivo: $env.env)";
            }
        }
        $io->text('Ambiente ativo: ' . $ambienteAtivo);
    }

    public function retornarEnvs($env = null): void
    {
        $envVars = $this->getEnvDir($env);
        if (!file_exists($envVars)) {
            if ($env !== null) {
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

    public function salvarEnv(array $newEnv = [], $env = null): void
    {
        $envVars = $this->getEnvDir($env);
        $this->retornarEnvs();
        $novoEnv = array_merge($_ENV, $newEnv);
        $envContent = '';
        foreach ($this->envVars as $key => $valor) {
            if (!isset($novoEnv[$key])) {
                $novoEnv[$key] = $valor;
            }
            $envContent .= "$key=" . $novoEnv[$key] . "\n";
        }
        file_put_contents($envVars, $envContent);
        $this->retornarEnvs();
    }

    public function getEnvEscope(): array
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

    public function getEnvDir($env = null): string
    {
        $this->alterarCaminhoEnv();
        $envPath = __DIR__ . '/../../envs';
        $envFile = $envPath . ($env ? '/' . $env . '.env' : '/.env');
        return $envFile;
    }

    public function alterarCaminhoEnv()
    {
        $envPath = __DIR__ . '/../..';
        $envFile = $envPath . '/.env';
        if (file_exists($envFile)) {
            $envName = basename($envFile);
            $newEnv = $envPath . '/envs/' . $envName;
            rename($envFile, $newEnv);
        }
    }

}
