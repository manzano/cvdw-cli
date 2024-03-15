<?php
namespace Tests\ApiCVDW;

use Tests\Support\ApiTester;
use Tests\ApiCVDW\Common;
use Codeception\Util\HttpCode;
use PHPUnit\Framework\Assert;

class ReservasSiengeCest extends Common
{
    public function getReservasSienge(ApiTester $I)
    {
        $I->sendGet('/reservas/sienge', ['pagina' => 1, 'registros' => 500]);
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

        // Estrutura de 'dados[0]'
        $I->seeResponseMatchesJsonType([
            'referencia' => 'integer|string',
            'referencia_data' => 'datetime',
            'idsienge_reserva' => 'integer|null',
            'idreserva' => 'integer|null',
            'previsao_entrega' => 'string|null',
            'nome_contrato' => 'string|null',
            'tipo_contrato' => 'string|null',
            'data_contrato' => 'datetime|null',
            'tipo_correcao' => 'string|null',
            'tipo_correcao_anual' => 'string|null',
            'mes_reajuste' => 'string|null',
            'corrigir_parcela_a_partir' => 'string|null',
            'gerar_residuo' => 'string|null',
            'diluir_valor_residuo' => 'string|null',
            'corrigir_parcela_a_cada' => 'integer|null',
            'tipo_juros' => 'string|null',
            'percentual_juros' => 'number|null',
            'plano_financeiro' => 'string|null',
            'indexador' => 'string|null',
            'codigo_corretor' => 'integer|null',
            'data_base' => 'datetime|null',
            'qtd_meses_carencia' => 'integer|null',
            'data_base_juros' => 'datetime|null',
            'data_cad' => 'datetime|null',
            'enviado' => 'string|null',
            'data_envio' => 'datetime|null',
            'ativo' => 'string|null',
            'coincidir_vencimentos' => 'string|null',
            'enviar_valor_com_comissao' => 'string|null',
            'percentual_multa_acrescimo_mora' => 'number|null',
            'calculo_acrescimo_mora_diario' => 'string|null',
            'valor_mora_diario' => 'number|null',
            'data_contabil' => 'datetime|null',
            'juros_mora_mensal' => 'number|null',
            'data_modificacao' => 'datetime|null'
        ], '$.dados[0]');

        //sleep(3);
        $I->wait(3);

    }
}