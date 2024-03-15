<?php
namespace Tests\ApiCVDW;

use Tests\Support\ApiTester;
use Tests\ApiCVDW\Common;
use Codeception\Util\HttpCode;
use PHPUnit\Framework\Assert;

class ComissoesCest extends Common
{
    public function getComissoes(ApiTester $I)
    {
        $I->sendGet('/comissoes', ['pagina' => 1, 'registros' => 500]);
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
            'numero' => 'integer|null',
            'situacao' => 'string|null',
            'idsituacao' => 'integer|null',
            'idreserva' => 'string|null',
            'corretor' => 'string|null',
            'imobiliaria' => 'string|null',
            'empreendimento' => 'string|null',
            'bloco' => 'string|null',
            'etapa' => 'string|null',
            'unidade' => 'string|null',
            'regiao' => 'string|null',
            'cliente' => 'string|null',
            'cep_cliente' => 'string|null',
            'valor_contrato' => 'string|null',
            'porcentagem_comissao' => 'number|null',
            'valor_comissao' => 'number|null',
            'valor_comissao_apagar' => 'number|null',
            'valor_pagamento' => 'number|null',
            'nota_fiscal' => 'string|null',
            'data_pagamento' => 'datetime|null',
            'data_cad' => 'datetime|null'
        ], '$.dados[0]');

        //sleep(3);
        $I->wait(3);

    }
}