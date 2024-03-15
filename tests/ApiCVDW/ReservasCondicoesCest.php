<?php
namespace Tests\ApiCVDW;

use Tests\Support\ApiTester;
use Tests\Helper\CvdwHelper;
use Tests\ApiCVDW\Common;
use Codeception\Util\HttpCode;
use PHPUnit\Framework\Assert;

class ReservasCondicoesCest extends Common
{
    public function getReservasCondicoes(ApiTester $I)
    {
        
        //sleep(3);
        
        $I->sendGet('/reservas/condicoes', ['pagina' => 1, 'registros' => 1]);
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
            'idreservascondicoes' => 'integer|null',
            'idreserva' => 'integer|null',
            'serie' => 'string|null',
            'parcela_quantidade' => 'integer|null',
            'valor' => 'integer|null',
            'valor_com_juros' => 'integer|null',
            'valor_com_comissao_fora_do_contrato' => 'integer|null',
            'valor_sem_comissao' => 'integer|null',
            'vencimento' => 'string|null',
            'portador' => 'string|null',
            'indexador' => 'string|null',
            'data_reservas_condicoes' => 'string|null'
        ], '$.dados[0]');
        */

    }
}