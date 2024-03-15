<?php
namespace Tests\ApiCVDW;

use Tests\Support\ApiTester;
use Tests\ApiCVDW\Common;
use Codeception\Util\HttpCode;
use PHPUnit\Framework\Assert;

class AssistenciasItensCest extends Common
{
    public function getAssistenciasItens(ApiTester $I)
    {
        $I->sendGet('/assistencias/itens', ['pagina' => 1, 'registros' => 500]);
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
            'referencia_data' => 'string|datetime',
            'idassistencia_item' => 'integer|null',
            'idassistencia' => 'integer|null',
            'item' => 'string|null',
            'data_cad' => 'datetime|null',
            'descricao' => 'text|null',
            'idsituacao' => 'integer|null',
            'situacao' => 'string|null',
            'data_conclusao' => 'datetime|null',
            'data_previsao' => 'datetime|null',
            'horas_servico' => 'integer|null',
            'ativo' => 'string|null',
            'data_sincronizacao' => 'datetime|null'
        ], '$.dados[0]');

        //sleep(3);
        $I->wait(3);

    }
}