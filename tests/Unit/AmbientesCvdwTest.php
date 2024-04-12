<?php


namespace Tests\Unit;

use Tests\Support\UnitTester;
use Manzano\CvdwCli\Services\Ambientes;

class AmbientesCvdwTest extends \Codeception\Test\Unit
{

    protected UnitTester $tester;
    /**
     * @var Objeto
     */
    public $ambienteObj;
    public $chaveAmbiente;

    protected function _before()
    {
        $this->removeArquivosTEST();
    }

    public function testAmbientePadrao()
    {
        $this->ambienteObj = new Ambientes();
        $ambienteAtivo = $this->ambienteObj->ambienteAtivo();
        $this->assertIsString($ambienteAtivo, "Deve retornar uma string");
        $this->assertTrue($this->ambienteObj->retornarEnvs());
        $escopo = $this->ambienteObj->getEnvEscope();
        $this->assertIsArray($escopo, "Deve retornar um array");
        foreach($escopo as $key => $valor){
            $this->assertIsString($key, "Deve retornar uma string");
            $this->assertTrue(isset($_ENV[$key]), 'Deve existir $_ENV['.$key.']');
            codecept_debug('Deve existir $_ENV[' . $key . '] = ' . $_ENV[$key]);
        }

    }

    public function testAmbienteNovo()
    {
        $this->chaveAmbiente = "TEST_".hash("sha512", uniqid(random_int(0, 99)));
        codecept_debug('Chave: ' . $this->chaveAmbiente);

        $this->ambienteObj = new Ambientes($this->chaveAmbiente);

        $ambienteAtivo = $this->ambienteObj->ambienteAtivo();
        codecept_debug('Ambiente: ' . $ambienteAtivo);
        $this->assertIsString($ambienteAtivo, "Deve retornar uma string");
        $this->assertStringContainsString($this->chaveAmbiente, $ambienteAtivo, "Deve conter a chave do ambiente");
        $this->assertTrue($this->ambienteObj->retornarEnvs());

        $diretorioEnv = $this->ambienteObj->getEnvDir();
        codecept_debug('Arquivo: ' . $diretorioEnv);
        $this->assertFileExists($diretorioEnv, "Deve criar o arquivo de ambiente");

        $newEnv = [
            'CV_URL' => $this->chaveAmbiente
        ];
        $this->ambienteObj->salvarEnv($newEnv);
        $this->assertStringContainsString($this->chaveAmbiente, $_ENV['CV_URL'], "Deve conter a chave do ambiente");

        $escopo = $this->ambienteObj->getEnvEscope();
        $this->assertIsArray($escopo, "Deve retornar um array");
        foreach ($escopo as $key => $valor) {
            $this->assertIsString($key, "Deve retornar uma string");
            $this->assertTrue(isset($_ENV[$key]), 'Deve existir $_ENV[' . $key . ']');
            codecept_debug('Deve existir $_ENV[' . $key . '] = ' . $_ENV[$key]);
        }

        $this->removeArquivosTEST();

    }

    private function removeArquivosTEST(){
        // Remover todos os arquivos que tenham o nome com TEST_*.env em __DIR__ . '/../../envs'
        $arquivosBusca = __DIR__ . '/../../src/envs/TEST_*.env';
        $arquivos = glob($arquivosBusca);
        foreach ($arquivos as $arquivo) {
            unlink($arquivo);
        }
    }
    
}
