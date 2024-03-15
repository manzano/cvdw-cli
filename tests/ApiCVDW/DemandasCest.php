<?php
namespace Tests\ApiCVDW;

use Tests\Support\ApiTester;
use Tests\ApiCVDW\Common;
use Codeception\Util\HttpCode;
use PHPUnit\Framework\Assert;

class DemandasCest extends Common
{
    public function getDemandas(ApiTester $I)
    {
        $I->sendGet('/demandas', ['pagina' => 1, 'registros' => 500]);
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

        // Estrutura de 'dados[0]'
        $I->seeResponseMatchesJsonType([
            'referencia' => 'integer|string',
            'referencia_data' => 'datetime',
            'iddemanda' => 'integer|null',
            'demanda' => 'string|null',
            'data_cad' => 'datetime|null',
            'data_encerramento' => 'datetime|null',
            'data_conclusao' => 'datetime|null',
            'agencia' => 'string|null',
            'data_situacao_finalizada' => 'datetime|null',
            'situacao_demanda' => 'string|null',
            'empreendimento' => 'string|null',
            'data_ult_situacao' => 'datetime|null'
        ], '$.dados[0]');

        //sleep(3);
        $I->wait(3);

    }
}