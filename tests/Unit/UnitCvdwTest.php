<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class UnitCvdwTest extends TestCase
{
    private array $originalEnv;

    protected function setUp(): void
    {
        $this->backupEnvironmentVariables();
        $this->setTestEnvironmentVariables();
    }

    protected function tearDown(): void
    {
        $this->restoreEnvironmentVariables();
    }

    private function backupEnvironmentVariables(): void
    {
        $this->originalEnv = $_ENV;
    }

    private function setTestEnvironmentVariables(): void
    {
        $_ENV = [
            'CV_URL' => 'teste_ambiente',
            'CV_EMAIL' => 'teste@exemplo.com',
            'CV_TOKEN' => 'token_teste_123',
            'DB_CONNECTION' => 'pdo_mysql',
            'DB_HOST' => 'localhost',
            'DB_PORT' => '3306',
            'DB_DATABASE' => 'teste_db',
            'DB_USERNAME' => 'usuario_teste',
            'DB_PASSWORD' => 'senha_teste',
            'DB_SCHEMA' => 'public',
            'ANONIMIZAR' => 'true',
            'ANONIMIZAR_TIPO' => 'Asteriscos',
        ];
    }

    private function restoreEnvironmentVariables(): void
    {
        $_ENV = $this->originalEnv;
    }

    public function testVariaveisDeAmbienteEstaoConfiguradas(): void
    {
        $this->assertArrayHasKey('CV_URL', $_ENV, "CV_URL deve estar configurada");
        $this->assertArrayHasKey('CV_EMAIL', $_ENV, "CV_EMAIL deve estar configurada");
        $this->assertArrayHasKey('CV_TOKEN', $_ENV, "CV_TOKEN deve estar configurada");
        $this->assertArrayHasKey('DB_CONNECTION', $_ENV, "DB_CONNECTION deve estar configurada");
        $this->assertArrayHasKey('DB_HOST', $_ENV, "DB_HOST deve estar configurada");
        $this->assertArrayHasKey('DB_PORT', $_ENV, "DB_PORT deve estar configurada");
        $this->assertArrayHasKey('DB_DATABASE', $_ENV, "DB_DATABASE deve estar configurada");
        $this->assertArrayHasKey('DB_USERNAME', $_ENV, "DB_USERNAME deve estar configurada");
        $this->assertArrayHasKey('DB_PASSWORD', $_ENV, "DB_PASSWORD deve estar configurada");
        $this->assertArrayHasKey('DB_SCHEMA', $_ENV, "DB_SCHEMA deve estar configurada");
        $this->assertArrayHasKey('ANONIMIZAR', $_ENV, "ANONIMIZAR deve estar configurada");
        $this->assertArrayHasKey('ANONIMIZAR_TIPO', $_ENV, "ANONIMIZAR_TIPO deve estar configurada");
    }

    public function testValoresDasVariaveisDeAmbiente(): void
    {
        $this->assertEquals('teste_ambiente', $_ENV['CV_URL']);
        $this->assertEquals('teste@exemplo.com', $_ENV['CV_EMAIL']);
        $this->assertEquals('token_teste_123', $_ENV['CV_TOKEN']);
        $this->assertEquals('pdo_mysql', $_ENV['DB_CONNECTION']);
        $this->assertEquals('localhost', $_ENV['DB_HOST']);
        $this->assertEquals('3306', $_ENV['DB_PORT']);
        $this->assertEquals('teste_db', $_ENV['DB_DATABASE']);
        $this->assertEquals('usuario_teste', $_ENV['DB_USERNAME']);
        $this->assertEquals('senha_teste', $_ENV['DB_PASSWORD']);
        $this->assertEquals('public', $_ENV['DB_SCHEMA']);
        $this->assertEquals('true', $_ENV['ANONIMIZAR']);
        $this->assertEquals('Asteriscos', $_ENV['ANONIMIZAR_TIPO']);
    }
}
