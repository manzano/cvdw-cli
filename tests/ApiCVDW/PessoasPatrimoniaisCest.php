<?php
namespace Tests\ApiCVDW;

use Tests\Support\ApiTester;
use Tests\ApiCVDW\Common;
use Codeception\Util\HttpCode;
use PHPUnit\Framework\Assert;

class PessoasPatrimoniaisCest extends Common
{
    public function getPessoasPatrimoniais(ApiTester $I)
    {
        $I->sendGet('/pessoas/patrimoniais', ['pagina' => 1, 'registros' => 500]);
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
            'idpessoa' => 'integer|null',
            'idpessoa_int' => 'string|null',
            'possui_bem' => 'string|null',
            'quantidade_imoveis_possui' => 'integer|null',
            'quais_bens' => 'string|null',
            'situacao_do_bem' => 'string|null',
            'valor_do_bem' => 'string|null',
            'novos_bens' => 'array|null'
        ], '$.dados[0]');

        //sleep(3);
        $I->wait(3);

    }
}