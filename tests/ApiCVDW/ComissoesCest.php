<?php
namespace Tests\ApiCVDW;

use Tests\Support\ApiTester;
use Tests\Helper\CvdwHelper;
use Tests\ApiCVDW\Common;
use Codeception\Util\HttpCode;
use PHPUnit\Framework\Assert;

class ComissoesCest extends Common
{
    public function getComissoes(ApiTester $I)
    {
        
        sleep(3);
        
        $I->sendGet('/comissoes', ['pagina' => 1, 'registros' => 1]);
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
            'porcentagem_comissao' => 'integer|null',
            'valor_comissao' => 'integer|null',
            'valor_comissao_apagar' => 'integer|null',
            'valor_pagamento' => 'integer|null',
            'nota_fiscal' => 'string|null',
            'data_pagamento' => 'string|null',
            'data_cad' => 'string|null'
        ], '$.dados[0]');
        */

    }
}