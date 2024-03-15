<?php
namespace Tests\ApiCVDW;

use Tests\Support\ApiTester;
use Tests\Helper\CvdwHelper;
use Tests\ApiCVDW\Common;
use Codeception\Util\HttpCode;
use PHPUnit\Framework\Assert;

class ReservasSiengeCest extends Common
{
    public function getReservasSienge(ApiTester $I)
    {
        
        //sleep(3);
        
        $I->sendGet('/reservas/sienge', ['pagina' => 1, 'registros' => 1]);
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
}