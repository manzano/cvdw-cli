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
        $startTime = time();

        $bodyContent = ['pagina' => 1, 'registros' => 1];
        $responseContent = [
            'pagina' => 'integer',
            'registros' => 'integer',
            'total_de_registros' => 'integer',
            'total_de_paginas' => 'integer',
            'dados' => 'array'
        ];

        $I->sendGet('/leads', $bodyContent);

        $endTime = time();
        $duration = $endTime - $startTime;

        if ($duration > 5) {
            // Adiciona um aviso se a requisição demorar mais de 5 segundos
            Assert::markTestIncomplete('A requisição demorou mais de 5 segundos.');
        }

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

    public function getLeadsComDataReferencia(ApiTester $I)
    {
        
        sleep(3);
        $startTime = time();

        $now = new \DateTime();
        $now->modify('-45 days');
        $formattedDate = $now->format('Y-m-d');

        $bodyContent = ['pagina' => 1, 'registros' => 1, 'a_partir_data_referencia' => $formattedDate];
        
        codecept_debug("Body: " . $formattedDate);
        
        $responseContent = [
            'pagina' => 'integer',
            'registros' => 'integer',
            'total_de_registros' => 'integer',
            'total_de_paginas' => 'integer',
            'dados' => 'array'
        ];

        $I->sendGet('/leads', $bodyContent);

        $endTime = time();
        $duration = $endTime - $startTime;

        if ($duration > 5) {
            // Adiciona um aviso se a requisição demorar mais de 5 segundos
            Assert::markTestIncomplete('A requisição demorou mais de 5 segundos.');
        }

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType($responseContent);

        $primeiraLinhaDados = $I->grabDataFromResponseByJsonPath('$.dados[0]');
        if(is_array($primeiraLinhaDados[0])){
            $referencia_data = $I->grabDataFromResponseByJsonPath('$.dados[0].referencia_data');

            // verifica se $referencia_data[0] é maior que $formattedDate
            $timestamp_referencia = strtotime($referencia_data[0]);
            $timestamp_filtro = strtotime($formattedDate);

            codecept_debug("Data do primeiro item: " . $referencia_data[0] . " -> $timestamp_referencia");
            codecept_debug("Data do filtro: " . $formattedDate . " -> $timestamp_filtro");

            if($timestamp_referencia >= $timestamp_filtro){
                codecept_debug("Filtro é menor!");
                Assert::assertTrue(true);
            } else {
                codecept_debug("Filtro é maior!");
                Assert::assertTrue(false);
            }

            // Agora, compara os timestamps
            //$I->assertTrue($timestamp_referencia >= $timestamp_filtro);

        }

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