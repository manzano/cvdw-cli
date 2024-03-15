<?php
namespace Tests\ApiCVDW;

use Tests\Support\ApiTester;
use Tests\ApiCVDW\Common;
use Codeception\Util\HttpCode;
use PHPUnit\Framework\Assert;

class PessoasFinanceirosCest extends Common
{
    public function getPessoasFinanceiros(ApiTester $I)
    {
        $I->sendGet('/pessoas/financeiros', ['pagina' => 1, 'registros' => 500]);
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
            'data_vigencia_aluguel_pj' => 'datetime|null'
        ], '$.dados[0]');

        //sleep(3);
        $I->wait(3);

    }
}