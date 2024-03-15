<?php
namespace Tests\ApiCVDW;

use Tests\Support\ApiTester;
use Tests\Helper\CvdwHelper;
use Tests\ApiCVDW\Common;
use Codeception\Util\HttpCode;
use PHPUnit\Framework\Assert;

class PessoasBancariosCest extends Common
{
    public function getPessoasBancarios(ApiTester $I)
    {
        
        //sleep(3);
        
        $I->sendGet('/pessoas/bancarios', ['pagina' => 1, 'registros' => 1]);
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
            'idpessoa' => 'string|null',
            'idpessoa_int' => 'string|null',
            'banco' => 'string|null',
            'banco_nome' => 'string|null',
            'banco_agencia' => 'string|null',
            'banco_conta' => 'string|null',
            'banco_nome_titular' => 'string|null',
            'banco_tipo_doc' => 'string|null',
            'banco_cpf_titular' => 'string|null',
            'banco_cnpj_titular' => 'string|null',
            'banco_chave_pix' => 'string|null',
            'banco_observacoes' => 'string|null'
        ], '$.dados[0]');
        */

    }
}