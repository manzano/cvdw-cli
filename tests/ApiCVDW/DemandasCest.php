<?php
namespace Tests\ApiCVDW;

use Tests\Support\ApiTester;
use Tests\Helper\CvdwHelper;
use Tests\ApiCVDW\Common;
use Codeception\Util\HttpCode;
use PHPUnit\Framework\Assert;

class DemandasCest extends Common
{
    public function getDemandas(ApiTester $I)
    {
        
        //sleep(3);
        
        $I->sendGet('/demandas', ['pagina' => 1, 'registros' => 1]);
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
            'iddemanda' => 'integer|null',
            'demanda' => 'string|null',
            'data_cad' => 'string|null',
            'data_encerramento' => 'string|null',
            'data_conclusao' => 'string|null',
            'agencia' => 'string|null',
            'data_situacao_finalizada' => 'string|null',
            'situacao_demanda' => 'string|null',
            'empreendimento' => 'string|null',
            'data_ult_situacao' => 'string|null'
        ], '$.dados[0]');
        */

    }
}