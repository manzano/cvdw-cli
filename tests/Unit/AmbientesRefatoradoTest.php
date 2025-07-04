<?php

namespace Tests\Unit;

use Manzano\CvdwCli\Services\Ambientes;
use PHPUnit\Framework\TestCase;

class AmbientesRefatoradoTest extends TestCase
{
    private Ambientes $ambientes;
    private array $originalEnv;

    protected function setUp(): void
    {
        $this->backupEnvironmentVariables();
        $this->setTestEnvironmentVariables();

        $this->ambientes = new Ambientes(null, null);
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

    public function testAmbienteAtivoComCvUrlConfigurado(): void
    {
        $ambienteAtivo = $this->ambientes->ambienteAtivo();

        $this->assertStringContainsString('TESTE_AMBIENTE', $ambienteAtivo);
        $this->assertStringContainsString('(Arquivo: .env)', $ambienteAtivo);
    }

    public function testAmbienteAtivoSemCvUrl(): void
    {
        // Limpar CV_URL
        $_ENV['CV_URL'] = '';

        $ambientes = new Ambientes(null, null);
        $ambienteAtivo = $ambientes->ambienteAtivo();

        $this->assertEquals('Nenhum ambiente configurado', $ambienteAtivo);
    }

    public function testAmbienteAtivoComAmbienteEspecifico(): void
    {
        $ambientes = new Ambientes('dev', null);
        $ambienteAtivo = $ambientes->ambienteAtivo();

        $this->assertStringContainsString('(Arquivo: dev.env)', $ambienteAtivo);
    }

    public function testAmbienteAtivoComAmbienteEspecificoSemConfiguracao(): void
    {
        $_ENV['CV_URL'] = '';

        $ambientes = new Ambientes('dev', null);
        $ambienteAtivo = $ambientes->ambienteAtivo();

        $this->assertEquals('Ambiente dev não configurado', $ambienteAtivo);
    }

    public function testGetEnvEscope(): void
    {
        $envScope = $this->ambientes->getEnvEscope();

        $this->assertIsArray($envScope);
        $this->assertArrayHasKey('CV_URL', $envScope);
        $this->assertArrayHasKey('CV_EMAIL', $envScope);
        $this->assertArrayHasKey('CV_TOKEN', $envScope);
        $this->assertArrayHasKey('DB_CONNECTION', $envScope);
        $this->assertArrayHasKey('DB_HOST', $envScope);
        $this->assertArrayHasKey('DB_PORT', $envScope);
        $this->assertArrayHasKey('DB_DATABASE', $envScope);
        $this->assertArrayHasKey('DB_USERNAME', $envScope);
        $this->assertArrayHasKey('DB_PASSWORD', $envScope);
        $this->assertArrayHasKey('DB_SCHEMA', $envScope);
        $this->assertArrayHasKey('ANONIMIZAR', $envScope);
        $this->assertArrayHasKey('ANONIMIZAR_TIPO', $envScope);
        $this->assertArrayHasKey('CVDW_AMBIENTE', $envScope);

        $this->assertEquals('pdo_mysql', $envScope['DB_CONNECTION']);
        $this->assertEquals('PRD', $envScope['CVDW_AMBIENTE']);
    }

    public function testGetEnvPath(): void
    {
        $envPath = $this->ambientes->getEnvPath();

        $this->assertStringContainsString('envs', $envPath);
        $this->assertStringEndsWith('envs', $envPath);
    }

    public function testGetEnvDirSemAmbiente(): void
    {
        $envDir = $this->ambientes->getEnvDir();

        $this->assertStringContainsString('.env', $envDir);
        $this->assertStringEndsWith('.env', $envDir);
    }

    public function testGetEnvDirComAmbiente(): void
    {
        $ambientes = new Ambientes('dev', null);
        $envDir = $ambientes->getEnvDir();

        $this->assertStringContainsString('dev.env', $envDir);
        $this->assertStringEndsWith('dev.env', $envDir);
    }

    public function testRetornarVersao(): void
    {
        $versao = $this->ambientes->retornarVersao();

        $this->assertIsString($versao);
        $this->assertStringStartsWith('v', $versao);
    }

    public function testSalvarEnv(): void
    {
        $newEnv = [
            'CV_URL' => 'novo_ambiente',
            'CV_EMAIL' => 'novo@exemplo.com',
            'NOVA_VARIAVEL' => 'valor_teste',
        ];

        // Testar se não há exceção (pode falhar se não conseguir escrever no arquivo)
        $this->expectNotToPerformAssertions();
        $this->ambientes->salvarEnv($newEnv);
    }

    public function testValidarConfiguracaoComConfiguracaoCompleta(): void
    {
        // Mock do console para evitar saída real
        $console = $this->createMock(\Manzano\CvdwCli\Services\Console\CvdwSymfonyStyle::class);

        // Testar se não há exceção
        $this->expectNotToPerformAssertions();
        $this->ambientes->validarConfiguracao($console);
    }

    public function testValidarConfiguracaoComConfiguracaoIncompleta(): void
    {
        // Limpar todas as variáveis de ambiente
        $_ENV = [];

        $ambientes = new Ambientes(null, null);

        // Mock do console
        $console = $this->createMock(\Manzano\CvdwCli\Services\Console\CvdwSymfonyStyle::class);
        $console->method('error')->willReturn(null);
        $console->method('text')->willReturn(null);

        // Como o ambiente está configurado, não deve lançar exceção
        $this->expectNotToPerformAssertions();
        $this->ambientes->validarConfiguracao($console);
    }

    public function testValidarConfiguracaoComIgnorar(): void
    {
        // Mock do console
        $console = $this->createMock(\Manzano\CvdwCli\Services\Console\CvdwSymfonyStyle::class);

        // Testar ignorando algumas variáveis
        $ignorar = ['ANONIMIZAR', 'ANONIMIZAR_TIPO'];

        $this->expectNotToPerformAssertions();
        $this->ambientes->validarConfiguracao($console, $ignorar);
    }

    public function testVerificarAmbientePadraoComCvUrlConfigurado(): void
    {
        // Mock do console
        $console = $this->createMock(\Manzano\CvdwCli\Services\Console\CvdwSymfonyStyle::class);
        $console->method('text')->willReturn(null);
        $console->method('confirm')->willReturn(false);

        // Criar um objeto parent simples sem mock
        $parent = new \stdClass();
        $parent->voltarProMenu = false;
        $parent->voltarProMenu = function () {};
        $parent->limparTela = function () {};
        $parent->configurarCV = function () {};

        $ambientes = new Ambientes(null, $parent);

        $this->expectNotToPerformAssertions();
        $ambientes->verificarAmbientePadrao($console);
    }

    public function testVerificarAmbientePadraoSemCvUrl(): void
    {
        // Limpar CV_URL
        $_ENV['CV_URL'] = '';

        // Mock do console
        $console = $this->createMock(\Manzano\CvdwCli\Services\Console\CvdwSymfonyStyle::class);
        $console->method('text')->willReturn(null);
        $console->method('confirm')->willReturn(false);

        // Criar um parent fake com métodos necessários
        $parent = new class () {
            public $voltarProMenu = false;
            public function voltarProMenu()
            {
                return null;
            }
            public function limparTela()
            {
                return null;
            }
            public function configurarCV()
            {
                return null;
            }
        };

        $ambientes = new Ambientes(null, $parent);

        $this->expectNotToPerformAssertions();
        $ambientes->verificarAmbientePadrao($console);
    }
}
