<?php
namespace Tests\ApiCVDW;

use Tests\Support\ApiTester;
use Tests\ApiCVDW\Common;
use Codeception\Util\HttpCode;
use PHPUnit\Framework\Assert;

class AtendimentosTimesCest extends Common
{
    public function getAtendimentosTimes(ApiTester $I)
    {
        $I->sendGet('/atendimentos/times', ['pagina' => 1, 'registros' => 500]);
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
            'idtime' => 'integer|null',
            'idgestor' => 'integer|null',
            'gestor' => 'string|null',
            'nome' => 'string|null',
            'descricao' => 'string|null',
            'tempo_redistribuicao' => 'integer|null',
            'direcionar_para' => 'string|null',
            'ativo_painel' => 'string|null',
            'data_cad' => 'datetime|null',
            'data_modificacao' => 'datetime|null'
        ], '$.dados[0]');

        //sleep(3);
        $I->wait(3);

    }
}