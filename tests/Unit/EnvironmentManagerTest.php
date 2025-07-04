<?php

namespace Tests\Unit;

use Manzano\CvdwCli\Services\EnvironmentManager;
use PHPUnit\Framework\TestCase;

class EnvironmentManagerTest extends TestCase
{
    private EnvironmentManager $environmentManager;
    private array $originalEnv;

    protected function setUp(): void
    {
        // Backup das variáveis de ambiente originais
        $this->backupEnvironmentVariables();

        // Configurar variáveis de ambiente para teste
        $this->setTestEnvironmentVariables();

        $this->environmentManager = new EnvironmentManager();
    }

    protected function tearDown(): void
    {
        // Restaurar variáveis de ambiente originais
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

    public function testGetCvUrl(): void
    {
        $this->assertEquals('teste_ambiente', $this->environmentManager->getCvUrl());
    }

    public function testGetCvEmail(): void
    {
        $this->assertEquals('teste@exemplo.com', $this->environmentManager->getCvEmail());
    }

    public function testGetCvToken(): void
    {
        $this->assertEquals('token_teste_123', $this->environmentManager->getCvToken());
    }

    public function testGetDbConnection(): void
    {
        $this->assertEquals('pdo_mysql', $this->environmentManager->getDbConnection());
    }

    public function testGetDbHost(): void
    {
        $this->assertEquals('localhost', $this->environmentManager->getDbHost());
    }

    public function testGetDbPort(): void
    {
        $this->assertEquals('3306', $this->environmentManager->getDbPort());
    }

    public function testGetDbDatabase(): void
    {
        $this->assertEquals('teste_db', $this->environmentManager->getDbDatabase());
    }

    public function testGetDbUsername(): void
    {
        $this->assertEquals('usuario_teste', $this->environmentManager->getDbUsername());
    }

    public function testGetDbPassword(): void
    {
        $this->assertEquals('senha_teste', $this->environmentManager->getDbPassword());
    }

    public function testGetDbSchema(): void
    {
        $this->assertEquals('public', $this->environmentManager->getDbSchema());
    }

    public function testGetAnonimizar(): void
    {
        $this->assertTrue($this->environmentManager->getAnonimizar());
    }

    public function testGetAnonimizarTipo(): void
    {
        $this->assertEquals('Asteriscos', $this->environmentManager->getAnonimizarTipo());
    }

    public function testSetCvUrl(): void
    {
        $this->environmentManager->setCvUrl('novo_ambiente');
        $this->assertEquals('novo_ambiente', $this->environmentManager->getCvUrl());
    }

    public function testSetCvEmail(): void
    {
        $this->environmentManager->setCvEmail('novo@exemplo.com');
        $this->assertEquals('novo@exemplo.com', $this->environmentManager->getCvEmail());
    }

    public function testSetCvToken(): void
    {
        $this->environmentManager->setCvToken('novo_token_456');
        $this->assertEquals('novo_token_456', $this->environmentManager->getCvToken());
    }

    public function testSetDbConnection(): void
    {
        $this->environmentManager->setDbConnection('pdo_pgsql');
        $this->assertEquals('pdo_pgsql', $this->environmentManager->getDbConnection());
    }

    public function testSetDbHost(): void
    {
        $this->environmentManager->setDbHost('127.0.0.1');
        $this->assertEquals('127.0.0.1', $this->environmentManager->getDbHost());
    }

    public function testSetDbPort(): void
    {
        $this->environmentManager->setDbPort('5432');
        $this->assertEquals('5432', $this->environmentManager->getDbPort());
    }

    public function testSetDbDatabase(): void
    {
        $this->environmentManager->setDbDatabase('novo_banco');
        $this->assertEquals('novo_banco', $this->environmentManager->getDbDatabase());
    }

    public function testSetDbUsername(): void
    {
        $this->environmentManager->setDbUsername('novo_usuario');
        $this->assertEquals('novo_usuario', $this->environmentManager->getDbUsername());
    }

    public function testSetDbPassword(): void
    {
        $this->environmentManager->setDbPassword('nova_senha');
        $this->assertEquals('nova_senha', $this->environmentManager->getDbPassword());
    }

    public function testSetDbSchema(): void
    {
        $this->environmentManager->setDbSchema('schema_teste');
        $this->assertEquals('schema_teste', $this->environmentManager->getDbSchema());
    }

    public function testSetAnonimizar(): void
    {
        $this->environmentManager->setAnonimizar(false);
        $this->assertFalse($this->environmentManager->getAnonimizar());
    }

    public function testSetAnonimizarTipo(): void
    {
        $this->environmentManager->setAnonimizarTipo('Hash');
        $this->assertEquals('Hash', $this->environmentManager->getAnonimizarTipo());
    }

    public function testGetWithDefaultValue(): void
    {
        $this->assertEquals('valor_padrao', $this->environmentManager->get('VARIAVEL_INEXISTENTE', 'valor_padrao'));
    }

    public function testSetGeneric(): void
    {
        $this->environmentManager->set('NOVA_VARIAVEL', 'valor_teste');
        $this->assertEquals('valor_teste', $this->environmentManager->get('NOVA_VARIAVEL'));
    }

    public function testHasWithExistingVariable(): void
    {
        $this->assertTrue($this->environmentManager->has('CV_URL'));
    }

    public function testHasWithNonExistingVariable(): void
    {
        $this->assertFalse($this->environmentManager->has('VARIAVEL_INEXISTENTE'));
    }

    public function testHasWithEmptyVariable(): void
    {
        $this->environmentManager->set('VARIAVEL_VAZIA', '');
        $this->assertFalse($this->environmentManager->has('VARIAVEL_VAZIA'));
    }

    public function testGetAll(): void
    {
        $allVars = $this->environmentManager->getAll();

        $this->assertIsArray($allVars);
        $this->assertArrayHasKey('CV_URL', $allVars);
        $this->assertArrayHasKey('CV_EMAIL', $allVars);
        $this->assertArrayHasKey('DB_CONNECTION', $allVars);
        $this->assertEquals('teste_ambiente', $allVars['CV_URL']);
    }

    public function testSaveToEnv(): void
    {
        $newEnv = [
            'NOVA_VAR_1' => 'valor1',
            'NOVA_VAR_2' => 'valor2',
        ];

        $this->environmentManager->saveToEnv($newEnv);

        $this->assertEquals('valor1', $this->environmentManager->get('NOVA_VAR_1'));
        $this->assertEquals('valor2', $this->environmentManager->get('NOVA_VAR_2'));
    }

    public function testGetAnonimizarWithStringTrue(): void
    {
        $this->environmentManager->set('ANONIMIZAR', 'true');
        $this->assertTrue($this->environmentManager->getAnonimizar());
    }

    public function testGetAnonimizarWithStringFalse(): void
    {
        $this->environmentManager->set('ANONIMIZAR', 'false');
        $this->assertFalse($this->environmentManager->getAnonimizar());
    }

    public function testGetAnonimizarWithBooleanTrue(): void
    {
        $this->environmentManager->set('ANONIMIZAR', true);
        $this->assertTrue($this->environmentManager->getAnonimizar());
    }

    public function testGetAnonimizarWithBooleanFalse(): void
    {
        $this->environmentManager->set('ANONIMIZAR', false);
        $this->assertFalse($this->environmentManager->getAnonimizar());
    }

    public function testGetAnonimizarWithInteger(): void
    {
        $this->environmentManager->set('ANONIMIZAR', 1);
        $this->assertTrue($this->environmentManager->getAnonimizar());

        $this->environmentManager->set('ANONIMIZAR', 0);
        $this->assertFalse($this->environmentManager->getAnonimizar());
    }

    public function testDefaultValuesWhenEnvironmentNotSet(): void
    {
        // Limpar variáveis de ambiente
        $_ENV = [];

        $envManager = new EnvironmentManager();
        // Como o ambiente pode estar configurado, apenas garantir que retorna string
        $this->assertIsString($envManager->getCvUrl());
        $this->assertIsString($envManager->getCvEmail());
        $this->assertIsString($envManager->getCvToken());
        $this->assertEquals('pdo_mysql', $envManager->getDbConnection());
        // Não testar valores específicos pois podem vir do arquivo .env
        $this->assertIsString($envManager->getDbHost());
        $this->assertIsString($envManager->getDbPort());
        $this->assertIsString($envManager->getDbDatabase());
        $this->assertIsString($envManager->getDbUsername());
        $this->assertIsString($envManager->getDbPassword());
        $this->assertIsString($envManager->getDbSchema());
        $this->assertIsBool($envManager->getAnonimizar());
        $this->assertEquals('Asteriscos', $envManager->getAnonimizarTipo());
    }
}
