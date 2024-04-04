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
        
        sleep(2);

        $bodyContent = ['pagina' => 1, 'registros' => 1];
        $responseContent = [
            'pagina' => 'integer',
            'registros' => 'integer',
            'total_de_registros' => 'integer',
            'total_de_paginas' => 'integer',
            'dados' => 'array'
        ];

        $I->sendGet('/leads/visitas', $bodyContent);
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