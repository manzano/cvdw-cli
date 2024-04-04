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
        
        sleep(2);

        $bodyContent = ['pagina' => 1, 'registros' => 1];
        $responseContent = [
            'pagina' => 'integer',
            'registros' => 'integer',
            'total_de_registros' => 'integer',
            'total_de_paginas' => 'integer',
            'dados' => 'array'
        ];

        $I->sendGet('/processos', $bodyContent);
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