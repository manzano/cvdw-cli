<?php


namespace Tests\Unit;

use Tests\Support\UnitTester;
use Manzano\CvdwCli\Services\Log;

class LogCvdwTest extends \Codeception\Test\Unit
{

    protected UnitTester $tester;
    /**
     * @var Objeto
     */
    protected $arquivoLog = 'UnitTest.log';
    public $logObj;
    public $chaveLog;

    protected function _before()
    {
        $this->logObj = new Log($this->arquivoLog);
        // Gera uma chave aleatória para testar a escrita no log
        $this->chaveLog = hash("sha512", uniqid(random_int(0, 99)));
    }

    public function testLog()
    {
        $this->logObj->criarArquivoLog();
        $diretorioLog = $this->logObj->retornarDiretorioLog();
        codecept_debug("Arquivo do log: ".$diretorioLog . "/" . $this->arquivoLog);
        $this->assertFileExists($diretorioLog."/".$this->arquivoLog, "Deve criar o arquivo de log");
    }

    public function testEscrevendoNoLog(){
        $this->logObj->escreverLog($this->chaveLog);
        $diretorioLog = $this->logObj->retornarDiretorioLog();
        $conteudo = file_get_contents($diretorioLog."/".$this->arquivoLog);
        $this->assertStringContainsString($this->chaveLog, $conteudo, "Deve conter a chave no arquivo de log");
        $this->removerArquivoLog();
    }

    protected function removerArquivoLog(): void{
        $diretorioLog = $this->logObj->retornarDiretorioLog();
        unlink($diretorioLog . "/" . $this->arquivoLog);
    }
    
}
