<?php
namespace Tests\ApiCVDW;

use Tests\Support\ApiTester;
use Tests\Helper\CvdwHelper;
use Tests\ApiCVDW\Common;
use Codeception\Util\HttpCode;
use PHPUnit\Framework\Assert;

class RepassesHistoricoSituacoesCest extends Common
{
    public function getRepassesHistoricoSituacoes(ApiTester $I)
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

        $I->sendGet('/repasses/historico/situacoes', $bodyContent);
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
            'idhistorico' => 'integer|null',
            'idrepasse' => 'integer|null',
            'idusuario' => 'integer|null',
            'idcorrespondente' => 'integer|null',
            'usuario' => 'string|null',
            'correspondente' => 'string|null',
            'data_cad' => 'string|null',
            'de' => 'integer|null',
            'para' => 'integer|null',
            'de_nome' => 'string|null',
            'para_nome' => 'string|null'
        ], '$.dados[0]');
        */

    }
}