<?php
namespace Tests\ApiCVDW;

use Tests\Support\ApiTester;
use Tests\Helper\CvdwHelper;
use Tests\ApiCVDW\Common;
use Codeception\Util\HttpCode;
use PHPUnit\Framework\Assert;

class ProcessosCest extends Common
{
    public function getProcessos(ApiTester $I)
    {
        
        sleep(3);
        
        $I->sendGet('/processos', ['pagina' => 1, 'registros' => 1]);
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
            'idprocesso' => 'integer|null',
            'tipo_processo' => 'string|null',
            'idempreendimento_avulso' => 'integer|null',
            'nome_empreendimento_avulso' => 'string|null',
            'numero' => 'string|null',
            'valor_causa' => 'string|null',
            'valor_pago' => 'string|null',
            'forma_pagamento' => 'string|null',
            'data_atualizacao_processos' => 'string|null',
            'data_cad' => 'string|null',
            'data_pagamento' => 'string|null',
            'data_citacao' => 'string|null',
            'idempreendimento' => 'integer|null',
            'codigointerno_empreendimento' => 'string|null',
            'empreendimento' => 'string|null',
            'idcliente' => 'integer|null',
            'documento_cliente' => 'string|null',
            'cliente' => 'string|null',
            'cep_cliente' => 'string|null',
            'idsituacao' => 'integer|null',
            'situacao' => 'string|null',
            'unidade' => 'string|null',
            'etapa' => 'string|null',
            'bloco' => 'string|null',
            'idcausa' => 'integer|null',
            'causa' => 'string|null',
            'data_ult_hist' => 'string|null'
        ], '$.dados[0]');
        */

    }
}