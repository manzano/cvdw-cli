<?php
namespace Tests\ApiCVDW;

use Tests\Support\ApiTester;
use Tests\Helper\CvdwHelper;
use Tests\ApiCVDW\Common;
use Codeception\Util\HttpCode;
use PHPUnit\Framework\Assert;

class SimulacoesCest extends Common
{
    public function getSimulacoes(ApiTester $I)
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

        $I->sendGet('/simulacoes', $bodyContent);

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
            'idsimulacao' => 'integer|null',
            'data_cad' => 'string|null',
            'data_vencimento' => 'string|null',
            'data_email_venc_corretor' => 'string|null',
            'situacao_simulacao' => 'string|null',
            'idsituacao' => 'integer|null',
            'idusuario' => 'integer|null',
            'idusuario_imobiliaria' => 'integer|null',
            'corretor' => 'string|null',
            'idreserva' => 'integer|null',
            'idprecadastro' => 'integer|null',
            'imobiliaria' => 'string|null',
            'lead' => 'string|null',
            'empreendimento' => 'string|null',
            'etapa' => 'string|null',
            'bloco' => 'string|null',
            'unidade' => 'string|null',
            'idtabela' => 'integer|null',
            'data_alteracao_situacao' => 'string|null',
            'quantidade_mensagens' => 'integer|null',
            'solicitacao_aprovacao' => 'string|null',
            'condicao_aprovada' => 'string|null',
            'data_primeira_aprovacao' => 'string|null',
            'data_entrega' => 'string|null',
            'data_base_calculo_juros' => 'string|null',
            'descricao_motivo_reprovacao' => 'text|null',
            'idmotivo_cancelamento' => 'integer|null',
            'descricao_motivo_cancelamento' => 'text|null',
            'valor_contrato' => 'integer|null',
            'valor_presente' => 'integer|null',
            'juros_vpl' => 'integer|null',
            'juros_vpl_apos_entrega' => 'integer|null',
            'juros_futuro' => 'integer|null',
            'juros_vpl_fixa_adicional' => 'integer|null',
            'renda_familiar' => 'integer|null',
            'sla_vencimento' => 'string|null',
            'data_condicao_aprovada' => 'string|null',
            'idusuario_condicao_aprovada' => 'integer|null',
            'idtabela_condicao_aprovada' => 'integer|null',
            'juros_condicao_aprovada' => 'integer|null',
            'juros_apos_entrega_condicao_aprovada' => 'integer|null',
            'idlead' => 'integer|null',
            'aprovacao_vpl_valor' => 'integer|null',
            'aprovacao_vpl' => 'integer|null',
            'aprovacao_absoluto' => 'integer|null',
            'aprovacao_absoluto_percentual' => 'integer|null',
            'aprovacao_margem' => 'integer|null',
            'desconto' => 'integer|null',
            'idalcada' => 'integer|null',
            'percentual_custa_escrituracao' => 'integer|null',
            'juros' => 'string|null',
            'juros_embutido' => 'string|null',
            'quantidade_parcelas_min' => 'integer|null',
            'quantidade_parcelas_max' => 'integer|null',
            'data_base_juros_futuro_cadastro' => 'string|null',
            'data_base_calculo_pv' => 'string|null',
            'data_base_calculo_pv_alterada' => 'string|null',
            'data_contrato_aprovacao_comercial' => 'string|null',
            'usuario_preencheu_data_contrato' => 'string|null',
            'porcentagem_total_comissao' => 'integer|null',
            'pagamento_total_comissao' => 'integer|null',
            'porcentagem_total_premiacao' => 'integer|null',
            'pagamento_total_premiacao' => 'integer|null',
            'adimplencia_premiada' => 'integer|null',
            'ignorar_validacao_adimplencia_premiada' => 'string|null',
            'calculo_juros_price_baseado_em' => 'string|null',
            'porcentagem_total_contrato' => 'integer|null',
            'pagamento_total_contrato' => 'string|null',
            'porcentagem_total_fora_contrato' => 'integer|null',
            'pagamento_total_fora_contrato' => 'string|null',
            'idtipovenda' => 'integer|null',
            'hash' => 'string|null',
            'condicoes_pagamento' => 'array|null'
        ], '$.dados[0]');
        */

    }

    public function getSimulacoesComDataReferencia(ApiTester $I)
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

        $I->sendGet('/simulacoes', $bodyContent);

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