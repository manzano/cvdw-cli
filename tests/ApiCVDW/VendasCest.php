<?php
namespace Tests\ApiCVDW;

use Tests\Support\ApiTester;
use Tests\Helper\CvdwHelper;
use Tests\ApiCVDW\Common;
use Codeception\Util\HttpCode;
use PHPUnit\Framework\Assert;

class VendasCest extends Common
{
    public function getVendas(ApiTester $I)
    {
        
        //sleep(3);
        
        $I->sendGet('/vendas', ['pagina' => 1, 'registros' => 1]);
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
            'idreserva' => 'integer|null',
            'aprovada' => 'string|null',
            'valor_contrato' => 'integer|null',
            'contrato_interno' => 'string|null',
            'data' => 'string|null',
            'data_venda' => 'string|null',
            'cliente' => 'string|null',
            'idcliente' => 'integer|null',
            'documento_cliente' => 'string|null',
            'email' => 'string|null',
            'cidade' => 'string|null',
            'cep_cliente' => 'string|null',
            'renda' => 'integer|null',
            'sexo' => 'string|null',
            'idade' => 'integer|null',
            'estado_civil' => 'string|null',
            'idcorretor' => 'integer|null',
            'corretor' => 'string|null',
            'idimobiliaria' => 'integer|null',
            'imobiliaria' => 'string|null',
            'unidade' => 'string|null',
            'empreendimento' => 'string|null',
            'area_privativa' => 'integer|null',
            'idempreendimento' => 'integer|null',
            'codigointerno_empreendimento' => 'string|null',
            'bloco' => 'string|null',
            'regiao' => 'string|null',
            'planta' => 'string|null',
            'campanha' => 'string|null',
            'idmidia' => 'integer|null',
            'midia' => 'string|null',
            'idtabela' => 'integer|null',
            'idtipovenda' => 'integer|null',
            'tipovenda' => 'string|null',
            'nometabela' => 'string|null',
            'codigointernotabela' => 'string|null',
            'idlead' => 'integer|null'
        ], '$.dados[0]');
        */

    }
}