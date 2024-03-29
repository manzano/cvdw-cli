<?php
namespace Tests\ApiCVDW;

use Tests\Support\ApiTester;
use Tests\Helper\CvdwHelper;
use Tests\ApiCVDW\Common;
use Codeception\Util\HttpCode;
use PHPUnit\Framework\Assert;

class LeadsCest extends Common
{
    public function getLeads(ApiTester $I)
    {
        
        sleep(3);
        
        $I->sendGet('/leads', ['pagina' => 1, 'registros' => 1]);
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
            'idlead' => 'integer|null',
            'idsituacao' => 'integer|null',
            'situacao' => 'string|null',
            'data_cad' => 'string|null',
            'nome' => 'string|null',
            'email' => 'string|null',
            'telefone' => 'string|null',
            'documento_cliente' => 'string|null',
            'cep_cliente' => 'string|null',
            'idponto_venda' => 'integer|null',
            'ponto_venda' => 'string|null',
            'conversao_original' => 'string|null',
            'conversao_ultimo' => 'string|null',
            'conversao' => 'string|null',
            'idempreendimento' => 'string|null',
            'codigointerno_empreendimento' => 'string|null',
            'empreendimento' => 'text|null',
            'idempreendimento_primeiro' => 'integer|null',
            'empreendimento_primeiro' => 'string|null',
            'idempreendimento_ultimo' => 'integer|null',
            'empreendimento_ultimo' => 'string|null',
            'idmotivo' => 'integer|null',
            'motivo' => 'string|null',
            'reserva' => 'integer|null',
            'idgestor' => 'integer|null',
            'gestor' => 'string|null',
            'idcorretor' => 'integer|null',
            'corretor' => 'string|null',
            'idimobiliaria' => 'integer|null',
            'imobiliaria' => 'string|null',
            'caracteristicas' => 'string|null',
            'feedback' => 'string|null',
            'idorigem' => 'integer|null',
            'origem' => 'string|null',
            'idorigem_ultimo' => 'integer|null',
            'origem_ultimo' => 'string|null',
            'midia_original' => 'string|null',
            'midia_ultimo' => 'string|null',
            'renda_familiar' => 'integer|null',
            'motivo_cancelamento' => 'string|null',
            'data_cancelamento' => 'string|null',
            'data_sincronizacao' => 'string|null',
            'data_ultima_interacao' => 'string|null',
            'cidade' => 'string|null',
            'estado' => 'string|null',
            'regiao' => 'string|null',
            'ultima_data_conversao' => 'string|null',
            'data_reativacao' => 'string|null',
            'idsituacao_anterior' => 'integer|null',
            'nome_situacao_anterior_lead' => 'string|null',
            'tags' => 'text|null',
            'descricao_motivo_cancelamento' => 'text|null',
            'possibilidade_venda' => 'integer|null',
            'inserido_bolsao' => 'string|null',
            'data_primeira_interacao_gestor' => 'string|null',
            'data_primeira_interacao_corretor' => 'string|null',
            'score' => 'integer|null',
            'idgestor_ultimo' => 'integer|null',
            'gestor_ultimo' => 'string|null',
            'idcorretor_ultimo' => 'integer|null',
            'corretor_ultimo' => 'string|null',
            'idcorretor_penultimo' => 'integer|null',
            'corretor_penultimo' => 'string|null',
            'nome_momento_lead' => 'string|null',
            'novo' => 'string|null',
            'retorno' => 'string|null',
            'data_ultima_alteracao' => 'string|null'
        ], '$.dados[0]');
        */

    }
}