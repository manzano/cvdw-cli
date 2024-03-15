<?php
namespace Tests\ApiCVDW;

use Tests\Support\ApiTester;
use Tests\ApiCVDW\Common;
use Codeception\Util\HttpCode;
use PHPUnit\Framework\Assert;

class UnidadesPrecosCest extends Common
{
    public function getUnidadesPrecos(ApiTester $I)
    {
        $I->sendGet('/unidades-precos', ['pagina' => 1, 'registros' => 500]);
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
            'idunidade' => 'integer|null',
            'unidade' => 'string|null',
            'empreendimento' => 'string|null',
            'idempreendimento' => 'integer|null',
            'etapa' => 'string|null',
            'idetapa' => 'integer|null',
            'bloco' => 'string|null',
            'idbloco' => 'integer|null',
            'valor' => 'string|null',
            'idtabela' => 'integer|null',
            'valor_avaliacao' => 'string|null'
        ], '$.dados[0]');

        //sleep(3);
        $I->wait(3);

    }
}