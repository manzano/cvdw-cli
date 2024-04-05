<?php


namespace Tests\Unit;

use Tests\Support\UnitTester;
use Manzano\CvdwCli;

class UnitCvdwTest extends \Codeception\Test\Unit
{

    protected UnitTester $tester;

    protected function _before()
    {
    }

    public function testValidarDataComHora()
    {
        $dataComHora = "2023-04-05T14:00:00";
        $resultado = validarData($dataComHora);
        $this->assertTrue($resultado, "A data com hora deve ser válida.");
    }

    public function testValidarDataSemHora()
    {
        $dataSemHora = "2023-04-05";
        $resultado = validarData($dataSemHora);
        $this->assertTrue($resultado, "A data sem hora deve ser válida.");
    }

    public function testValidarDataInvalida()
    {
        $dataInvalida = "2023-02-30";
        $resultado = validarData($dataInvalida);
        $this->assertFalse($resultado, "A data deve ser inválida.");
    }

}
