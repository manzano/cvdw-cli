<?php

namespace Manzano\CvdwCli\Services;

class Ambientes
{
    private $envVars;
    public $env;

    public function __construct($env = null)
    {
        $this->env = $env;
        $this->envVars = $this->getEnvEscope();
        $this->retornarEnvs($env);
        $this->envVars = $this->getEnvEscope();
    }

    public function retornarVersao(): string
    {
        return 'v1.2.1';
    }

    public function ambienteAtivo(): string
    {
        $this->retornarEnvs($this->env);
        $ambienteAtivo = strtoupper($_ENV['CV_URL'] ?? '');
        if ($ambienteAtivo == '') {
            if($this->env !== null){
                $ambienteAtivo = 'Ambiente '. $this->env.' não configurado';
            } else {
                $ambienteAtivo = 'Nenhum ambiente configurado';
            }
        } else {
            if ($this->env == null) {
                $ambienteAtivo .= " (Arquivo: .env)";
            } else {
                $ambienteAtivo .= " (Arquivo: {$this->env}.env)";
            }
        }
        return $ambienteAtivo;
    }

    public function retornarEnvs(): bool
    {
        $envVars = $this->getEnvDir();
        if (!file_exists($envVars)) {
            file_put_contents($envVars, '');
            //chmod($envVars, 0755);
            $this->salvarEnv();
        }
        $dotenv = new \Symfony\Component\Dotenv\Dotenv();
        $dotenv->load($envVars);
        return true;
    }

    public function salvarEnv(array $newEnv = []): void
    {
        $envVars = $this->getEnvDir();
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

    public function getEnvDir(): string
    {
        $this->alterarCaminhoEnv();
        $envPath = __DIR__ . '/../../envs';
        $envFile = $envPath . ($this->env ? '/' . $this->env . '.env' : '/.env');
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

    public function atualizarCVDW(): void
    {
        // Atualizar versão do CVDW
    }

}
