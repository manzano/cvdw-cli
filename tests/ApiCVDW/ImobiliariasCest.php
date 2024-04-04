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
        
        sleep(2);

        $bodyContent = ['pagina' => 1, 'registros' => 1];
        $responseContent = [
            'pagina' => 'integer',
            'registros' => 'integer',
            'total_de_registros' => 'integer',
            'total_de_paginas' => 'integer',
            'dados' => 'array'
        ];

        $I->sendGet('/imobiliarias', $bodyContent);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType($responseContent);

        $primeiraLinhaDados = $I->grabDataFromResponseByJsonPath('$.dados[0]');
        codecept_debug("Referência do primeiro item: " . $primeiraLinhaDados[0]['referencia']);
        if(is_array($primeiraLinhaDados[0])){
            $referencia_data = $I->grabDataFromResponseByJsonPath('$.dados[0].referencia_data');
            codecept_debug("Data do primeiro item: " . $referencia_data[0]);
            $I->validarFormatoDaData($referencia_data[0], 'Y-m-d H:i:s');
        }
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