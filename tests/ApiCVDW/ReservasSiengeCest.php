<?php
namespace Tests\ApiCVDW;

use Tests\Support\ApiTester;
use Tests\Helper\CvdwHelper;
use Tests\ApiCVDW\Common;
use Codeception\Util\HttpCode;
use PHPUnit\Framework\Assert;

class ReservasSiengeCest extends Common
{
    public function getReservasSienge(ApiTester $i)
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

        $i->sendGet('/reservas/sienge', $bodyContent);

        $endTime = time();
        $duration = $endTime - $startTime;

        if ($duration > 5) {
            // Adiciona um aviso se a requisição demorar mais de 5 segundos
            Assert::markTestIncomplete('A requisição demorou mais de 5 segundos.');
        }

        $i->seeResponseCodeIs(HttpCode::OK);
        $i->seeResponseIsJson();
        $i->seeResponseMatchesJsonType($responseContent);

        $primeiraLinhaDados = $i->grabDataFromResponseByJsonPath('$.dados[0]');
        codecept_debug("Referência do primeiro item: " . $primeiraLinhaDados[0]['referencia']);
        if(is_array($primeiraLinhaDados[0])){
            $referencia_data = $i->grabDataFromResponseByJsonPath('$.dados[0].referencia_data');
            codecept_debug("Data do primeiro item: " . $referencia_data[0]);
            $i->validarFormatoDaData($referencia_data[0], 'Y-m-d H:i:s');
        }
        // Estrutura de 'dados[0]'
        /*
        $i->seeResponseMatchesJsonType([
            'referencia' => 'string',
            'idsienge_reserva' => 'integer|null',
            'idreserva' => 'integer|null',
            'previsao_entrega' => 'string|null',
            'nome_contrato' => 'string|null',
            'tipo_contrato' => 'string|null',
            'data_contrato' => 'string|null',
            'tipo_correcao' => 'string|null',
            'tipo_correcao_anual' => 'string|null',
            'mes_reajuste' => 'string|null',
            'corrigir_parcela_a_partir' => 'string|null',
            'gerar_residuo' => 'string|null',
            'diluir_valor_residuo' => 'string|null',
            'corrigir_parcela_a_cada' => 'integer|null',
            'tipo_juros' => 'string|null',
            'percentual_juros' => 'integer|null',
            'plano_financeiro' => 'string|null',
            'indexador' => 'string|null',
            'codigo_corretor' => 'integer|null',
            'data_base' => 'string|null',
            'qtd_meses_carencia' => 'integer|null',
            'data_base_juros' => 'string|null',
            'data_cad' => 'string|null',
            'enviado' => 'string|null',
            'data_envio' => 'string|null',
            'ativo' => 'string|null',
            'coincidir_vencimentos' => 'string|null',
            'enviar_valor_com_comissao' => 'string|null',
            'percentual_multa_acrescimo_mora' => 'integer|null',
            'calculo_acrescimo_mora_diario' => 'string|null',
            'valor_mora_diario' => 'integer|null',
            'data_contabil' => 'string|null',
            'juros_mora_mensal' => 'integer|null',
            'data_modificacao' => 'string|null'
        ], '$.dados[0]');
        */

    }

    public function getReservasSiengeComDataReferencia(ApiTester $i)
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

        $i->sendGet('/reservas/sienge', $bodyContent);

        $endTime = time();
        $duration = $endTime - $startTime;

        if ($duration > 5) {
            // Adiciona um aviso se a requisição demorar mais de 5 segundos
            Assert::markTestIncomplete('A requisição demorou mais de 5 segundos.');
        }

        $i->seeResponseCodeIs(HttpCode::OK);
        $i->seeResponseIsJson();
        $i->seeResponseMatchesJsonType($responseContent);

        $primeiraLinhaDados = $i->grabDataFromResponseByJsonPath('$.dados[0]');
        if(is_array($primeiraLinhaDados[0])){
            $referencia_data = $i->grabDataFromResponseByJsonPath('$.dados[0].referencia_data');

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
            //$i->assertTrue($timestamp_referencia >= $timestamp_filtro);

        }

    }    
}