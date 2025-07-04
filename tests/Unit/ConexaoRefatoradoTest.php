<?php

namespace Tests\Unit;

use Manzano\CvdwCli\Services\EnvironmentManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

class ConexaoRefatoradoTest extends TestCase
{
    private array $originalEnv;
    private ArrayInput $input;
    private NullOutput $output;

    protected function setUp(): void
    {
        $this->backupEnvironmentVariables();
        $this->setTestEnvironmentVariables();
        
        $this->input = new ArrayInput([]);
        $this->output = new NullOutput();
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

    public function testConectarDBComEnvironmentManager(): void
    {
        $environmentManager = new EnvironmentManager();
        
        // Configurar valores de teste no EnvironmentManager
        $environmentManager->setDbHost('127.0.0.1');
        $environmentManager->setDbPort('3306');
        $environmentManager->setDbDatabase('teste_db');
        $environmentManager->setDbUsername('usuario_teste');
        $environmentManager->setDbPassword('senha_teste');
        $environmentManager->setDbConnection('pdo_mysql');
        
        $connection = \conectarDB($this->input, $this->output, false, $environmentManager);
        
        $this->assertInstanceOf(\Doctrine\DBAL\Connection::class, $connection);
    }

    public function testConectarDBSemEnvironmentManager(): void
    {
        // A função deve criar um EnvironmentManager automaticamente
        $connection = \conectarDB($this->input, $this->output, false);
        
        $this->assertInstanceOf(\Doctrine\DBAL\Connection::class, $connection);
    }

    public function testConectarDBComPostgreSQL(): void
    {
        $environmentManager = new EnvironmentManager();
        
        // Configurar para PostgreSQL
        $environmentManager->setDbConnection('pdo_pgsql');
        $environmentManager->setDbHost('127.0.0.1');
        $environmentManager->setDbPort('5432');
        $environmentManager->setDbDatabase('teste_db');
        $environmentManager->setDbUsername('usuario_teste');
        $environmentManager->setDbPassword('senha_teste');
        $environmentManager->setDbSchema('schema_teste');
        
        $connection = \conectarDB($this->input, $this->output, false, $environmentManager);
        
        $this->assertInstanceOf(\Doctrine\DBAL\Connection::class, $connection);
    }

    public function testConectarDBComPostgreSQLSchemaVazio(): void
    {
        $environmentManager = new EnvironmentManager();
        
        // Configurar para PostgreSQL sem schema
        $environmentManager->setDbConnection('pdo_pgsql');
        $environmentManager->setDbHost('127.0.0.1');
        $environmentManager->setDbPort('5432');
        $environmentManager->setDbDatabase('teste_db');
        $environmentManager->setDbUsername('usuario_teste');
        $environmentManager->setDbPassword('senha_teste');
        $environmentManager->setDbSchema(''); // Schema vazio
        
        $connection = \conectarDB($this->input, $this->output, false, $environmentManager);
        
        $this->assertInstanceOf(\Doctrine\DBAL\Connection::class, $connection);
    }

    public function testConectarDBComValoresVazios(): void
    {
        $environmentManager = new EnvironmentManager();
        
        // Configurar com valores vazios
        $environmentManager->setDbHost('');
        $environmentManager->setDbPort('');
        $environmentManager->setDbDatabase('');
        $environmentManager->setDbUsername('');
        $environmentManager->setDbPassword('');
        $environmentManager->setDbConnection('pdo_mysql');
        
        $connection = \conectarDB($this->input, $this->output, false, $environmentManager);
        
        $this->assertInstanceOf(\Doctrine\DBAL\Connection::class, $connection);
    }

    public function testConectarDBComSQLServer(): void
    {
        $environmentManager = new EnvironmentManager();
        
        // Configurar para SQL Server
        $environmentManager->setDbConnection('pdo_sqlsrv');
        $environmentManager->setDbHost('127.0.0.1');
        $environmentManager->setDbPort('1433');
        $environmentManager->setDbDatabase('teste_db');
        $environmentManager->setDbUsername('usuario_teste');
        $environmentManager->setDbPassword('senha_teste');
        
        $connection = \conectarDB($this->input, $this->output, false, $environmentManager);
        
        $this->assertInstanceOf(\Doctrine\DBAL\Connection::class, $connection);
    }

    public function testConectarDBComShowExceptionTrue(): void
    {
        $environmentManager = new EnvironmentManager();
        
        // Configurar com valores inválidos para forçar erro
        $environmentManager->setDbHost('host_invalido');
        $environmentManager->setDbPort('9999');
        $environmentManager->setDbDatabase('banco_invalido');
        $environmentManager->setDbUsername('usuario_invalido');
        $environmentManager->setDbPassword('senha_invalida');
        $environmentManager->setDbConnection('pdo_mysql');
        
        // Deve retornar uma conexão mesmo com erro (devido ao showException = true)
        $connection = \conectarDB($this->input, $this->output, true, $environmentManager);
        
        $this->assertInstanceOf(\Doctrine\DBAL\Connection::class, $connection);
    }

    public function testConectarDBComShowExceptionFalse(): void
    {
        $environmentManager = new EnvironmentManager();
        
        // Configurar com valores inválidos para forçar erro
        $environmentManager->setDbHost('host_invalido');
        $environmentManager->setDbPort('9999');
        $environmentManager->setDbDatabase('banco_invalido');
        $environmentManager->setDbUsername('usuario_invalido');
        $environmentManager->setDbPassword('senha_invalida');
        $environmentManager->setDbConnection('pdo_mysql');
        
        // Deve retornar uma conexão mesmo com erro (devido ao showException = false)
        $connection = \conectarDB($this->input, $this->output, false, $environmentManager);
        
        $this->assertInstanceOf(\Doctrine\DBAL\Connection::class, $connection);
    }

    public function testConectarDBComEnvironmentManagerNull(): void
    {
        // Testar passando null explicitamente
        $connection = \conectarDB($this->input, $this->output, false, null);
        
        $this->assertInstanceOf(\Doctrine\DBAL\Connection::class, $connection);
    }

    public function testConectarDBComDiferentesTiposDeInput(): void
    {
        $environmentManager = new EnvironmentManager();
        
        // Testar com diferentes tipos de input
        $arrayInput = new ArrayInput(['--test' => 'value']);
        $connection = \conectarDB($arrayInput, $this->output, false, $environmentManager);
        
        $this->assertInstanceOf(\Doctrine\DBAL\Connection::class, $connection);
    }

    public function testConectarDBComDiferentesTiposDeOutput(): void
    {
        $environmentManager = new EnvironmentManager();
        
        // Testar com diferentes tipos de output
        $bufferedOutput = new \Symfony\Component\Console\Output\BufferedOutput();
        $connection = \conectarDB($this->input, $bufferedOutput, false, $environmentManager);
        
        $this->assertInstanceOf(\Doctrine\DBAL\Connection::class, $connection);
    }
} 