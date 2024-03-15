<?php
namespace Tests\ApiCVDW;

use Tests\Support\ApiTester;
use Tests\Helper\CvdwHelper;
use Tests\ApiCVDW\Common;
use Codeception\Util\HttpCode;
use PHPUnit\Framework\Assert;

class CorretoresCest extends Common
{
    public function getCorretores(ApiTester $I)
    {
        
        //sleep(3);
        
        $I->sendGet('/corretores', ['pagina' => 1, 'registros' => 1]);
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
            'idcorretor' => 'integer|null',
            'documento' => 'string|null',
            'nome' => 'string|null',
            'sexo' => 'string|null',
            'ativo_login' => 'string|null',
            'data_cad' => 'string|null',
            'estado_civil' => 'string|null',
            'data_nasc' => 'string|null',
            'telefone' => 'string|null',
            'celular' => 'string|null',
            'rg' => 'string|null',
            'rg_orgao_expedidor' => 'string|null',
            'numero_pis' => 'string|null',
            'naturalidade' => 'string|null',
            'pais' => 'string|null',
            'possui_filhos' => 'string|null'
        ], '$.dados[0]');
        */

    }
}