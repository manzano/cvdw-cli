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
        
        sleep(2);

        $bodyContent = ['pagina' => 1, 'registros' => 1];
        $responseContent = [
            'pagina' => 'integer',
            'registros' => 'integer',
            'total_de_registros' => 'integer',
            'total_de_paginas' => 'integer',
            'dados' => 'array'
        ];

        $I->sendGet('/corretores', $bodyContent);
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