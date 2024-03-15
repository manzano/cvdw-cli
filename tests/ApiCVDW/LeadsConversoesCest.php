<?php
namespace Tests\ApiCVDW;

use Tests\Support\ApiTester;
use Tests\ApiCVDW\Common;
use Codeception\Util\HttpCode;
use PHPUnit\Framework\Assert;

class LeadsConversoesCest extends Common
{
    public function getLeadsConversoes(ApiTester $I)
    {
        $I->sendGet('/leads/conversoes', ['pagina' => 1, 'registros' => 500]);
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
            'idlead' => 'integer|null',
            'data_cad' => 'datetime|null',
            'nome' => 'string|null',
            'email' => 'string|null',
            'telefone' => 'string|null',
            'origem_conversao' => 'string|null',
            'conversao' => 'string|null',
            'origem' => 'string|null',
            'idorigem_ultimo' => 'integer|null',
            'origem_ultimo' => 'string|null',
            'midia' => 'string|null',
            'midia_conversao' => 'string|null',
            'gestor' => 'integer|null',
            'gestor_ultimo' => 'integer|null',
            'empreendimento_ultimo' => 'integer|null'
        ], '$.dados[0]');

        //sleep(3);
        $I->wait(3);

    }
}