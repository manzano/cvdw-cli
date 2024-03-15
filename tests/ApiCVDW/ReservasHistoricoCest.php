<?php
namespace Tests\ApiCVDW;

use Tests\Support\ApiTester;
use Tests\ApiCVDW\Common;
use Codeception\Util\HttpCode;
use PHPUnit\Framework\Assert;

class ReservasHistoricoCest extends Common
{
    public function getReservasHistorico(ApiTester $I)
    {
        $I->sendGet('/reservas/historico', ['pagina' => 1, 'registros' => 500]);
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
            'idhistorico' => 'integer|null',
            'idreserva' => 'integer|null',
            'data_cad' => 'datetime|null',
            'usuario' => 'string|null',
            'tipo' => 'string|null',
            'acao' => 'string|null',
            'de' => 'text|null',
            'para' => 'text|null',
            'situacao_atual' => 'string|null'
        ], '$.dados[0]');

        //sleep(3);
        $I->wait(3);

    }
}