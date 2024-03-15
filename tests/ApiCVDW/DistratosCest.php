<?php
namespace Tests\ApiCVDW;

use Tests\Support\ApiTester;
use Tests\ApiCVDW\Common;
use Codeception\Util\HttpCode;
use PHPUnit\Framework\Assert;

class DistratosCest extends Common
{
    public function getDistratos(ApiTester $I)
    {
        $I->sendGet('/distratos', ['pagina' => 1, 'registros' => 500]);
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
            'idreserva' => 'integer|null',
            'aprovada' => 'string|null',
            'data' => 'datetime|null',
            'situacao_data' => 'datetime|null',
            'situacao_atual' => 'string|null',
            'idsituacao' => 'integer|null',
            'idempreendimento' => 'integer|null',
            'codigointerno_empreendimento' => 'string|null',
            'empreendimento' => 'string|null',
            'bloco' => 'string|null',
            'unidade' => 'string|null',
            'regiao' => 'string|null',
            'venda' => 'string|null',
            'idcliente' => 'integer|null',
            'documento' => 'string|null',
            'cliente' => 'string|null',
            'cep_cliente' => 'string|null',
            'idcorretor' => 'integer|null',
            'corretor' => 'string|null',
            'idimobiliaria' => 'integer|null',
            'imobiliaria' => 'string|null',
            'motivo_distrato' => 'string|null',
            'valor_contrato' => 'number|null',
            'data_sincronizacao' => 'datetime|null'
        ], '$.dados[0]');

        //sleep(3);
        $I->wait(3);

    }
}