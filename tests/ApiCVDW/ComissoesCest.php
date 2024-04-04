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
        
        sleep(2);

        $bodyContent = ['pagina' => 1, 'registros' => 1];
        $responseContent = [
            'pagina' => 'integer',
            'registros' => 'integer',
            'total_de_registros' => 'integer',
            'total_de_paginas' => 'integer',
            'dados' => 'array'
        ];

        $I->sendGet('/comissoes', $bodyContent);
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