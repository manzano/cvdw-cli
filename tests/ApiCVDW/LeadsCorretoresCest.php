<?php
namespace Tests\ApiCVDW;

use Tests\Support\ApiTester;
use Tests\Helper\CvdwHelper;
use Tests\ApiCVDW\Common;
use Codeception\Util\HttpCode;
use PHPUnit\Framework\Assert;

class LeadsCorretoresCest extends Common
{
    public function getLeadsCorretores(ApiTester $I)
    {
        
        sleep(3);
        
        $I->sendGet('/leads/corretores', ['pagina' => 1, 'registros' => 1]);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();

        $I->seeResponseMatchesJsonType([
            'pagina' => 'integer',
            'registros' => 'integer',
            'total_de_registros' => 'integer',
            'total_de_paginas' => 'integer',
            'dados' => 'array'
        ]);

        // Testar a estrutura de 'dados'
        $dados = $I->grabDataFromResponseByJsonPath('$.dados[0]');
        Assert::assertNotEmpty($dados); // Assegura que 'dados' não está vazio

        //$I->validarFormatoDaData('referencia_data', 'Y-m-d H:i:s', $dados[0]);

        //print_r($dados[0]);
        // Estrutura de 'dados[0]'
        /*
        $I->seeResponseMatchesJsonType([
            'referencia' => 'string',
            'idlead' => 'integer|null',
            'idcorretor' => 'integer|null',
            'corretor' => 'string|null',
            'idimobiliaria' => 'integer|null',
            'imobiliaria' => 'string|null',
            'data_entrada' => 'string|null',
            'data_sincronizacao' => 'string|null'
        ], '$.dados[0]');
        */

    }
}