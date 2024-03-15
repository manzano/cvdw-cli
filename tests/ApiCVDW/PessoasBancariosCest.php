<?php
namespace Tests\ApiCVDW;

use Tests\Support\ApiTester;
use Tests\ApiCVDW\Common;
use Codeception\Util\HttpCode;
use PHPUnit\Framework\Assert;

class PessoasBancariosCest extends Common
{
    public function getPessoasBancarios(ApiTester $I)
    {
        $I->sendGet('/pessoas/bancarios', ['pagina' => 1, 'registros' => 500]);
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

        //sleep(3);
        $I->wait(3);

    }
}