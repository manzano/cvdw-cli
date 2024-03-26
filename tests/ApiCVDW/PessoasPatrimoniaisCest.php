<?php
namespace Tests\ApiCVDW;

use Tests\Support\ApiTester;
use Tests\Helper\CvdwHelper;
use Tests\ApiCVDW\Common;
use Codeception\Util\HttpCode;
use PHPUnit\Framework\Assert;

class PessoasPatrimoniaisCest extends Common
{
    public function getPessoasPatrimoniais(ApiTester $I)
    {
        
        sleep(3);
        
        $I->sendGet('/pessoas/patrimoniais', ['pagina' => 1, 'registros' => 1]);
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
            'idpessoa' => 'integer|null',
            'idpessoa_int' => 'string|null',
            'possui_bem' => 'string|null',
            'quantidade_imoveis_possui' => 'integer|null',
            'quais_bens' => 'string|null',
            'situacao_do_bem' => 'string|null',
            'valor_do_bem' => 'string|null',
            'novos_bens' => 'array|null'
        ], '$.dados[0]');
        */

    }
}