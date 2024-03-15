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
        
        //sleep(3);
        
        $I->sendGet('/atendimentos/respostas', ['pagina' => 1, 'registros' => 1]);
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