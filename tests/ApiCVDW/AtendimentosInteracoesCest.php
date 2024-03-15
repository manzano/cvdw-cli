<?php
namespace Tests\ApiCVDW;

use Tests\Support\ApiTester;
use Tests\ApiCVDW\Common;
use Codeception\Util\HttpCode;
use PHPUnit\Framework\Assert;

class AtendimentosInteracoesCest extends Common
{
    public function getAtendimentosInteracoes(ApiTester $I)
    {
        $I->sendGet('/atendimentos/interacoes', ['pagina' => 1, 'registros' => 500]);
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
            'idinteracao' => 'integer|null',
            'idatendimento' => 'integer|null',
            'idusuario' => 'integer|null',
            'usuario' => 'string|null',
            'data_cad' => 'datetime|null',
            'ativo' => 'string|null',
            'descricao' => 'text|null',
            'idcanal' => 'integer|null',
            'canal' => 'string|null',
            'data_modificacao' => 'datetime|null'
        ], '$.dados[0]');

        //sleep(3);
        $I->wait(3);

    }
}