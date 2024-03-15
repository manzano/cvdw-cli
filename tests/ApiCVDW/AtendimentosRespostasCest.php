<?php
namespace Tests\ApiCVDW;

use Tests\Support\ApiTester;
use Tests\ApiCVDW\Common;
use Codeception\Util\HttpCode;
use PHPUnit\Framework\Assert;

class AtendimentosRespostasCest extends Common
{
    public function getAtendimentosRespostas(ApiTester $I)
    {
        $I->sendGet('/atendimentos/respostas', ['pagina' => 1, 'registros' => 500]);
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
            'data_cad' => 'datetime|null',
            'resposta' => 'text|null',
            'publica' => 'string|null',
            'tempo_resposta' => 'integer|null',
            'origem' => 'string|null',
            'data_modificacao' => 'datetime|null'
        ], '$.dados[0]');

        //sleep(3);
        $I->wait(3);

    }
}