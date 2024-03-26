<?php
namespace Tests\ApiCVDW;

use Tests\Support\ApiTester;
use Tests\Helper\CvdwHelper;
use Tests\ApiCVDW\Common;
use Codeception\Util\HttpCode;
use PHPUnit\Framework\Assert;

class LeadsVisitasCest extends Common
{
    public function getLeadsVisitas(ApiTester $I)
    {
        
        sleep(3);
        
        $I->sendGet('/leads/visitas', ['pagina' => 1, 'registros' => 1]);
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
            'idtarefa' => 'integer|null',
            'idinteracao' => 'integer|null',
            'idlead' => 'integer|null',
            'data_cad' => 'string|null',
            'data' => 'string|null',
            'idresponsavel' => 'integer|null',
            'responsavel' => 'string|null',
            'tipo_responsavel' => 'string|null',
            'situacao' => 'string|null',
            'tipo_interacao' => 'string|null',
            'idtipo_visita' => 'string|null',
            'nome_tipo_visita' => 'string|null',
            'funcionalidade' => 'string|null',
            'data_conclusao' => 'string|null',
            'pdv' => 'string|null',
            'visita_virtual' => 'string|null'
        ], '$.dados[0]');
        */

    }
}