<?php
namespace Tests\ApiCVDW;

use Tests\Support\ApiTester;
use Tests\ApiCVDW\Common;
use Codeception\Util\HttpCode;
use PHPUnit\Framework\Assert;

class ReservasCest extends Common
{
    public function getReservas(ApiTester $I)
    {
        $I->sendGet('/reservas', ['pagina' => 1, 'registros' => 500]);
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
            'idreserva' => 'integer|null',
            'aprovada' => 'string|null',
            'data_cad' => 'datetime|null',
            'data_venda' => 'datetime|null',
            'situacao' => 'string|null',
            'idsituacao' => 'integer|null',
            'situacao_comercial' => 'string|null',
            'idempreendimento' => 'integer|null',
            'codigointerno_empreendimento' => 'string|null',
            'empreendimento' => 'string|null',
            'data_entrega_chaves_contrato_cliente' => 'datetime|null',
            'bloco' => 'string|null',
            'unidade' => 'string|null',
            'regiao' => 'string|null',
            'venda' => 'string|null',
            'idcliente' => 'integer|null',
            'documento_cliente' => 'string|null',
            'cliente' => 'string|null',
            'email' => 'string|null',
            'cidade' => 'string|null',
            'cep_cliente' => 'string|null',
            'renda' => 'number|null',
            'sexo' => 'string|null',
            'idade' => 'integer|null',
            'estado_civil' => 'string|null',
            'idcorretor' => 'integer|null',
            'corretor' => 'string|null',
            'idimobiliaria' => 'integer|null',
            'imobiliaria' => 'string|null',
            'valor_contrato' => 'number|null',
            'vencimento' => 'string|null',
            'campanha' => 'string|null',
            'cessao' => 'string|null',
            'motivo_cancelamento' => 'string|null',
            'espacos_complementares' => 'text|null',
            'idlead' => 'text|null',
            'data_ultima_alteracao_situacao' => 'datetime|null',
            'empresa_correspondente' => 'string|null',
            'valor_fgts' => 'number|null',
            'valor_financiamento' => 'number|null',
            'valor_subsidio' => 'number|null',
            'nome_usuario' => 'string|null',
            'idunidade' => 'integer|null',
            'idprecadastro' => 'integer|null',
            'idmidia' => 'integer|null',
            'midia' => 'string|null',
            'descricao_motivo_cancelamento' => 'text|null',
            'idsituacao_anterior' => 'integer|null',
            'situacao_anterior' => 'string|null',
            'idtabela' => 'integer|null',
            'nometabela' => 'string|null',
            'codigointernotabela' => 'string|null',
            'data_contrato' => 'datetime|null',
            'valor_proposta' => 'number|null',
            'vpl_tabela' => 'number|null',
            'vpl_reserva' => 'number|null',
            'usuario_aprovacao' => 'string|null',
            'data_aprovacao' => 'datetime|null',
            'juros_condicao_aprovada' => 'number|null',
            'juros_apos_entrega_condicao_aprovada' => 'number|null',
            'idtabela_condicao_aprovada' => 'integer|null',
            'data_primeira_aprovacao' => 'datetime|null',
            'aprovacao_absoluto' => 'number|null',
            'aprovacao_vpl_valor' => 'number|null',
            'idtipovenda' => 'integer|null',
            'tipovenda' => 'string|null',
            'idgrupo' => 'integer|null',
            'grupo' => 'string|null',
            'data_modificacao' => 'datetime|null',
            'campos_adicionais' => 'array|null'
        ], '$.dados[0]');

        //sleep(3);
        $I->wait(3);

    }
}