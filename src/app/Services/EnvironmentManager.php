<?php

namespace Manzano\CvdwCli\Services;

class EnvironmentManager
{
    private array $envVars = [];

    public function __construct()
    {
        $this->loadEnvironmentVariables();
    }

    private function loadEnvironmentVariables(): void
    {
        // Carregar variáveis do $_ENV (que podem ter sido carregadas pelo Dotenv)
        $this->envVars = [
            'CV_URL' => $_ENV['CV_URL'] ?? '',
            'CV_EMAIL' => $_ENV['CV_EMAIL'] ?? '',
            'CV_TOKEN' => $_ENV['CV_TOKEN'] ?? '',
            'DB_CONNECTION' => $_ENV['DB_CONNECTION'] ?? 'pdo_mysql',
            'DB_HOST' => $_ENV['DB_HOST'] ?? '',
            'DB_PORT' => $_ENV['DB_PORT'] ?? '',
            'DB_DATABASE' => $_ENV['DB_DATABASE'] ?? '',
            'DB_USERNAME' => $_ENV['DB_USERNAME'] ?? '',
            'DB_PASSWORD' => $_ENV['DB_PASSWORD'] ?? '',
            'DB_SCHEMA' => $_ENV['DB_SCHEMA'] ?? '',
            'ANONIMIZAR' => $_ENV['ANONIMIZAR'] ?? false,
            'ANONIMIZAR_TIPO' => $_ENV['ANONIMIZAR_TIPO'] ?? 'Asteriscos',
            'CVDW_AMBIENTE' => $_ENV['CVDW_AMBIENTE'] ?? 'PRD',
        ];
        
        // Se as variáveis estão vazias, tentar carregar do arquivo .env diretamente
        if (empty($this->envVars['DB_HOST'])) {
            $this->loadFromEnvFile();
        }
    }
    
    private function loadFromEnvFile(): void
    {
        $envFile = __DIR__ . '/../../envs/.env';
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
                    list($key, $value) = explode('=', $line, 2);
                    $key = trim($key);
                    $value = trim($value);
                    
                    // Remover aspas se existirem
                    if ((substr($value, 0, 1) === '"' && substr($value, -1) === '"') ||
                        (substr($value, 0, 1) === "'" && substr($value, -1) === "'")) {
                        $value = substr($value, 1, -1);
                    }
                    
                    if (isset($this->envVars[$key])) {
                        $this->envVars[$key] = $value;
                    }
                }
            }
        }
    }

    public function get(string $key, $default = null)
    {
        return $this->envVars[$key] ?? $default;
    }

    public function set(string $key, $value): void
    {
        $this->envVars[$key] = $value;
        $_ENV[$key] = $value;
        putenv("$key=$value");
    }

    public function has(string $key): bool
    {
        return isset($this->envVars[$key]) && $this->envVars[$key] !== '';
    }

    public function getCvUrl(): string
    {
        return $this->get('CV_URL', '');
    }

    public function getCvEmail(): string
    {
        return $this->get('CV_EMAIL', '');
    }

    public function getCvToken(): string
    {
        return $this->get('CV_TOKEN', '');
    }

    public function getDbConnection(): string
    {
        return $this->get('DB_CONNECTION', 'pdo_mysql');
    }

    public function getDbHost(): string
    {
        return $this->get('DB_HOST', '');
    }

    public function getDbPort(): string
    {
        return $this->get('DB_PORT', '');
    }

    public function getDbDatabase(): string
    {
        return $this->get('DB_DATABASE', '');
    }

    public function getDbUsername(): string
    {
        return $this->get('DB_USERNAME', '');
    }

    public function getDbPassword(): string
    {
        return $this->get('DB_PASSWORD', '');
    }

    public function getDbSchema(): string
    {
        return $this->get('DB_SCHEMA', '');
    }

    public function getAnonimizar(): bool
    {
        $value = $this->get('ANONIMIZAR', false);
        if ($value === 'true') {
            return true;
        }
        if ($value === 'false') {
            return false;
        }
        return (bool) $value;
    }

    public function getAnonimizarTipo(): string
    {
        return $this->get('ANONIMIZAR_TIPO', 'Asteriscos');
    }

    public function getCvdwAmbiente(): string
    {
        return $this->get('CVDW_AMBIENTE', 'PRD');
    }

    public function setCvUrl(string $value): void
    {
        $this->set('CV_URL', $value);
    }

    public function setCvEmail(string $value): void
    {
        $this->set('CV_EMAIL', $value);
    }

    public function setCvToken(string $value): void
    {
        $this->set('CV_TOKEN', $value);
    }

    public function setDbConnection(string $value): void
    {
        $this->set('DB_CONNECTION', $value);
    }

    public function setDbHost(string $value): void
    {
        $this->set('DB_HOST', $value);
    }

    public function setDbPort(string $value): void
    {
        $this->set('DB_PORT', $value);
    }

    public function setDbDatabase(string $value): void
    {
        $this->set('DB_DATABASE', $value);
    }

    public function setDbUsername(string $value): void
    {
        $this->set('DB_USERNAME', $value);
    }

    public function setDbPassword(string $value): void
    {
        $this->set('DB_PASSWORD', $value);
    }

    public function setDbSchema(string $value): void
    {
        $this->set('DB_SCHEMA', $value);
    }

    public function setAnonimizar(bool $value): void
    {
        $this->set('ANONIMIZAR', $value ? 'true' : 'false');
    }

    public function setAnonimizarTipo(string $value): void
    {
        $this->set('ANONIMIZAR_TIPO', $value);
    }

    public function setCvdwAmbiente(string $value): void
    {
        $this->set('CVDW_AMBIENTE', $value);
    }

    public function getAll(): array
    {
        return $this->envVars;
    }

    public function saveToEnv(array $newEnv = []): void
    {
        foreach ($newEnv as $key => $value) {
            $this->set($key, $value);
        }
    }
} 