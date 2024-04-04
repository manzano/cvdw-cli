<?php
namespace Tests\ApiCVDW;

use Tests\Support\ApiTester;
use Tests\Helper\CvdwHelper;
use Tests\ApiCVDW\Common;
use Codeception\Util\HttpCode;
use PHPUnit\Framework\Assert;

class RepassesCest extends Common
{
    public function getRepasses(ApiTester $I)
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

        $I->sendGet('/repasses', $bodyContent);
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
            'idrepasse' => 'integer|null',
            'idsituacao' => 'integer|null',
            'situacao' => 'string|null',
            'reserva' => 'integer|null',
            'idempreendimento' => 'integer|null',
            'codigointerno_empreendimento' => 'string|null',
            'empreendimento' => 'string|null',
            'etapa' => 'string|null',
            'bloco' => 'string|null',
            'unidade' => 'string|null',
            'regiao' => 'string|null',
            'idcliente' => 'integer|null',
            'documento_cliente' => 'string|null',
            'cliente' => 'string|null',
            'cep_cliente' => 'string|null',
            'parcela' => 'string|null',
            'idcontrato' => 'string|null',
            'contrato' => 'string|null',
            'valor_previsto' => 'integer|null',
            'parcela_conclusao' => 'integer|null',
            'saldo_devedor' => 'integer|null',
            'valor_divida' => 'integer|null',
            'valor_subsidio' => 'integer|null',
            'valor_fgts' => 'integer|null',
            'valor_financiado' => 'integer|null',
            'numero_contrato' => 'string|null',
            'data_registro' => 'string|null',
            'correspondente' => 'string|null',
            'banco' => 'string|null',
            'agencia' => 'string|null',
            'data_alteracao_status' => 'string|null',
            'data_venda' => 'string|null',
            'data_contrato_contabilizado' => 'string|null',
            'data_assinatura_de_contrato' => 'string|null',
            'idlead' => 'string|null',
            'data_recurso_liberado' => 'string|null',
            'data_sincronizacao' => 'string|null',
            'data_cadastro' => 'string|null',
            'idunidade' => 'string|null',
            'data_modificacao' => 'string|null',
            'campos_adicionais' => 'array|null'
        ], '$.dados[0]');
        */

    }
}