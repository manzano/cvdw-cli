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
        
        sleep(2);

        $bodyContent = ['pagina' => 1, 'registros' => 1];
        $responseContent = [
            'pagina' => 'integer',
            'registros' => 'integer',
            'total_de_registros' => 'integer',
            'total_de_paginas' => 'integer',
            'dados' => 'array'
        ];

        $I->sendGet('/reservas/condicoes', $bodyContent);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType($responseContent);

        $primeiraLinhaDados = $I->grabDataFromResponseByJsonPath('$.dados[0]');
        codecept_debug("Referência do primeiro item: " . $primeiraLinhaDados[0]['referencia']);
        if(is_array($primeiraLinhaDados[0])){
            $referencia_data = $I->grabDataFromResponseByJsonPath('$.dados[0].referencia_data');
            codecept_debug("Data do primeiro item: " . $referencia_data[0]);
            $I->validarFormatoDaData($referencia_data[0], 'Y-m-d H:i:s');
        }
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