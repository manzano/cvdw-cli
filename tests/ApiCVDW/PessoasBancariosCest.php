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
        
        sleep(2);

        $bodyContent = ['pagina' => 1, 'registros' => 1];
        $responseContent = [
            'pagina' => 'integer',
            'registros' => 'integer',
            'total_de_registros' => 'integer',
            'total_de_paginas' => 'integer',
            'dados' => 'array'
        ];

        $I->sendGet('/pessoas/bancarios', $bodyContent);
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