<?php


namespace Tests\Functional;

use Tests\Support\FunctionalTester;

class CvdwTest extends \Codeception\Test\Unit
{

    protected FunctionalTester $tester;

    protected function _before()
    {
    }

    public function executandoCVDW(FunctionalTester $I)
    {
        $I->runSymfonyConsoleCommand('app:cvdw');

        // Você pode adicionar asserções aqui para verificar a saída do comando
        // Por exemplo, verificar se um arquivo esperado foi criado ou se a saída contém uma string específica
        $I->seeInConsoleOutput('Comando executado com sucesso');
    }
}
