<?php
namespace Tests\ApiCVDW;

use Tests\Support\ApiTester;
use Tests\Helper\CvdwHelper;
use Tests\ApiCVDW\Common;
use Codeception\Util\HttpCode;
use PHPUnit\Framework\Assert;

class ImobiliariasCest extends Common
{
    public function getImobiliarias(ApiTester $I)
    {
        
        sleep(3);
        
        $I->sendGet('/imobiliarias', ['pagina' => 1, 'registros' => 1]);
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
            'idimobiliaria' => 'integer|null',
            'ativo' => 'string|null',
            'data_cad' => 'string|null',
            'idestado' => 'integer|null',
            'idcidade' => 'integer|null',
            'nome' => 'string|null',
            'razao_social' => 'string|null',
            'cnpj' => 'string|null',
            'cnpj_faturamento' => 'string|null',
            'idlogradouro' => 'integer|null',
            'endereco' => 'string|null',
            'complemento' => 'string|null',
            'numero' => 'string|null',
            'bairro' => 'string|null',
            'cep' => 'string|null',
            'telefone' => 'string|null',
            'celular' => 'string|null',
            'email' => 'string|null',
            'creci' => 'string|null',
            'avatar_nome' => 'string|null',
            'validade_creci' => 'string|null',
            'gerente_nome' => 'string|null',
            'gerente_cpf' => 'string|null',
            'gerente_telefone' => 'string|null',
            'gerente_celular' => 'string|null',
            'gerente_email' => 'string|null',
            'sigla' => 'string|null',
            'codigointerno' => 'string|null',
            'observacoes' => 'string|null'
        ], '$.dados[0]');
        */

    }
}