<?php

namespace Tests\Unit;

use Manzano\CvdwCli\Services\Log;
use PHPUnit\Framework\TestCase;

class LogCvdwTest extends TestCase
{
    private Log $log;
    private array $originalEnv;

    protected function setUp(): void
    {
        $this->backupEnvironmentVariables();
        $this->setTestEnvironmentVariables();
        $this->log = new Log('test.log');
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

    public function testLogPodeSerInstanciado(): void
    {
        $this->assertInstanceOf(Log::class, $this->log);
    }

    public function testLogComMensagemSimples(): void
    {
        $mensagem = "Teste de log simples";

        // Testar se não há exceção ao escrever log
        $this->expectNotToPerformAssertions();
        $this->log->escreverLog($mensagem);
    }

    public function testLogComMensagemComplexa(): void
    {
        $mensagem = "Teste de log com dados: " . json_encode(['teste' => 'valor']);

        // Testar se não há exceção ao escrever log complexo
        $this->expectNotToPerformAssertions();
        $this->log->escreverLog($mensagem);
    }

    public function testLogComMensagemVazia(): void
    {
        // Testar se não há exceção ao escrever log vazio
        $this->expectNotToPerformAssertions();
        $this->log->escreverLog("");
    }

    public function testLogComMensagemNull(): void
    {
        // Testar se não há exceção ao escrever log null
        $this->expectNotToPerformAssertions();
        $this->log->escreverLog(null);
    }

    public function testCriarArquivoLog(): void
    {
        // Testar se não há exceção ao criar arquivo de log
        $this->expectNotToPerformAssertions();
        $this->log->criarArquivoLog();
    }

    public function testRetornarDiretorioLog(): void
    {
        $diretorio = $this->log->retornarDiretorioLog();
        $this->assertIsString($diretorio);
        $this->assertStringContainsString('logs', $diretorio);
    }
}
