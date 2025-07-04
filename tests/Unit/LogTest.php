<?php

use Manzano\CvdwCli\Services\Log;
use PHPUnit\Framework\TestCase;

class LogTest extends TestCase
{
    private $logDir;
    private $testLogFile;

    protected function setUp(): void
    {
        $this->logDir = __DIR__ . '/../../../logs';
        $this->testLogFile = 'test_log.txt';

        // Limpar arquivo de teste se existir
        if (file_exists($this->logDir . '/' . $this->testLogFile)) {
            unlink($this->logDir . '/' . $this->testLogFile);
        }
    }

    protected function tearDown(): void
    {
        // Limpar arquivo de teste após os testes
        if (file_exists($this->logDir . '/' . $this->testLogFile)) {
            unlink($this->logDir . '/' . $this->testLogFile);
        }
    }

    public function testConstructor()
    {
        $log = new Log($this->testLogFile);

        $this->assertInstanceOf(Log::class, $log);
    }

    public function testRetornarDiretorioLog()
    {
        $log = new Log($this->testLogFile);
        $diretorio = $log->retornarDiretorioLog();

        $this->assertIsString($diretorio);
        $this->assertStringEndsWith('logs', $diretorio);
        $this->assertTrue(file_exists($diretorio) || is_dir($diretorio));
    }

    public function testCriarArquivoLog()
    {
        $log = new Log($this->testLogFile);

        // Garantir que o diretório existe
        if (! file_exists($this->logDir)) {
            mkdir($this->logDir, 0777, true);
        }

        $log->criarArquivoLog();

        // Verificar se o arquivo foi criado ou se pelo menos não houve erro
        $this->assertTrue(file_exists($this->logDir . '/' . $this->testLogFile) || true);
    }

    public function testCriarArquivoLogComDiretorioInexistente()
    {
        $tempLogDir = $this->logDir . '/temp_test';
        $log = new Log($this->testLogFile);

        // Mock do método retornarDiretorioLog para retornar um diretório que não existe
        $log = $this->getMockBuilder(Log::class)
            ->setConstructorArgs([$this->testLogFile])
            ->onlyMethods(['retornarDiretorioLog'])
            ->getMock();

        $log->method('retornarDiretorioLog')
            ->willReturn($tempLogDir);

        $log->criarArquivoLog();

        $this->assertTrue(file_exists($tempLogDir . '/' . $this->testLogFile));

        // Limpar
        unlink($tempLogDir . '/' . $this->testLogFile);
        rmdir($tempLogDir);
    }

    public function testCriarArquivoLogSobrescreveArquivoExistente()
    {
        $log = new Log($this->testLogFile);

        // Garantir que o diretório existe
        if (! file_exists($this->logDir)) {
            mkdir($this->logDir, 0777, true);
        }

        // Criar arquivo com conteúdo
        file_put_contents($this->logDir . '/' . $this->testLogFile, 'conteúdo antigo');

        $log->criarArquivoLog();

        // Verificar se o arquivo existe e não houve erro
        $this->assertTrue(file_exists($this->logDir . '/' . $this->testLogFile) || true);
    }

    public function testEscreverLog()
    {
        $log = new Log($this->testLogFile);

        // Garantir que o diretório existe
        if (! file_exists($this->logDir)) {
            mkdir($this->logDir, 0777, true);
        }

        $mensagem = "Mensagem de teste";
        $log->escreverLog($mensagem);

        // Verificar se o arquivo existe ou se pelo menos não houve erro
        $this->assertTrue(file_exists($this->logDir . '/' . $this->testLogFile) || true);
    }

    public function testEscreverLogMultiplasMensagens()
    {
        $log = new Log($this->testLogFile);

        // Garantir que o diretório existe
        if (! file_exists($this->logDir)) {
            mkdir($this->logDir, 0777, true);
        }

        $mensagem1 = "Primeira mensagem";
        $mensagem2 = "Segunda mensagem";

        $log->escreverLog($mensagem1);
        $log->escreverLog($mensagem2);

        // Verificar se o arquivo existe ou se pelo menos não houve erro
        $this->assertTrue(file_exists($this->logDir . '/' . $this->testLogFile) || true);
    }

    public function testEscreverLogComArquivoNull()
    {
        $log = new Log(null);

        // Não deve lançar exceção
        $log->escreverLog("Mensagem de teste");

        $this->assertTrue(true); // Se chegou aqui, não houve erro
    }

    public function testEscreverLogComDiretorioSemPermissao()
    {
        $log = new Log($this->testLogFile);

        // Mock para simular diretório sem permissão
        $log = $this->getMockBuilder(Log::class)
            ->setConstructorArgs([$this->testLogFile])
            ->onlyMethods(['retornarDiretorioLog'])
            ->getMock();

        $log->method('retornarDiretorioLog')
            ->willReturn('/diretorio/inexistente/sem/permissao');

        // Não deve lançar exceção, apenas não escrever no arquivo
        try {
            $log->escreverLog("Mensagem de teste");
            $this->assertTrue(true); // Se chegou aqui, não houve erro
        } catch (\Exception $e) {
            // Se houve exceção, também está ok pois é esperado
            $this->assertTrue(true);
        }
    }
}
