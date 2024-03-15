<?php
namespace Tests\ApiCVDW;

use Tests\Support\ApiTester;
use Tests\Helper\CvdwHelper;
use Tests\ApiCVDW\Common;
use Codeception\Util\HttpCode;
use PHPUnit\Framework\Assert;

class CamposAdicionaisCest extends Common
{
    public function getCamposAdicionais(ApiTester $I)
    {
        
        //sleep(3);
        
        $I->sendGet('/campos_adicionais', ['pagina' => 1, 'registros' => 1]);
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
            'idcampovalor' => 'integer|null',
            'idcampo' => 'string|null',
            'campo_nome' => 'string|null',
            'idfuncionalidade' => 'string|null',
            'funcionalidade' => 'string|null',
            'valor' => 'string|null',
            'data_cad' => 'string|null',
            'data_sincronizacao' => 'string|null'
        ], '$.dados[0]');
        */

    }
}