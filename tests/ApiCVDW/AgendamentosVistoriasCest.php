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
        
        //sleep(3);
        
        $I->sendGet('/agendamentos/vistorias', ['pagina' => 1, 'registros' => 1]);
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