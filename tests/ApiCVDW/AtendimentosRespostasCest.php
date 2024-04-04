<?php
namespace Tests\ApiCVDW;

use Tests\Support\ApiTester;
use Tests\Helper\CvdwHelper;
use Tests\ApiCVDW\Common;
use Codeception\Util\HttpCode;
use PHPUnit\Framework\Assert;

class AtendimentosRespostasCest extends Common
{
    public function getAtendimentosRespostas(ApiTester $I)
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

        $I->sendGet('/atendimentos/respostas', $bodyContent);
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
            'idresposta' => 'integer|null',
            'idatendimento' => 'integer|null',
            'idusuario' => 'integer|null',
            'usuario' => 'string|null',
            'idpessoa' => 'integer|null',
            'pessoa' => 'string|null',
            'idusuario_imobiliaria' => 'integer|null',
            'usuario_imobiliaria' => 'string|null',
            'idcorretor' => 'integer|null',
            'corretor' => 'string|null',
            'idresposta_automatica' => 'integer|null',
            'ativo' => 'string|null',
            'data_cad' => 'string|null',
            'resposta' => 'text|null',
            'publica' => 'string|null',
            'tempo_resposta' => 'integer|null',
            'origem' => 'string|null',
            'data_modificacao' => 'string|null'
        ], '$.dados[0]');
        */

    }
}