<?php

namespace Manzano\CvdwCli\Services;

class Ambientes
{
    private $envVars;
    public $env;
    private $parent;
    public $envPath = __DIR__ . '/../../envs';
    private EnvironmentManager $environmentManager;

    public function __construct($env = null, $parent = null)
    {
        $this->env = $env;
        $this->parent = $parent;
        $this->environmentManager = new EnvironmentManager();
        $this->envVars = $this->getEnvEscope();
        $this->retornarEnvs();
        $this->envVars = $this->getEnvEscope();
    }

    public function retornarVersao(): string
    {
        return 'v1.10.1';
    }

    public function getEnvPath(): string
    {
        return $this->envPath;
    }

    public function ambienteAtivo(): string
    {
        $this->retornarEnvs();
        $ambienteAtivo = strtoupper($this->environmentManager->getCvUrl());
        if ($ambienteAtivo == '') {
            if ($this->env !== null) {
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
        if (! file_exists($envVars)) {
            file_put_contents($envVars, '');
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
        $novoEnv = array_merge($this->environmentManager->getAll(), $newEnv);
        $envContent = '';
        foreach ($this->envVars as $key => $valor) {
            if (! isset($novoEnv[$key])) {
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
            'DB_CONNECTION' => "pdo_mysql",
            'DB_HOST' => null,
            'DB_PORT' => null,
            'DB_DATABASE' => null,
            'DB_USERNAME' => null,
            'DB_PASSWORD' => null,
            'DB_SCHEMA' => null,
            'ANONIMIZAR' => null,
            'ANONIMIZAR_TIPO' => null,
            'CVDW_AMBIENTE' => 'PRD',
        ];
    }

    public function getEnvDir(): string
    {
        $this->alterarCaminhoEnv();
        $envPath = __DIR__ . '/../../envs';

        return $envPath . ($this->env ? '/' . $this->env . '.env' : '/.env');
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

    public function listarAmbientes(): array
    {
        $ambientes = [];
        $envPadrao = glob($this->envPath . '/.env');
        $envs = glob($this->envPath . '/*.env');
        $envs = array_merge($envs, $envPadrao);
        foreach ($envs as $env) {
            $nomeAux = explode('/', $env);
            $nome = end($nomeAux);
            // Ler a primeira linha do arquivo .env
            $linhas = file($env);
            $cvUrl = '';
            $cvEmail = '';
            foreach ($linhas as $linha) {
                if (strpos($linha, 'CV_URL') !== false) {
                    $arrayExplode = explode('=', $linha);
                    $cvUrl = str_replace("\n", "", $arrayExplode[1] ?? '');
                }
                if (strpos($linha, 'CV_EMAIL') !== false) {
                    $arrayExplode = explode('=', $linha);
                    $cvEmail = str_replace("\n", "", $arrayExplode[1] ?? '');
                }
            }
            $arrayAux = [];
            $arrayAux['referencia'] = $cvUrl;
            $arrayAux['arquivo'] = $env;
            $arrayAux['nome'] = $nome;
            $arrayAux['email'] = $cvEmail;
            $ambientes[] = $arrayAux;
        }

        return $ambientes;
    }

    public function verificarAmbientePadrao($console): void
    {
        if ($this->environmentManager->getCvUrl() == '' && $this->env == null) {
            $console->text('<fg=white;bg=red>[PROBLEMA]</> Você ainda não tem um ambiente padrão configurado!');
            $console->text([
                '',
                'Para seguir, você precisa configurar o ambiente.',
                '',
            ]);
            if ($console->confirm('Vamos configurar um ambiente agora?', true)) {
                $this->parent->limparTela();
                $this->parent->configurarCV();
            } else {
                $this->parent->voltarProMenu = true;
                $this->parent->voltarProMenu();
            }

        }
    }

    public function validarConfiguracao($console, $ignorar = []): void
    {
        $envVars = $this->getEnvEscope();
        // Listar todas as variáveis de $envVars e verificar se todas tem valor
        foreach ($envVars as $envVar => $_) {

            if (in_array($envVar, $ignorar)) {
                continue;
            }

            if (! $this->environmentManager->has($envVar)) {
                $console->error('Configuração não encontrada, invalida ou incompleta. (' . $envVar . ')');
                $console->text(['Por favor use o comando "cvdw configurar" para configurar o CVDW-CLI.']);
                $console->text(['']);
                exit;
            }
        }
    }

}
