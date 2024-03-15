<?php
namespace Tests\ApiCVDW;

use Tests\Support\ApiTester;
use Tests\ApiCVDW\Common;
use Codeception\Util\HttpCode;
use PHPUnit\Framework\Assert;

class ReservasCondicoesCest extends Common
{
    public function getReservasCondicoes(ApiTester $I)
    {
        $I->sendGet('/reservas/condicoes', ['pagina' => 1, 'registros' => 500]);
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
            'idreservascondicoes' => 'integer|null',
            'idreserva' => 'integer|null',
            'serie' => 'string|null',
            'parcela_quantidade' => 'integer|null',
            'valor' => 'number|null',
            'valor_com_juros' => 'number|null',
            'valor_com_comissao_fora_do_contrato' => 'number|null',
            'valor_sem_comissao' => 'number|null',
            'vencimento' => 'string|null',
            'portador' => 'string|null',
            'indexador' => 'string|null',
            'data_reservas_condicoes' => 'datetime|null'
        ], '$.dados[0]');

        //sleep(3);
        $I->wait(3);

    }
}