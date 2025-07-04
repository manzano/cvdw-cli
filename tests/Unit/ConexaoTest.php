<?php

require_once __DIR__ . '/../../src/app/Inc/Conexao.php';

use PHPUnit\Framework\TestCase;
use Manzano\CvdwCli\Services\EnvironmentManager;
use Manzano\CvdwCli\Services\Console\CvdwSymfonyStyle;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Manzano\CvdwCli\Inc\Conexao;

class ConexaoTest extends TestCase
{
    private $input;
    private $output;
    private $environmentManager;
    
    protected function setUp(): void
    {
        $this->input = new ArrayInput([]);
        $this->output = new BufferedOutput();
        $this->environmentManager = $this->createMock(EnvironmentManager::class);
    }
    
    public function testConectarDBWithEnvironmentManager()
    {
        $this->environmentManager->method('getDbDatabase')->willReturn('test_db');
        $this->environmentManager->method('getDbUsername')->willReturn('test_user');
        $this->environmentManager->method('getDbPassword')->willReturn('test_pass');
        $this->environmentManager->method('getDbHost')->willReturn('localhost');
        $this->environmentManager->method('getDbPort')->willReturn('5432');
        $this->environmentManager->method('getDbConnection')->willReturn('pdo_pgsql');
        $this->environmentManager->method('getDbSchema')->willReturn('public');
        $this->environmentManager->method('has')->willReturn(true);
        
        try {
            $connection = Conexao::conectarDB($this->input, $this->output, false, $this->environmentManager);
            $this->assertTrue(true);
        } catch (Exception $e) {
            $this->fail('A função conectarDB não deveria lançar exceção com showException = false');
        }
    }
    
    public function testConectarDBWithNullEnvironmentManager()
    {
        try {
            $connection = Conexao::conectarDB($this->input, $this->output, false);
            $this->assertTrue(true);
        } catch (Exception $e) {
            $this->fail('A função conectarDB não deveria lançar exceção com showException = false');
        }
    }
    
    public function testConectarDBWithMySQLDriver()
    {
        $this->environmentManager->method('getDbDatabase')->willReturn('test_db');
        $this->environmentManager->method('getDbUsername')->willReturn('test_user');
        $this->environmentManager->method('getDbPassword')->willReturn('test_pass');
        $this->environmentManager->method('getDbHost')->willReturn('localhost');
        $this->environmentManager->method('getDbPort')->willReturn('3306');
        $this->environmentManager->method('getDbConnection')->willReturn('pdo_mysql');
        
        try {
            $connection = Conexao::conectarDB($this->input, $this->output, false, $this->environmentManager);
            $this->assertTrue(true);
        } catch (Exception $e) {
            $this->fail('A função conectarDB não deveria lançar exceção com showException = false');
        }
    }
    
    public function testConectarDBWithEmptyParameters()
    {
        $this->environmentManager->method('getDbDatabase')->willReturn('');
        $this->environmentManager->method('getDbUsername')->willReturn('');
        $this->environmentManager->method('getDbPassword')->willReturn('');
        $this->environmentManager->method('getDbHost')->willReturn('');
        $this->environmentManager->method('getDbPort')->willReturn('');
        $this->environmentManager->method('getDbConnection')->willReturn('pdo_pgsql');
        
        try {
            $connection = Conexao::conectarDB($this->input, $this->output, false, $this->environmentManager);
            $this->assertTrue(true);
        } catch (Exception $e) {
            $this->fail('A função conectarDB não deveria lançar exceção com showException = false');
        }
    }
    
    public function testConectarDBWithShowExceptionTrue()
    {
        $this->environmentManager->method('getDbDatabase')->willReturn('test_db');
        $this->environmentManager->method('getDbUsername')->willReturn('test_user');
        $this->environmentManager->method('getDbPassword')->willReturn('test_pass');
        $this->environmentManager->method('getDbHost')->willReturn('localhost');
        $this->environmentManager->method('getDbPort')->willReturn('5432');
        $this->environmentManager->method('getDbConnection')->willReturn('pdo_pgsql');
        $this->environmentManager->method('getDbSchema')->willReturn('public');
        $this->environmentManager->method('has')->willReturn(true);
        
        try {
            $connection = Conexao::conectarDB($this->input, $this->output, true, $this->environmentManager);
            $this->assertTrue(true);
        } catch (Exception $e) {
            $this->fail('A função conectarDB não deveria lançar exceção');
        }
    }
    

} 