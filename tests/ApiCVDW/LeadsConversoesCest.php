<?php
namespace Tests\ApiCVDW;

use Tests\Support\ApiTester;
use Tests\Helper\CvdwHelper;
use Tests\ApiCVDW\Common;
use Codeception\Util\HttpCode;
use PHPUnit\Framework\Assert;

class LeadsConversoesCest extends Common
{
    public function getLeadsConversoes(ApiTester $I)
    {
        
        //sleep(3);
        
        $I->sendGet('/leads/conversoes', ['pagina' => 1, 'registros' => 1]);
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
            'idlead' => 'integer|null',
            'data_cad' => 'string|null',
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
        */

    }
}