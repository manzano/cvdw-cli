<?php
namespace Tests\ApiCVDW;

use Tests\Support\ApiTester;
use Tests\ApiCVDW\Common;
use Codeception\Util\HttpCode;
use PHPUnit\Framework\Assert;

class RepassesCest extends Common
{
    public function getRepasses(ApiTester $I)
    {
        $I->sendGet('/repasses', ['pagina' => 1, 'registros' => 500]);
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
            'idrepasse' => 'integer|null',
            'idsituacao' => 'integer|null',
            'situacao' => 'string|null',
            'reserva' => 'integer|null',
            'idempreendimento' => 'integer|null',
            'codigointerno_empreendimento' => 'string|null',
            'empreendimento' => 'string|null',
            'etapa' => 'string|null',
            'bloco' => 'string|null',
            'unidade' => 'string|null',
            'regiao' => 'string|null',
            'idcliente' => 'integer|null',
            'documento_cliente' => 'string|null',
            'cliente' => 'string|null',
            'cep_cliente' => 'string|null',
            'parcela' => 'string|null',
            'idcontrato' => 'string|null',
            'contrato' => 'string|null',
            'valor_previsto' => 'number|null',
            'parcela_conclusao' => 'number|null',
            'saldo_devedor' => 'number|null',
            'valor_divida' => 'number|null',
            'valor_subsidio' => 'number|null',
            'valor_fgts' => 'number|null',
            'valor_financiado' => 'number|null',
            'numero_contrato' => 'string|null',
            'data_registro' => 'datetime|null',
            'correspondente' => 'string|null',
            'banco' => 'string|null',
            'agencia' => 'string|null',
            'data_alteracao_status' => 'datetime|null',
            'data_venda' => 'datetime|null',
            'data_contrato_contabilizado' => 'datetime|null',
            'data_assinatura_de_contrato' => 'datetime|null',
            'idlead' => 'string|null',
            'data_recurso_liberado' => 'datetime|null',
            'data_sincronizacao' => 'datetime|null',
            'data_cadastro' => 'datetime|null',
            'idunidade' => 'string|null',
            'data_modificacao' => 'datetime|null',
            'campos_adicionais' => 'array|null'
        ], '$.dados[0]');

        //sleep(3);
        $I->wait(3);

    }
}