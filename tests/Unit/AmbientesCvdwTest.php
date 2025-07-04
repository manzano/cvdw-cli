<?php

namespace Tests\Unit;

use Manzano\CvdwCli\Services\Ambientes;
use PHPUnit\Framework\TestCase;

class AmbientesCvdwTest extends TestCase
{
    private Ambientes $ambienteObj;
    private string $chaveAmbiente;
    private array $originalEnv;

    protected function setUp(): void
    {
        $this->backupEnvironmentVariables();
        $this->setTestEnvironmentVariables();
        $this->removeArquivosTEST();
    }

    protected function tearDown(): void
    {
        $this->restoreEnvironmentVariables();
        $this->removeArquivosTEST();
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

    public function testAmbientePadrao(): void
    {
        $this->ambienteObj = new Ambientes();

        $ambienteAtivo = $this->ambienteObj->ambienteAtivo();
        $this->assertIsString($ambienteAtivo, "Deve retornar uma string");
        $this->assertStringContainsString('TESTE_AMBIENTE', $ambienteAtivo);
        $this->assertStringContainsString('(Arquivo: .env)', $ambienteAtivo);

        $this->assertTrue($this->ambienteObj->retornarEnvs());

        $escopo = $this->ambienteObj->getEnvEscope();
        $this->assertIsArray($escopo, "Deve retornar um array");

        // Verificar se as chaves principais existem
        $chavesEsperadas = ['CV_URL', 'CV_EMAIL', 'CV_TOKEN', 'DB_CONNECTION', 'DB_HOST'];
        foreach ($chavesEsperadas as $chave) {
            $this->assertArrayHasKey($chave, $escopo, "Deve conter a chave {$chave}");
            $this->assertIsString($chave, "A chave deve ser uma string");
        }
    }

    public function testAmbienteNovo(): void
    {
        $this->chaveAmbiente = "TEST_" . hash("sha512", uniqid(random_int(0, 99), true));

        $this->ambienteObj = new Ambientes($this->chaveAmbiente);

        $ambienteAtivo = $this->ambienteObj->ambienteAtivo();
        $this->assertIsString($ambienteAtivo, "Deve retornar uma string");
        $this->assertStringContainsString($this->chaveAmbiente, $ambienteAtivo, "Deve conter a chave do ambiente");
        $this->assertTrue($this->ambienteObj->retornarEnvs());

        $diretorioEnv = $this->ambienteObj->getEnvDir();
        $this->assertStringContainsString($this->chaveAmbiente . '.env', $diretorioEnv, "Deve conter o nome do arquivo de ambiente");

        $newEnv = [
            'CV_URL' => $this->chaveAmbiente,
        ];

        // Testar salvamento (pode falhar se não conseguir escrever no arquivo)
        $this->ambienteObj->salvarEnv($newEnv);

        $escopo = $this->ambienteObj->getEnvEscope();
        $this->assertIsArray($escopo, "Deve retornar um array");

        // Verificar se as chaves principais existem
        $chavesEsperadas = ['CV_URL', 'CV_EMAIL', 'CV_TOKEN', 'DB_CONNECTION', 'DB_HOST'];
        foreach ($chavesEsperadas as $chave) {
            $this->assertArrayHasKey($chave, $escopo, "Deve conter a chave {$chave}");
            $this->assertIsString($chave, "A chave deve ser uma string");
        }
    }

    public function testAmbienteSemConfiguracao(): void
    {
        // Limpar variáveis de ambiente
        $_ENV = [];

        $this->ambienteObj = new Ambientes();

        $ambienteAtivo = $this->ambienteObj->ambienteAtivo();
        // Como o ambiente está configurado, esperamos que contenha o ambiente atual
        $this->assertStringContainsString('(Arquivo:', $ambienteAtivo);

        $escopo = $this->ambienteObj->getEnvEscope();
        $this->assertIsArray($escopo, "Deve retornar um array mesmo sem configuração");
    }

    public function testRetornarVersao(): void
    {
        $this->ambienteObj = new Ambientes();

        $versao = $this->ambienteObj->retornarVersao();
        $this->assertIsString($versao, "Deve retornar uma string");
        $this->assertStringStartsWith('v', $versao, "Deve começar com 'v'");
    }

    public function testGetEnvPath(): void
    {
        $this->ambienteObj = new Ambientes();

        $envPath = $this->ambienteObj->getEnvPath();
        $this->assertIsString($envPath, "Deve retornar uma string");
        $this->assertStringContainsString('envs', $envPath, "Deve conter 'envs' no caminho");
        $this->assertStringEndsWith('envs', $envPath, "Deve terminar com 'envs'");
    }

    private function removeArquivosTEST(): void
    {
        // Remover todos os arquivos que tenham o nome com TEST_*.env em __DIR__ . '/../../envs'
        $arquivosBusca = __DIR__ . '/../../src/envs/TEST_*.env';
        $arquivos = glob($arquivosBusca);
        if ($arquivos !== false) {
            foreach ($arquivos as $arquivo) {
                if (file_exists($arquivo)) {
                    unlink($arquivo);
                }
            }
        }
    }
}
