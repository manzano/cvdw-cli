<?php
namespace Tests\ApiCVDW;

use Tests\Support\ApiTester;
use Tests\Helper\CvdwHelper;
use Tests\ApiCVDW\Common;
use Codeception\Util\HttpCode;
use PHPUnit\Framework\Assert;

class AgendamentosVistoriasCest extends Common
{
    public function getAgendamentosVistorias(ApiTester $I)
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

        $I->sendGet('/agendamentos/vistorias', $bodyContent);
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
            'idvistoria' => 'integer|null',
            'idvistoria_pai' => 'integer|null',
            'idempreendimento' => 'integer|null',
            'empreendimento' => 'string|null',
            'codigointerno_empreendimento' => 'string|null',
            'etapa' => 'string|null',
            'bloco' => 'string|null',
            'unidade' => 'string|null',
            'idunidade' => 'string|null',
            'cliente' => 'string|null',
            'idcliente' => 'integer|null',
            'cep_cliente' => 'string|null',
            'data_agendamento' => 'string|null',
            'horario' => 'string|null',
            'vistoriador' => 'string|null',
            'tipo' => 'string|null',
            'situacao' => 'string|null',
            'quitado' => 'string|null',
            'chave_liberada' => 'string|null',
            'chave_entregue' => 'string|null',
            'idreserva' => 'integer|null',
            'data_modificacao' => 'string|null'
        ], '$.dados[0]');
        */

    }
}