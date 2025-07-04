<?php

namespace Tests\Unit;

use Manzano\CvdwCli\Services\Objeto;
use PHPUnit\Framework\TestCase;

class ObjetoCvdwTest extends TestCase
{
    private Objeto $objeto;
    private array $originalEnv;

    protected function setUp(): void
    {
        $this->backupEnvironmentVariables();
        $this->setTestEnvironmentVariables();
        $this->objeto = new Objeto(
            new \Symfony\Component\Console\Input\ArrayInput([]),
            new \Symfony\Component\Console\Output\NullOutput()
        );
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

    public function testObjetoPodeSerInstanciado(): void
    {
        $this->assertInstanceOf(Objeto::class, $this->objeto);
    }

    public function testRetornarObjetosComObjetoValido(): void
    {
        $objetos = $this->objeto->retornarObjetos('leads');
        $this->assertIsArray($objetos, "Deve retornar um array para um objeto válido");
        $this->assertArrayHasKey('leads', $objetos, "Deve conter a chave 'leads'");
    }

    public function testRetornarObjetosComObjetoInvalido(): void
    {
        $objetos = $this->objeto->retornarObjetos('objeto_invalido');
        $this->assertIsArray($objetos, "Deve retornar um array para um objeto inválido");
        $this->assertCount(0, $objetos, "Deve retornar um array vazio para um objeto inválido");
    }

    public function testRetornarObjetosComMultiplosObjetos(): void
    {
        $objetos = $this->objeto->retornarObjetos('leads+reservas');
        $this->assertIsArray($objetos, "Deve retornar um array");
        $this->assertArrayHasKey('leads', $objetos, "Deve conter a chave 'leads'");
        $this->assertArrayHasKey('reservas', $objetos, "Deve conter a chave 'reservas'");
    }

    public function testRetornarObjetosComAll(): void
    {
        $objetos = $this->objeto->retornarObjetos('all');
        $this->assertIsArray($objetos, "Deve retornar um array");
        $this->assertGreaterThan(0, count($objetos), "Deve retornar objetos");
    }

    public function testRetornarObjetoEspecifico(): void
    {
        $objeto = $this->objeto->retornarObjeto('leads');
        $this->assertIsArray($objeto, "Deve retornar um array para um objeto válido");
    }

    public function testRetornarObjetoInvalido(): void
    {
        $objeto = $this->objeto->retornarObjeto('objeto_invalido');
        $this->assertIsArray($objeto, "Deve retornar um array vazio para um objeto inválido");
        $this->assertCount(0, $objeto, "Deve retornar um array vazio para um objeto inválido");
    }

    public function testRetornarObjetoTabelas(): void
    {
        $tabelas = $this->objeto->retornarObjetoTabelas('reservas');
        $this->assertIsArray($tabelas, "Deve retornar um array");
    }

    public function testRetornarObjetoTabelasComObjetoInvalido(): void
    {
        $tabelas = $this->objeto->retornarObjetoTabelas('objeto_invalido');
        $this->assertIsArray($tabelas, "Deve retornar um array vazio para um objeto inválido");
        $this->assertCount(0, $tabelas, "Deve retornar um array vazio para um objeto inválido");
    }

    public function testIdentificarTipoDeDados(): void
    {
        $dadosTabela = [
            'coluna1' => ['valor1', 'valor2'],
            'coluna2' => ['valor3', 'valor4']
        ];
        $tipo = $this->objeto->identificarTipoDeDados($dadosTabela);
        $this->assertEquals('TABELA', $tipo, "Deve identificar como TABELA");

        $dadosComponente = [
            'nome' => 'Teste',
            'valor' => '123'
        ];
        $tipo = $this->objeto->identificarTipoDeDados($dadosComponente);
        $this->assertEquals('COMPONENTE', $tipo, "Deve identificar como COMPONENTE");
    }

    public function testRetornarConstantesObjetos(): void
    {
        $constantes = $this->objeto->retornarConstantesObjetos();
        $this->assertIsArray($constantes, "Deve retornar um array");
        $this->assertGreaterThan(0, count($constantes), "Deve retornar constantes");
    }
}
