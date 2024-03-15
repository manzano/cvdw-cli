<?php
namespace Tests\ApiCVDW;

use Tests\Support\ApiTester;
use Tests\ApiCVDW\Common;
use Codeception\Util\HttpCode;
use PHPUnit\Framework\Assert;

class PrecadastrosCest extends Common
{
    public function getPrecadastros(ApiTester $I)
    {
        $I->sendGet('/precadastros', ['pagina' => 1, 'registros' => 500]);
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
            'idprecadastro' => 'integer|null',
            'idsituacao' => 'integer|null',
            'situacao' => 'string|null',
            'condicao_aprovada' => 'string|null',
            'idempreendimento' => 'integer|null',
            'empreendimento' => 'string|null',
            'idcorretor' => 'integer|null',
            'corretor' => 'string|null',
            'idempresa' => 'integer|null',
            'empresa' => 'string|null',
            'pessoa' => 'string|null',
            'cep_cliente' => 'string|null',
            'idusuario_correspondente' => 'integer|null',
            'usuario_correspondente' => 'string|null',
            'idpessoa' => 'integer|null',
            'idlead' => 'string|null',
            'valor_avaliacao' => 'number|null',
            'valor_aprovado' => 'number|null',
            'valor_subsidio' => 'number|null',
            'valor_total' => 'number|null',
            'valor_fgts' => 'number|null',
            'saldo_devedor' => 'number|null',
            'prazo' => 'string|null',
            'observacoes' => 'string|null',
            'tabela' => 'string|null',
            'valor_prestacao' => 'number|null',
            'carta_credito' => 'string|null',
            'vencimento_aprovacao' => 'string|null',
            'idmotivo_reprovacao' => 'integer|null',
            'motivo_reprovacao' => 'string|null',
            'descricao_motivo_reprovacao' => 'text|null',
            'idmotivo_cancelamento' => 'integer|null',
            'motivo_cancelamento' => 'string|null',
            'descricao_motivo_cancelamento' => 'text|null',
            'sla_vencimento' => 'string|null',
            'data_cad' => 'datetime|null',
            'empresa_correspondente' => 'string|null',
            'idsituacao_anterior' => 'integer|null',
            'situacao_anterior' => 'string|null',
            'data_ultima_alteracao_situacao' => 'datetime|null'
        ], '$.dados[0]');

        //sleep(3);
        $I->wait(3);

    }
}