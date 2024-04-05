<?php
namespace Tests\ApiCVDW;

use Tests\Support\ApiTester;
use Tests\Helper\CvdwHelper;
use Tests\ApiCVDW\Common;
use Codeception\Util\HttpCode;
use PHPUnit\Framework\Assert;

class PessoasFinanceirosCest extends Common
{
    public function getPessoasFinanceiros(ApiTester $I)
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

        $I->sendGet('/pessoas/financeiros', $bodyContent);

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
            'idpessoa' => 'integer|null',
            'idpessoa_int' => 'string|null',
            'dados_financeiro_pj' => 'string|null',
            'valor_faturamento_mensal_pj' => 'string|null',
            'valor_lucro_liquido_mensal_pj' => 'string|null',
            'valor_contas_receber_pj' => 'string|null',
            'valor_estoque_pj' => 'string|null',
            'valor_instalacoes_pj' => 'string|null',
            'valor_moveis_pj' => 'string|null',
            'valor_emprestimos_pj' => 'string|null',
            'valor_inicial_pj' => 'string|null',
            'valor_capital_registrado_atual_pj' => 'string|null',
            'valor_capital_integralizado_pj' => 'string|null',
            'reservas_pj' => 'string|null',
            'valor_total_vendas_ultimo_exercicio_pj' => 'string|null',
            'porcentagem_a_vista_vendas_ultimo_exercicio_pj' => 'string|null',
            'medio_prazo_dias_vendas_ultimo_exercicio_pj' => 'string|null',
            'valor_media_mensal_vendas_pj' => 'string|null',
            'porcentagem_a_vista_media_mensal_vendas_pj' => 'string|null',
            'preco_medio_dias_media_mensal_vendas_pj' => 'string|null',
            'valor_media_mensal_compras_pj' => 'string|null',
            'porcentagem_a_vista_media_mensal_compras_pj' => 'string|null',
            'preco_medio_dias_media_mensal_compras_pj' => 'string|null',
            'principais_clientes_pj' => 'string|null',
            'concorrentes_diretos_pj' => 'string|null',
            'despesas_fixas_mensais_pj' => 'string|null',
            'predio_proprio_pj' => 'string|null',
            'valor_aluguel_mes_pj' => 'string|null',
            'data_vigencia_aluguel_pj' => 'string|null'
        ], '$.dados[0]');
        */

    }

    public function getPessoasFinanceirosComDataReferencia(ApiTester $I)
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

        $I->sendGet('/pessoas/financeiros', $bodyContent);

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
            'idpessoa' => 'integer|null',
            'idpessoa_int' => 'string|null',
            'dados_financeiro_pj' => 'string|null',
            'valor_faturamento_mensal_pj' => 'string|null',
            'valor_lucro_liquido_mensal_pj' => 'string|null',
            'valor_contas_receber_pj' => 'string|null',
            'valor_estoque_pj' => 'string|null',
            'valor_instalacoes_pj' => 'string|null',
            'valor_moveis_pj' => 'string|null',
            'valor_emprestimos_pj' => 'string|null',
            'valor_inicial_pj' => 'string|null',
            'valor_capital_registrado_atual_pj' => 'string|null',
            'valor_capital_integralizado_pj' => 'string|null',
            'reservas_pj' => 'string|null',
            'valor_total_vendas_ultimo_exercicio_pj' => 'string|null',
            'porcentagem_a_vista_vendas_ultimo_exercicio_pj' => 'string|null',
            'medio_prazo_dias_vendas_ultimo_exercicio_pj' => 'string|null',
            'valor_media_mensal_vendas_pj' => 'string|null',
            'porcentagem_a_vista_media_mensal_vendas_pj' => 'string|null',
            'preco_medio_dias_media_mensal_vendas_pj' => 'string|null',
            'valor_media_mensal_compras_pj' => 'string|null',
            'porcentagem_a_vista_media_mensal_compras_pj' => 'string|null',
            'preco_medio_dias_media_mensal_compras_pj' => 'string|null',
            'principais_clientes_pj' => 'string|null',
            'concorrentes_diretos_pj' => 'string|null',
            'despesas_fixas_mensais_pj' => 'string|null',
            'predio_proprio_pj' => 'string|null',
            'valor_aluguel_mes_pj' => 'string|null',
            'data_vigencia_aluguel_pj' => 'string|null'
        ], '$.dados[0]');
        */

    }    
}