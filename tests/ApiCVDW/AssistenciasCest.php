<?php
namespace Tests\ApiCVDW;

use Tests\Support\ApiTester;
use Tests\ApiCVDW\Common;
use Codeception\Util\HttpCode;
use PHPUnit\Framework\Assert;

class AssistenciasCest extends Common
{
    public function getAssistencias(ApiTester $I)
    {
        $I->sendGet('/assistencias', ['pagina' => 1, 'registros' => 500]);
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
            'idassistencia' => 'integer|null',
            'idcliente' => 'integer|null',
            'documento_cliente' => 'string|null',
            'cliente' => 'string|null',
            'cep_cliente' => 'string|null',
            'idempreendimento' => 'integer|null',
            'codigointerno_empreendimento' => 'string|null',
            'empreendimento' => 'string|null',
            'etapa' => 'string|null',
            'bloco' => 'string|null',
            'unidade' => 'string|null',
            'ativo' => 'string|null',
            'data_cad' => 'datetime|null',
            'idsituacao' => 'integer|null',
            'situacao' => 'string|null',
            'unidade_manual' => 'string|null',
            'bloco_manual' => 'string|null',
            'empreendimento_manual' => 'string|null',
            'data_prevista_termino' => 'datetime|null',
            'data_conclusao' => 'datetime|null',
            'recorrente' => 'string|null',
            'total_horas' => 'integer|null',
            'custo_previsto' => 'number|null',
            'idatendimento' => 'integer|null',
            'empreendimento_localidade' => 'string|null',
            'unidade_area' => 'string|null',
            'idlocalidade' => 'integer|null',
            'localidade' => 'string|null',
            'descricao_localidade' => 'text|null',
            'idarea' => 'integer|null',
            'area' => 'string|null',
            'descricao_area' => 'text|null',
            'prioridade' => 'string|null',
            'ultima_atualizacao_situacao' => 'string|null',
            'data_modificacao' => 'datetime|null'
        ], '$.dados[0]');

        //sleep(3);
        $I->wait(3);

    }
}