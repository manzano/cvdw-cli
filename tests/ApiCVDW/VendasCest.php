<?php
namespace Tests\ApiCVDW;

use Tests\Support\ApiTester;
use Tests\ApiCVDW\Common;
use Codeception\Util\HttpCode;
use PHPUnit\Framework\Assert;

class VendasCest extends Common
{
    public function getVendas(ApiTester $I)
    {
        $I->sendGet('/vendas', ['pagina' => 1, 'registros' => 500]);
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
            'idlead' => 'text|null',
            'aprovada' => 'string|null',
            'data' => 'datetime|null',
            'data_venda' => 'datetime|null',
            'empreendimento' => 'string|null',
            'idempreendimento' => 'integer|null',
            'codigointerno_empreendimento' => 'string|null',
            'regiao' => 'string|null',
            'planta' => 'string|null',
            'bloco' => 'string|null',
            'unidade' => 'string|null',
            'area_privativa' => 'number|null',
            'cliente' => 'string|null',
            'idcliente' => 'integer|null',
            'documento_cliente' => 'string|null',
            'email' => 'string|null',
            'cidade' => 'string|null',
            'cep_cliente' => 'string|null',
            'renda' => 'number|null',
            'sexo' => 'string|null',
            'idade' => 'integer|null',
            'estado_civil' => 'string|null',
            'idcorretor' => 'integer|null',
            'corretor' => 'string|null',
            'idimobiliaria' => 'integer|null',
            'imobiliaria' => 'string|null',
            'campanha' => 'string|null',
            'valor_contrato' => 'number|null',
            'idmidia' => 'integer|null',
            'midia' => 'string|null',
            'idtabela' => 'string|null',
            'nometabela' => 'string|null',
            'codigointernotabela' => 'string|null',
            'idtipovenda' => 'integer|null',
            'tipovenda' => 'string|null'
        ], '$.dados[0]');

        //sleep(3);
        $I->wait(3);

    }
}