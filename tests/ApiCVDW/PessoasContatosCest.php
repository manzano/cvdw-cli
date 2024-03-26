<?php
namespace Tests\ApiCVDW;

use Tests\Support\ApiTester;
use Tests\Helper\CvdwHelper;
use Tests\ApiCVDW\Common;
use Codeception\Util\HttpCode;
use PHPUnit\Framework\Assert;

class PessoasContatosCest extends Common
{
    public function getPessoasContatos(ApiTester $I)
    {
        
        sleep(3);
        
        $I->sendGet('/pessoas/contatos', ['pagina' => 1, 'registros' => 1]);
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
            'idpessoa_int' => 'string|null',
            'idpessoa' => 'integer|null',
            'email' => 'string|null',
            'telefone' => 'string|null',
            'celular' => 'string|null',
            'referencia_nome' => 'string|null',
            'referencia_telefone' => 'string|null',
            'referencia_parentesco' => 'string|null',
            'cep_contato_relacionamento' => 'string|null',
            'endereco_contato_relacionamento' => 'string|null',
            'bairro_contato_relacionamento' => 'string|null',
            'numero_contato_relacionamento' => 'string|null',
            'complemento_contato_relacionamento' => 'string|null',
            'estado_contato_relacionamento' => 'string|null',
            'cidade_contato_relacionamento' => 'string|null',
            'pais_contato_relacionamento' => 'string|null',
            'telefone_contato_relacionamento' => 'string|null',
            'celular_contato_relacionamento' => 'string|null',
            'email_contato_relacionamento' => 'string|null',
            'nome_representante_pj' => 'string|null',
            'documento_representante_pj' => 'string|null',
            'cargo_pj' => 'string|null',
            'email_relacionamento_pj' => 'string|null',
            'telefone_relacionamento_pj' => 'string|null',
            'genero_representante' => 'string|null'
        ], '$.dados[0]');
        */

    }
}