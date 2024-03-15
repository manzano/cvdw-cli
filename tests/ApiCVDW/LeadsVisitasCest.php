<?php
namespace Tests\ApiCVDW;

use Tests\Support\ApiTester;
use Tests\ApiCVDW\Common;
use Codeception\Util\HttpCode;
use PHPUnit\Framework\Assert;

class LeadsVisitasCest extends Common
{
    public function getLeadsVisitas(ApiTester $I)
    {
        $I->sendGet('/leads/visitas', ['pagina' => 1, 'registros' => 500]);
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
            'idtarefa' => 'integer|null',
            'idinteracao' => 'integer|null',
            'idlead' => 'integer|null',
            'data_cad' => 'datetime|null',
            'data' => 'datetime|null',
            'idresponsavel' => 'integer|null',
            'responsavel' => 'string|null',
            'tipo_responsavel' => 'string|null',
            'situacao' => 'string|null',
            'tipo_interacao' => 'string|null',
            'idtipo_visita' => 'string|null',
            'nome_tipo_visita' => 'string|null',
            'funcionalidade' => 'string|null',
            'data_conclusao' => 'datetime|null',
            'pdv' => 'string|null',
            'visita_virtual' => 'string|null'
        ], '$.dados[0]');

        //sleep(3);
        $I->wait(3);

    }
}