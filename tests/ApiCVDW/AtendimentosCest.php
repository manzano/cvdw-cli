<?php
namespace Tests\ApiCVDW;

use Tests\Support\ApiTester;
use Tests\Helper\CvdwHelper;
use Tests\ApiCVDW\Common;
use Codeception\Util\HttpCode;
use PHPUnit\Framework\Assert;

class AtendimentosCest extends Common
{
    public function getAtendimentos(ApiTester $I)
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

        $I->sendGet('/atendimentos', $bodyContent);

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
            'idatendimento' => 'integer|null',
            'protocolo' => 'string|null',
            'data_cad' => 'string|null',
            'prioridade' => 'string|null',
            'tempo_finalizado' => 'integer|null',
            'telefone_atendimento' => 'string|null',
            'nome_cliente' => 'string|null',
            'email_cliente' => 'string|null',
            'tempo_resposta' => 'integer|null',
            'encerrado_primeiro_contato' => 'string|null',
            'humor_cliente' => 'string|null',
            'idcanal' => 'integer|null',
            'idatendimento_vinculo' => 'string|null',
            'previsao_conclusao' => 'string|null',
            'idlead' => 'integer|null',
            'ativo_painel' => 'string|null',
            'avaliacao' => 'integer|null',
            'canal' => 'string|null',
            'idempreendimento' => 'integer|null',
            'codigointerno_empreendimento' => 'string|null',
            'empreendimento' => 'string|null',
            'etapa' => 'string|null',
            'bloco' => 'string|null',
            'unidade' => 'text|null',
            'idcliente' => 'integer|null',
            'documento_cliente' => 'string|null',
            'cliente' => 'string|null',
            'cep_cliente' => 'string|null',
            'regiao' => 'string|null',
            'situacao' => 'string|null',
            'idprotocolo' => 'integer|null',
            'assunto' => 'string|null',
            'subassunto' => 'string|null',
            'usuario' => 'string|null',
            'data_situacao' => 'string|null',
            'idclassificacao' => 'integer|null',
            'classificacao' => 'string|null',
            'idtipo' => 'integer|null',
            'tipo' => 'string|null',
            'imobiliaria' => 'string|null',
            'corretor' => 'string|null',
            'idsituacao' => 'integer|null',
            'idresponsavel' => 'integer|null',
            'responsavel' => 'string|null',
            'idunidade' => 'string|null',
            'idcorretor' => 'integer|null',
            'idimobiliaria' => 'string|null',
            'sigla_empreendimento' => 'string|null',
            'codigointerno_cliente' => 'string|null',
            'tags' => 'text|null',
            'quantidade_mensagens' => 'integer|null',
            'quantidade_interacoes' => 'integer|null',
            'sinalizador_juridico' => 'string|null',
            'times' => 'string|null',
            'origem' => 'string|null',
            'data_modificacao' => 'string|null'
        ], '$.dados[0]');
        */

    }

    public function getAtendimentosComDataReferencia(ApiTester $I)
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

        $I->sendGet('/atendimentos', $bodyContent);

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

    }    
}