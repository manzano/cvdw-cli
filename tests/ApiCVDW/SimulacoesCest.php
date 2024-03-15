<?php
namespace Tests\ApiCVDW;

use Tests\Support\ApiTester;
use Tests\ApiCVDW\Common;
use Codeception\Util\HttpCode;
use PHPUnit\Framework\Assert;

class SimulacoesCest extends Common
{
    public function getSimulacoes(ApiTester $I)
    {
        $I->sendGet('/simulacoes', ['pagina' => 1, 'registros' => 500]);
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
            'idsimulacao' => 'integer|null',
            'data_cad' => 'datetime|null',
            'data_vencimento' => 'datetime|null',
            'data_email_venc_corretor' => 'datetime|null',
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
            'data_alteracao_situacao' => 'datetime|null',
            'quantidade_mensagens' => 'integer|null',
            'solicitacao_aprovacao' => 'string|null',
            'condicao_aprovada' => 'string|null',
            'data_primeira_aprovacao' => 'datetime|null',
            'data_entrega' => 'datetime|null',
            'data_base_calculo_juros' => 'datetime|null',
            'descricao_motivo_reprovacao' => 'text|null',
            'idmotivo_cancelamento' => 'integer|null',
            'descricao_motivo_cancelamento' => 'text|null',
            'valor_contrato' => 'number|null',
            'valor_presente' => 'number|null',
            'juros_vpl' => 'number|null',
            'juros_vpl_apos_entrega' => 'number|null',
            'juros_futuro' => 'number|null',
            'juros_vpl_fixa_adicional' => 'number|null',
            'renda_familiar' => 'number|null',
            'sla_vencimento' => 'datetime|null',
            'data_condicao_aprovada' => 'datetime|null',
            'idusuario_condicao_aprovada' => 'integer|null',
            'idtabela_condicao_aprovada' => 'integer|null',
            'juros_condicao_aprovada' => 'number|null',
            'juros_apos_entrega_condicao_aprovada' => 'number|null',
            'idlead' => 'integer|null',
            'aprovacao_vpl_valor' => 'number|null',
            'aprovacao_vpl' => 'number|null',
            'aprovacao_absoluto' => 'number|null',
            'aprovacao_absoluto_percentual' => 'number|null',
            'aprovacao_margem' => 'number|null',
            'desconto' => 'number|null',
            'idalcada' => 'integer|null',
            'percentual_custa_escrituracao' => 'number|null',
            'juros' => 'string|null',
            'juros_embutido' => 'string|null',
            'quantidade_parcelas_min' => 'integer|null',
            'quantidade_parcelas_max' => 'integer|null',
            'data_base_juros_futuro_cadastro' => 'datetime|null',
            'data_base_calculo_pv' => 'datetime|null',
            'data_base_calculo_pv_alterada' => 'string|null',
            'data_contrato_aprovacao_comercial' => 'datetime|null',
            'usuario_preencheu_data_contrato' => 'string|null',
            'porcentagem_total_comissao' => 'number|null',
            'pagamento_total_comissao' => 'number|null',
            'porcentagem_total_premiacao' => 'number|null',
            'pagamento_total_premiacao' => 'number|null',
            'adimplencia_premiada' => 'number|null',
            'ignorar_validacao_adimplencia_premiada' => 'string|null',
            'calculo_juros_price_baseado_em' => 'string|null',
            'porcentagem_total_contrato' => 'number|null',
            'pagamento_total_contrato' => 'string|null',
            'porcentagem_total_fora_contrato' => 'number|null',
            'pagamento_total_fora_contrato' => 'string|null',
            'idtipovenda' => 'integer|null',
            'hash' => 'string|null',
            'condicoes_pagamento' => 'array|null'
        ], '$.dados[0]');

        //sleep(3);
        $I->wait(3);

    }
}