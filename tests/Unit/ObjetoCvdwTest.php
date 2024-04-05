<?php


namespace Tests\Unit;

use Tests\Support\UnitTester;
use Manzano\CvdwCli\Services\Objeto;
use Manzano\CvdwCli\Services\Console\CvdwSymfonyStyle;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

class ObjetoCvdwTest extends \Codeception\Test\Unit
{

    protected UnitTester $tester;
    /**
     * @var Objeto
     */
    protected $objeto;
    protected $objetoComTabelas;
    protected $objetoSemTabelas;

    protected function _before()
    {
        $this->objeto = new Objeto(new ArrayInput([]), new NullOutput());
    }

    public function testRetornarObjetosConstante()
    {
        $result = $this->objeto->retornarConstantesObjetos("leads");
        $this->assertIsArray($result, "Deve retornar um array");
        $this->assertArrayHasKey("leads", $result, "Array deve conter a chave 'leads'");
    }

    public function testRetornarObjetosSemArgumentos()
    {
        $result = $this->objeto->retornarObjetos();
        $this->assertIsArray($result, "Deve retornar um array");
        $this->assertArrayHasKey("leads", $result, "Array deve conter a chave 'leads'");
    }

    public function testRetornarObjetosComObjetoValido()
    {
        $result = $this->objeto->retornarObjetos("leads");
        $this->assertIsArray($result, "Deve retornar um array para um objeto válido");
        $this->assertCount(1, $result, "Deve retornar um array com um único elemento para um objeto válido");
    }

    public function testRetornarObjetoInvalido()
    {
        $result = $this->objeto->retornarObjetos("invalido");
        $this->assertIsArray($result, "Deve retornar um array para um objeto inválido");
        $this->assertCount(0, $result, "Deve retornar um array vazio para um objeto inválido");
    }

    public function testRetornarYamlObjeto(){
        $result = $this->objeto->retornarObjeto("leads");
        $this->assertIsArray($result, "Deve retornar um array para um objeto válido");
        $this->assertTrue(count($result) > 0, "Deve retornar um array com um único elemento para um objeto válido");
    }

    public function testRetornarYamlObjetoInvalido()
    {
        $result = $this->objeto->retornarObjeto("objeto_invalido");
        $this->assertIsArray($result, "Deve retornar um array vazio para um objeto inválido");
        $this->assertCount(0, $result, "Deve retornar um array vazio para um objeto inválido");
    }

    public function testRetornarObjetoComTabelas()
    {
        $this->objetoComTabelas = $this->objeto->retornarObjetoTabelas("reservas");
        $this->assertIsArray($this->objetoComTabelas, "Deve retornar um array para um objeto válido");
        $this->assertTrue(count($this->objetoComTabelas) > 0, "Deve retornar um array vazio para um objeto inválido");
    }

    public function testRetornarObjetoSemTabelas()
    {
        $this->objetoSemTabelas = $this->objeto->retornarObjetoTabelas("leads");
        $this->assertIsArray($this->objetoSemTabelas, "Deve retornar um array para um objeto válido");
        $this->assertTrue(count($this->objetoSemTabelas) == 0, "Deve retornar um array vazio para um objeto inválido");
    }

    public function testRetornarObjetoTabelasComObjetoInvalido()
    {
        $result = $this->objeto->retornarObjetoTabelas("objeto_invalido");
        $this->assertIsArray($result, "Deve retornar um array para um objeto válido");
        $this->assertTrue(count($result) == 0, "Deve retornar um array vazio para um objeto inválido");
    }

    public function testRetornarTipoDadosTabela()
    {
        $componentes = 0;
        $tabelas = 0;
        $erros = 0;
        
        $objeto = $this->objeto->retornarObjeto("reservas");
        foreach($objeto['response']['dados'] as $dados){
            $tipo = $this->objeto->identificarTipoDeDados($dados);
            switch ($tipo) {
                case 'TABELA':
                        $tabelas++;
                    break;
                case 'COMPONENTE':
                        $componentes++;
                    break;
                default:
                        $erros++;
                break;
            }
        }
        $this->assertTrue($componentes > 0, "Retornou componentes");
        $this->assertTrue($tabelas > 0, "Retornou tabelas");
        $this->assertTrue($erros == 0, "Não retornou erros");
    }

    
}
