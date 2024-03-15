<?php
namespace Tests\ApiCVDW;

use Tests\Support\ApiTester;
use Tests\ApiCVDW\Common;
use Codeception\Util\HttpCode;
use PHPUnit\Framework\Assert;

class ImobiliariasCest extends Common
{
    public function getImobiliarias(ApiTester $I)
    {
        $I->sendGet('/imobiliarias', ['pagina' => 1, 'registros' => 500]);
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
            'idimobiliaria' => 'integer|null',
            'ativo' => 'string|null',
            'data_cad' => 'datetime|null',
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

        //sleep(3);
        $I->wait(3);

    }
}